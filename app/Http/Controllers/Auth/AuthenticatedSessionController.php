<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function destroy(Request $request)
    {
        $user = Auth::user();
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($user->isUndergraduate()) {
            return redirect()->route('degree-login');
        }

        return redirect()->route('login'); // Redirect to the registration page
    }
}