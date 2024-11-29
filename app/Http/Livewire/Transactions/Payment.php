<?php

namespace App\Http\Livewire\Transactions;

use App\Enums\ProgrammesEnum;
use Livewire\Component;
use App\Models\Transaction;
use Livewire\Attributes\Computed;

class Payment extends Component
{
    public function mount()
    {
        if (auth()->user()->isUndergraduate()) {
            to_route('search-utme');
        }
    }
    public string $selected;
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
