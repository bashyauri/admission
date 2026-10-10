<div class="min-h-screen space-y-6 bg-slate-50 p-4 sm:p-6">
    <header class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Undergraduate Student Records</p>
        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Student Status Management</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">Review authoritative status and history, manage withdrawal recommendations, record Senate decisions, and process reinstatement reviews.</p>
            </div>
            @if(auth()->user()->canActAsExamOfficer())
                <a href="{{ route('exam-officer.withdrawal-ledger') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-soft-xs hover:bg-slate-50 hover:text-violet-700 transition">
                    <svg class="mr-2 h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Withdrawal Reports Ledger
                </a>
            @endif
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- Left Sidebar: Student Search & Inspector --}}
        <section class="space-y-5 xl:col-span-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl">
                <label for="student-search" class="block text-sm font-bold text-slate-800">Find an undergraduate student</label>
                <p class="mt-1 text-xs text-slate-500">Search by matriculation number or name within your assigned department scope.</p>
                <div class="relative mt-3">
                    <input id="student-search" type="search" wire:model.live.debounce.400ms="studentSearch" placeholder="Enter matric number or student name..." class="h-11 w-full rounded-xl border-slate-200 pl-10 text-sm focus:border-violet-500 focus:ring-violet-300" autocomplete="off">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                @error('studentSearch') <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror

                @if(trim($studentSearch) !== '')
                    <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-soft-xs">
                        @forelse($students as $student)
                            <button type="button" wire:click="selectStudent('{{ $student->id }}')" x-data="{ loading: false }" x-on:click="loading = true" x-on:livewire:loadend.window="loading = false" class="flex w-full items-center justify-between gap-3 px-3.5 py-3 text-left hover:bg-violet-50/50 transition">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-bold text-slate-800">{{ $student->surname }} {{ $student->firstname }} {{ $student->m_name }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500 font-mono">{{ $student->academicDetail?->matric_no ?? 'No Matric' }} · {{ $student->academicDetail?->department?->name ?? 'No Dept' }}</span>
                                </span>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">
                                    <span x-show="loading" class="h-3 w-3 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                    <span x-show="!loading">Inspect</span>
                                    <span x-show="loading" class="text-[10px] uppercase">Loading</span>
                                </span>
                            </button>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-500">
                                No matching undergraduate students found. Try a different name or matriculation number.
                            </div>
                        @endforelse
                    </div>
                    @if($students->count() === 12)
                        <p class="mt-2 text-xs text-slate-400 text-center">Showing the top 12 matches. Refine search to narrow down.</p>
                    @endif
                @endif
            </div>

            @if($selectedStudent)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl space-y-4">
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                        <div>
                            <span class="inline-block rounded-md bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-800 mb-1">Selected Student Profile</span>
                            <h2 class="text-base font-bold text-slate-800">{{ $selectedStudent->surname }} {{ $selectedStudent->firstname }} {{ $selectedStudent->m_name }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500 font-mono">{{ $selectedStudent->academicDetail?->matric_no }} · {{ $selectedStudent->academicDetail?->department?->name }}</p>
                        </div>
                        <button type="button" wire:click="clearSelectedStudent" class="rounded-lg border border-slate-200 p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" title="Close inspector">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="rounded-xl border p-4 {{ $currentStatus && !$currentStatus->status->isActive() ? 'border-rose-200 bg-rose-50/70' : 'border-emerald-200 bg-emerald-50/70' }}">
                        <p class="text-[10px] font-bold uppercase tracking-wider {{ $currentStatus && !$currentStatus->status->isActive() ? 'text-rose-700' : 'text-emerald-700' }}">Authoritative Current Status</p>
                        <p class="mt-1 text-sm font-bold {{ $currentStatus && !$currentStatus->status->isActive() ? 'text-rose-900' : 'text-emerald-900' }}">{{ $currentStatus?->status?->label() ?? 'Active (No active restriction record)' }}</p>
                        @if($currentStatus)
                            <p class="mt-1 text-xs text-slate-600">{{ $currentStatus->academic_session }} · Effective {{ $currentStatus->effective_date?->format('d M Y') ?? 'date not set' }}</p>
                            @if($currentStatus->senate_reference)<p class="mt-0.5 text-xs font-mono text-slate-600">Senate Ref: {{ $currentStatus->senate_reference }}</p>@endif
                        @endif
                    </div>

                    @if($progression)
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                                <dt class="text-slate-400 font-medium">Academic Standing</dt>
                                <dd class="mt-1 font-bold text-slate-800">{{ ucfirst(strtolower($progression['standing'] ?? 'N/A')) }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                                <dt class="text-slate-400 font-medium">CGPA</dt>
                                <dd class="mt-1 font-bold text-slate-800">{{ number_format((float) ($progression['cgpa'] ?? 0), 2) }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                                <dt class="text-slate-400 font-medium">Student Level</dt>
                                <dd class="mt-1 font-bold text-slate-800">{{ $selectedStudent->academicDetail?->studentLevel?->level ?? 'N/A' }} Level</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                                <dt class="text-slate-400 font-medium">Reinstatement</dt>
                                <dd class="mt-1 font-bold text-slate-800">{{ $currentStatus ? ($currentStatus->reinstatement_eligible ? 'Eligible' : 'Not Eligible') : 'N/A' }}</dd>
                            </div>
                        </div>
                    @endif

                    <details class="rounded-xl border border-slate-200 bg-slate-50/50 p-3">
                        <summary class="cursor-pointer text-xs font-bold text-slate-700 flex items-center justify-between">
                            <span>Recent Registration History ({{ $registrationHistory->count() }})</span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        @if($registrationHistory->isNotEmpty())
                            <ul class="mt-3 space-y-2 max-h-48 overflow-y-auto pr-1">
                                @foreach($registrationHistory as $registration)
                                    <li class="border-t border-slate-200/60 pt-2 text-xs text-slate-600 flex justify-between gap-2">
                                        <span class="font-semibold text-slate-800">{{ $registration->academic_session }}</span>
                                        <span class="truncate font-mono">{{ $registration->departmentCourse?->studentCourse?->code ?? 'Course' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-2 text-xs text-slate-500">No course registration history found.</p>
                        @endif
                    </details>

                    @if($history->isNotEmpty())
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Status History Trail</h3>
                            <ol class="space-y-3 border-l-2 border-slate-200 pl-4 text-xs">
                                @foreach($history as $entry)
                                    <li class="relative">
                                        <span class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full border-2 border-white {{ $entry->status->isActive() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <p class="font-bold text-slate-800">{{ $entry->status->label() }}</p>
                                        <p class="text-slate-500">{{ $entry->academic_session }} · Effective {{ $entry->effective_date?->format('d M Y') ?? 'N/A' }}</p>
                                        <div class="mt-1 inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                            Workflow: {{ str_replace('_', ' ', (string) $entry->senate_decision) }}
                                        </div>
                                        @if($entry->senate_reference)<p class="mt-0.5 font-mono text-[11px] text-slate-500">Ref: {{ $entry->senate_reference }}</p>@endif
                                        @if($entry->reason)<p class="mt-1 text-slate-600 italic">"{{ $entry->reason }}"</p>@endif
                                        @if($entry->senate_decision === \App\Services\StudentStatusService::WORKFLOW_RECOMMENDED && $entry->reason_code !== 'REINSTATEMENT_REQUEST')
                                            @can('student-status.submit-for-senate', $selectedStudent)
                                                <button type="button" wire:click="submitRecommendation({{ $entry->id }})" wire:loading.attr="disabled" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-violet-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-violet-800 transition disabled:opacity-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                    Submit Recommendation to Senate
                                                </button>
                                            @endcan
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500 text-center">No status history recorded for this student.</p>
                    @endif

                    @if($auditEntries->isNotEmpty())
                        <details class="rounded-xl border border-slate-200 bg-slate-50/50 p-3">
                            <summary class="cursor-pointer text-xs font-bold text-slate-700 flex items-center justify-between">
                                <span>Audit Log Trail ({{ $auditEntries->count() }})</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <ul class="mt-3 space-y-2 max-h-40 overflow-y-auto pr-1">
                                @foreach($auditEntries as $audit)
                                    <li class="border-t border-slate-200/60 pt-2 text-[11px] text-slate-600">
                                        <span class="font-bold text-slate-800">{{ str_replace('_', ' ', $audit->action) }}</span>
                                        <span class="block text-slate-400">{{ $audit->occurred_at?->format('d M Y H:i') }} by {{ $audit->actor?->name ?? 'System' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endif
        </section>

        {{-- Main Workflow Section --}}
        <section class="space-y-6 xl:col-span-8">
            {{-- Workflow Metric Summary Bar --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Due for Review</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-amber-100 text-amber-800 text-xs font-bold">1</span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $dueForWithdrawalReview->count() }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">Match withdrawal rules</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Recommendations</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-sky-100 text-sky-800 text-xs font-bold">2</span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $pendingSubmissions->count() }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">Pending Senate dispatch</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Pending Senate</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-violet-100 text-violet-800 text-xs font-bold">3</span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $pendingDecisions->count() }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">Awaiting Senate decision</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Reinstatement</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold">4</span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $reinstatementRequests->count() }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">Under review workflow</p>
                </div>
            </div>

            <div x-data="{ tab: 'overview' }" class="space-y-6">
                {{-- Tab Navigation Bar --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-soft-xl">
                    <nav class="flex flex-wrap gap-1.5" aria-label="Workflow Navigation Tabs">
                        <button type="button" x-on:click="tab = 'overview'" :class="tab === 'overview' ? 'bg-violet-700 text-white shadow-soft-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Student Action Form
                        </button>
                        <button type="button" x-on:click="tab = 'review'" :class="tab === 'review' ? 'bg-violet-700 text-white shadow-soft-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition">
                            <span>Due for Review</span>
                            <span :class="tab === 'review' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $dueForWithdrawalReview->count() }}</span>
                        </button>
                        <button type="button" x-on:click="tab = 'recommendations'" :class="tab === 'recommendations' ? 'bg-violet-700 text-white shadow-soft-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition">
                            <span>Recommendations</span>
                            <span :class="tab === 'recommendations' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $pendingSubmissions->count() }}</span>
                        </button>
                        <button type="button" x-on:click="tab = 'senate'" :class="tab === 'senate' ? 'bg-violet-700 text-white shadow-soft-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition">
                            <span>Pending Senate</span>
                            <span :class="tab === 'senate' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $pendingDecisions->count() }}</span>
                        </button>
                        <button type="button" x-on:click="tab = 'reinstatement'" :class="tab === 'reinstatement' ? 'bg-violet-700 text-white shadow-soft-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition">
                            <span>Reinstatement</span>
                            <span :class="tab === 'reinstatement' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'" class="rounded-full px-2 py-0.5 text-[10px] font-bold">{{ $reinstatementRequests->count() }}</span>
                        </button>
                    </nav>
                </div>

                {{-- Tab 1: Student Overview & Action Form --}}
                <div x-show="tab === 'overview'" x-transition class="space-y-6">
                    @if($selectedStudent && $selectedStudent->isUndergraduate() && $selectedStudent->academicDetail && $currentStatus?->status?->isActive() !== false)
                        @if(Gate::allows('student-status.recommend', $selectedStudent) || Gate::allows('student-status.process-voluntary', $selectedStudent) || Gate::allows('student-status.process-medical', $selectedStudent))
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                                <h2 class="text-base font-bold text-slate-800">{{ auth()->user()?->isAdmin() ? 'Record a Senate-Approved Decision' : 'Create Student Withdrawal Recommendation' }}</h2>
                                @if(auth()->user()?->isAdmin())
                                    <p class="mt-1 text-xs text-slate-500">For academic withdrawals, enter the verified Senate reference and decision date to record an already-approved Senate decision atomically in one step.</p>
                                @else
                                    <p class="mt-1 text-xs text-slate-500">Academic withdrawal records created here remain recommendations until officially submitted to and approved by Senate.</p>
                                @endif
                                <form wire:submit.prevent="$set('showStatusConfirmation', true)" class="mt-5 space-y-4">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="withdrawal-type" class="mb-1 block text-xs font-semibold text-slate-700">Withdrawal Pathway</label>
                                            <select id="withdrawal-type" wire:model.live="withdrawalType" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                                                @if(Gate::allows('student-status.recommend', $selectedStudent)) <option value="academic">Academic withdrawal recommendation</option> @endif
                                                @if(Gate::allows('student-status.process-voluntary', $selectedStudent)) <option value="voluntary">Voluntary withdrawal</option> @endif
                                                @if(Gate::allows('student-status.process-medical', $selectedStudent)) <option value="medical">Medical withdrawal</option> @endif
                                            </select>
                                            @error('withdrawalType') <p class="mt-1 text-xs text-rose-600">Choose an authorized pathway.</p> @enderror
                                        </div>
                                        <div>
                                            <label for="status-session" class="mb-1 block text-xs font-semibold text-slate-700">Academic Session</label>
                                            <input id="status-session" list="student-status-sessions" wire:model="academicSession" placeholder="2025/2026" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                                            <datalist id="student-status-sessions">@foreach($sessions as $session)<option value="{{ $session }}">@endforeach</datalist>
                                            @error('academicSession') <p class="mt-1 text-xs text-rose-600">Format must be YYYY/YYYY.</p> @enderror
                                        </div>
                                        @if($withdrawalType === 'academic')
                                            <div>
                                                <label for="reason-code" class="mb-1 block text-xs font-semibold text-slate-700">Reason Code</label>
                                                <select id="reason-code" wire:model="reasonCode" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                                                    <optgroup label="CGPA-based">
                                                        <option value="CGPA_BELOW_UNIVERSITY_MINIMUM">CGPA below university minimum — Withdrawn from University</option>
                                                        <option value="CGPA_BELOW_PROGRAM_MINIMUM">CGPA below programme minimum — Withdrawn from Program</option>
                                                    </optgroup>
                                                    <optgroup label="Standing-based">
                                                        <option value="CONSECUTIVE_PROBATION">Consecutive academic probation</option>
                                                        <option value="CONSECUTIVE_REPEAT">Consecutive REPEAT standing</option>
                                                    </optgroup>
                                                    <optgroup label="Other">
                                                        <option value="MAX_RESIDENCY_EXCEEDED">Maximum programme residency exceeded</option>
                                                        <option value="NON_REGISTRATION_PATTERN">Non-registration pattern</option>
                                                        <option value="OTHER_APPROVED_REASON">Other approved reason</option>
                                                    </optgroup>
                                                </select>
                                                @error('reasonCode') <p class="mt-1 text-xs text-rose-600">Select a reason code.</p> @enderror
                                            </div>
                                        @endif
                                        @if($withdrawalType === 'academic' && auth()->user()?->isAdmin())
                                            <div>
                                                <label for="admin-senate-reference" class="mb-1 block text-xs font-semibold text-slate-700">Senate Reference *</label>
                                                <input id="admin-senate-reference" wire:model="senateReference" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300" placeholder="SEN-2026-104">
                                                @error('senateReference') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div>
                                                <label for="admin-senate-date" class="mb-1 block text-xs font-semibold text-slate-700">Senate Decision Date *</label>
                                                <input id="admin-senate-date" type="date" wire:model="senateDecisionDate" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                                                @error('senateDecisionDate') <p class="mt-1 text-xs text-rose-600">Select Senate decision date.</p> @enderror
                                            </div>
                                        @endif
                                        <div>
                                            <label for="status-semester" class="mb-1 block text-xs font-semibold text-slate-700">Semester <span class="font-normal text-slate-400">(optional)</span></label>
                                            <select id="status-semester" wire:model="semester" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                                                <option value="">Entire Session</option>
                                                <option value="1">Semester 1</option>
                                                <option value="2">Semester 2</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="effective-date" class="mb-1 block text-xs font-semibold text-slate-700">Effective Date</label>
                                            <input id="effective-date" type="date" wire:model="effectiveDate" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                                            @error('effectiveDate') <p class="mt-1 text-xs text-rose-600">Enter a valid effective date.</p> @enderror
                                        </div>
                                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 md:col-span-2 hover:bg-slate-50 transition cursor-pointer">
                                            <input type="checkbox" wire:model="reinstatementEligible" class="mt-0.5 rounded border-slate-300 text-violet-700 focus:ring-violet-500">
                                            <span>
                                                <span class="block text-xs font-semibold text-slate-800">Allow Future Reinstatement Request</span>
                                                <span class="mt-0.5 block text-xs text-slate-500">Record whether this student may apply for future reinstatement under Senate guidelines.</span>
                                            </span>
                                        </label>
                                        <div class="md:col-span-2">
                                            <label for="status-reason" class="mb-1 block text-xs font-semibold text-slate-700">Detailed Reason</label>
                                            <textarea id="status-reason" wire:model="reason" rows="3" class="w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300" placeholder="Provide factual context for this status record..."></textarea>
                                            @error('reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="status-notes" class="mb-1 block text-xs font-semibold text-slate-700">Supporting Notes <span class="font-normal text-slate-400">(optional)</span></label>
                                            <textarea id="status-notes" wire:model="notes" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300" placeholder="Board minutes or additional context..."></textarea>
                                        </div>
                                    </div>
                                    @error('selectedStudentId') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                    <div class="flex justify-end pt-2">
                                        <button type="submit" class="rounded-xl bg-violet-700 px-6 py-2.5 text-sm font-bold text-white shadow-soft-xs hover:bg-violet-800 transition" wire:loading.attr="disabled">
                                            Review & Submit Action
                                        </button>
                                    </div>
                                </form>

                                @if($showStatusConfirmation)
                                    <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-4" role="alertdialog" aria-label="Confirm status action">
                                        <h3 class="text-sm font-bold text-amber-900">Confirm Status Action</h3>
                                        <p class="mt-1 text-xs text-amber-800">
                                            @if(auth()->user()?->isAdmin() && $withdrawalType === 'academic') 
                                                You are recording a Senate-approved academic withdrawal for <strong>{{ $selectedStudent->surname }} {{ $selectedStudent->firstname }}</strong> ({{ $selectedStudent->academicDetail?->matric_no }}), effective {{ $effectiveDate }}, under reference <strong>{{ $senateReference }}</strong> dated {{ $senateDecisionDate }}.
                                            @else 
                                                You are creating a <strong>{{ $withdrawalType }}</strong> withdrawal record for <strong>{{ $selectedStudent->surname }} {{ $selectedStudent->firstname }}</strong> ({{ $selectedStudent->academicDetail?->matric_no }}), effective {{ $effectiveDate }}.
                                            @endif 
                                            This action will be written to the immutable status audit trail.
                                        </p>
                                        <div class="mt-3 flex flex-wrap justify-end gap-2">
                                            <button type="button" wire:click="$set('showStatusConfirmation', false)" class="rounded-lg border border-amber-300 bg-white px-4 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100 transition">Go back</button>
                                            <button type="button" wire:click="createStatusRecord" wire:loading.attr="disabled" class="rounded-lg bg-rose-700 px-4 py-2 text-xs font-bold text-white hover:bg-rose-800 transition shadow-soft-xs">
                                                {{ auth()->user()?->isAdmin() && $withdrawalType === 'academic' ? 'Record Senate Approval' : 'Confirm & Create Record' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-soft-xl">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 mb-3">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">No Student Selected</h3>
                            <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Use the search box on the left panel to find a student, or select a student from the workflow tabs to view their full profile and take status actions.</p>
                        </div>
                    @endif
                </div>

                {{-- Tab 2: Due for Withdrawal Review --}}
                <div x-show="tab === 'review'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Due for withdrawal review</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Students who match academic progression withdrawal triggers (e.g. consecutive probation, low CGPA) requiring departmental review.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select aria-label="Filter by academic session" wire:model.live="dueReviewSession" class="h-9 rounded-lg border-slate-200 text-xs focus:border-violet-500 focus:ring-violet-300">
                                <option value="">All Sessions</option>
                                @foreach($sessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach
                            </select>
                            <select aria-label="Filter by department" wire:model.live="dueReviewDepartment" class="h-9 rounded-lg border-slate-200 text-xs focus:border-violet-500 focus:ring-violet-300">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left text-xs text-slate-600">
                            <thead class="border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-400 font-bold bg-slate-50/50">
                                <tr>
                                    <th class="px-3.5 py-3">Student</th>
                                    <th class="px-3.5 py-3">Triggered Rule</th>
                                    <th class="px-3.5 py-3">Session / Department</th>
                                    <th class="px-3.5 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($dueForWithdrawalReview as $student)
                                    @php($eligibility = app(\App\Services\AcademicProgressionService::class)->evaluateWithdrawalEligibility($student))
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-3.5 py-3">
                                            <span class="block font-bold text-slate-800">{{ $student->surname }} {{ $student->firstname }}</span>
                                            <span class="font-mono text-slate-400 text-[11px]">{{ $student->academicDetail?->matric_no }}</span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="inline-block rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-[11px] font-bold text-rose-700">
                                                {{ $eligibility['reason_code'] ?? 'ELIGIBLE' }}
                                            </span>
                                            <p class="mt-1 max-w-xs text-[11px] text-slate-500 line-clamp-2">{{ $eligibility['reason'] ?? 'Triggered by progression policy.' }}</p>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="font-semibold text-slate-700">{{ $student->academicDetail?->acad_session ?? 'N/A' }}</span>
                                            <span class="block text-slate-400 text-[11px]">{{ $student->academicDetail?->department?->name ?? 'No Dept' }}</span>
                                        </td>
                                        <td class="px-3.5 py-3 text-right">
                                            <button type="button" wire:click="selectStudent('{{ $student->id }}')" x-on:click="tab = 'overview'" class="inline-flex items-center gap-1 rounded-lg bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700 hover:bg-violet-100 transition">
                                                <span>Open Student Profile</span>
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3.5 py-10 text-center text-slate-500">
                                            <p class="font-semibold">No students currently due for withdrawal review.</p>
                                            <p class="text-xs text-slate-400 mt-0.5">All undergraduate student academic standings are clear of pending triggers.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 3: Recommendations Pending Submission --}}
                <div x-show="tab === 'recommendations'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Recommendations Pending Senate Submission</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Withdrawal recommendations created by departments awaiting submission to Senate for approval.</p>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-xs text-slate-600">
                            <thead class="border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-400 font-bold bg-slate-50/50">
                                <tr>
                                    <th class="px-3.5 py-3">Student</th>
                                    <th class="px-3.5 py-3">Withdrawal Type</th>
                                    <th class="px-3.5 py-3">Session / Department</th>
                                    <th class="px-3.5 py-3">Reason / Details</th>
                                    <th class="px-3.5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($pendingSubmissions as $record)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-3.5 py-3">
                                            <span class="block font-bold text-slate-800">{{ $record->user?->surname }} {{ $record->user?->firstname }}</span>
                                            <span class="font-mono text-slate-400 text-[11px]">{{ $record->user?->academicDetail?->matric_no }}</span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="inline-block rounded-full border border-sky-200 bg-sky-50 px-2.5 py-0.5 text-[11px] font-bold text-sky-800">
                                                {{ $record->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="font-semibold text-slate-700">{{ $record->academic_session }}</span>
                                            <span class="block text-slate-400 text-[11px]">{{ $record->user?->academicDetail?->department?->name }}</span>
                                        </td>
                                        <td class="px-3.5 py-3 max-w-xs">
                                            <p class="text-slate-700 font-medium line-clamp-2">{{ $record->reason }}</p>
                                        </td>
                                        <td class="px-3.5 py-3 text-right space-x-2">
                                            <button type="button" wire:click="selectStudent('{{ $record->user_id }}')" x-on:click="tab = 'overview'" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                                Inspect
                                            </button>
                                            @can('student-status.submit-for-senate', $record->user)
                                                <button type="button" wire:click="submitRecommendation({{ $record->id }})" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-lg bg-violet-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-violet-800 transition shadow-soft-xs">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                    Submit to Senate
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3.5 py-10 text-center text-slate-500">
                                            <p class="font-semibold">No recommendations pending Senate submission.</p>
                                            <p class="text-xs text-slate-400 mt-0.5">All created recommendations have either been submitted or processed.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 4: Pending Senate Approval Queue --}}
                <div x-show="tab === 'senate'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Pending Senate withdrawal decisions</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Undergraduate recommendations awaiting an official Senate approval or rejection decision.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select aria-label="Filter by academic session" wire:model.live="senateQueueSession" class="h-9 rounded-lg border-slate-200 text-xs focus:border-violet-500 focus:ring-violet-300">
                                <option value="">All Sessions</option>
                                @foreach($sessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach
                            </select>
                            <select aria-label="Filter by department" wire:model.live="senateQueueDepartment" class="h-9 rounded-lg border-slate-200 text-xs focus:border-violet-500 focus:ring-violet-300">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                            </select>
                            <select aria-label="Filter by withdrawal type" wire:model.live="senateQueueType" class="h-9 rounded-lg border-slate-200 text-xs focus:border-violet-500 focus:ring-violet-300">
                                <option value="">All Types</option>
                                <option value="academic">Academic</option>
                                <option value="voluntary">Voluntary</option>
                                <option value="medical">Medical</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-xs text-slate-600">
                            <thead class="border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-400 font-bold bg-slate-50/50">
                                <tr>
                                    <th class="px-3.5 py-3">Student</th>
                                    <th class="px-3.5 py-3">Status / Pathway</th>
                                    <th class="px-3.5 py-3">Session / Department</th>
                                    <th class="px-3.5 py-3">Reason</th>
                                    <th class="px-3.5 py-3 text-right">Senate Decision</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($pendingDecisions as $record)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-3.5 py-3">
                                            <span class="block font-bold text-slate-800">{{ $record->user?->surname }} {{ $record->user?->firstname }}</span>
                                            <span class="font-mono text-slate-400 text-[11px]">{{ $record->user?->academicDetail?->matric_no }}</span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="inline-block rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                                                {{ $record->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="font-semibold text-slate-700">{{ $record->academic_session }}</span>
                                            <span class="block text-slate-400 text-[11px]">{{ $record->user?->academicDetail?->department?->name }}</span>
                                        </td>
                                        <td class="px-3.5 py-3 max-w-xs">
                                            <p class="text-slate-700 font-medium line-clamp-2">{{ $record->reason }}</p>
                                        </td>
                                        <td class="px-3.5 py-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button" wire:click="selectStudent('{{ $record->user_id }}')" x-on:click="tab = 'overview'" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                                    Inspect
                                                </button>
                                                @can('student-status.decide-senate', $record->user)
                                                    <button type="button" wire:click="beginSenateDecision({{ $record->id }}, true)" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-800 transition shadow-soft-xs">
                                                        Approve
                                                    </button>
                                                    <button type="button" wire:click="beginSenateDecision({{ $record->id }}, false)" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">
                                                        Reject
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3.5 py-10 text-center text-slate-500">
                                            <p class="font-semibold">No Senate withdrawal decisions currently pending.</p>
                                            <p class="text-xs text-slate-400 mt-0.5">Submitted recommendations will appear here for official Senate decision recording.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 5: Reinstatement Workflow --}}
                <div x-show="tab === 'reinstatement'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Reinstatement requests</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Department, Faculty, and Senate multi-tier reinstatement approvals.</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($reinstatementRequests as $request)
                            <article class="rounded-xl border border-slate-200 p-4 hover:border-slate-300 transition">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-800">
                                            {{ $request->user?->surname }} {{ $request->user?->firstname }} 
                                            <span class="font-mono font-normal text-slate-500">· {{ $request->user?->academicDetail?->matric_no }}</span>
                                        </h3>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $request->user?->academicDetail?->department?->name }} · Session {{ $request->academic_session }}</p>
                                        @if($request->originalWithdrawal)
                                            <p class="mt-1 text-xs text-slate-600">
                                                Original Status: <span class="font-semibold text-rose-700">{{ $request->originalWithdrawal->status->label() }}</span> · Effective {{ $request->originalWithdrawal->effective_date?->format('d M Y') ?? 'N/A' }}
                                                @if($request->originalWithdrawal->senate_reference) · Senate Ref: {{ $request->originalWithdrawal->senate_reference }}@endif
                                            </p>
                                        @endif
                                        <div class="mt-2 inline-block rounded-md bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">
                                            Stage: {{ str_replace('_', ' ', $request->senate_decision) }}
                                        </div>
                                        @if($request->notes)<p class="mt-2 text-xs text-slate-600 italic">"{{ $request->notes }}"</p>@endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="w-full min-w-[200px] lg:w-60">
                                            <label class="sr-only" for="review-notes-{{ $request->id }}">Review Notes</label>
                                            <textarea id="review-notes-{{ $request->id }}" wire:model.defer="reviewNotesByRequest.{{ $request->id }}" rows="2" class="w-full rounded-lg border-slate-200 text-xs focus:border-violet-500 focus:ring-violet-300" placeholder="Optional review notes..."></textarea>
                                        </div>
                                        @if($request->senate_decision === \App\Services\StudentStatusService::REINSTATEMENT_REQUESTED)
                                            @can('student-status.review-department-reinstatement', $request->user)
                                                <button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'DEPARTMENT', true)" class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white shadow-soft-xs hover:bg-violet-800 transition">Dept Approve</button>
                                                <button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'DEPARTMENT', false)" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">Dept Reject</button>
                                            @endcan
                                        @elseif($request->senate_decision === \App\Services\StudentStatusService::REINSTATEMENT_DEPARTMENT_APPROVED)
                                            @can('student-status.review-faculty-reinstatement', $request->user)
                                                <button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'FACULTY', true)" class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white shadow-soft-xs hover:bg-violet-800 transition">Faculty Approve</button>
                                                <button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'FACULTY', false)" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">Faculty Reject</button>
                                            @endcan
                                        @elseif($request->senate_decision === \App\Services\StudentStatusService::REINSTATEMENT_FACULTY_APPROVED)
                                            @can('student-status.decide-senate', $request->user)
                                                <button type="button" wire:click="beginReinstatementDecision({{ $request->id }}, true)" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white shadow-soft-xs hover:bg-emerald-800 transition">Senate Approve</button>
                                                <button type="button" wire:click="beginReinstatementDecision({{ $request->id }}, false)" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">Senate Reject</button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                                No reinstatement requests currently awaiting review.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Senate Decision Modal --}}
    @if($showDecisionModal)
        <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="senate-decision-title" x-data x-on:keydown.escape.window="$wire.closeDecisionModal()">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-soft-xl" x-on:click.outside="$wire.closeDecisionModal()">
                <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 id="senate-decision-title" class="text-base font-bold text-slate-800">{{ $approveDecision ? 'Confirm Senate Approval' : 'Record Senate Rejection' }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $approveDecision ? 'Approval officializes status change and locks undergraduate permissions.' : 'Rejection leaves student active and retains recommendation history.' }}</p>
                    </div>
                    <button type="button" wire:click="closeDecisionModal" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4 space-y-4 text-xs">
                    <div>
                        <label for="decision-ref" class="mb-1 block font-semibold text-slate-700">Senate Reference Number {{ $approveDecision ? '*' : '(optional)' }}</label>
                        <input id="decision-ref" wire:model="senateReference" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300" placeholder="e.g. SEN-2026-104">
                        @error('senateReference')<p class="mt-1 text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="decision-date" class="mb-1 block font-semibold text-slate-700">Decision Date *</label>
                        <input id="decision-date" type="date" wire:model="senateDecisionDate" class="h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300">
                        @error('senateDecisionDate')<p class="mt-1 text-rose-600 font-medium">Select a valid date.</p>@enderror
                    </div>
                    <div>
                        <label for="decision-notes" class="mb-1 block font-semibold text-slate-700">{{ $approveDecision ? 'Decision Notes (optional)' : 'Reason for Rejection *' }}</label>
                        <textarea id="decision-notes" wire:model="decisionNotes" rows="3" class="w-full rounded-xl border-slate-200 text-sm focus:border-violet-500 focus:ring-violet-300" placeholder="Enter notes or rejection rationale..."></textarea>
                        @error('decisionNotes')<p class="mt-1 text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="closeDecisionModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="button" wire:click="{{ $decisionIsReinstatement ? 'decideReinstatement' : 'decideWithdrawal' }}" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl {{ $approveDecision ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-rose-700 hover:bg-rose-800' }} px-5 py-2.5 text-xs font-bold text-white transition shadow-soft-xs">
                        {{ $approveDecision ? 'Confirm Approval' : 'Record Rejection' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
