<div class="relative flex items-center" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="notification-menu" aria-label="Notifications{{ $unreadCount > 0 ? ', '.$unreadCount.' unread' : '' }}"
        class="relative inline-flex min-h-10 items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3 text-sm font-semibold text-sky-800 shadow-sm transition hover:border-sky-300 hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2 dark:border-sky-800 dark:bg-sky-950 dark:text-sky-100 dark:hover:bg-sky-900">
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 0 1-6 0v-1m6 0H9" />
        </svg>
        <span class="hidden sm:inline">Notifications</span>
        @if ($unreadCount > 0)
            <span class="flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-[11px] font-bold leading-none text-white ring-2 ring-white dark:ring-sky-950" aria-hidden="true">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div id="notification-menu" x-cloak x-show="open" x-transition.origin.top.right
        class="absolute right-0 top-full z-[9999] mt-3 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        role="region" aria-label="Recent notifications">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3">
            <div>
                <h2 class="font-bold text-slate-900">Notifications</h2>
                <p class="mt-0.5 text-xs font-medium text-slate-600">{{ $unreadCount }} unread</p>
            </div>
            <a href="{{ route('notifications') }}" class="rounded-lg px-2 py-1 text-sm font-semibold text-sky-700 hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-500">View all</a>
        </div>

        @if ($notifications->isNotEmpty())
            <ul class="max-h-[min(24rem,65vh)] divide-y divide-slate-100 overflow-y-auto" role="list">
                @foreach ($notifications as $notification)
                    <li wire:key="notification-bell-{{ $notification->id }}" class="px-4 py-3 hover:bg-slate-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $notification->title }}</p>
                                <p class="mt-1 line-clamp-2 text-sm leading-5 text-slate-600">{{ $notification->message }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                            <button type="button" wire:click.prevent.stop="markAsRead('{{ $notification->id }}')" wire:loading.attr="disabled" wire:target="markAsRead('{{ $notification->id }}')" aria-label="Mark {{ $notification->title }} as read"
                                class="inline-flex min-h-8 shrink-0 items-center rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-600 hover:border-sky-300 hover:bg-sky-50 hover:text-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:cursor-wait disabled:opacity-60">
                                <span wire:loading.remove wire:target="markAsRead('{{ $notification->id }}')">Mark read</span>
                                <span wire:loading wire:target="markAsRead('{{ $notification->id }}')">Saving…</span>
                            </button>
                        </div>
                        @if ($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="mt-2 inline-flex min-h-8 items-center text-xs font-semibold text-sky-700 hover:text-sky-900 focus:outline-none focus:underline">Open review <span class="sr-only">: {{ $notification->title }}</span></a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-4 py-8 text-center">
                <p class="font-semibold text-slate-700">You’re all caught up</p>
                <p class="mt-1 text-sm text-slate-500">New result workflow updates will appear here.</p>
            </div>
        @endif
    </div>
</div>
