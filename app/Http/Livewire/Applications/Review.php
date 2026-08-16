<?php

namespace App\Http\Livewire\Applications;

use App\Models\Lga;
use App\Models\State;
use App\Models\Course;
use Livewire\Component;
use App\Models\Programme;
use App\Models\Department;
use App\Models\ProposedCourse;
use App\Models\Transaction;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class Review extends Component
{
    use LivewireAlert;
    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }

        if (ProposedCourse::where('user_id', auth()->user()->id)->count() === 0) {
            to_route('proposed-course')->with('warning', 'Please select a course');
        }
    }
    public function render()
    {
        $transaction = Transaction::where(['user_id' => auth()->id(), 'resource' => config('remita.admission.description')])->first();

        $lga = Lga::find(auth()->user()->lga_id);
        $state = State::find(auth()->user()->state_id);
        $programme = Programme::find(auth()->user()->programme_id);
        $department = auth()->user()->proposedCourse ? Department::find(auth()->user()->proposedCourse->department_id) : null;
        $course = auth()->user()->proposedCourse ? Course::find(auth()->user()->proposedCourse->course_id) : null;

        return view('livewire.applications.review', [
            'RRR' => $transaction->RRR,
            'lga' => $lga?->name ?? 'N/A',
            'state' => $state?->name ?? 'N/A',
            'programme' => $programme?->name ?? 'N/A',
            'department' => $department?->name ?? 'N/A',
            'course' => $course?->name ?? 'N/A'
        ]);
    }
}
