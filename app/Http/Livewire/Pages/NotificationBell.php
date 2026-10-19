<?php

namespace App\Http\Livewire\Pages;

use App\Models\ResultWorkflowNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    #[On('result-workflow-notification-read')]
    public function refreshNotifications(): void
    {
        // Re-render to recalculate the unread count and recent unread items.
    }

    public function markAsRead(string $id): void
    {
        $updated = ResultWorkflowNotification::query()
            ->where('user_id', Auth::id())
            ->whereKey($id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            $this->dispatch('result-workflow-notification-read');
        }
    }

    public function render()
    {
        $userId = Auth::id();

        return view('livewire.pages.notification-bell', [
            'unreadCount' => ResultWorkflowNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->count(),
            'notifications' => ResultWorkflowNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
