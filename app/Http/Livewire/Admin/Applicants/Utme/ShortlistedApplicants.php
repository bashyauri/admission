<?php

namespace App\Http\Livewire\Admin\Applicants\Utme;

use Livewire\Component;
use App\Models\ProposedCourse;
use App\Enums\ApplicationStatus;
use Illuminate\Support\Facades\Log;
use App\Services\Report\UtmeService;
use App\Services\UTMEApplicantService;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ShortlistedApplicants extends Component
{
    use LivewireAlert;
    public $shortlistedApplicants;
    public function mount(UtmeService $utmeService): void
    {
        $this->shortlistedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::SHORTLISTED->toString());
    }
    public function drop(ProposedCourse $proposedCourse, UtmeService $utmeService, UTMEApplicantService $uTMEApplicantService)
    {
        try {
            $uTMEApplicantService->drop(id: $proposedCourse->id);
            $this->shortlistedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::SHORTLISTED->toString());



            $this->alert('success', 'Status Updated Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->alert('error', 'An error occurred while updating the status.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }
    }


    public function render()
    {
        return view('livewire.admin.applicants.utme.shortlisted-applicants');
    }
}