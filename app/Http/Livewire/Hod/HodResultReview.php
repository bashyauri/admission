<?php

declare(strict_types=1);

namespace App\Http\Livewire\Hod;

use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\Setting;
use App\Services\AcademicSessionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class HodResultReview extends Component
{
    use LivewireAlert;

    // Academic Filter Context
    public string $selectedSession = '';
    public string $selectedSemester = 'first';
    public array $availableSessions = [];
    public $selectedDepartmentId = null;
    public array $availableDepartments = [];
    public bool $canSelectDepartment = false;

    // Course Selection & Inspection
    public $selectedCourseId = null;
    public ?DepartmentCourse $inspectingCourse = null;
    public $studentsWithResults = [];
    public string $searchQuery = '';
    public string $statusFilter = 'all';

    // Rejection Modal State
    public bool $showRejectModal = false;
    public string $rejectionReason = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->canActAsHod()) {
            abort(403, 'Unauthorized access to HOD Result Review.');
        }
        
        // Note: HOD result review is deprecated
        // Results now flow: Lecturer → Coordinator → Exam Officer

        // Determine if user can select department (Admin/CIT)
        if ($user->canActAsAdmin() || $user->canActAsCit()) {
            $this->canSelectDepartment = true;
            $this->availableDepartments = Department::orderBy('name')->get()->toArray();
            $this->selectedDepartmentId = $this->availableDepartments[0]['id'] ?? null;
        } elseif ($user->hodDetails) {
            $this->selectedDepartmentId = $user->hodDetails->department_id;
        } else {
            $capDepts = $user->capabilityDepartments('hod');
            $this->selectedDepartmentId = $capDepts[0] ?? null;
        }

        // Resolve sessions — merge settings values + distinct values from actual registered_courses data
        $service = new AcademicSessionService();
        $defaultSession = $service->getAcademicSession($user);

        $sessionKeys = ['ACADEMIC_SESSION', 'HOD_ACADEMIC_SESSION', 'PG_ACADEMIC_SESSION', 'ADMIN_ACADEMIC_SESSION'];
        $dbSessions = Setting::whereIn('key', $sessionKeys)->pluck('value')->filter()->unique()->values()->toArray();

        // Pull actual sessions from registered_courses so the dropdown always shows real data
        $actualSessions = \App\Models\RegisteredCourse::distinct()->pluck('academic_session')->filter()->toArray();

        $allSessions = array_values(array_unique(array_merge($dbSessions, $actualSessions, [$defaultSession])));
        sort($allSessions);
        $this->availableSessions = $allSessions;

        // Default to the most recent session that actually has registered courses in this department
        $sessionWithData = null;
        if ($this->selectedDepartmentId) {
            $deptCourseIds = \App\Models\DepartmentCourse::where('department_id', $this->selectedDepartmentId)
                ->pluck('id');
            $sessionWithData = \App\Models\RegisteredCourse::whereIn('department_course_id', $deptCourseIds)
                ->orderByDesc('academic_session')
                ->value('academic_session');
        }

        $this->selectedSession = $sessionWithData ?? $defaultSession;
    }

    public function updatedSelectedSession(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
    }

    public function updatedSelectedSemester(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
    }

    public function updatedSelectedDepartmentId(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
    }

    public function inspectCourse($departmentCourseId): void
    {
        $this->selectedCourseId = $departmentCourseId;
        $this->inspectingCourse = DepartmentCourse::with(['studentCourse', 'department'])->find($departmentCourseId);
        $this->loadStudentScores();
    }

    public function closeInspection(): void
    {
        $this->selectedCourseId = null;
        $this->inspectingCourse = null;
        $this->studentsWithResults = [];
    }

    public function loadStudentScores(): void
    {
        if (!$this->selectedCourseId) {
            $this->studentsWithResults = [];
            return;
        }

        // Get registered students for this department course & session
        $registered = RegisteredCourse::with(['academicDetail.user'])
            ->where('department_course_id', $this->selectedCourseId)
            ->where('academic_session', $this->selectedSession)
            ->get();

        // Get existing results
        $results = Result::where('department_course_id', $this->selectedCourseId)
            ->where('academic_session', $this->selectedSession)
            ->where('semester', $this->selectedSemester)
            ->get()
            ->keyBy('user_id');

        $rows = [];
        foreach ($registered as $reg) {
            $userId = $reg->academicDetail->user_id ?? null;
            if (!$userId) continue;

            $result = $results[$userId] ?? null;

            $rows[] = [
                'user_id' => $userId,
                'matric_no' => $reg->academicDetail->matric_no ?? 'N/A',
                'name' => trim(($reg->academicDetail->user->surname ?? '') . ' ' . ($reg->academicDetail->user->firstname ?? '') . ' ' . ($reg->academicDetail->user->m_name ?? '')),
                'ca_score' => $result?->ca_score,
                'exam_score' => $result?->exam_score,
                'total_score' => $result?->total_score,
                'grade' => $result?->grade ?? '-',
                'grade_point' => $result?->grade_point ?? '-',
                'status' => $result?->status ?? 'pending',
                'remarks' => $result?->remarks,
            ];
        }

        $this->studentsWithResults = $rows;
    }

    public function approveCourseResults(): void
    {
        if (!$this->selectedCourseId) return;

        $session = $this->selectedSession;
        $semester = $this->selectedSemester;

        // Note: HOD is no longer in the result approval workflow
        // Results now flow: Lecturer → Coordinator → Exam Officer
        $this->alert('info', 'HOD result review is no longer required. Results are processed by coordinators and exam officers.');
        return;

        // Create approval audit record
        ResultApproval::create([
            'department_id' => $this->selectedDepartmentId,
            'academic_session' => $session,
            'semester' => $semester,
            'approval_level' => 'hod',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'status' => 'approved',
            'comments' => "HOD approved {$updatedCount} result(s) for course ID {$this->selectedCourseId}",
        ]);

        $this->alert('success', "{$updatedCount} result(s) approved and forwarded to Exam Officer.");
        $this->loadStudentScores();
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

    public function rejectCourseResults(): void
    {
        if (!$this->selectedCourseId) return;

        $this->validate([
            'rejectionReason' => 'required|min:5|max:500',
        ], [
            'rejectionReason.required' => 'Please state the reason for rejecting these results.',
            'rejectionReason.min' => 'The reason must be at least 5 characters long.',
        ]);

        $session = $this->selectedSession;
        $semester = $this->selectedSemester;

        $updatedCount = Result::where('department_course_id', $this->selectedCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->whereIn('status', ['submitted', 'hod_approved'])
            ->update([
                'status' => 'pending',
                'remarks' => $this->rejectionReason,
            ]);

        // Create audit log
        ResultApproval::create([
            'department_id' => $this->selectedDepartmentId,
            'academic_session' => $session,
            'semester' => $semester,
            'approval_level' => 'hod',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'status' => 'rejected',
            'comments' => $this->rejectionReason,
        ]);

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        $this->alert('warning', "Results returned to lecturer for revision. Reason recorded.");
        $this->loadStudentScores();
    }

    public function render()
    {
        $session = $this->selectedSession;
        $semester = $this->selectedSemester;
        $deptId = $this->selectedDepartmentId;

        // Fetch courses offered by this department
        $departmentCourses = collect();
        if ($deptId) {
            $departmentCourses = DepartmentCourse::with(['studentCourse', 'department'])
                ->where('department_id', $deptId)
                ->when($this->searchQuery, function ($q) {
                    $q->whereHas('studentCourse', function ($sq) {
                        $sq->where('code', 'like', '%' . $this->searchQuery . '%')
                           ->orWhere('title', 'like', '%' . $this->searchQuery . '%');
                    });
                })
                ->get()
                ->map(function ($dc) use ($session, $semester) {
                    // Registered students count
                    $totalRegistered = RegisteredCourse::where('department_course_id', $dc->id)
                        ->where('academic_session', $session)
                        ->count();

                    // Result statuses
                    $resultCounts = Result::where('department_course_id', $dc->id)
                        ->where('academic_session', $session)
                        ->where('semester', $semester)
                        ->selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray();

                    $dc->total_registered = $totalRegistered;
                    $dc->pending_count = $resultCounts['pending'] ?? 0;
                    $dc->submitted_count = $resultCounts['submitted'] ?? 0;
                    $dc->coordinator_approved_count = $resultCounts['coordinator_approved'] ?? 0;
                    $dc->hod_approved_count = $resultCounts['hod_approved'] ?? 0;
                    $dc->released_count = $resultCounts['released'] ?? 0;

                    // Assigned lecturer
                    $allocation = CourseAllocation::with('lecturer')
                        ->where('department_course_id', $dc->id)
                        ->where('academic_session', $session)
                        ->where('semester', $semester)
                        ->first();

                    $dc->allocated_lecturer = $allocation ? trim(($allocation->lecturer->surname ?? '') . ' ' . ($allocation->lecturer->firstname ?? '')) : 'Unallocated';

                    return $dc;
                });

            if ($this->statusFilter === 'submitted') {
                $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->submitted_count > 0);
            } elseif ($this->statusFilter === 'coordinator_approved') {
                $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->coordinator_approved_count > 0);
            } elseif ($this->statusFilter === 'hod_approved') {
                $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->hod_approved_count > 0);
            } elseif ($this->statusFilter === 'released') {
                $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->released_count > 0);
            } elseif ($this->statusFilter === 'pending') {
                $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->pending_count > 0 || ($dc->total_registered > 0 && ($dc->submitted_count + $dc->coordinator_approved_count + $dc->hod_approved_count + $dc->released_count) === 0));
            }
        }

        // Summary counts
        $totalCoursesCount = $departmentCourses->count();
        $submittedCoursesCount = $departmentCourses->filter(fn($dc) => $dc->submitted_count > 0)->count();
        $coordinatorApprovedCoursesCount = $departmentCourses->filter(fn($dc) => $dc->coordinator_approved_count > 0)->count();
        $approvedCoursesCount = $departmentCourses->filter(fn($dc) => $dc->hod_approved_count > 0)->count();
        $releasedCoursesCount = $departmentCourses->filter(fn($dc) => $dc->released_count > 0)->count();

        return view('livewire.hod.hod-result-review', [
            'departmentCourses' => $departmentCourses,
            'totalCoursesCount' => $totalCoursesCount,
            'submittedCoursesCount' => $submittedCoursesCount,
            'coordinatorApprovedCoursesCount' => $coordinatorApprovedCoursesCount,
            'approvedCoursesCount' => $approvedCoursesCount,
            'releasedCoursesCount' => $releasedCoursesCount,
        ])->layout('layouts.app');
    }
}
