<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;

use Livewire\Attributes\Computed;
use App\Services\Report\ApplicantReportService;


class HodIndex extends Component
{
    public $totalApplicants;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $this->totalApplicants = $applicantReportService->totalApplicants(auth()->user()->hodDetails->department_id);
    }

    #[Computed()]
    public function totalApplicants()
    {

        return $this->totalApplicants;
    }
    public function render()
    {

        return view('livewire.dashboards.hod-index');
    }
}
