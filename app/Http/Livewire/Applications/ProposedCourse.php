<?php

namespace App\Http\Livewire\Applications;

use App\Models\Course;
use App\Models\Programme;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProposedCourse extends Component
{

    #[Computed()]
    public function departments()
    {
        $program = Programme::findOrFail(auth()->user()->programme->id);

        return $program->departments;
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
