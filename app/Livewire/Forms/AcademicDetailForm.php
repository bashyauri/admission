<?php

namespace App\Livewire\Forms;

use App\Enums\Role;
use App\Enums\StudentLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Form;


class AcademicDetailForm extends Form
{

    public $matricNumber;
    protected function rules()
    {
        return [

            'matricNumber' => 'required|unique:academic_details,matric_no|regex:/^[A-Za-z]{4}\/[A-Za-z]{3}\/[A-Za-z]{2}\/\d{2}\/\d{4}$/',

        ];
    }
    public function messages()
    {
        return [
            'matricNumber.regex' => 'The :attribute should be in the format of WUFP/PGD/ED/20/0041',
        ];
    }
    public function store()
    {
        $this->validate();
        $user = auth()->user();
        DB::transaction(function () use ($user) {
            $user->academicDetail()->create([
                'matric_no' => $this->matricNumber,
                'department_id' => $user->proposedCourse->department_id,
                'programme_id' =>  $user->programme_id,
                'course_id' =>  $user->proposedCourse->course_id,
                'student_level_id' => StudentLevel::YEAR_ONE,
            ]);
            $this->changeToStudent($user);
        });
    }
    private function changeToStudent(User $user)
    {
        $user->update([
            'role' => Role::STUDENT
        ]);
    }
}