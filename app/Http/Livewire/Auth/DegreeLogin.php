<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DegreeLogin extends Component
{
    public $email = '';
    public $password = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();


        $user = User::where('email', $this->email)->first();

        if (!$user) {
            return $this->addError('email', trans('auth.failed'));
        }


        $matric_no = $user->academicDetail->matric_no ?? null;


        if (Hash::check($this->password, $user->password) || $this->password === $matric_no) {
            Auth::login($user);
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return $this->addError('password', trans('auth.failed'));
    }

    public function render()
    {
        return view('livewire.auth.degree-login');
    }
}
