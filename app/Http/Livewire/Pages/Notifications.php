<?php

namespace App\Http\Livewire\Pages;

use App\Models\ResultWorkflowNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public function markAsRead(string $id): void
    {
        ResultWorkflowNotification::query()
            ->where('user_id', Auth::id())
            ->whereKey($id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.pages.notifications', [
            'notifications' => ResultWorkflowNotification::query()
                ->where('user_id', Auth::id())
                ->latest()
                ->paginate(20),
        ]);
    }
}
