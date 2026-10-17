<section class="mb-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-soft-xl sm:p-5" aria-labelledby="workflow-notifications-heading">
    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700" aria-hidden="true">
                <i class="fas fa-bell"></i>
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="workflow-notifications-heading" class="font-bold text-slate-800">Recent notifications</h2>
                    @if ($unreadCount > 0)
                        <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800">{{ $unreadCount }} unread</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">Result workflow updates that may need your attention.</p>
            </div>
        </div>
        <a href="{{ route('notifications') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">View all</a>
    </div>

    @if ($notifications->isNotEmpty())
        <ul class="divide-y divide-slate-100" role="list">
            @foreach ($notifications as $notification)
                <li wire:key="dashboard-notification-{{ $notification->id }}" class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-slate-800">{{ $notification->title }}</h3>
                        <p class="mt-1 text-sm leading-5 text-slate-600">{{ $notification->message }}</p>
                        <p class="mt-2 text-xs text-slate-500">
                            @if (!empty($notification->context['course'])) <span>{{ $notification->context['course'] }}</span> @endif
                            @if (!empty($notification->context['session'])) <span>{{ !empty($notification->context['course']) ? ' · ' : '' }}{{ $notification->context['session'] }}</span> @endif
                            <span> · {{ $notification->created_at?->diffForHumans() }}</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ $notification->action_url ?: route('notifications') }}" class="inline-flex min-h-10 items-center rounded-lg bg-gradient-to-r from-sky-600 to-cyan-500 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">{{ $notification->action_url ? 'Open review' : 'View notification' }}</a>
                        <button type="button" wire:click="markAsRead('{{ $notification->id }}')" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">Mark read</button>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="py-7 text-center">
            <p class="font-semibold text-slate-700">You’re all caught up</p>
            <p class="mt-1 text-sm text-slate-500">New result workflow updates will appear here.</p>
        </div>
    @endif
</section>
