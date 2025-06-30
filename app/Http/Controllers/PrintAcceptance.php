<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Programme;
use App\Models\Department;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\ProposedCourse;

class PrintAcceptance extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validationResult = $this->validateAcceptance();
        if ($validationResult !== true) {
            return $validationResult; // Return redirect if checks fail
        }

        $transaction = Transaction::where(['user_id' => auth()->id(), 'resource' => config('remita.acceptance.description')])->first();

        return view('print.print-acceptance', [
            'RRR' => $transaction->RRR,
            'programme' => Programme::find(auth()->user()->programme_id)->name,
            'department' => Department::find(auth()->user()->proposedCourse?->department_id)->name,
            'course' => Course::find(auth()->user()->proposedCourse?->course_id)->name
        ]);
    }
    private function validateAcceptance()
    {
        if (!auth()->user()->hasPaid(config('remita.acceptance.description'))) {
            return to_route('transactions')->with('error', 'You have not made payment');
        }

        if (ProposedCourse::where('user_id', auth()->user()->id)->count() === 0) {
            return to_route('proposed-course')->with('error', 'You have not filled proposed course:');
        }

        return true;
    }
}
