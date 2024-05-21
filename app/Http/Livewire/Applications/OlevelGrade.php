<?php

namespace App\Http\Livewire\Applications;

use App\Models\Grade;
use App\Models\Subject;
use Livewire\Component;
use App\Models\OlevelExam;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\OlevelGradeForm;
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
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
        return to_route('olevel-grade');
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
    public function render()
    {
        return view('livewire.applications.olevel-grade');
    }
}
