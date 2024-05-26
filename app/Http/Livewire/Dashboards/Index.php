<?php

namespace App\Http\Livewire\Dashboards;

use App\Models\User;
use Livewire\Component;
use App\Models\Transaction;
use Livewire\Attributes\Computed;

class Index extends Component
{
    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
    }
    #[Computed(persist: true)]
    public function transactions()
    {
        return Transaction::where(['user_id' => auth()->id()])->get();
    }
    public function render()
    {

        return view('livewire.dashboards.index');
    }
}
