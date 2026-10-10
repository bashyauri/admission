<?php

declare(strict_types=1);

namespace App\Http\Livewire\Coordinator;

use App\Enums\TransactionStatus;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\Approval;
use App\Models\AcademicDetail;
use App\Models\RegisteredCourse;
use App\Models\StudentTransaction;
use App\Models\Coordinator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Services\AcademicSessionService;
use App\Services\CourseRegistrationService;

class GenerateStudentPin extends Component
{
    use LivewireAlert;

    public $search = '';
    public $courseId;
    public $departmentId;
    public $studentLevelId;
    public $academicSession;
    public $generatedPin;

    /** Currently selected coordinator assignment */
    public ?int $selectedAssignmentId = null;

    /** ID of the AcademicDetail record currently selected for course preview / approval */
    public ?int $selectedStudentId = null;

    public function mount(): void
    {
        // Select the first assignment by default, or the most recent one
        $firstAssignment = Coordinator::where('user_id', Auth::id())
            ->with(['course', 'course.department', 'department', 'studentLevel'])
            ->orderBy('academic_session', 'desc')
            ->first();

        if ($firstAssignment) {
            $this->selectAssignment($firstAssignment->id);
        }
    }

    /**
     * Select a coordinator assignment to work with
     */
    public function selectAssignment(int $assignmentId): void
    {
        $this->selectedAssignmentId = $assignmentId;
        $assignment = Coordinator::where('id', $assignmentId)
            ->where('user_id', Auth::id())
            ->with(['course', 'course.department', 'department', 'studentLevel'])
            ->first();

        if ($assignment) {
            // Support both course-based and department-based coordinators
            if ($assignment->isCourseBased()) {
                $this->courseId = $assignment->course_id;
                $this->departmentId = null;
            } elseif ($assignment->isDepartmentBased()) {
                $this->departmentId = $assignment->department_id;
                $this->courseId = null;
            }

            $this->studentLevelId = $assignment->student_level_id;
            $this->academicSession = $assignment->academic_session;
        }

        // Reset search and selection when switching assignments
        $this->search = '';
        $this->selectedStudentId = null;
        $this->generatedPin = null;
    }

    /**
     * Get the appropriate coordinator for a student based on their admission cohort.
     * Supports both course-based (new) and department-based (legacy) coordinators.
     */
    private function getCoordinatorForStudent(AcademicDetail $academicDetail): ?Coordinator
    {
        $admissionSession = $academicDetail->admission_session
            ?? app(AcademicSessionService::class)->getAcademicSession($academicDetail->user);

        $courseCoordinator = Coordinator::forCourseCohort(
            $academicDetail->course_id,
            $academicDetail->student_level_id,
            $admissionSession
        )->first();

        if ($courseCoordinator) {
            return $courseCoordinator;
        }

        if ($academicDetail->department_id) {
            return Coordinator::forDepartmentCohort(
                $academicDetail->department_id,
                $academicDetail->student_level_id,
                $admissionSession
            )->first();
        }

        return null;
    }

    /**
     * Generate a registration PIN for a student (requires school fees payment).
     */
    public function generatePin(AcademicDetail $academicDetail): void
    {
        $hasPaidSchoolFees = StudentTransaction::where('user_id', $academicDetail->user_id)
            ->whereIn('resource', [
                config('remita.schoolfees.description'),
                config('remita.schoolfees.ug_schoolfees_description'),
            ])
            ->where('status', TransactionStatus::APPROVED->value)
            ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession($academicDetail->user))
            ->exists();

        if (!$hasPaidSchoolFees) {
            $this->alert('error', 'Pin can only be generated after school fees payment is approved.', [
                'position' => 'top-end',
                'timer'    => 3500,
                'toast'    => true,
            ]);
            return;
        }

        try {
            $this->generatedPin = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $coordinator = $this->getCoordinatorForStudent($academicDetail);

            if (!$coordinator) {
                $this->alert('error', 'No coordinator assigned for this student\'s course/department, level, and admission session.', [
                    'position' => 'top-end',
                    'timer'    => 3500,
                    'toast'    => true,
                ]);
                return;
            }

            DB::transaction(function () use ($academicDetail, $coordinator) {
                Approval::updateOrCreate(
                    ['academic_detail_id' => $academicDetail->id],
                    [
                        'pin'            => $this->generatedPin,
                        'is_used'        => false,
                        'coordinator_id' => $coordinator->id,
                        'approval_date'  => now(),
                    ],
                );
                // Persist the coordinator assignment so it stays for the student's academic career
                $academicDetail->update(['coordinator_id' => $coordinator->id]);
            });

            $this->alert('success', 'Pin Generated and Cohort Coordinator Assigned', [
                'position'         => 'top-end',
                'timer'            => 3000,
                'toast'            => true,
                'showCancelButton' => false,
                'icon'             => 'success',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to generate pin! Error: ' . $e->getMessage(), [
                'position' => 'top-end',
                'timer'    => 3000,
            ]);
        }
    }

    /**
     * Select a student to preview their registered courses before approving.
     */
    public function selectStudent(int $academicDetailId): void
    {
        $isAssigned = $this->currentCoordinatorId() && AcademicDetail::query()
            ->whereKey($academicDetailId)
            ->where('student_level_id', $this->studentLevelId)
            ->where('admission_session', $this->academicSession)
            ->when($this->courseId, fn ($query) => $query->where('course_id', $this->courseId))
            ->when($this->departmentId, fn ($query) => $query->where('department_id', $this->departmentId))
            ->exists();

        if (!$isAssigned) {
            $this->selectedStudentId = null;
            return;
        }

        $this->selectedStudentId = $academicDetailId;
        $this->generatedPin = null;
    }

    /**
     * Approve the student's course registration.
     * Sets approval_status = 'Approved', which locks the student from
     * adding or removing courses in the student portal.
     */
    public function approveRegistration(AcademicDetail $academicDetail): void
    {
        $approval = $academicDetail->approval;

        if (!$approval?->isSubmitted() || $approval->coordinator_id !== $this->currentCoordinatorId()) {
            $this->alert('error', 'This submitted registration is not assigned to your Coordinator account.', ['position' => 'top-end', 'timer' => 4000, 'toast' => true]);
            return;
        }

        if (!$approval) {
            $this->alert('error', 'No approval record found. Generate a PIN for this student first.', [
                'position' => 'top-end',
                'timer'    => 4000,
                'toast'    => true,
            ]);
            return;
        }

        $hasRegistrations = RegisteredCourse::where('academic_detail_id', $academicDetail->id)
            ->where('academic_session', app(AcademicSessionService::class)->getAcademicSession($academicDetail->user))
            ->exists();

        if (!$hasRegistrations) {
            $this->alert('warning', 'This student has no registered courses for the current session. Cannot approve an empty registration.', [
                'position' => 'top-end',
                'timer'    => 4000,
                'toast'    => true,
            ]);
            return;
        }

        $approval->approve($this->currentCoordinatorId());

        $this->alert('success', 'Course registration approved and locked successfully.', [
            'position' => 'top-end',
            'timer'    => 3000,
            'toast'    => true,
            'icon'     => 'success',
        ]);
    }

    /**
     * Unlock a student's course registration so they can make changes.
     */
    public function unlockRegistration(AcademicDetail $academicDetail): void
    {
        $approval = $academicDetail->approval;

        if (!$approval || !$approval->isApproved()) {
            $this->alert('warning', 'This registration is not currently approved/locked.', [
                'position' => 'top-end',
                'timer'    => 3000,
                'toast'    => true,
            ]);
            return;
        }

        if ($approval->coordinator_id !== $this->currentCoordinatorId()) {
            $this->alert('error', 'This registration is not assigned to your Coordinator account.', ['position' => 'top-end', 'timer' => 4000, 'toast' => true]);
            return;
        }

        $approval->unlock();

        $this->alert('success', 'Course registration unlocked. The student can now modify their courses.', [
            'position' => 'top-end',
            'timer'    => 3000,
            'toast'    => true,
        ]);
    }

    public function close(): void
    {
        $this->generatedPin = null;
        $this->search = '';
        $this->selectedStudentId = null;
    }

    public function searchStudent(): Collection
    {
        $query = AcademicDetail::where('matric_no', $this->search)
            ->where('student_level_id', $this->studentLevelId)
            ->where('admission_session', $this->academicSession);

        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        } elseif ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        return $query
            ->with(['approval', 'user:id,surname,firstname,m_name,picture'])
            ->select(['user_id', 'matric_no', 'course_id', 'department_id', 'student_level_id', 'admission_session', 'id'])
            ->get();
    }

    private function currentCoordinatorId(): ?int
    {
        return Coordinator::where('id', $this->selectedAssignmentId)
            ->where('user_id', Auth::id())
            ->value('id');
    }

    public function render()
    {
        // Load all coordinator assignments for the logged-in user with eager-loaded relationships
        $coordinatorAssignments = Coordinator::where('user_id', Auth::id())
            ->with(['course', 'course.department', 'department', 'studentLevel'])
            ->orderBy('academic_session', 'desc')
            ->get();

        $students = $this->searchStudent();

        $pendingSubmissions = collect();
        if ($coordinatorId = $this->currentCoordinatorId()) {
            $pendingSubmissions = AcademicDetail::query()
                ->whereHas('approval', fn ($query) => $query
                    ->where('coordinator_id', $coordinatorId)
                    ->whereNotNull('registration_submitted_at')
                    ->where('approval_status', '!=', 'Approved'))
                ->with(['user:id,surname,firstname,m_name', 'approval'])
                ->when($this->courseId, fn ($query) => $query->where('course_id', $this->courseId))
                ->when($this->departmentId, fn ($query) => $query->where('department_id', $this->departmentId))
                ->where('student_level_id', $this->studentLevelId)
                ->where('admission_session', $this->academicSession)
                ->get()
                ->sortBy(fn ($student) => $student->approval?->registration_submitted_at?->timestamp ?? 0)
                ->values();
        }

        $coordinatorId = $this->currentCoordinatorId();
        $selectedAcademicDetail = $this->selectedStudentId && $coordinatorId
            ? AcademicDetail::whereKey($this->selectedStudentId)
                ->where('student_level_id', $this->studentLevelId)
                ->where('admission_session', $this->academicSession)
                ->when($this->courseId, fn ($query) => $query->where('course_id', $this->courseId))
                ->when($this->departmentId, fn ($query) => $query->where('department_id', $this->departmentId))
                ->with(['user:id,surname,firstname,m_name', 'approval'])
                ->first()
            : null;

        $registeredCourses = collect();
        if ($selectedAcademicDetail) {
            $currentSession = app(AcademicSessionService::class)->getAcademicSession($selectedAcademicDetail->user);
            $registeredCourses = app(CourseRegistrationService::class)->getRegisteredCourses(
                $this->selectedStudentId,
                $currentSession
            );
        }

        return view('livewire.coordinator.generate-student-pin', [
            'students'              => $students,
            'selectedAcademicDetail' => $selectedAcademicDetail,
            'registeredCourses'     => $registeredCourses,
            'coordinatorAssignments' => $coordinatorAssignments,
            'pendingSubmissions' => $pendingSubmissions,
        ]);
    }
}
