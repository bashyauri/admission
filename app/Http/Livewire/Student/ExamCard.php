<?php

namespace App\Http\Livewire\Student;

use App\Services\CourseRegistrationService;
use Livewire\Component;

class ExamCard extends Component
{
    public $user;
    public $student;
    public $studentLevelId;
    public $departmentId;
    public $courses;
    public function mount()
    {
        $this->user = auth()->user();
        $this->student = $this->user->academicDetail;
    }
    public function getExamcards(CourseRegistrationService $service)
    {
        return  $service->getExamCard($this->student->id);
    }

    public function render()
    {

        return view(
            'livewire.student.exam-card',
            [
                'examCards' => $this->getExamcards(new CourseRegistrationService()),
            ]
        );
    }
}
