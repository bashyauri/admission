<?php

namespace App\Http\Livewire\Cit;

use Livewire\Component;
use App\Models\Transaction;
use Livewire\Attributes\Locked;
use App\Enums\ApplicationStatus;
use App\Enums\TransactionStatus;
use App\Services\PaymentService;

class PaidAcceptanceFees extends Component
{
    #[Locked]
    public $paidAcceptanceFees;

    public function mount(PaymentService $paymentService)
    {
        abort(403, 'School Fees Addition is in progress');
        $this->paidAcceptanceFees = $paymentService->getPaidAcceptanceFeePayments();
    }

    public function render()
    {
        return view('livewire.cit.paid-acceptance-fees');
    }
}
