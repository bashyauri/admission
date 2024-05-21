<?php

namespace App\Http\Livewire\Applications;

use App\Models\Grade;
use App\Models\Subject;
use Livewire\Component;
use App\Models\OlevelExam;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\OlevelGradeForm;
use App\Models\OlevelSubjectGrade;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class OlevelGrade extends Component
{
    use LivewireAlert;

    public OlevelGradeForm $form;
    public function save()
    {

        try {

            $this->form->store();
            $this->alert('success', 'Saved Successfully', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);
        }
        $this->redirect('olevel-grade');
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
