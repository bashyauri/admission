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
            $this->alert('warning', 'Course not Selected', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'showConfirmButton' => true,
                'onConfirmed' => '',
                'width' => '500',
                'confirmButtonText' => 'take me',
                'text' => 'Select Course first',
            ]);
            to_route('proposed-course');
        }
    }
    public function render()
    {
        $transaction = Transaction::where(['user_id' => auth()->id(), 'resource' => config('remita.admission.description')])->first();


        return view('livewire.applications.review', [
            'RRR' => $transaction->RRR,
            'lga' => Lga::find(auth()->user()->lga_id)->name,
            'state' => State::find(auth()->user()->state_id)->name,
            'programme' => Programme::find(auth()->user()->programme_id)->name,
            'department' => Department::find(auth()->user()->proposedCourse?->department_id)->name,
            'course' => Course::find(auth()->user()->proposedCourse?->course_id)->name
        ]);
    }
}