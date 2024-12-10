<?php

namespace App\Http\Livewire\Admin\Applicants\Utme;

use App\Enums\ApplicationStatus;
use Livewire\Component;
use App\Services\Report\UtmeService;

class RecommendedApplicants extends Component
{
    public $recommendedApplicants;
    public function mount(UtmeService $utmeService)
    {
        $this->recommendedApplicants = $utmeService->getAllUTMEApplicants(ApplicationStatus::RECOMMENDED->toString());
    }
    public function render()
    {
        return view('livewire.admin.applicants.utme.recommended-applicants');
    }
}