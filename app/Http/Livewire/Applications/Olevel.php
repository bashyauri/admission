<?php

namespace App\Http\Livewire\Applications;

use App\Livewire\Forms\OlevelExamForm;
use App\Models\OlevelExam;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Olevel extends Component
{
    use LivewireAlert;

    public OlevelExamForm $form;
    public $editingOlevelExamId;

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
                'timer' => 3000,
                'toast' => true,
            ]);
            to_route('olevel');
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            to_route('olevel');
        }
    }

    #[Computed()]
    public function olevelExams()
    {
        return OlevelExam::all();
    }

    public function edit($examId)
    {
        $this->editingOlevelExamId = $examId;
        $exam = OlevelExam::findOrFail($examId);
        $this->form->examName = $exam->exam_name;
        $this->form->examNumber = $exam->exam_name;
        $this->form->examYear = $exam->exam_year;
    }
    public function update()
    {
        try {

            $this->form->update($this->editingOlevelExamId);
            $this->alert('success', 'Updated Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Update failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }

        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.applications.olevel');
    }
}
