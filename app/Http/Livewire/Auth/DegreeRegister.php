<?php

declare(strict_types=1);

namespace App\Http\Livewire\Auth;



use Livewire\Component;
use App\Livewire\Forms\DegreeRegisterForm;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Throwable;


class DegreeRegister extends Component
{
    public DegreeRegisterForm $form;
    public function register()
    {
        try {
            $user = $this->form->store();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            report($exception);

            if ((string) $exception->getCode() === '23000') {
                return redirect()->back()->with('error', 'An account with this email address may already exist. Please sign in or try a different email address.');
            }

            return redirect()->back()->with('error', 'We couldn’t create your undergraduate account right now. Please try again. If you already registered, sign in instead.');
        } catch (Throwable $exception) {
            Log::error('Undergraduate account registration failed.', [
                'exception' => $exception,
            ]);

            return redirect()->back()->with('error', 'We couldn’t create your undergraduate account right now. Please try again. If you already registered, sign in instead.');
        }

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
            report($e);

            return redirect()->back()->with('warning', 'Your undergraduate account was created, but we couldn’t send the verification email. Please try again later or contact support.');
        }
    }
    public function render()
    {
        return view('livewire.auth.degree-register');
    }
}
