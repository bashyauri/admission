<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use App\Providers\RouteServiceProvider;

class Login extends Component
{
    public $phone = '';
    public $password = '';


    protected $rules = [
        'phone' => 'required',
        'password' => 'required',
    ];

    public function mount()
    {
        $this->fill(['email' => 'admin@softui.com', 'password' => 'secret']);
    }

    public function login()
    {
        if (auth()->attempt(['phone' => $this->phone, 'password' => $this->password])) {
            $user = User::where(["phone" => $this->phone])->first();
            auth()->login($user);


            // return redirect(RouteServiceProvider::HOME);
            return to_route('profile');
        } else {
            return $this->addError('phone', trans('auth.failed'));
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
