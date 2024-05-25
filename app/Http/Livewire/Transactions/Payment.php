<?php

namespace App\Http\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use Livewire\Attributes\Computed;

class Payment extends Component
{
    #[Computed(persist: true)]
    public function transactions()
    {
        return Transaction::where(['user_id' => auth()->id()])->get();
    }
    public function render()
    {
        return view('livewire.transactions.payment');
    }
}
