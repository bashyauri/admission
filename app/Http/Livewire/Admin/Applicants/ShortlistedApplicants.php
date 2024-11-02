<?php

namespace App\Http\Livewire\Admin\Applicants;

use Livewire\Component;
use App\Services\Report\ApplicantReportService;

class ShortlistedApplicants extends Component
{
    public $shortlistedApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->shortlistedApplicants = $applicantReportService->getApplicantsShortlisted();
    }

    public function render()
    {
        return view('livewire.admin.applicants.shortlisted-applicants');
    }
}
