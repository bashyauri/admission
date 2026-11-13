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
        // Eager-load relations required by isDe and department code lookup.
        // Without this, isDe silently returns false (lazy loading guard) and
        // the wrong mode-of-entry prefix is generated.
        $user->loadMissing(['proposedCourse.department', 'postUtmeUpload']);
        $this->user = $user;
    }

    public function addRegistrationNumber(UTMEApplicantService $uTMEApplicantService)
    {
        try {
            // Eager-load relations immediately when action runs (Livewire rehydrates models without relations)
            $this->user->loadMissing(['proposedCourse.department', 'postUtmeUpload', 'academicDetail']);

            // Check if the student already has a matric number
            if ($this->user->academicDetail?->matric_no) {
                $this->alert('warning', 'Registration number already exists for this student!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                    'text' => 'No new registration number was generated.',
                    'showConfirmButton' => false,
                    'showCancelButton' => false,
                    'icon' => 'warning',
                ]);
                return;
            }

            // Use a database transaction to ensure data consistency and atomic registration number assignment
            DB::transaction(function () use ($uTMEApplicantService) {
                // Ensure relations are freshly loaded inside transaction
                $this->user->loadMissing(['proposedCourse.department', 'postUtmeUpload']);

                // ✅ Determine mode of entry
                // UTME = 10, DE = 20
                $modeOfEntry = $this->user->isDe ? "20" : "10";

                $departmentCode = $this->user->proposedCourse->department->code;
                $facultyCode = "90";
                $departmentId = $this->user->proposedCourse->department_id;
                $year = config('remita.settings.academic_session');

                // Generate the matric number inside the transaction
                $this->matricNo = $uTMEApplicantService->generateUgRegistrationNumber(
                    year: $year,
                    modeOfEntry: $modeOfEntry,
                    facultyCode: $facultyCode,
                    departmentCode: $departmentCode,
                    departmentId: $departmentId
                );

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
