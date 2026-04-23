<?php

declare(strict_types=1);

namespace App\Http\Livewire\Auth;



use Livewire\Component;

use App\Livewire\Forms\DegreeRegisterForm;


class DegreeRegister extends Component
{
    public DegreeRegisterForm $form;
    public function register()
    {
        $user = $this->form->store();
        $rateLimitKey = 'send-email-verification:' . $user->id;
        $maxAttempts = 3;
        $decaySeconds = 600; // 10 minutes

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            return redirect()->back()->with('error', 'Too many verification emails sent. Please try again in ' . $seconds . ' seconds.');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, $decaySeconds);

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
