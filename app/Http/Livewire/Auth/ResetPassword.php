<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;


class ResetPassword extends Component
{
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $urlID = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed'
    ];

    public function mount($id)
    {
        $existingUser = User::find($id);
        $this->urlID = $existingUser->id;
    }

    public function resetPassword()
    {
        $this->validate();
        $existingUser = User::where('email', $this->email)->first();
        if ($existingUser && $existingUser->id == $this->urlID) {
            $existingUser->update([
                'password' => Hash::make($this->password)
            ]);
            redirect()->route('login')->with('status', 'Your password has been reset!');
        } else {
            return back()->with('email', "We could not find any user with that email address.");
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
