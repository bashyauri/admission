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
        abort_unless(auth()->user()->isAdmin() || auth()->user()->role === 'cit' || auth()->user()->hasCapability('cit'), 403, 'You must be logged in as an Admin or CIT staff to view this page');
        $this->schoolFees = $uTMEApplicantService->getUtmeFirstSchoolFeesPayment();
    }
    public function render()
    {
        return view('livewire.cit.first-school-fees');
    }
}
