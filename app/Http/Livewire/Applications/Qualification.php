<?php

namespace App\Http\Livewire\Applications;

use App\Models\School;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Qualification extends Component
{
    #[Computed()]
    public function certificates()
    {
        return School::get(['certificate_name']);
    }
    public function render()
    {
        return view('livewire.applications.qualification');
    }
}
