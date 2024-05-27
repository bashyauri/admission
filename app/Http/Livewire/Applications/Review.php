<?php

namespace App\Http\Livewire\Applications;

use App\Models\Lga;
use App\Models\State;
use App\Models\Transaction;
use Livewire\Component;

class Review extends Component
{
    public function render()
    {
        $transaction = Transaction::where(['user_id' => auth()->id(), 'resource' => config('remita.admission.description')])->first();


        return view('livewire.applications.review', [
            'RRR' => $transaction->RRR,
            'lga' => Lga::find(auth()->user()->lga_id)->name,
            'state' => State::find(auth()->user()->state_id)->name
        ]);
    }
}