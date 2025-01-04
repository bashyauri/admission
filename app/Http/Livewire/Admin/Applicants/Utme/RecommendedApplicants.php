<?php

namespace App\Http\Livewire\Admin\Applicants\Utme;

use Livewire\Component;
use App\Models\ProposedCourse;
use App\Enums\ApplicationStatus;
use App\Notifications\Shortlist;
use Illuminate\Support\Facades\Log;
use App\Services\Report\UtmeService;
use App\Services\UTMEApplicantService;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class RecommendedApplicants extends Component
{
    use LivewireAlert;

    public $recommendedApplicants;
    public $utmeService;

    public function mount(UtmeService $utmeService)
    {
        $this->recommendedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::RECOMMENDED->toString());
    }

    public function shortlist(ProposedCourse $proposedCourse, UtmeService $utmeService)
    {
        DB::transaction(function () use ($proposedCourse, $utmeService) {
            try {
                $proposedCourse->update(['status' => ApplicationStatus::SHORTLISTED]);
                $proposedCourse->user->notify(new Shortlist());
                $this->recommendedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::RECOMMENDED->toString());


                $this->alert('success', 'Status Updated Successfully', [
                    'position' => 'center',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $this->alert('error', 'An error occurred while updating the status.', [
                    'position' => 'center',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }
        });
    }

    public function drop(ProposedCourse $proposedCourse, UtmeService $utmeService, UTMEApplicantService $uTMEApplicantService)
    {
        try {
            $uTMEApplicantService->drop(id: $proposedCourse->id);
            $this->recommendedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::RECOMMENDED->toString());



            $this->alert('success', 'Status Updated Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
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
        return view('livewire.admin.applicants.utme.recommended-applicants');
    }
}
