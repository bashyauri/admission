<?php

declare(strict_types=1);

namespace App\Http\Livewire\Coordinator;

use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\RegisteredCourse;
use App\Models\User;
use App\Services\AcademicSessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CoordinatorResultReview extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Academic Filters
    |--------------------------------------------------------------------------
    */

    public string $selectedSession = '';

    public string $selectedSemester = 'first';

    public ?int $selectedLevelId = null;

    public string $statusFilter = 'all';

    public string $searchQuery = '';

    public array $availableSessions = [];

    public array $availableLevels = [];


    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    public $inspectingDepartment = null;


    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */

    public array $courseSummaries = [];

    public ?int $selectedDepartmentCourseId = null;


    /*
    |--------------------------------------------------------------------------
    | Results
    |--------------------------------------------------------------------------
    */

    public array $studentsWithResults = [];

    public int $totalStudentResults = 0;

    public int $currentResultPage = 1;

    public int $lastResultPage = 1;


    /*
    |--------------------------------------------------------------------------
    | Rejection
    |--------------------------------------------------------------------------
    */

    public bool $showRejectModal = false;

    public bool $showApprovalModal = false;

    public string $rejectionReason = '';


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $coordinators = $user->coordinators()
            ->whereNotNull('department_id')
            ->with(['department', 'studentLevel'])
            ->get();

        if ($coordinators->isEmpty()) {
            abort(
                403,
                'This page is only available to department coordinators.'
            );
        }

        $coordinator = $coordinators->first();

        $this->inspectingDepartment = $coordinator->department;

        $this->availableLevels = $coordinators
            ->where('department_id', $coordinator->department_id)
            ->filter(fn ($assignment) => $assignment->studentLevel)
            ->map(fn ($assignment) => [
                'id' => $assignment->student_level_id,
                'label' => $assignment->studentLevel->level . ' Level',
            ])
            ->unique('id')
            ->sortBy('label')
            ->values()
            ->all();

        $this->selectedLevelId = $this->availableLevels[0]['id'] ?? null;


        /*
        |--------------------------------------------------------------------------
        | Academic Session
        |--------------------------------------------------------------------------
        */

        $sessionService = new AcademicSessionService();

        $defaultSession = $sessionService->getAcademicSession($user);


        /*
        |--------------------------------------------------------------------------
        | Available Sessions
        |--------------------------------------------------------------------------
        */

        $sessions = RegisteredCourse::query()
            ->whereHas('academicDetail', function ($query) use ($coordinator) {
                $query->where(
                    'department_id',
                    $coordinator->department_id
                );
            })
            ->whereNotNull('academic_session')
            ->distinct()
            ->pluck('academic_session')
            ->filter()
            ->values()
            ->toArray();


        $this->availableSessions = array_values(
            array_unique([
                ...$sessions,
                $defaultSession,
            ])
        );

        rsort($this->availableSessions);


        /*
        |--------------------------------------------------------------------------
        | Default Session
        |--------------------------------------------------------------------------
        */

        $this->selectedSession =
            $this->availableSessions[0]
            ?? $defaultSession;


        /*
        |--------------------------------------------------------------------------
        | Load Courses
        |--------------------------------------------------------------------------
        */

        $this->loadCourseSummaries();
    }


    /*
    |--------------------------------------------------------------------------
    | Session Changed
    |--------------------------------------------------------------------------
    */

    public function updatedSelectedSession(): void
    {
        $this->resetReview();
    }


    /*
    |--------------------------------------------------------------------------
    | Semester Changed
    |--------------------------------------------------------------------------
    */

    public function updatedSelectedSemester(): void
    {
        $this->resetReview();
    }

    public function updatedSelectedLevelId(): void
    {
        $this->resetReview();
    }


    /*
    |--------------------------------------------------------------------------
    | Status Changed
    |--------------------------------------------------------------------------
    */

    public function updatedStatusFilter(): void
    {
        $this->loadCourseSummaries();

        if ($this->selectedDepartmentCourseId) {
            $this->resetPage('resultsPage');
            $this->loadStudentScores();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Search Changed
    |--------------------------------------------------------------------------
    */

    public function updatedSearchQuery(): void
    {
        $this->resetPage('resultsPage');
        $this->loadStudentScores();
    }


    /*
    |--------------------------------------------------------------------------
    | Load Course Summaries
    |--------------------------------------------------------------------------
    */

    private function loadLegacyCourseSummaries(): void
    {
        $coordinator = Auth::user()->coordinator;

        if (
            !$coordinator ||
            !$coordinator->department_id
        ) {
            $this->courseSummaries = [];

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Get courses belonging to coordinator's department
        |--------------------------------------------------------------------------
        |
        | department_courses.student_course_id
        |             ↓
        | student_courses.id
        |
        */

        $departmentCourses = DB::table('department_courses')
            ->join(
                'student_courses',
                'student_courses.id',
                '=',
                'department_courses.student_course_id'
            )
            ->where(
                'department_courses.department_id',
                $coordinator->department_id
            )
            ->select([
                'department_courses.id as department_course_id',
                'department_courses.student_course_id',
                'student_courses.code as course_code',
                'student_courses.title as course_title',
                'student_courses.units',
            ])
            ->orderBy('student_courses.code')
            ->get();


        if ($departmentCourses->isEmpty()) {
            $this->courseSummaries = [];

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Get results
        |--------------------------------------------------------------------------
        */

        $results = Result::query()
            ->where(
                'academic_session',
                $this->selectedSession
            )
            ->where(
                'semester',
                $this->selectedSemester
            )
            ->whereIn(
                'department_course_id',
                $departmentCourses
                    ->pluck('department_course_id')
                    ->toArray()
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Build summaries
        |--------------------------------------------------------------------------
        */

        $summaries = [];


        foreach ($departmentCourses as $course) {

            $courseResults = $results->where(
                'department_course_id',
                $course->department_course_id
            );


            /*
            |--------------------------------------------------------------------------
            | Determine workflow counts
            |--------------------------------------------------------------------------
            */

            $submitted = $courseResults
                ->filter(function ($result) {
                    return
                        $result->status === 'submitted'
                        &&
                        empty($result->coordinator_approved_at);
                })
                ->count();


            $coordinatorApproved = $courseResults
                ->filter(function ($result) {
                    return
                        !empty($result->coordinator_approved_at)
                        &&
                        empty($result->exam_officer_approved_at)
                        &&
                        $result->status !== 'released';
                })
                ->count();


            $examOfficerApproved = $courseResults
                ->filter(function ($result) {
                    return
                        !empty($result->exam_officer_approved_at)
                        &&
                        $result->status !== 'released';
                })
                ->count();


            $released = $courseResults
                ->where('status', 'released')
                ->count();


            $pending = $courseResults
                ->where('status', 'pending')
                ->count();


            /*
            |--------------------------------------------------------------------------
            | Apply Status Filter
            |--------------------------------------------------------------------------
            */

            $visibleCount = match ($this->statusFilter) {

                'submitted' =>
                    $submitted,

                'coordinator_approved' =>
                    $coordinatorApproved,

                'exam_officer_approved' =>
                    $examOfficerApproved,

                'released' =>
                    $released,

                'pending' =>
                    $pending,

                default =>
                    $courseResults
                        ->pluck('user_id')
                        ->filter()
                        ->unique()
                        ->count(),
            };


            /*
            |--------------------------------------------------------------------------
            | Don't show courses that don't match selected status
            |--------------------------------------------------------------------------
            */

            if (
                $this->statusFilter !== 'all'
                &&
                $visibleCount === 0
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Unique students
            |--------------------------------------------------------------------------
            */

            $studentCount = $courseResults
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->count();


            $summaries[] = [

                'department_course_id' =>
                    (int) $course->department_course_id,

                'student_course_id' =>
                    (int) $course->student_course_id,

                'course_code' =>
                    $course->course_code,

                'course_title' =>
                    $course->course_title,

                'units' =>
                    $course->units,

                'student_count' =>
                    $studentCount,

                'total_results' =>
                    $courseResults->count(),

                'submitted' =>
                    $submitted,

                'coordinator_approved' =>
                    $coordinatorApproved,

                'exam_officer_approved' =>
                    $examOfficerApproved,

                'released' =>
                    $released,

                'pending' =>
                    $pending,
            ];
        }


        $this->courseSummaries = $summaries;


        /*
        |--------------------------------------------------------------------------
        | Select first course awaiting review
        |--------------------------------------------------------------------------
        */

        if (!$this->selectedDepartmentCourseId) {

            $course = collect($this->courseSummaries)
                ->first();


            if ($course) {

                $this->selectedDepartmentCourseId =
                    $course['department_course_id'];

                $this->loadStudentScores();
            }
        }
    }


    public function loadCourseSummaries(): void
    {
        $assignmentIds = $this->assignedCoordinatorIds();

        if ($assignmentIds === []) {
            $this->courseSummaries = [];

            return;
        }

        $this->courseSummaries = DB::table('department_courses')
            ->join('student_courses', 'student_courses.id', '=', 'department_courses.student_course_id')
            ->join('results', function ($join) use ($assignmentIds) {
                $join->on('results.department_course_id', '=', 'department_courses.id')
                    ->where('results.academic_session', $this->selectedSession)
                    ->where('results.semester', $this->selectedSemester)
                    ->whereIn('results.coordinator_id', $assignmentIds);
            })
            ->where('department_courses.department_id', $this->inspectingDepartment?->id)
            ->select([
                'department_courses.id as department_course_id',
                'department_courses.student_course_id',
                'student_courses.code as course_code',
                'student_courses.title as course_title',
                'department_courses.units',
                DB::raw('COUNT(DISTINCT results.user_id) as student_count'),
                DB::raw('COUNT(results.id) as total_results'),
                DB::raw("SUM(CASE WHEN results.status = 'submitted' AND results.coordinator_approved_at IS NULL THEN 1 ELSE 0 END) as submitted"),
                DB::raw("SUM(CASE WHEN results.status = 'exam_officer_approved' THEN 1 ELSE 0 END) as coordinator_approved"),
                DB::raw("SUM(CASE WHEN results.status = 'released' THEN 1 ELSE 0 END) as released"),
                DB::raw("SUM(CASE WHEN results.status = 'pending' THEN 1 ELSE 0 END) as pending"),
            ])
            ->groupBy('department_courses.id', 'department_courses.student_course_id', 'student_courses.code', 'student_courses.title', 'department_courses.units')
            ->orderBy('student_courses.code')
            ->get()
            ->map(function ($course): array {
                return [
                    'department_course_id' => (int) $course->department_course_id,
                    'student_course_id' => (int) $course->student_course_id,
                    'course_code' => $course->course_code,
                    'course_title' => $course->course_title,
                    'units' => $course->units,
                    'student_count' => (int) $course->student_count,
                    'total_results' => (int) $course->total_results,
                    'submitted' => (int) $course->submitted,
                    'coordinator_approved' => (int) $course->coordinator_approved,
                    'exam_officer_approved' => 0,
                    'released' => (int) $course->released,
                    'pending' => (int) $course->pending,
                ];
            })
            ->filter(function (array $course): bool {
                return match ($this->statusFilter) {
                    'submitted' => $course['submitted'] > 0,
                    'coordinator_approved' => $course['coordinator_approved'] > 0,
                    'released' => $course['released'] > 0,
                    'pending' => $course['pending'] > 0,
                    default => true,
                };
            })
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Select Course
    |--------------------------------------------------------------------------
    */

    public function selectCourse(int $departmentCourseId): void
    {
        if (!collect($this->courseSummaries)->contains('department_course_id', $departmentCourseId)) {

            session()->flash(
                'error',
                'You are not authorized to review this course.'
            );

            return;
        }


        $this->selectedDepartmentCourseId =
            $departmentCourseId;

        $this->resetPage('resultsPage');
        $this->loadStudentScores();
    }


    /*
    |--------------------------------------------------------------------------
    | Load Student Results
    |--------------------------------------------------------------------------
    */

    private function loadLegacyStudentScores(): void
    {
        if (!$this->selectedDepartmentCourseId) {
            $this->studentsWithResults = [];

            return;
        }


        $coordinator = Auth::user()->coordinator;


        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        */

        $allowed = DB::table('department_courses')
            ->where(
                'id',
                $this->selectedDepartmentCourseId
            )
            ->where(
                'department_id',
                $coordinator->department_id
            )
            ->exists();


        if (!$allowed) {

            $this->studentsWithResults = [];

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Query results
        |--------------------------------------------------------------------------
        */

        $query = Result::query()
            ->with([
                'user',
                'academicDetail',
            ])
            ->where(
                'department_course_id',
                $this->selectedDepartmentCourseId
            )
            ->where(
                'academic_session',
                $this->selectedSession
            )
            ->where(
                'semester',
                $this->selectedSemester
            );


        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        */

        match ($this->statusFilter) {

            'submitted' =>
                $query
                    ->where('status', 'submitted')
                    ->whereNull('coordinator_approved_at'),

            'coordinator_approved' =>
                $query
                    ->whereNotNull('coordinator_approved_at')
                    ->whereNull('exam_officer_approved_at')
                    ->where('status', '!=', 'released'),

            'exam_officer_approved' =>
                $query
                    ->whereNotNull('exam_officer_approved_at')
                    ->where('status', '!=', 'released'),

            'released' =>
                $query->where('status', 'released'),

            'pending' =>
                $query->where('status', 'pending'),

            default =>
                null,
        };


        /*
        |--------------------------------------------------------------------------
        | Get
        |--------------------------------------------------------------------------
        */

        $results = $query
            ->orderByDesc('created_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate students
        |--------------------------------------------------------------------------
        */

        $results = $results
            ->unique('user_id')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Course information
        |--------------------------------------------------------------------------
        */

        $course = DB::table('department_courses')
            ->join(
                'student_courses',
                'student_courses.id',
                '=',
                'department_courses.student_course_id'
            )
            ->where(
                'department_courses.id',
                $this->selectedDepartmentCourseId
            )
            ->select([
                'student_courses.code as course_code',
                'student_courses.title as course_title',
            ])
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Build rows
        |--------------------------------------------------------------------------
        */

        $rows = [];


        foreach ($results as $result) {

            $user = $result->user;

            $academicDetail = $result->academicDetail;


            $name = trim(
                ($user->surname ?? '')
                . ' '
                . ($user->firstname ?? '')
                . ' '
                . ($user->m_name ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | Determine display status
            |--------------------------------------------------------------------------
            */

            if ($result->status === 'released') {

                $displayStatus = 'released';

            } elseif (!empty($result->exam_officer_approved_at)) {

                $displayStatus = 'exam_officer_approved';

            } elseif (!empty($result->coordinator_approved_at)) {

                $displayStatus = 'coordinator_approved';

            } elseif ($result->status === 'submitted') {

                $displayStatus = 'submitted';

            } else {

                $displayStatus = 'pending';
            }


            $rows[] = [

                'result_id' =>
                    $result->id,

                'user_id' =>
                    $result->user_id,

                'course_code' =>
                    $course?->course_code
                    ?? 'N/A',

                'course_title' =>
                    $course?->course_title
                    ?? 'N/A',

                'matric_no' =>
                    $academicDetail?->matric_no
                    ?? 'N/A',

                'name' =>
                    $name !== ''
                    ? $name
                    : 'Unknown Student',

                'ca_score' =>
                    $result->ca_score,

                'exam_score' =>
                    $result->exam_score,

                'total_score' =>
                    $result->total_score,

                'grade' =>
                    $result->grade
                    ?? '-',

                'grade_point' =>
                    $result->grade_point
                    ?? '-',

                'status' =>
                    $displayStatus,

                'remarks' =>
                    $result->remarks,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (trim($this->searchQuery) !== '') {

            $search =
                mb_strtolower(
                    trim($this->searchQuery)
                );


            $rows = array_values(
                array_filter(
                    $rows,
                    function ($student) use ($search) {

                        return
                            str_contains(
                                mb_strtolower(
                                    $student['name']
                                ),
                                $search
                            )

                            ||

                            str_contains(
                                mb_strtolower(
                                    $student['matric_no']
                                ),
                                $search
                            );
                    }
                )
            );
        }


        $this->studentsWithResults = $rows;
    }


    public function loadStudentScores(): void
    {
        if (!$this->selectedDepartmentCourseId) {
            $this->studentsWithResults = [];
            $this->totalStudentResults = 0;

            return;
        }

        $course = $this->selectedCourse;

        if (!$course) {
            $this->studentsWithResults = [];
            $this->totalStudentResults = 0;

            return;
        }

        $query = Result::query()
            ->with([
                'user',
                'academicDetail',
                'registeredCourse.departmentCourse.studentCourse',
            ])
            ->where('department_course_id', $this->selectedDepartmentCourseId)
            ->where('academic_session', $this->selectedSession)
            ->where('semester', $this->selectedSemester)
            ->whereIn('coordinator_id', $this->assignedCoordinatorIds());

        match ($this->statusFilter) {
            'submitted' => $query->where('status', 'submitted')->whereNull('coordinator_approved_at'),
            'coordinator_approved' => $query->where('status', 'exam_officer_approved'),
            'released' => $query->where('status', 'released'),
            'pending' => $query->where('status', 'pending'),
            default => null,
        };

        if (trim($this->searchQuery) !== '') {
            $search = '%' . trim($this->searchQuery) . '%';

            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('surname', 'like', $search)
                        ->orWhere('firstname', 'like', $search)
                        ->orWhere('m_name', 'like', $search);
                })->orWhereHas('academicDetail', function ($query) use ($search) {
                    $query->where('matric_no', 'like', $search);
                });
            });
        }

        $results = $query->latest()->paginate(25, ['*'], 'resultsPage');

        $this->totalStudentResults = $results->total();
        $this->currentResultPage = $results->currentPage();
        $this->lastResultPage = $results->lastPage();
        $this->studentsWithResults = $results->getCollection()
            ->map(function (Result $result) use ($course): array {
                $user = $result->user;
                $academicDetail = $result->academicDetail;
                $registeredCourse = $result->registeredCourse;
                $originalCourse = $registeredCourse?->departmentCourse?->studentCourse;

                return [
                    'result_id' => $result->id,
                    'user_id' => $result->user_id,
                    'course_code' => filled($result->course_code_snapshot)
                        ? $result->course_code_snapshot
                        : ($registeredCourse?->course_code_snapshot
                            ?? $originalCourse?->code
                            ?? $course['course_code']
                            ?? 'N/A'),
                    'course_title' => filled($result->course_title_snapshot)
                        ? $result->course_title_snapshot
                        : ($registeredCourse?->course_title_snapshot
                            ?? $originalCourse?->title
                            ?? $course['course_title']
                            ?? 'N/A'),
                    'matric_no' => $academicDetail?->matric_no ?? 'N/A',
                    'name' => trim(($user->surname ?? '') . ' ' . ($user->firstname ?? '') . ' ' . ($user->m_name ?? '')) ?: 'Unknown Student',
                    'ca_score' => $result->ca_score,
                    'exam_score' => $result->exam_score,
                    'total_score' => $result->total_score,
                    'grade' => $result->grade ?? '-',
                    'grade_point' => $result->grade_point ?? '-',
                    'status' => $result->status === 'exam_officer_approved' ? 'coordinator_approved' : $result->status,
                    'remarks' => $result->remarks,
                ];
            })
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Approve Results
    |--------------------------------------------------------------------------
    */

    public function approveStudentResults(): void
    {
        $departmentCourse = collect($this->courseSummaries)
            ->firstWhere('department_course_id', $this->selectedDepartmentCourseId);

        if (!$departmentCourse) {

            session()->flash(
                'error',
                'You are not authorized to approve these results.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted results only
        |--------------------------------------------------------------------------
        */

        $resultsQuery = Result::query()
            ->where(
                'department_course_id',
                $this->selectedDepartmentCourseId
            )
            ->where(
                'academic_session',
                $this->selectedSession
            )
            ->where(
                'semester',
                $this->selectedSemester
            )
            ->where(
                'status',
                'submitted'
            )
            ->whereNull(
                'coordinator_approved_at'
            )
            ->whereIn('coordinator_id', $this->assignedCoordinatorIds());


        $count = $resultsQuery->count();


        if ($count === 0) {

            session()->flash(
                'info',
                'There are no submitted results awaiting coordinator approval.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Approve
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $count,
            $departmentCourse,
            $resultsQuery
        ) {

            $resultsQuery->update([

                'coordinator_approved_by' =>
                    Auth::id(),

                'coordinator_approved_at' =>
                    now(),

                'status' =>
                    'exam_officer_approved',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            if (class_exists(ResultApproval::class)) {

                ResultApproval::create([

                    'department_id' =>
                        $this->inspectingDepartment?->id,

                    'academic_session' =>
                        $this->selectedSession,

                    'semester' =>
                        $this->selectedSemester,

                    'approval_level' =>
                        'coordinator',

                    'approved_by' =>
                        Auth::id(),

                    'approved_at' =>
                        now(),

                    'status' =>
                        'approved',

                    'comments' =>
                        "Department Coordinator approved {$count} result(s).",
                ]);
            }
        });


        session()->flash(
            'success',
            "{$count} result(s) approved and forwarded to the Exam Officer."
        );

        $this->showApprovalModal = false;


        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        $this->loadCourseSummaries();

        $this->loadStudentScores();
    }


    /*
    |--------------------------------------------------------------------------
    | Reject / Return
    |--------------------------------------------------------------------------
    */

    public function openApprovalModal(): void
    {
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
    }

    public function openRejectModal(): void
    {
        $this->rejectionReason = '';

        $this->showRejectModal = true;
    }


    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;

        $this->rejectionReason = '';
    }


    public function rejectStudentResults(): void
    {
        $this->validate([
            'rejectionReason' =>
                'required|min:5|max:500',
        ]);


        if (!collect($this->courseSummaries)->contains('department_course_id', $this->selectedDepartmentCourseId)) {

            session()->flash(
                'error',
                'You are not authorized to return these results.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Submitted results
        |--------------------------------------------------------------------------
        */

        $query = Result::query()
            ->where(
                'department_course_id',
                $this->selectedDepartmentCourseId
            )
            ->where(
                'academic_session',
                $this->selectedSession
            )
            ->where(
                'semester',
                $this->selectedSemester
            )
            ->where(
                'status',
                'submitted'
            )
            ->whereNull(
                'coordinator_approved_at'
            )
            ->whereIn('coordinator_id', $this->assignedCoordinatorIds());


        $count = $query->count();


        if ($count === 0) {

            session()->flash(
                'info',
                'There are no submitted results to return.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Return to lecturer
        |--------------------------------------------------------------------------
        */

        $query->update([

            'status' =>
                'pending',

            'remarks' =>
                $this->rejectionReason,
        ]);

            ResultApproval::create([
                'department_id' => $this->inspectingDepartment?->id,
                'academic_session' => $this->selectedSession,
                'semester' => $this->selectedSemester,
                'approval_level' => 'coordinator',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'status' => 'rejected',
                'comments' => $this->rejectionReason,
            ]);


        $this->showRejectModal = false;

        $this->rejectionReason = '';


        session()->flash(
            'warning',
            "{$count} result(s) returned to the lecturer."
        );


        $this->loadCourseSummaries();

        $this->loadStudentScores();
    }


    public function gotoResultPage(int $page): void
    {
        $this->setPage(
            max(1, min($page, $this->lastResultPage)),
            'resultsPage'
        );

        $this->loadStudentScores();
    }

    private function resetReview(): void
    {
        $this->selectedDepartmentCourseId = null;
        $this->studentsWithResults = [];
        $this->totalStudentResults = 0;
        $this->currentResultPage = 1;
        $this->lastResultPage = 1;
        $this->resetPage('resultsPage');
        $this->loadCourseSummaries();
    }

    private function assignedCoordinatorIds(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$this->inspectingDepartment) {
            return [];
        }

        return $user->coordinators()
            ->where('department_id', $this->inspectingDepartment->id)
            ->when(
                $this->selectedLevelId,
                fn ($query) => $query->where('student_level_id', $this->selectedLevelId)
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Summary Counts
    |--------------------------------------------------------------------------
    */

    public function getSubmittedCountProperty(): int
    {
        return collect($this->courseSummaries)
            ->sum('submitted');
    }


    public function getCoordinatorApprovedCountProperty(): int
    {
        return collect($this->courseSummaries)
            ->sum('coordinator_approved');
    }


    public function getExamOfficerApprovedCountProperty(): int
    {
        return collect($this->courseSummaries)
            ->sum('exam_officer_approved');
    }


    public function getReleasedCountProperty(): int
    {
        return collect($this->courseSummaries)
            ->sum('released');
    }


    public function getPendingCountProperty(): int
    {
        return collect($this->courseSummaries)
            ->sum('pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Course
    |--------------------------------------------------------------------------
    */

    public function getSelectedCourseProperty(): ?array
    {
        if (!$this->selectedDepartmentCourseId) {
            return null;
        }


        return collect($this->courseSummaries)
            ->firstWhere(
                'department_course_id',
                $this->selectedDepartmentCourseId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.coordinator.coordinator-result-review'
        )->layout('layouts.app');
    }
}
