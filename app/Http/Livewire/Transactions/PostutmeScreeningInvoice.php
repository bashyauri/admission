<?php

namespace App\Http\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Services\TransactionService;

class PostutmeScreeningInvoice extends Component
{

    public $transactionId;
    public $amount;
    public $description;
    public $serviceid;
    protected $transactionService;


    public function mount()
    {
        $this->transactionService = new TransactionService();
        if ($this->transactionService->hasInvoice(config('remita.postutme.description'))) {
            $data = Transaction::where([
                'user_id' => auth()->user()->id,
                'resource' => config('remita.postutme.description')
            ])->first();

            to_route('payment', ['transaction' => $data])->with('success', $data->status);
        }
        $this->transactionId = $this->transactionService->generateTransactionId("WUFPDHS");
        $this->amount = config('remita.postutme.fee');
        $this->description = config('remita.postutme.description');
        $this->serviceid = config('remita.settings.serviceid');
    }


    public function render()
    {
        return view('livewire.transactions.postutme-screening-invoice');
    }
}
