<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use App\Services\Report\ApplicantReportService;

class HodIndex extends Component
{
    public int $totalApplicants;
    public int $notRecommendedApplicants;
    public int $shortlistedApplicants;
    public int $paidAdmissionFees;
    public int $paidAcceptanceFees;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $departmentId = auth()->user()->hodDetails->department_id;

        $this->totalApplicants = $applicantReportService->totalApplicants($departmentId);
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended($departmentId);
        $this->shortlistedApplicants = $applicantReportService->applicantsShortlisted($departmentId);
        $this->paidAdmissionFees = $applicantReportService->getPaidAdmissionFees($departmentId);
        $this->paidAcceptanceFees = $applicantReportService->getPaidAcceptanceFees($departmentId);
    }

    public function render()
    {
        return view('livewire.dashboards.hod-index', [
            'totalApplicants' => $this->totalApplicants,
            'notRecommendedApplicants' => $this->notRecommendedApplicants,
            'shortlistedApplicants' => $this->shortlistedApplicants,
            'paidAdmissionFees' => $this->paidAdmissionFees,
            'paidAcceptanceFees' => $this->paidAcceptanceFees,
        ]);
    }
}
