<?php

namespace App\Http\Livewire\Transactions;

use App\Models\User;
use Livewire\Component;
use App\Models\Transaction;
use App\Services\TransactionService;

class AcceptanceInvoice extends Component
{
    public $transactionId;
    public $amount;
    public $description;
    public $serviceid;
    protected $transactionService;
    public function mount()
    {

        $this->transactionService = new TransactionService();
        if ($this->transactionService->hasInvoice(config('remita.acceptance.description'))) {
            $data = Transaction::where([
                'user_id' => auth()->user()->id,
                'resource' => config('remita.acceptance.description')
            ])->first();
            to_route('payment', ['transaction' => $data])->with('success', $data->status);
        }
        $this->transactionId = $this->transactionService->generateTransactionId("WUFPDHS");
        $this->amount = config('remita.acceptance.fee');
        $this->description = config('remita.acceptance.description');
        $this->serviceid = config('remita.settings.serviceid');
    }
    public function render()
    {
        $this->authorize('generateAcceptanceInvoice', User::class);
        return view('livewire.transactions.acceptance-invoice');
    }
}
