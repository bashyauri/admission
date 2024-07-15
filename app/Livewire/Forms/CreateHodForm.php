<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use App\Models\HodUser;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateHodForm extends Form
{


    public $surName;

    public $firstName;
    public $password = '12345678';

    public $email;
    public $department_id;
    public $confirmationEmail;

    protected function rules()
    {
        return [

            'email' => 'required|email|unique:users,email',
            'confirmationEmail' => 'required|email|same:email',
            'surName' => 'required',
            'firstName' => 'required',
            'department_id' => 'required',
        ];
    }
    public function store()
    {
        $this->validate();

        $user = User::create([
            'surname' => $this->surName,
            'firstname' => $this->firstName,
            'email' => $this->email,
            'role' => 'hod',
            'password' => Hash::make($this->password),
            'vpassword' => $this->password,
        ]);


        $this->storeHod($user, $this->department_id["value"]);
    }
    public function storeHod(User $user, $department_id)
    {
        return   HodUser::create([
            'user_id' => $user->id,
            'department_id' => $department_id,
        ]);
    }
}