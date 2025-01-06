<?php

namespace App\Http\Livewire\Admin\Applicants\Utme;

use Livewire\Component;
use App\Enums\ApplicationStatus;
use App\Services\Report\UtmeService;

class ShortlistedApplicants extends Component
{
    public $shortlistedApplicants;
    public function mount(UtmeService $utmeService): void
    {
        $this->shortlistedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::SHORTLISTED->toString());
    }
    public function render()
    {
        return view('livewire.admin.applicants.utme.shortlisted-applicants');
    }
}