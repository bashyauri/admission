<?php

namespace App\Http\Livewire\Admin\Applicants;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Services\Report\UtmeService;

class ImportedApplicants extends Component
{
    public $importedApplicants;
    public function mount(UtmeService $utmeService): void
    {
        $this->importedApplicants = $utmeService->getImportedApplicants();
    }
    #[Computed()]
    public function allApplicants()
    {
        return $this->importedApplicants;
    }
    public function render()
    {
        return view('livewire.admin.applicants.imported-applicants');
    }
}