<?php

namespace App\Http\Controllers;

use App\Models\Lga;
use App\Models\State;
use App\Models\Course;
use App\Models\Programme;
use App\Models\Department;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\ProposedCourse;

class PrintForm extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
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
        $transaction = Transaction::where(['user_id' => auth()->id(), 'resource' => config('remita.admission.description')])->first();
        return view('print.print-form', [
            'RRR' => $transaction->RRR,
            'lga' => Lga::find(auth()->user()->lga_id)->name,
            'state' => State::find(auth()->user()->state_id)->name,
            'programme' => Programme::find(auth()->user()->programme_id)->name,
            'department' => Department::find(auth()->user()->proposedCourse?->department_id)->name,
            'course' => Course::find(auth()->user()->proposedCourse?->course_id)->name
        ]);
    }
}