<?php

namespace App\Livewire\Forms;

use App\Models\ProposedCourse;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProposedCourseForm extends Form
{
    #[Validate('required', message: "please select department")]
    public $departmentID;
    #[Validate('required', message: "please select your course")]
    public $courseID;

    public function setProposedCourse(ProposedCourse $course)
    {
        $this->departmentID = $course->department_id;
        $this->courseID = $course->course_id;
    }

    public function store()
    {
        $this->validate();
        ProposedCourse::updateOrCreate(
            ['user_id' => auth()->id()],
            [

                'department_id' => $this->departmentID,
                'course_id' => $this->courseID,
            ]
        );
    }
}
