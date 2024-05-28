<?php

namespace App\Http\Livewire\Applications;

use App\Models\Grade;
use App\Models\Subject;
use Livewire\Component;
use App\Models\OlevelExam;
use Livewire\Attributes\Computed;
use App\Models\OlevelSubjectGrade;
use App\Livewire\Forms\OlevelGradeForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;

class OlevelGrade extends Component
{
    use LivewireAlert;

    public OlevelGradeForm $form;

    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
    }
    public function save()
    {

        try {

            $this->form->store();
            $this->alert('success', 'Saved Successfully', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);
            return to_route('olevel-grade');
        } catch (ValidationException $e) {

            // Display validation errors
            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', "$errorMessages", [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed'

            ]);


            // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
            $this->redirect(route('olevel-grade'));
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return to_route('olevel-grade');
        }
    }
    public function confirmed()
    {

        $this->redirect(route('olevel-grade'));
    }


    #[Computed()]
    public function exams()
    {
        return OlevelExam::all();
    }
    #[Computed()]
    public function subjects()
    {
        return Subject::all();
    }
    #[Computed()]
    public function grades()
    {
        return Grade::all();
    }
    #[Computed()]
    public function subjectGrades()
    {
        return OlevelSubjectGrade::latest()->get();
    }
    public function delete(OlevelSubjectGrade $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete');
            return;
        }

        $this->alert('success', 'Deleted Successfully', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        return view('livewire.applications.olevel-grade');
    }
}