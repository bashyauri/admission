<?php

declare(strict_types=1);

namespace App\Http\Livewire\Transactions;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Enums\TransactionStatus;
use App\Services\PaymentService;
use App\Models\StudentTransaction;
use Illuminate\Contracts\View\View;
use App\Services\StudentTransactionService;

class UtmeSchoolFeesInvoice extends Component
{
    #[Locked]
    public $user;
    #[Locked]
    public $currentLevel;
    #[Locked]
    public $description;
    #[Locked]
    public $amount;
    #[Locked]
    public $transactionId;
    private $paymentService;
    private $transactionService;


    public function mount(User $user): void
    {
        $this->paymentService = new PaymentService();
        $this->transactionService = new StudentTransactionService();
        $this->user = $user;
        $this->description = config('remita.schoolfees.ug_schoolfees_description');





        if ($data = $this->paymentService->getStudentInvoice($this->user->id, $this->description)) {


            to_route('cit.payment', ['studenttransaction' => $data])->with('success', $data->status);
        }

        $this->currentLevel = $this->paymentService->getUgStudentLevel($this->user->id);
        $paymentDetail = $this->paymentService->getStudentFee($this->user->id);
        $this->amount = $paymentDetail->fee_amount;
        $this->transactionId = $this->transactionService->generateTransactionId("WUFPDHS");
    }


    public function render(): View
    {
        return view('livewire.transactions.utme-school-fees-invoice');
    }
}
