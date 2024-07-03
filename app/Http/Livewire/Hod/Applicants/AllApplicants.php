<?php

namespace App\Http\Livewire\Hod\Applicants;

use App\Models\ProposedCourse;
use App\Models\User;
use App\Services\Report\ApplicantReportService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AllApplicants extends Component
{
    public $getAllApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->getAllApplicants = $applicantReportService->getApplicants();
    }

    #[Computed()]
    public function allApplicants()
    {
        return $this->getAllApplicants;
    }

    public function render()
    {
        return view('livewire.hod.applicants.all-applicants');
    }
}
