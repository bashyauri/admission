<?php

namespace App\Http\Livewire\Cit;

use App\Enums\ApplicationStatus;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\PaymentService;
use Livewire\Component;

class PaidAcceptanceFees extends Component
{
    public $paidAcceptanceFees;

    public function mount(PaymentService $paymentService)
    {
        abort_unless(auth()->user()->isCit(), 403, 'You must be logged in as a CIT Staff to view this page');
        $this->paidAcceptanceFees = $paymentService->getPaidAcceptanceFeePayments();
    }

    public function render()
    {
        return view('livewire.cit.paid-acceptance-fees');
    }
}
