<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\ProposedCourse;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use App\Services\AcademicSessionService;

class ProposedCourseForm extends Form
{
    #[Validate('required', message: "please select department")]
    public $departmentID;
    #[Validate('required', message: "please select your course")]
    public $courseID;

    public function setProposedCourse($course)
    {

        $this->departmentID = $course->department_id;
        $this->courseID = $course->course_id;
    }
    public function storeUnderGraduate($jambData)
    {
        $this->validate();
        ProposedCourse::updateOrCreate(
            ['user_id' => auth()->id()],
            [

                'department_id' => $this->departmentID,
                'course_id' => $this->courseID,
                'jamb_no' => $jambData->jamb_no,
                'course' => $jambData->course,
                'jamb_score' => $jambData->jamb_score,
                'acad_session_id' => app(AcademicSessionService::class)->getAcademicSession(Auth::user()),

            ]
        );
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
