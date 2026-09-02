<?php

namespace App\Livewire\Forms;

use App\Enums\Role;
use Livewire\Form;
use App\Models\User;
use App\Models\HodUser;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateUserForm extends Form
{


    public $surName;

    public $firstName;
    public $password = '12345678';

    public $email;
    public $department_id;
    public $confirmationEmail;

    public $role;

    protected function rules()
    {
        return [

            'email' => 'required|email|unique:users,email',
            'confirmationEmail' => 'required|email|same:email',
            'surName' => 'required',
            'firstName' => 'required',
            'department_id' => 'required',
            'role' => 'required|in:hod,admin,cit,coordinator,idcard_officer,lecturer,exam_officer',

        ];
    }
    public function store()
    {
        $this->validate();

        $user = User::create([
            'surname' => $this->surName,
            'firstname' => $this->firstName,
            'email' => $this->email,
            'role' => $this->role,
            'password' => Hash::make($this->password),
            'vpassword' => $this->password,
        ]);

        // Only create HodUser if the role is HOD
        if ($this->role === 'hod') {
            $this->storeHod($user, $this->department_id);
        }
    }

    public function storeHod(User $user, $department_id)
    {
        return HodUser::create([
            'user_id' => $user->id,
            'department_id' => $department_id,
        ]);
    }
}