<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;

use App\Services\Report\ApplicantReportService;
use App\Services\Report\UtmeService;

class AdminIndex extends Component
{
    public int $totalApplicants;
    public int $notRecommendedApplicants;
    public int $shortlistedApplicants;
    public int $paidAdmissionFees;
    public int $paidAcceptanceFees;
    public int $totalCandidatesUploded;
    public int $totalUTMEApplicants;
    public int $totalUTMERecommendedApplicants;
    public int $totalUTMEShortlistedApplicants;
    public int $undergraduateSchoolFeesPaid;

    public function mount(ApplicantReportService $applicantReportService, UtmeService $utmeService)
    {
        $this->totalApplicants = $applicantReportService->totalApplicants();
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended();
        $this->shortlistedApplicants = $applicantReportService->applicantsShortlisted();
        $this->paidAdmissionFees = $applicantReportService->getPaidAdmissionFees();
        $this->paidAcceptanceFees = $applicantReportService->getPaidAcceptanceFees();
        $this->totalUTMEApplicants = $utmeService->getUTMEApplicants();
        $this->totalUTMERecommendedApplicants = $utmeService->getUTMERecommendedApplicants();
        $this->totalUTMEShortlistedApplicants = $utmeService->getUTMEShortlistedApplicants();
        $this->undergraduateSchoolFeesPaid = $utmeService->getUndergraduateSchoolFeesPayments()->count();
    }

    public function render()
    {
        return view('livewire.dashboards.admin-index', [
            'totalApplicants' => $this->totalApplicants,
            'notRecommendedApplicants' => $this->notRecommendedApplicants,
            'shortlistedApplicants' => $this->shortlistedApplicants,
            'paidAdmissionFees' => $this->paidAdmissionFees,
            'paidAcceptanceFees' => $this->paidAcceptanceFees,
            'undergraduateSchoolFeesPaid' => $this->undergraduateSchoolFeesPaid,
        ]);
    }
}
