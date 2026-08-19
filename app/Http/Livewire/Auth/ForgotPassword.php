<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPassword;

class ForgotPassword extends Component
{

    use Notifiable;

    public $email = '';

    protected $rules = [
        'email' => 'required|email',
    ];

    public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function recoverPassword()
    {
        $this->validate();
        $user = User::where('email', $this->email)->first();
        if ($user) {
            $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $user->forceFill([
                'password_reset_code' => $code,
                'password_reset_code_expires_at' => now()->addMinutes(15),
            ])->save();

            $user->notify(new ResetPassword($code));

            session()->flash('status', "A 6-digit reset code has been sent to your email.");
            return redirect()->route('reset-password');
        } else {
            session()->flash('email', "We could not find any user with that email address.");
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
