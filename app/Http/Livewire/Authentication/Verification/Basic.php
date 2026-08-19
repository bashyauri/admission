<?php

namespace App\Http\Livewire\Authentication\Verification;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class Basic extends Component
{
    public string $code = '';

    protected array $rules = [
        'code' => 'required|string|size:6',
    ];

    public function verify()
    {
        $this->validate();

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('dashboard/analytics');
        }

        if ($user->email_verification_code !== $this->code) {
            $this->addError('code', 'The verification code you entered is invalid.');
            session()->flash('error', 'The verification code you entered is invalid. Please check the code sent to your email.');
            return;
        }

        if (now()->greaterThan($user->email_verification_code_expires_at)) {
            $this->addError('code', 'The verification code has expired.');
            session()->flash('error', 'The verification code has expired. Please click "Resend code" below to get a fresh code.');
            return;
        }

        // Verify the user
        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ])->save();

        session()->flash('success', 'Your email has been verified successfully!');
        return redirect()->intended('dashboard/analytics');
    }

    public function resend()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $rateLimitKey = 'resend-verification-code:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            session()->flash('error', "Too many attempts. Please wait {$seconds} seconds before requesting a new code.");
            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $user->sendEmailVerificationNotification();
            $this->reset('code');
            $this->resetErrorBag();
            session()->flash('success', 'A new 6-digit verification code has been sent to ' . $user->email . '. Please check your inbox and spam folder.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send the verification code. Please try again later.');
        }
    }

    public function render()
    {
        return view('livewire.authentication.verification.basic')
            ->layout('layouts.base');
    }
}
