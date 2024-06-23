<?php

namespace App\Http\Livewire\Dashboards;

use App\Models\User;
use Livewire\Component;
use App\Models\ProposedCourse;
use Livewire\Attributes\Computed;
use App\Services\Report\StudentReportService;


class HodIndex extends Component
{


    #[Computed()]
    public function totalApplicants()
    {
        return ProposedCourse::where(['department_id' => '1', 'academic_session' => config('remita.settings.academic_session')])->count();
    }
    public function render()
    {

        return view('livewire.dashboards.hod-index');
    }
}