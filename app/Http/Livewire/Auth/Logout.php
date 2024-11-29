<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;

class Logout extends Component
{
    public function logout()
    {
        $user = auth()->user();
        auth()->logout();
        if ($user && $user->isUndergraduate()) {
            return redirect()->route('degree-login');
        }
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
