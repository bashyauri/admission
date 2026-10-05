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

class GenerateStudentPin extends Component
{
    use LivewireAlert;

    public $search = '';
    public $courseId;
    public $departmentId;
    public $studentLevelId;
    public $academicSession;
    public $generatedPin;

    /** ID of the AcademicDetail record currently selected for course preview / approval */
    public ?int $selectedStudentId = null;

    public function mount(): void
    {
        $coordinator = Auth::user()->coordinator;

        // Support both course-based and department-based coordinators
        if ($coordinator->isCourseBased()) {
            $this->courseId = $coordinator->course_id;
            $this->departmentId = null;
        } elseif ($coordinator->isDepartmentBased()) {
            $this->departmentId = $coordinator->department_id;
            $this->courseId = null;
        }

        $this->studentLevelId = $coordinator->student_level_id;
        $this->academicSession = $coordinator->academic_session;
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

        $coordinator = $this->getCoordinatorForStudent($academicDetail);
        $approval->approve($coordinator?->id);

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

    public function render()
    {
        $students = $this->searchStudent();

        $selectedAcademicDetail = $this->selectedStudentId
            ? AcademicDetail::with(['user:id,surname,firstname,m_name', 'approval'])->find($this->selectedStudentId)
            : null;

        $registeredCourses = collect();
        if ($selectedAcademicDetail) {
            $currentSession = app(AcademicSessionService::class)->getAcademicSession($selectedAcademicDetail->user);
            $registeredCourses = RegisteredCourse::with(['departmentCourse.studentCourse'])
                ->where('academic_detail_id', $this->selectedStudentId)
                ->where('academic_session', $currentSession)
                ->get();
        }

        return view('livewire.coordinator.generate-student-pin', [
            'students'              => $students,
            'selectedAcademicDetail' => $selectedAcademicDetail,
            'registeredCourses'     => $registeredCourses,
        ]);
    }
}
