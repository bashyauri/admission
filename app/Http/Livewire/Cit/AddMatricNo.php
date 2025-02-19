<?php

namespace App\Http\Livewire\Cit;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Services\UTMEApplicantService;
use App\Livewire\Forms\UgAcademicDetailForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class AddMatricNo extends Component
{
    use LivewireAlert;

    public $matricNo;
    public $user;
    public UgAcademicDetailForm $form;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function addRegistrationNumber(UTMEApplicantService $uTMEApplicantService)
    {
        try {
            // Check if the student already has a matric number
            if ($this->user->academicDetail->matric_no) {
                $this->alert('warning', 'Registration number already exists for this student!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                    'text' => 'No new registration number was generated.',
                    'showConfirmButton' => false,
                    'showCancelButton' => false,
                    'icon' => 'warning',
                ]);
                return; // Exit the function if a matric number already exists
            }

            $modeOfEntry = "10";
            $departmentCode = $this->user->proposedCourse->department->code;
            $facultyCode = "90";
            $departmentId = $this->user->proposedCourse->department_id;
            $year = config('remita.settings.academic_session');

            // Generate the matric number
            $this->matricNo = $uTMEApplicantService->generateUgRegistrationNumber(
                year: $year,
                modeOfEntry: $modeOfEntry,
                facultyCode: $facultyCode,
                departmentCode: $departmentCode,
                departmentId: $departmentId
            );

            // Use a database transaction to ensure data consistency
            DB::transaction(function () use ($uTMEApplicantService) {
                // Store the matric number and other academic details
                $this->form->store($this->user, $this->matricNo);
                // Change the user's status to "student"
                $uTMEApplicantService->changeToStudent($this->user);
            });

            // Display success message
            $this->alert('success', 'Registration number added successfully!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
                'text' => '',
                'showConfirmButton' => false,
                'showCancelButton' => false,
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            // Display error message if something goes wrong
            $this->alert('error', 'Failed to add registration number!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
                'text' => $e->getMessage(),
                'showConfirmButton' => false,
                'showCancelButton' => false,
                'icon' => 'error',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.cit.add-matric-no');
    }
}