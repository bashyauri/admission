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

    protected $messages = [
        'email.required' => 'Enter your email address.',
        'email.email' => 'Enter a valid email address.',
        'email.unique' => 'An account with this email already exists. Sign in or use a different email address.',
        'password.required' => 'Create a password for your account.',
        'password.min' => 'Your password must be at least 8 characters long.',
        'confirm_password.required' => 'Enter your password again to confirm it.',
        'confirm_password.same' => 'The passwords do not match. Check both fields and try again.',
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
