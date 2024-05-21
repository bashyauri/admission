<?php

namespace App\Livewire\Forms;

use App\Models\OlevelExam;
use Livewire\Form;

use Livewire\Attributes\Validate;

class OlevelExamForm extends Form
{
    #[Validate('required')]
    public $examName;
    #[Validate('required')]
    public $examNumber;
    #[Validate('required')]
    public $examYear;
    public function store()
    {
        $this->validate();
        auth()->user()->olevelExams()->create([
            'exam_name' => $this->examName,
            'exam_number' => $this->examNumber,
            'exam_year' => $this->examYear,
        ]);
    }
    public function update($examId)
    {
        OlevelExam::find($examId)->update(
            [
                'exam_name' => $this->examName,
                'exam_number' => $this->examNumber,
                'exam_year' => $this->examYear,
            ]
        );
    }
}
