<?php

namespace App\Http\Livewire\Admin\Applicants\Utme;

use App\Enums\ApplicationStatus;
use App\Services\Report\UtmeService;
use Livewire\Component;

class AllApplicants extends Component
{

    public $allApplicants;
    public function mount(UtmeService $utmeService): void
    {
        $this->allApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::PENDING);
    }
    public function render()
    {
        return view('livewire.admin.applicants.utme.all-applicants');
    }
}