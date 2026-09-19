<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Services\CarryOverRegistrationService;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ExamOfficerResultReview extends Component
{
    use LivewireAlert;

    // ── Academic Filter Context ───────────────────────────────────────────────

    public string $selectedSession = '';

    public string $selectedSemester = 'first';

    public array $availableSessions = [];

    public $selectedDepartmentId = 'all';

    public array $availableDepartments = [];
    public int $pendingCoordinatorCount = 0;

    // ── Inspection State ──────────────────────────────────────────────────────

    public $selectedCourseId = null;

    public ?DepartmentCourse $inspectingCourse = null;

    public array $studentsWithResults = [];

    public string $searchQuery = '';

    public string $statusFilter = 'all';

    // ── Rejection Modal ───────────────────────────────────────────────────────

    public bool $showRejectModal = false;

    public string $rejectionReason = '';


    // =========================================================================
    // MOUNT
    // =========================================================================

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Exam Officer Result Review.');
        }

        /*
         * Departments remain available globally.
         */
        $this->availableDepartments = Department::query()
            ->orderBy('name')
            ->get()
            ->toArray();


        /*
         * Academic sessions come from ACTUAL STUDENT REGISTRATIONS.
         *
         * registered_courses.academic_session is the source of truth.
         */
        $this->availableSessions = DB::table('registered_courses')
            ->whereNotNull('academic_session')
            ->where('academic_session', '!=', '')
            ->distinct()
            ->orderByDesc('academic_session')
            ->pluck('academic_session')
            ->map(fn ($session) => (string) $session)
            ->values()
            ->toArray();


        /*
         * Default to the most recent academic session that actually
         * has student registrations.
         */
        $this->selectedSession = $this->availableSessions[0] ?? '';

        $this->loadStudentScores();
    }


    // =========================================================================
    // FILTER EVENTS
    // =========================================================================

    public function updatedSelectedSession(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
        $this->studentsWithResults = [];
    }

    public function updatedSelectedSemester(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
        $this->studentsWithResults = [];
    }

    public function updatedSelectedDepartmentId(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
        $this->studentsWithResults = [];
    }

    public function updatedSearchQuery(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
    }

    public function updatedStatusFilter(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
    }


    // =========================================================================
    // COURSE INSPECTION
    // =========================================================================

    public function inspectCourse($departmentCourseId): void
    {
        /*
         * Only allow inspection of a course that actually has
         * Coordinator-approved or already released results for
         * the selected session/semester.
         */
        $exists = Result::query()
            ->join(
                'registered_courses as rc',
                'rc.id',
                '=',
                'results.registered_course_id'
            )
            ->where('results.department_course_id', $departmentCourseId)
            ->where('results.academic_session', $this->selectedSession)
            ->where('rc.academic_session', $this->selectedSession)
            ->where('results.semester', $this->selectedSemester)
            ->whereIn(
                'results.status',
                [
                    'exam_officer_approved',
                    'released',
                ]
            )
            ->exists();

        if (! $exists) {
            $this->alert(
                'info',
                'No Coordinator-approved results are available for this course in the selected session and semester.'
            );

            return;
        }

        $this->selectedCourseId = $departmentCourseId;

        $this->inspectingCourse = DepartmentCourse::with([
            'studentCourse',
            'department',
        ])->find($departmentCourseId);

        $this->loadStudentScores();
    }


    public function closeInspection(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
        $this->studentsWithResults = [];
    }


    // =========================================================================
    // LOAD STUDENT SCORES
    // =========================================================================

    public function loadStudentScores(): void
    {
        if (! $this->selectedCourseId || ! $this->selectedSession) {
            $this->studentsWithResults = [];

            return;
        }

        /*
         * Get ONLY students who actually registered for:
         *
         * selected course
         * selected academic session
         */
        $registered = RegisteredCourse::with([
            'academicDetail.user',
        ])
            ->where('department_course_id', $this->selectedCourseId)
            ->where('academic_session', $this->selectedSession)
            ->get();


        /*
         * Get results for the selected session + semester.
         *
         * Join registered_courses so the result must belong to
         * an actual registration in the selected session.
         */
        $results = Result::query()
            ->join(
                'registered_courses as rc',
                'rc.id',
                '=',
                'results.registered_course_id'
            )
            ->where('results.department_course_id', $this->selectedCourseId)
            ->where('results.academic_session', $this->selectedSession)
            ->where('rc.academic_session', $this->selectedSession)
            ->where('results.semester', $this->selectedSemester)
            ->whereIn(
                'results.status',
                [
                    'exam_officer_approved',
                    'released',
                ]
            )
            ->select('results.*')
            ->get()
            ->keyBy('user_id');


        $rows = [];

        foreach ($registered as $reg) {

            $userId = $reg->academicDetail->user_id ?? null;

            if (! $userId) {
                continue;
            }

            $result = $results[$userId] ?? null;

            /*
             * IMPORTANT:
             *
             * Do not display a registered student in the EO
             * score inspection table unless there is a
             * Coordinator-approved/released result.
             */
            if (! $result) {
                continue;
            }

            $rows[] = [
                'user_id' => $userId,

                'matric_no' =>
                    $reg->academicDetail->matric_no ?? 'N/A',

                'name' => trim(
                    ($reg->academicDetail->user->surname ?? '')
                    . ' '
                    . ($reg->academicDetail->user->firstname ?? '')
                    . ' '
                    . ($reg->academicDetail->user->m_name ?? '')
                ),

                'ca_score' => $result->ca_score,

                'exam_score' => $result->exam_score,

                'total_score' => $result->total_score,

                'grade' => $result->grade ?? '-',

                'grade_point' => $result->grade_point ?? '-',

                'status' => $result->status,

                'remarks' => $result->remarks,
            ];
        }

        /*
         * Sort students naturally by Matriculation Number in ascending order.
         * If matric_no is missing, sort by student name.
         */
        usort($rows, function ($a, $b) {
            $matricA = (string) ($a['matric_no'] ?? '');
            $matricB = (string) ($b['matric_no'] ?? '');

            if ($matricA !== 'N/A' && $matricB !== 'N/A') {
                $cmp = strnatcasecmp($matricA, $matricB);
                if ($cmp !== 0) {
                    return $cmp;
                }
            }

            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        $this->studentsWithResults = $rows;
    }


    // =========================================================================
    // RELEASE COURSE RESULTS
    // =========================================================================

    public function releaseCourseResults($courseId = null): void
    {
        $targetCourseId = $courseId ?? $this->selectedCourseId;

        if (! $targetCourseId) {
            return;
        }

        $session = $this->selectedSession;
        $semester = $this->selectedSemester;


        /*
         * ONLY release results where:
         *
         * 1. Result belongs to selected session
         * 2. Registration belongs to selected session
         * 3. Semester matches
         * 4. Coordinator has approved the result
         */
        $results = Result::with([
            'user.academicDetail',
            'registeredCourse',
            'departmentCourse',
        ])
            ->join(
                'registered_courses as rc',
                'rc.id',
                '=',
                'results.registered_course_id'
            )
            ->where(
                'results.department_course_id',
                $targetCourseId
            )
            ->where(
                'results.academic_session',
                $session
            )
            ->where(
                'rc.academic_session',
                $session
            )
            ->where(
                'results.semester',
                $semester
            )
            ->where(
                'results.status',
                'exam_officer_approved'
            )
            ->select('results.*')
            ->get();


        if ($results->isEmpty()) {

            $this->alert(
                'info',
                'No Coordinator-approved results ready for release in this course.'
            );

            return;
        }


        $gradeService = new GradeCalculationService();

        $carryOverService = new CarryOverRegistrationService();


        // ─────────────────────────────────────────────────────────────────────
        // Update results to released
        // ─────────────────────────────────────────────────────────────────────

        Result::query()
            ->join(
                'registered_courses as rc',
                'rc.id',
                '=',
                'results.registered_course_id'
            )
            ->where(
                'results.department_course_id',
                $targetCourseId
            )
            ->where(
                'results.academic_session',
                $session
            )
            ->where(
                'rc.academic_session',
                $session
            )
            ->where(
                'results.semester',
                $semester
            )
            ->where(
                'results.status',
                'exam_officer_approved'
            )
            ->update([
                'results.status' => 'released',
                'results.exam_officer_approved_by' => Auth::id(),
                'results.exam_officer_approved_at' => now(),
            ]);


        // ─────────────────────────────────────────────────────────────────────
        // Process GPA / Carry Over
        // ─────────────────────────────────────────────────────────────────────

        $deptCourse = DepartmentCourse::find($targetCourseId);

        $uniqueUsers = $results
            ->pluck('user')
            ->filter()
            ->unique('id');


        foreach ($results as $result) {

            $freshResult = Result::find($result->id);

            if (! $freshResult) {
                continue;
            }

            if ((float) $freshResult->total_score >= 40.0) {

                $carryOverService->processResultClearance(
                    $freshResult
                );

            } else {

                $carryOverService->recordFailedCourse(
                    $freshResult
                );
            }
        }


        foreach ($uniqueUsers as $studentUser) {

            $gradeService->processAndSaveGpaRecord(
                $studentUser,
                $session,
                $semester
            );
        }


        // ─────────────────────────────────────────────────────────────────────
        // Audit
        // ─────────────────────────────────────────────────────────────────────

        ResultApproval::create([
            'department_id' => $deptCourse?->department_id,
            'academic_session' => $session,
            'semester' => $semester,
            'approval_level' => 'exam_officer',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'status' => 'released',
            'comments' =>
                "Exam Officer released {$results->count()} result(s) "
                . "for course ID {$targetCourseId}. "
                . "GPAs and Carry-Overs updated.",
        ]);


        $this->alert(
            'success',
            "{$results->count()} result(s) released successfully! "
            . "Student GPAs and carry-overs have been recalculated."
        );


        if ($this->selectedCourseId) {
            $this->loadStudentScores();
        }
    }


    // =========================================================================
    // REJECTION
    // =========================================================================

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


    public function rejectCourseResults(): void
    {
        if (! $this->selectedCourseId) {
            return;
        }

        $this->validate([
            'rejectionReason' => 'required|min:5|max:500',
        ], [
            'rejectionReason.required' =>
                'Please provide a reason for returning results to the Coordinator.',

            'rejectionReason.min' =>
                'The reason must be at least 5 characters long.',
        ]);


        $session = $this->selectedSession;

        $semester = $this->selectedSemester;


        /*
         * Only return Coordinator-approved results belonging to
         * registered students in the selected session.
         */
        $updatedCount = Result::query()
            ->join(
                'registered_courses as rc',
                'rc.id',
                '=',
                'results.registered_course_id'
            )
            ->where(
                'results.department_course_id',
                $this->selectedCourseId
            )
            ->where(
                'results.academic_session',
                $session
            )
            ->where(
                'rc.academic_session',
                $session
            )
            ->where(
                'results.semester',
                $semester
            )
            ->where(
                'results.status',
                'exam_officer_approved'
            )
            ->update([
                'results.status' => 'submitted',

                'results.remarks' =>
                    '[Exam Officer]: ' . $this->rejectionReason,
            ]);


        $deptCourse = DepartmentCourse::find(
            $this->selectedCourseId
        );


        ResultApproval::create([
            'department_id' => $deptCourse?->department_id,
            'academic_session' => $session,
            'semester' => $semester,
            'approval_level' => 'exam_officer',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'status' => 'rejected',
            'comments' => $this->rejectionReason,
        ]);


        $this->showRejectModal = false;

        $this->rejectionReason = '';


        $this->alert(
            'warning',
            "{$updatedCount} result(s) returned to Coordinator for revision."
        );


        $this->loadStudentScores();
    }


    // =========================================================================
    // RENDER
    // =========================================================================

    public function render()
    {
        $session = $this->selectedSession;

        $semester = $this->selectedSemester;

        $deptId = $this->selectedDepartmentId;


        /*
         * ONLY show courses that have results approved by the Coordinator (exam_officer_approved)
         * or already released by the Exam Officer (released) for this session and semester.
         *
         * Courses still in draft (pending) or under coordinator review (submitted)
         * belong to the Lecturer and Coordinator workflows and MUST NOT appear here.
         */
        $query = DepartmentCourse::with([
            'studentCourse',
            'department',
        ])
            ->whereHas(
                'results',
                function ($rq) use ($session, $semester) {
                    $rq->whereNull('deleted_at')
                        ->where('academic_session', $session)
                        ->where('semester', $semester)
                        ->whereIn(
                            'status',
                            [
                                'exam_officer_approved',
                                'released',
                            ]
                        );
                }
            );

        // Department filter
        if ($deptId !== 'all' && ! empty($deptId)) {
            $query->where('department_id', $deptId);
        }

        // Course search
        if ($this->searchQuery) {
            $search = trim($this->searchQuery);
            $query->whereHas(
                'studentCourse',
                function ($sq) use ($search) {
                    $sq->where('code', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%');
                }
            );
        }

        $departmentCourses = $query
            ->get()
            ->map(
                function ($dc) use ($session, $semester) {
                    // Actual student registrations
                    $totalRegistered = RegisteredCourse::query()
                        ->where('department_course_id', $dc->id)
                        ->where('academic_session', $session)
                        ->count();

                    // Result status counts for Coordinator-approved and Released
                    $resultCounts = Result::query()
                        ->join(
                            'registered_courses as rc',
                            'rc.id',
                            '=',
                            'results.registered_course_id'
                        )
                        ->where('results.department_course_id', $dc->id)
                        ->where('results.academic_session', $session)
                        ->where('rc.academic_session', $session)
                        ->where('results.semester', $semester)
                        ->whereIn('results.status', ['exam_officer_approved', 'released'])
                        ->selectRaw('results.status, COUNT(*) as count')
                        ->groupBy('results.status')
                        ->pluck('count', 'status')
                        ->toArray();

                    // Course allocation
                    $allocation = CourseAllocation::with('lecturer')
                        ->where('department_course_id', $dc->id)
                        ->where('academic_session', $session)
                        ->where('semester', $semester)
                        ->first();

                    $dc->total_registered = $totalRegistered;
                    $dc->coordinator_approved_count = $resultCounts['exam_officer_approved'] ?? 0;
                    $dc->released_count = $resultCounts['released'] ?? 0;
                    $dc->allocated_lecturer = $allocation
                        ? trim(($allocation->lecturer->surname ?? '') . ' ' . ($allocation->lecturer->firstname ?? ''))
                        : 'Unallocated';

                    return $dc;
                }
            );

        /*
         * Filter by selected status tab
         */
        if ($this->statusFilter === 'coordinator_approved') {
            $departmentCourses = $departmentCourses->filter(
                fn ($dc) => $dc->coordinator_approved_count > 0
            );
        } elseif ($this->statusFilter === 'released') {
            $departmentCourses = $departmentCourses->filter(
                fn ($dc) => $dc->released_count > 0
            );
        }

        /*
         * Order courses:
         * 1. Courses requiring action (Ready for Release) first
         * 2. Then ordered alphabetically by Department name
         * 3. Then ordered naturally by Course code
         */
        $departmentCourses = $departmentCourses->sort(function ($a, $b) {
            if ($a->coordinator_approved_count > 0 && $b->coordinator_approved_count === 0) {
                return -1;
            }
            if ($a->coordinator_approved_count === 0 && $b->coordinator_approved_count > 0) {
                return 1;
            }

            $deptCmp = strcasecmp((string) ($a->department->name ?? ''), (string) ($b->department->name ?? ''));
            if ($deptCmp !== 0) {
                return $deptCmp;
            }

            return strnatcasecmp((string) ($a->studentCourse->code ?? ''), (string) ($b->studentCourse->code ?? ''));
        })->values();

        // Metrics for Coordinator-approved queue
        $totalCourses = $departmentCourses->count();
        $awaitingRelease = $departmentCourses->filter(fn ($dc) => $dc->coordinator_approved_count > 0)->count();
        $releasedCount = $departmentCourses->filter(fn ($dc) => $dc->released_count > 0)->count();

        return view(
            'livewire.exam-officer.exam-officer-result-review',
            [
                'departmentCourses' => $departmentCourses,
                'totalCourses' => $totalCourses,
                'awaitingRelease' => $awaitingRelease,
                'releasedCount' => $releasedCount,
            ]
        )->layout('layouts.app');
    }
}

