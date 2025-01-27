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
            $modeOfEntry = "10";
            $departmentCode = $this->user->proposedCourse->department->code;
            $facultyCode = "90";
            $year = config('remita.settings.academic_session');

            $this->matricNo = $uTMEApplicantService->generateUgRegistrationNumber(
                year: $year,
                modeOfEntry: $modeOfEntry,
                facultyCode: $facultyCode,
                departmentCode: $departmentCode
            );

            DB::transaction(function () use ($uTMEApplicantService) {
                $this->form->store($this->user, $this->matricNo);
                $uTMEApplicantService->changeToStudent($this->user);
            });

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