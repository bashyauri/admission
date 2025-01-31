<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Enums\Role;
use App\Models\User;
use App\Models\Coordinator;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
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


            $this->storeCoordinator($user, $this->department_id["value"]);
        });
    }
    public function storeCoordinator(User $user, $department_id)
    {
        return   Coordinator::create([
            'user_id' => $user->id,
            'department_id' => $department_id,
        ]);
    }
}