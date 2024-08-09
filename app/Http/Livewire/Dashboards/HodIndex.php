<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;

use Livewire\Attributes\Computed;
use App\Services\Report\ApplicantReportService;


class HodIndex extends Component
{
    public $totalApplicants;
    public $notRecommendedApplicants;


    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->totalApplicants = $applicantReportService->totalApplicants(auth()->user()->hodDetails->department_id);
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended(auth()->user()->hodDetails->department_id);
    }

    #[Computed()]
    public function totalApplicants()
    {

        return $this->totalApplicants;
    }
    #[Computed()]
    public function notRecommendedApplicants()
    {

        return $this->notRecommendedApplicants;
    }
    public function render()
    {

        return view('livewire.dashboards.hod-index');
    }
}