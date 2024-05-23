<?php

namespace App\Http\Livewire\Applications;

use App\Models\Course;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProposedCourse extends Component
{

    #[Computed()]
    public function departments()
    {
        return Course::where('programme_id', auth()->user()->programme_id)->get();
    }
    #[Computed()]
    public function courses()
    {
        return Course::where('programme_id', auth()->user()->programme_id)->get();
    }
    public function render()
    {
        return view('livewire.applications.proposed-course');
    }
}
