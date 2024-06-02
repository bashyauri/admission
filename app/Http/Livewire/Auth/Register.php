<?php

namespace App\Http\Livewire\Auth;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Programme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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


        DB::beginTransaction();

        try {
            $user = User::create([
                'surname' => $this->surname,
                'firstname' => $this->firstName,
                'm_name' => $this->middleName,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'vpassword' => $this->password,
                'programme_id' => $this->programme_id["value"],
            ]);





            try {
                event(new Registered($user));
                // Attempt to send the verification email
                // $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                // Log the error and set an error message

                Log::error('Email verification failed: ' . $e->getMessage());
                DB::rollBack();

                return redirect()->back()->with('error', 'Registration successful, but the verification email could not be sent. Please try again later.');
            }

            // Commit the transaction if no exceptions were thrown
            DB::commit();
            auth()->login($user);

            // Set session variable for first time email verification
            return redirect('/email/verify')->with('success', 'Registration successful! Please check your email for the verification link.')->with('first_verification', true);
        } catch (\Exception $e) {
            // Rollback the transaction and log the error if something goes wrong
            DB::rollBack();
            dd('bznnxz1');
            Log::error('User registration failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was a problem with your registration. Please try again.');
        }
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
