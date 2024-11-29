<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Providers\RouteServiceProvider;

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
        if (auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            $user = User::where(["email" => $this->email])->first();
            auth()->login($user);

            return redirect()->intended(RouteServiceProvider::HOME);
        } else {
            return $this->addError('email', trans('auth.failed'));
        }
    }
    public function render()
    {
        return view('livewire.auth.degree-login');
    }
}
