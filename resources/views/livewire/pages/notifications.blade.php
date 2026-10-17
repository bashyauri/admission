<div class="mx-auto w-full max-w-5xl px-3 py-6 sm:px-5">
    <section class="rounded-2xl bg-white p-4 shadow-soft-xl sm:p-6">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Notifications</h1>
                <p class="mt-1 text-sm text-slate-500">Staff result workflow updates and email delivery status.</p>
            </div>
        </div>

        <div class="space-y-3">
            @forelse ($notifications as $notification)
                <article class="rounded-xl border p-4 {{ $notification->read_at ? 'border-slate-100 bg-white' : 'border-blue-100 bg-blue-50/50' }}">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-800">{{ $notification->title }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                            <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                @foreach (['course' => 'Course', 'session' => 'Session', 'semester' => 'Semester', 'reason' => 'Return reason'] as $key => $label)
                                    @if (!empty($notification->context[$key]))
                                        <div><dt class="inline font-semibold">{{ $label }}:</dt> <dd class="inline">{{ $notification->context[$key] }}</dd></div>
                                    @endif
                                @endforeach
                            </dl>
                            <p class="mt-3 text-xs text-slate-400">{{ $notification->created_at?->format('d M Y, g:i A') }} · Email: {{ ucfirst($notification->email_status) }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if ($notification->action_url)
                                <a href="{{ $notification->action_url }}" class="inline-flex min-h-10 items-center rounded-lg bg-gradient-to-r from-blue-600 to-cyan-500 px-4 py-2 text-sm font-semibold text-white shadow-sm">Open review</a>
                            @endif
                            @if (!$notification->read_at)
                                <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="inline-flex min-h-10 items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Mark read</button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 px-5 py-12 text-center">
                    <h2 class="font-semibold text-slate-700">You’re all caught up</h2>
                    <p class="mt-1 text-sm text-slate-500">Result workflow updates for your assignments will appear here.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $notifications->links() }}</div>
    </section>
</div>
