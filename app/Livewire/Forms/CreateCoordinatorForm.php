<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;

class CreateCoordinatorForm extends Form
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
            'role' => 'required',

        ];
    }
    public function store()
    {
        $this->validate();

        $user = User::create([
            'surname' => $this->surName,
            'firstname' => $this->firstName,
            'email' => $this->email,
            'role' => $this->role["value"],
            'password' => Hash::make($this->password),
            'vpassword' => $this->password,
        ]);


        $this->storeCoordinator($user, $this->department_id["value"]);
    }
    public function storeCoordinator(User $user, $department_id)
    {
        return   HodUser::create([
            'user_id' => $user->id,
            'department_id' => $department_id,
        ]);
    }
}