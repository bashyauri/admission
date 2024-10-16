<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;

use App\Services\Report\ApplicantReportService;

class AdminIndex extends Component
{
    public int $totalApplicants;
    public int $notRecommendedApplicants;
    public int $shortlistedApplicants;
    public int $paidAdmissionFees;
    public int $paidAcceptanceFees;

    public function mount(ApplicantReportService $applicantReportService)
    {


        $this->totalApplicants = $applicantReportService->totalApplicants();
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended();
        $this->shortlistedApplicants = $applicantReportService->applicantsShortlisted();
        $this->paidAdmissionFees = $applicantReportService->getPaidAdmissionFees();
        $this->paidAcceptanceFees = $applicantReportService->getPaidAcceptanceFees();
    }

    public function render()
    {

        return view('livewire.dashboards.admin-index', [
            'totalApplicants' => $this->totalApplicants,
            'notRecommendedApplicants' => $this->notRecommendedApplicants,
            'shortlistedApplicants' => $this->shortlistedApplicants,
            'paidAdmissionFees' => $this->paidAdmissionFees,
            'paidAcceptanceFees' => $this->paidAcceptanceFees,

        ]);
    }
}
