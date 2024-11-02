<?php

namespace App\Http\Livewire\Admin\Applicants;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Services\Report\ApplicantReportService;

class AllApplicants extends Component
{
    public $getAllApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->getAllApplicants = $applicantReportService->getAllApplicants();
    }

    #[Computed()]
    public function allApplicants()
    {
        return $this->getAllApplicants;
    }
    public function render()
    {
        return view('livewire.admin.applicants.all-applicants');
    }
}
