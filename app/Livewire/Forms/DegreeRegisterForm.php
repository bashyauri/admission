<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use App\Enums\ProgrammesEnum;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;

class DegreeRegisterForm extends Form
{
    public $email, $password, $confirm_password;
    protected $rules = [
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'confirm_password' => 'required|same:password',
    ];
    public function store(): User
    {
        $this->validate();

        return $user = User::create([
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'programme_id' => ProgrammesEnum::Undergraduate,
            'vpassword' => $this->password
        ]);
    }
}
