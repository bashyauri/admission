<?php

namespace App\Http\Livewire\Hod\Applicants;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Services\Report\ApplicantReportService;

class NotRecommended extends Component
{
    public $notRecommendedApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->notRecommendedApplicants = $applicantReportService->getApplicantsNotRecommended(auth()->user()->hodDetails->department_id);
    }

    #[Computed()]
    public function notRecommended()
    {
        return $this->notRecommendedApplicants;
    }
    public function render()
    {
        return view('livewire.hod.applicants.not-recommended');
    }
}