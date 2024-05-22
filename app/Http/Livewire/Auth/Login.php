<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use App\Providers\RouteServiceProvider;

class Login extends Component
{
    public $email = '';
    public $password = '';


    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];



    public function login()
    {
        if (auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            $user = User::where(["email" => $this->email])->first();
            auth()->login($user);
            return redirect(RouteServiceProvider::HOME);
        } else {
            return $this->addError('email', trans('auth.failed'));
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
