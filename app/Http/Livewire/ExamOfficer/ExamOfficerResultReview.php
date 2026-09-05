<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\Setting;
use App\Services\AcademicSessionService;
use App\Services\CarryOverRegistrationService;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ExamOfficerResultReview extends Component
{
    use LivewireAlert;

    // Academic Filter Context
    public string $selectedSession = '';
    public string $selectedSemester = 'first';
    public array $availableSessions = [];
    public $selectedDepartmentId = 'all';
    public array $availableDepartments = [];

    // Inspection State
    public $selectedCourseId = null;
    public ?DepartmentCourse $inspectingCourse = null;
    public array $studentsWithResults = [];
    public string $searchQuery = '';
    public string $statusFilter = 'all';

    // Rejection Modal
    public bool $showRejectModal = false;
    public string $rejectionReason = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Exam Officer Result Review.');
        }

        $this->availableDepartments = Department::orderBy('name')->get()->toArray();

        // Resolve sessions
        $service = new AcademicSessionService();
        $defaultSession = $service->getAcademicSession($user);

        $sessionKeys = ['ACADEMIC_SESSION', 'HOD_ACADEMIC_SESSION', 'PG_ACADEMIC_SESSION', 'ADMIN_ACADEMIC_SESSION'];
        $dbSessions = Setting::whereIn('key', $sessionKeys)->pluck('value')->filter()->unique()->values()->toArray();

        $this->availableSessions = array_values(array_unique(array_merge($dbSessions, [$defaultSession])));
        sort($this->availableSessions);

        $this->selectedSession = $defaultSession;
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

        $registered = RegisteredCourse::with(['academicDetail.user'])
            ->where('department_course_id', $this->selectedCourseId)
            ->where('academic_session', $this->selectedSession)
            ->get();

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

    public function releaseCourseResults($courseId = null): void
    {
        $targetCourseId = $courseId ?? $this->selectedCourseId;
        if (!$targetCourseId) return;

        $session = $this->selectedSession;
        $semester = $this->selectedSemester;

        $results = Result::with(['user.academicDetail', 'registeredCourse', 'departmentCourse'])
            ->where('department_course_id', $targetCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'exam_officer_approved')
            ->get();

        if ($results->isEmpty()) {
            $this->alert('info', 'No coordinator-approved results ready for release in this course.');
            return;
        }

        $gradeService = new GradeCalculationService();
        $carryOverService = new CarryOverRegistrationService();

        // Update results to released
        Result::where('department_course_id', $targetCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'exam_officer_approved')
            ->update([
                'status' => 'released',
                'exam_officer_approved_by' => Auth::id(),
                'exam_officer_approved_at' => now(),
            ]);

        // Process GPA computation & carry-over clearance for each affected student
        $deptCourse = DepartmentCourse::find($targetCourseId);
        $uniqueUsers = $results->pluck('user')->filter()->unique('id');

        foreach ($results as $result) {
            // Re-fetch updated model instance
            $freshResult = Result::find($result->id);
            if ($freshResult) {
                if ((float) $freshResult->total_score >= 40.0) {
                    $carryOverService->processResultClearance($freshResult);
                } else {
                    $carryOverService->recordFailedCourse($freshResult);
                }
            }
        }

        foreach ($uniqueUsers as $studentUser) {
            $gradeService->processAndSaveGpaRecord($studentUser, $session, $semester);
        }

        // Record audit
        ResultApproval::create([
            'department_id' => $deptCourse?->department_id,
            'academic_session' => $session,
            'semester' => $semester,
            'approval_level' => 'exam_officer',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'status' => 'released',
            'comments' => "Exam Officer released {$results->count()} result(s) for course ID {$targetCourseId}. GPAs and Carry-Overs updated.",
        ]);

        $this->alert('success', "{$results->count()} result(s) released successfully! Student GPAs and carry-overs have been recalculated.");
        if ($this->selectedCourseId) {
            $this->loadStudentScores();
        }
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
            'rejectionReason.required' => 'Please provide a reason for returning results to the HOD.',
            'rejectionReason.min' => 'The reason must be at least 5 characters long.',
        ]);

        $session = $this->selectedSession;
        $semester = $this->selectedSemester;

        $updatedCount = Result::where('department_course_id', $this->selectedCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'exam_officer_approved')
            ->update([
                'status' => 'submitted', // Returns to HOD for review
                'remarks' => '[Exam Officer]: ' . $this->rejectionReason,
            ]);

        $deptCourse = DepartmentCourse::find($this->selectedCourseId);

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
        $this->alert('warning', "Results returned to HOD for revision. Reason recorded.");
        $this->loadStudentScores();
    }

    public function render()
    {
        $session = $this->selectedSession;
        $semester = $this->selectedSemester;
        $deptId = $this->selectedDepartmentId;

        $query = DepartmentCourse::with(['studentCourse', 'department']);

        if ($deptId !== 'all' && !empty($deptId)) {
            $query->where('department_id', $deptId);
        }

        if ($this->searchQuery) {
            $query->whereHas('studentCourse', function ($sq) {
                $sq->where('code', 'like', '%' . $this->searchQuery . '%')
                   ->orWhere('title', 'like', '%' . $this->searchQuery . '%');
            });
        }

        $departmentCourses = $query->get()->map(function ($dc) use ($session, $semester) {
            $totalRegistered = RegisteredCourse::where('department_course_id', $dc->id)
                ->where('academic_session', $session)
                ->count();

            $resultCounts = Result::where('department_course_id', $dc->id)
                ->where('academic_session', $session)
                ->where('semester', $semester)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $allocation = CourseAllocation::with('lecturer')
                ->where('department_course_id', $dc->id)
                ->where('academic_session', $session)
                ->where('semester', $semester)
                ->first();

            $dc->total_registered = $totalRegistered;
            $dc->pending_count = $resultCounts['pending'] ?? 0;
            $dc->submitted_count = $resultCounts['submitted'] ?? 0;
            $dc->hod_approved_count = $resultCounts['hod_approved'] ?? 0;
            $dc->released_count = $resultCounts['released'] ?? 0;
            $dc->allocated_lecturer = $allocation ? trim(($allocation->lecturer->surname ?? '') . ' ' . ($allocation->lecturer->firstname ?? '')) : 'Unallocated';

            return $dc;
        });

        if ($this->statusFilter === 'hod_approved') {
            $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->hod_approved_count > 0);
        } elseif ($this->statusFilter === 'released') {
            $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->released_count > 0);
        } elseif ($this->statusFilter === 'submitted') {
            $departmentCourses = $departmentCourses->filter(fn($dc) => $dc->submitted_count > 0);
        }

        // Metrics
        $totalCourses = $departmentCourses->count();
        $awaitingRelease = $departmentCourses->filter(fn($dc) => $dc->hod_approved_count > 0)->count();
        $releasedCount = $departmentCourses->filter(fn($dc) => $dc->released_count > 0)->count();
        $pendingHodCount = $departmentCourses->filter(fn($dc) => $dc->submitted_count > 0)->count();

        return view('livewire.exam-officer.exam-officer-result-review', [
            'departmentCourses' => $departmentCourses,
            'totalCourses' => $totalCourses,
            'awaitingRelease' => $awaitingRelease,
            'releasedCount' => $releasedCount,
            'pendingHodCount' => $pendingHodCount,
        ])->layout('layouts.app');
    }
}
