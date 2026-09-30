<div class="min-h-screen space-y-6 bg-slate-50 p-4 sm:p-6">
    <header class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Student Portal</p>
        <h1 class="mt-2 text-xl font-bold text-slate-800">My Student Status</h1>
        <p class="mt-1 text-sm text-slate-500">View your current institutional status, decisions, and status history.</p>
    </header>

    @if($successMessage !== '')
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ $successMessage }}</div>
    @endif

    @if($currentStatus && !$currentStatus->status->isActive())
        <section role="status" class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-soft-xl">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Your current status</p>
            <h2 class="mt-1 text-lg font-extrabold text-rose-900">{{ strtoupper($currentStatus->status->label()) }}</h2>
            <p class="mt-2 text-sm text-rose-900">Effective {{ $currentStatus->effective_date?->format('d F Y') ?? 'date not recorded' }} for {{ $currentStatus->academic_session }}.</p>
            @if($currentStatus->senate_reference)<p class="mt-1 text-sm text-rose-900">Senate reference: {{ $currentStatus->senate_reference }}</p>@endif
            <p class="mt-2 text-sm text-rose-800">Previously released results, transcripts, and payment history remain available. New academic actions are limited while this status is current.</p>
        </section>
    @else
        <section role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-soft-xl">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Your current status</p>
            <h2 class="mt-1 text-lg font-extrabold text-emerald-900">{{ $currentStatus?->status?->label() ?? 'Active' }}</h2>
            <p class="mt-2 text-sm text-emerald-800">No current withdrawal status is restricting your undergraduate academic activity.</p>
        </section>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-base font-bold text-slate-800">Your academic records remain accessible</h2><p class="mt-1 text-xs text-slate-500">A status decision does not remove historical academic or payment records.</p></div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.my-results') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">My results</a>
                <a href="{{ route('student.transcript.preview') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Transcript</a>
                <a href="{{ route('student.course-history') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Course history</a>
            </div>
        </div>
    </section>

    @if($canRequestReinstatement)
        <section class="rounded-2xl border border-violet-200 bg-white p-5 shadow-soft-xl sm:p-6">
            <h2 class="text-base font-bold text-slate-800">Request reinstatement</h2>
            <p class="mt-1 text-sm text-slate-500">Your request will be added to the status history for Department, Faculty, and Senate review.</p>
            <form wire:submit="requestReinstatement" class="mt-4">
                <label for="reinstatement-notes" class="mb-1 block text-xs font-semibold text-slate-700">Note for reviewers <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea id="reinstatement-notes" wire:model="reinstatementNotes" rows="3" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Add information that will help the reviewers assess your request."></textarea>
                @error('reinstatementNotes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                <div class="mt-3 flex justify-end"><button type="submit" wire:confirm="Submit a reinstatement request for Department, Faculty, and Senate review?" wire:loading.attr="disabled" class="rounded-lg bg-violet-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-violet-800 disabled:opacity-50">Submit request</button></div>
            </form>
        </section>
    @elseif($history->contains(fn ($record) => $record->reason_code === 'REINSTATEMENT_REQUEST' && in_array($record->senate_decision, [\App\Services\StudentStatusService::REINSTATEMENT_REQUESTED, \App\Services\StudentStatusService::REINSTATEMENT_DEPARTMENT_APPROVED, \App\Services\StudentStatusService::REINSTATEMENT_FACULTY_APPROVED], true)))
        <p class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800" role="status">Your reinstatement request is in review. You can see the recorded stage in your status history below.</p>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
        <h2 class="text-base font-bold text-slate-800">Status history</h2>
        <ol class="mt-5 space-y-4 border-l-2 border-slate-100 pl-5">
            @forelse($history as $record)
                <li class="relative">
                    <span class="absolute -left-[26px] top-1.5 h-3 w-3 rounded-full border-2 border-white {{ $record->status->isActive() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                        <div><h3 class="text-sm font-bold text-slate-800">{{ $record->status->label() }}</h3><p class="text-xs text-slate-500">{{ $record->academic_session }}{{ $record->semester ? ' · Semester ' . $record->semester : '' }} · Effective {{ $record->effective_date?->format('d M Y') ?? 'date unavailable' }}</p></div>
                        <span class="w-fit rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold text-slate-600">{{ str_replace('_', ' ', (string) $record->senate_decision) }}</span>
                    </div>
                    @if($record->reason)<p class="mt-2 text-xs text-slate-600">{{ $record->reason }}</p>@endif
                    @if($record->senate_reference)<p class="mt-1 text-xs text-slate-500">Senate reference: {{ $record->senate_reference }}</p>@endif
                </li>
            @empty
                <li class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No status events have been recorded. Your student account is treated as active.</li>
            @endforelse
        </ol>
    </section>
</div>
