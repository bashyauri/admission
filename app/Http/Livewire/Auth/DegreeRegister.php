<?php

declare(strict_types=1);

namespace App\Http\Livewire\Auth;


use Throwable;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use App\Livewire\Forms\DegreeRegisterForm;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;

class DegreeRegister extends Component
{
    public DegreeRegisterForm $form;
    public function register()
    {

        $user = $this->form->store();
        try {
            $user->sendEmailVerificationNotification();
            return redirect()->route('degree-login')
                ->with('success', 'An Email Verification has been sent to your Email. Please check your inbox/spam folder.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send verification email. Please try again.');
        }
    }
    public function render()
    {
        return view('livewire.auth.degree-register');
    }
}
