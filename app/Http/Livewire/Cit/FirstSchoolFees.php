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
        $this->schoolFees = $uTMEApplicantService->getUtmeFirstSchoolFeesPayment();
        dd($this->schoolFees);
    }
    public function render()
    {
        return view('livewire.cit.first-school-fees');
    }
}
