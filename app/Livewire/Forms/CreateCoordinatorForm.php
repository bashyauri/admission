<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Enums\Role;
use App\Models\User;
use App\Models\Coordinator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateCoordinatorForm extends Form
{
    public $surName;

    public $firstName;
    public $password = '12345678';
    public $phone;

    public $email;

    public $department_id;
    public $course_id;
    public $student_level_id;
    public $academic_session;
    public $confirmationEmail;

    public $role;

    protected function rules(): array
    {
        return [
            'surName' => 'required',
            'firstName' => 'required',
            'email' => 'required|email|unique:users,email',
            'confirmationEmail' => 'required|email|same:email',
            'department_id' => 'required',
            'phone' => 'required|unique:users,phone',
        ];
    }
    public function store(): void
    {
        $this->validate();
        DB::transaction(function () {
            $user = User::create([
                'surname' => $this->surName,
                'firstname' => $this->firstName,
                'email' => $this->email,
                'role' => Role::COORDINATOR,
                'password' => Hash::make($this->password),
                'vpassword' => $this->password,
            ]);

            $deptId = is_array($this->department_id) ? ($this->department_id["value"] ?? null) : $this->department_id;
            $courseId = is_array($this->course_id) ? ($this->course_id["value"] ?? null) : $this->course_id;

            $this->storeCoordinator(
                $user, 
                $deptId,
                $courseId ?: null,
                $this->student_level_id ?: null,
                $this->academic_session ?: null
            );
        });
    }
    public function storeCoordinator(User $user, $department_id, $course_id = null, $student_level_id = null, $academic_session = null)
    {
        return Coordinator::create([
            'user_id' => $user->id,
            'department_id' => $department_id,
            'course_id' => $course_id,
            'student_level_id' => $student_level_id,
            'academic_session' => $academic_session,
        ]);
    }
}

