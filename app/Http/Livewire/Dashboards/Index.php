<?php

namespace App\Http\Livewire\Dashboards;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
    }
    public function render()
    {

        return view('livewire.dashboards.index');
    }
}
