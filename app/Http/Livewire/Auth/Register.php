<?php

namespace App\Http\Livewire\Auth;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Programme;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;

class Register extends Component
{

    public $surname = '';
    public $firstName = '';
    public $middleName = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $programmes = '';
    public $programme_id = '';



    protected $rules = [
        'surname' => 'required|min:3',
        'firstName' => 'required|min:3',
        'middleName' => 'min:1',
        'phone' => 'required|unique:users,phone',
        'email' => 'required|email:rfc,dns|unique:users,email',
        'password' => 'required|min:6',
        'programme_id' => 'required'

    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'surname' => $this->surname,
            'firstname' => $this->firstName,
            'm_name' => $this->middleName,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'vpassword' => $this->password,
            'programme_id' => $this->programme_id,
        ]);
        event(new Registered($user));

        auth()->login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function mount()
    {
        $this->programmes = Programme::where('id', 6)->get();
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
