<?php

namespace App\Http\Livewire\Hod\Applicants;

use Livewire\Component;
use App\Services\Report\ApplicantReportService;

class ShortlistedApplicants extends Component
{
    public $shortlistedApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->shortlistedApplicants = $applicantReportService->getApplicantsShortlisted(auth()->user()->hodDetails->department_id);
    }

    public function render()
    {
        return view('livewire.hod.applicants.shortlisted-applicants');
    }
}