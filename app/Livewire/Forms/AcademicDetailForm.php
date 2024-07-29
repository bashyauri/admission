<?php

namespace App\Livewire\Forms;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Form;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;

class AcademicDetailForm extends Form
{

    public $matricNumber;
    protected function rules()
    {
        return [

            'matricNumber' => 'required|unique:academic_details,matric_no|regex:/^[A-Z]{4}\/[A-Z]{3}\/[A-Z]{2}\/\d{2}\/\d{4}$/',

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
                'department_id' => auth()->user()->proposedCourse->department_id,
                'programme_id' => auth()->user()->programme_id,
                'course_id' => auth()->user()->proposedCourse->course_id,
                'level' => 'PGD 1',
            ]);
            $this->changeRole($user);
        });
    }
    private function changeRole(User $user)
    {
        $user->update([
            'role' => Role::STUDENT
        ]);
    }
}
