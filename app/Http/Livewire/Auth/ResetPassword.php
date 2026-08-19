<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;


class ResetPassword extends Component
{
    public $email = '';
    public $code = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'email' => 'required|email',
        'code' => 'required|string|size:6',
        'password' => 'required|min:6|confirmed'
    ];

    public function mount()
    {
        // Handled via session/email and OTP verification
    }

    public function resetPassword()
    {
        $this->validate();
        
        $user = User::where('email', $this->email)->first();
        
        if (!$user) {
            session()->flash('email', "We could not find any user with that email address.");
            return;
        }

        if ($user->password_reset_code !== $this->code) {
            session()->flash('email', "The reset code is incorrect.");
            return;
        }

        if (now()->greaterThan($user->password_reset_code_expires_at)) {
            session()->flash('email', "The reset code has expired. Please request a new one.");
            return;
        }

        $user->forceFill([
            'password' => Hash::make($this->password),
            'vpassword' => $this->password,
            'password_reset_code' => null,
            'password_reset_code_expires_at' => null,
        ])->save();

        session()->flash('status', 'Your password has been reset successfully!');
        
        if ($user->isUndergraduate()) {
            return redirect()->route('degree-login');
        }

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
