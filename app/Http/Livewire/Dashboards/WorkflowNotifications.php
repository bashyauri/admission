<?php

namespace App\Http\Livewire\Dashboards;

use App\Models\ResultWorkflowNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WorkflowNotifications extends Component
{
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
        $userId = Auth::id();

        return view('livewire.dashboards.workflow-notifications', [
            'unreadCount' => ResultWorkflowNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->count(),
            'notifications' => ResultWorkflowNotification::query()
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }
}
