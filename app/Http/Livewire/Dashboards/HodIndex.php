<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use App\Services\Report\ApplicantReportService;

class HodIndex extends Component
{
    public int $totalApplicants;
    public int $notRecommendedApplicants;
    public int $shortlistedApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $departmentId = auth()->user()->hodDetails->department_id;

        $this->totalApplicants = $applicantReportService->totalApplicants($departmentId);
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended($departmentId);
        $this->shortlistedApplicants = $applicantReportService->applicantsShortlisted($departmentId);
    }

    public function render()
    {
        return view('livewire.dashboards.hod-index', [
            'totalApplicants' => $this->totalApplicants,
            'notRecommendedApplicants' => $this->notRecommendedApplicants,
            'shortlistedApplicants' => $this->shortlistedApplicants,
        ]);
    }
}