<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\StudentTransaction;

class StudentIndex extends Component
{
    #[Computed(persist: true)]
    public function transactions()
    {
        return StudentTransaction::where(['user_id' => auth()->id()])->get();
    }
    public function render()
    {
        return view('livewire.dashboards.student-index');
    }
}
