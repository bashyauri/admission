<?php

namespace App\Http\Livewire\Admin\Applicants;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Services\Report\ApplicantReportService;

class NotRecommended extends Component
{
    public $notRecommendedApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->notRecommendedApplicants = $applicantReportService->getApplicantsNotRecommended();
    }

    #[Computed()]
    public function notRecommended()
    {
        return $this->notRecommendedApplicants;
    }
    public function render()
    {
        return view('livewire.admin.applicants.not-recommended');
    }
}
