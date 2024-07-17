<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Rules\ValidateCurrentPassword;

class UpdateHodPasswordForm extends Form
{

    public $currentPassword = '';
    public $newPassword = '';
    public $confirmPassword = '';
    public function rules()
    {
        return [
            'currentPassword' => ['required', new ValidateCurrentPassword],
            'newPassword' => ['required', 'same:confirmPassword'],
        ];
    }
    public function updatePassword()
    {
        $this->validate();


        // Update password logic using your Laravel Auth system (e.g., User model)
        auth()->user()->update([
            'password' => Hash::make($this->newPassword),
            'vpassword' => $this->newPassword
        ]);
    }
}
