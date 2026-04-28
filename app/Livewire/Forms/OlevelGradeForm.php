<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class OlevelGradeForm extends Form
{
    #[Validate('required')]
    public $subjectName = '';

    #[Validate('required')]
    public $examName = '';

    #[Validate('required')]
    public $grade = '';

    public function store()
    {
        $this->validate();

        auth()->user()->olevelSubjectGrades()->create([
            'subject_name' => $this->subjectName,
            'exam_name'    => $this->examName,
            'grade'        => $this->grade,
        ]);

        // Optional: You can reset here, but better to reset from component
    }

    /**
     * Reset all form fields
     */
    public function resetForm()
    {
        $this->reset(['subjectName', 'examName', 'grade']);
    }
}
