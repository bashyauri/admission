<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use App\Models\Department;

use App\Services\Report\ApplicantReportService;

class AdminIndex extends Component
{
    public int $totalApplicants;
    public int $notRecommendedApplicants;
    public int $shortlistedApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {


        $this->totalApplicants = $applicantReportService->totalApplicants();
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended();
        $this->shortlistedApplicants = $applicantReportService->applicantsShortlisted();
    }
    public function render()
    {

        return view('livewire.dashboards.admin-index', [
            'totalApplicants' => $this->totalApplicants,
            'notRecommendedApplicants' => $this->notRecommendedApplicants,
            'shortlistedApplicants' => $this->shortlistedApplicants,
        ]);
    }
}