<?php

namespace App\Http\Livewire\Dashboards;

use App\Models\User;
use Livewire\Component;
use AuthorizesRequests;

class HodIndex extends Component
{
    public function render()
    {
        $this->authorize('manage-users', User::class);
        return view('livewire.dashboards.hod-index');
    }
}