<?php

namespace App\Http\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use App\Services\TransactionService;

class AdmissionInvoice extends Component
{


    public $transactionId;
    public $amount;
    public $description;
    public $serviceid;
    protected $transactionService;


    public function mount()
    {
        $this->transactionService = new TransactionService();
        if ($this->transactionService->hasInvoice(config('remita.admission.description'))) {

            to_route('payment');
        }
        $this->transactionId = $this->transactionService->generateTransactionId("WUFPDHS");
        $this->amount = config('remita.admission.fee');
        $this->description = config('remita.admission.description');
        $this->serviceid = config('remita.settings.serviceid');
    }




    public function render()
    {
        return view('livewire.transactions.admission-invoice');
    }
}
