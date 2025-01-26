<?php

namespace App\Http\Livewire\Cit;

use App\Services\UTMEApplicantService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FirstSchoolFees extends Component
{
    #[Locked]
    public $schoolFees;


    public function mount(UTMEApplicantService $uTMEApplicantService): void
    {
        abort_unless(auth()->user()->isCit(), 403, 'You must be logged in as a CIT Staff to view this page');
        $this->schoolFees = $uTMEApplicantService->getUtmeFirstSchoolFeesPayment();
    }
    public function render()
    {
        return view('livewire.cit.first-school-fees');
    }
}
