<?php

namespace App\Livewire\Forms;

use App\Models\StudentCourse;
use Livewire\Attributes\Validate;
use Livewire\Form;

class StudentCourseForm extends Form
{
    public $code;

    public $title;
    public $units;

    public $level_id;


    protected function rules(): array
    {
        return [


            'code' => 'required|unique:student_courses',
            'title' => 'required',
            'units' => 'required',
            'level_id' => 'required',

        ];
    }
    public function store()
    {
        $this->validate();

        $lastDigit = substr($this->code, -1);
        $semester = $lastDigit % 2 === 0 ? 2 : 1;

        StudentCourse::create([
            'code' => $this->code,
            'title' => $this->title,
            'units' => $this->units,
            'student_level_id' => (int)$this->level_id,
            'semester' => (int)$semester


        ]);
    }
}
