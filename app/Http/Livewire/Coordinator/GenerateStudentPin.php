<?php

declare(strict_types=1);

namespace App\Http\Livewire\Coordinator;

use App\Enums\TransactionStatus;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\Approval;
use App\Models\AcademicDetail;
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
     * Get the appropriate coordinator for a student based on their admission cohort
     * Supports both course-based (new) and department-based (legacy) coordinators for backward compatibility
     * This ensures students from the same cohort maintain the same coordinator throughout their academic program
     */
    private function getCoordinatorForStudent(AcademicDetail $academicDetail): ?Coordinator
    {
        // Use admission_session if available, otherwise fall back to current academic session
        $admissionSession = $academicDetail->admission_session ?? app(AcademicSessionService::class)->getAcademicSession($academicDetail->user);
        
        // First try to find course-based coordinator (new system)
        $courseCoordinator = Coordinator::forCourseCohort(
            $academicDetail->course_id, 
            $academicDetail->student_level_id,
            $admissionSession
        )->first();
        
        if ($courseCoordinator) {
            return $courseCoordinator;
        }
        
        // Fall back to department-based coordinator (legacy system)
        if ($academicDetail->department_id) {
            return Coordinator::forDepartmentCohort(
                $academicDetail->department_id,
                $academicDetail->student_level_id,
                $admissionSession
            )->first();
        }
        
        return null;
    }

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
                'timer' => 3500,
                'toast' => true,
            ]);
            return;
        }

        try {
            $this->generatedPin = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Get the appropriate coordinator for this student (supports both course and department-based)
            $coordinator = $this->getCoordinatorForStudent($academicDetail);
            
            if (!$coordinator) {
                $this->alert('error', 'No coordinator assigned for this student\'s course/department, level, and admission session.', [
                    'position' => 'top-end',
                    'timer' => 3500,
                    'toast' => true,
                ]);
                return;
            }
            
            DB::transaction(function () use ($academicDetail, $coordinator) {
                Approval::updateOrCreate(
                    ['academic_detail_id' => $academicDetail->id],
                    ['pin' => $this->generatedPin, 'is_used' => false, 'coordinator_id' => $coordinator->id, 'approval_date' => now()],

                );
                // Assign the coordinator to the student - this persists throughout their academic career
                $academicDetail->update(['coordinator_id' => $coordinator->id]);
            });
            
            $this->alert('success', 'Pin Generated and Cohort Coordinator Assigned', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
                'showCancelButton' => false,
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to generate pin! Error: ' . $e->getMessage(), [
                'position' => 'top-end',
                'timer' => 3000,
            ]);
        }
    }
    public function close(): void
    {
        $this->generatedPin = null;
        $this->search = '';
    }

    public function searchStudent(): Collection
    {
        $query = AcademicDetail::where('matric_no',  $this->search)
            ->where('student_level_id', $this->studentLevelId) // Filter by level coordinator's assigned level
            ->where('admission_session', $this->academicSession); // Filter by coordinator's assigned cohort
        
        // Support both course-based and department-based coordinators
        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        } elseif ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }
        
        return $query->select(['user_id', 'matric_no', 'course_id', 'department_id', 'student_level_id', 'admission_session', 'id'])
            ->get();
    }

    public function render()
    {
        return view('livewire.coordinator.generate-student-pin', [
            'students' => $this->searchStudent()
        ]);
    }
}
