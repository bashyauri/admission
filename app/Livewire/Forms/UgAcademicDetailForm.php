<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use App\Enums\StudentLevel;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;

class UgAcademicDetailForm extends Form
{

    public function store(User $user, string $matricNumber)
    {
        // $this->validate();


        $user->academicDetail()->create([
            'matric_no' => $matricNumber,
            'department_id' => $user->proposedCourse->department_id,
            'programme_id' =>  $user->programme_id,
            'course_id' =>  $user->proposedCourse->course_id,
            'student_level_id' => StudentLevel::YEAR_ONE,
            'acad_session' => config('remita.settings.academic_session'),
        ]);
    }
}