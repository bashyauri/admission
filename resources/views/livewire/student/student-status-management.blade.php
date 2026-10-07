<div class="min-h-screen space-y-6 bg-slate-50 p-4 sm:p-6">
    <header class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Undergraduate Student Records</p>
        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Student Status Management</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">Review authoritative status and history before creating a withdrawal record, forwarding a recommendation, recording a Senate decision, or reviewing reinstatement.</p>
            </div>
            @if(auth()->user()->canActAsExamOfficer())
                <a href="{{ route('exam-officer.withdrawal-ledger') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Withdrawal reports</a>
            @endif
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <section class="space-y-5 xl:col-span-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl">
                <label for="student-search" class="block text-sm font-bold text-slate-700">Find an undergraduate student</label>
                <p class="mt-1 text-xs text-slate-500">Search by matriculation number or name. Results follow your assigned department scope.</p>
                <input id="student-search" type="search" wire:model.live.debounce.400ms="studentSearch" placeholder="Matric number or student name" class="mt-3 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-violet-400 focus:ring-violet-300" autocomplete="off">
                @error('studentSearch') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                @if(trim($studentSearch) !== '')
                    <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-100">
                        @forelse($students as $student)
                            <button type="button" wire:click="selectStudent('{{ $student->id }}')" x-data="{ loading: false }" x-on:click="loading = true" x-on:livewire:loadend.window="loading = false" class="flex w-full items-center justify-between gap-3 px-3 py-3 text-left hover:bg-slate-50">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-slate-800">{{ $student->surname }} {{ $student->firstname }} {{ $student->m_name }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500">{{ $student->academicDetail?->matric_no ?? 'Matric number unavailable' }} · {{ $student->academicDetail?->department?->name ?? 'Department unavailable' }}</span>
                                </span>
                                <span class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-violet-700">
                                    <span x-show="loading" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                    <span x-show="!loading">Open</span>
                                    <span x-show="loading" class="text-[10px] uppercase tracking-wide">Loading</span>
                                </span>
                            </button>
                        @empty
                            <p class="px-3 py-4 text-sm text-slate-500">No matching undergraduate students. Try a different name or matriculation number.</p>
                        @endforelse
                    </div>
                    @if($students->count() === 12)
                        <p class="mt-2 text-xs text-slate-400">Showing the first 12 matches. Refine the search to narrow the list.</p>
                    @endif
                @endif
            </div>

            @if($selectedStudent)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">{{ $selectedStudent->surname }} {{ $selectedStudent->firstname }} {{ $selectedStudent->m_name }}</h2>
                            <p class="mt-1 text-xs text-slate-500">{{ $selectedStudent->academicDetail?->matric_no }} · {{ $selectedStudent->academicDetail?->department?->name }}</p>
                        </div>
                        <button type="button" wire:click="clearSelectedStudent" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-50">Close</button>
                    </div>

                    <div class="mt-4 rounded-xl border p-4 {{ $currentStatus && !$currentStatus->status->isActive() ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50' }}">
                        <p class="text-[11px] font-bold uppercase tracking-wider {{ $currentStatus && !$currentStatus->status->isActive() ? 'text-rose-700' : 'text-emerald-700' }}">Authoritative current status</p>
                        <p class="mt-1 text-sm font-bold {{ $currentStatus && !$currentStatus->status->isActive() ? 'text-rose-900' : 'text-emerald-900' }}">{{ $currentStatus?->status?->label() ?? 'Active (no current status record)' }}</p>
                        @if($currentStatus)
                            <p class="mt-1 text-xs text-slate-600">{{ $currentStatus->academic_session }} · Effective {{ $currentStatus->effective_date?->format('d M Y') ?? 'date not recorded' }}</p>
                            @if($currentStatus->senate_reference)<p class="mt-1 text-xs text-slate-600">Senate reference: {{ $currentStatus->senate_reference }}</p>@endif
                        @endif
                    </div>

                    @if($progression)
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-xl bg-slate-50 p-3"><dt class="text-slate-500">Academic standing</dt><dd class="mt-1 font-bold text-slate-800">{{ ucfirst(strtolower($progression['standing'] ?? 'N/A')) }}</dd></div>
                            <div class="rounded-xl bg-slate-50 p-3"><dt class="text-slate-500">CGPA</dt><dd class="mt-1 font-bold text-slate-800">{{ number_format((float) ($progression['cgpa'] ?? 0), 2) }}</dd></div>
                            <div class="rounded-xl bg-slate-50 p-3"><dt class="text-slate-500">Level</dt><dd class="mt-1 font-bold text-slate-800">{{ $selectedStudent->academicDetail?->studentLevel?->level ?? 'N/A' }}</dd></div>
                            <div class="rounded-xl bg-slate-50 p-3"><dt class="text-slate-500">Reinstatement eligibility</dt><dd class="mt-1 font-bold text-slate-800">{{ $currentStatus ? ($currentStatus->reinstatement_eligible ? 'Eligible' : 'Not eligible') : 'Not applicable' }}</dd></div>
                        </dl>
                    @endif

                    <details class="mt-4 rounded-xl border border-slate-200 p-3">
                        <summary class="cursor-pointer text-xs font-bold text-slate-700">Recent registration history ({{ $registrationHistory->count() }})</summary>
                        @if($registrationHistory->isNotEmpty())
                            <ul class="mt-3 space-y-2">
                                @foreach($registrationHistory as $registration)
                                    <li class="border-t border-slate-100 pt-2 text-xs text-slate-600"><span class="font-semibold">{{ $registration->academic_session }}</span> · {{ $registration->departmentCourse?->studentCourse?->code ?? 'Course' }} — {{ $registration->departmentCourse?->studentCourse?->title ?? 'Title unavailable' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-3 text-xs text-slate-500">No course registration history is available.</p>
                        @endif
                    </details>

                    @if($history->isNotEmpty())
                        <div class="mt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Status history</h3>
                            <ol class="mt-3 space-y-3 border-l-2 border-slate-100 pl-4">
                                @foreach($history as $entry)
                                    <li class="relative">
                                        <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white {{ $entry->status->isActive() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <p class="text-sm font-semibold text-slate-800">{{ $entry->status->label() }}</p>
                                        <p class="text-xs text-slate-500">{{ $entry->academic_session }} · {{ $entry->effective_date?->format('d M Y') ?? 'Date unavailable' }} · {{ str_replace('_', ' ', (string) $entry->senate_decision) }}</p>
                                        @if($entry->senate_reference)<p class="text-xs text-slate-500">Senate reference: {{ $entry->senate_reference }}</p>@endif
                                        @if($entry->reason)<p class="mt-1 text-xs text-slate-600">{{ $entry->reason }}</p>@endif
                                        @if($entry->senate_decision === \App\Services\StudentStatusService::WORKFLOW_RECOMMENDED && $entry->reason_code !== 'REINSTATEMENT_REQUEST')
                                            @can('student-status.submit-for-senate', $selectedStudent)
                                                <button type="button" wire:click="submitRecommendation({{ $entry->id }})" wire:loading.attr="disabled" x-data="{ submitting: false }" x-on:click="submitting = true" x-on:livewire:loadend.window="submitting = false" class="mt-2 inline-flex items-center gap-2 rounded-lg bg-violet-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-violet-800 disabled:opacity-50">
                                                    <span x-show="submitting" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                                    <span x-show="!submitting">Submit to Senate</span>
                                                    <span x-show="submitting" class="text-[10px] uppercase tracking-wide">Sending</span>
                                                </button>
                                            @endcan
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">No status history has been recorded for this student.</p>
                    @endif

                    @if($auditEntries->isNotEmpty())
                        <details class="mt-4 rounded-xl border border-slate-200 p-3">
                            <summary class="cursor-pointer text-xs font-bold text-slate-700">Authorized status audit history ({{ $auditEntries->count() }})</summary>
                            <ul class="mt-3 space-y-2">
                                @foreach($auditEntries as $audit)
                                    <li class="border-t border-slate-100 pt-2 text-xs text-slate-600"><span class="font-semibold">{{ str_replace('_', ' ', $audit->action) }}</span> · {{ $audit->occurred_at?->format('d M Y H:i') }} · {{ $audit->actor?->name ?? $audit->actor?->email ?? 'System' }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endif
        </section>

        <section class="space-y-6 xl:col-span-8">
            <div x-data="{ tab: 'overview' }" class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-soft-xl">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" x-on:click="tab = 'overview'" :class="tab === 'overview' ? 'bg-violet-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-lg px-3 py-2 text-xs font-bold transition">Student overview</button>
                        <button type="button" x-on:click="tab = 'review'" :class="tab === 'review' ? 'bg-violet-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-lg px-3 py-2 text-xs font-bold transition">Withdrawal review</button>
                        <button type="button" x-on:click="tab = 'senate'" :class="tab === 'senate' ? 'bg-violet-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-lg px-3 py-2 text-xs font-bold transition">Pending Senate</button>
                        <button type="button" x-on:click="tab = 'reinstatement'" :class="tab === 'reinstatement' ? 'bg-violet-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-lg px-3 py-2 text-xs font-bold transition">Reinstatement</button>
                    </div>
                </div>

                <div x-show="tab === 'overview'" x-transition class="space-y-6">
                    @if($selectedStudent && $selectedStudent->isUndergraduate() && $selectedStudent->academicDetail && $currentStatus?->status?->isActive() !== false)
                        @if(Gate::allows('student-status.recommend', $selectedStudent) || Gate::allows('student-status.process-voluntary', $selectedStudent) || Gate::allows('student-status.process-medical', $selectedStudent))
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                                <h2 class="text-base font-bold text-slate-800">Create a withdrawal record</h2>
                                <p class="mt-1 text-xs text-slate-500">Every withdrawal starts as a recommendation. A separately authorized Senate decision records approval or rejection.</p>
                                <form wire:submit.prevent="$set('showStatusConfirmation', true)" class="mt-5 space-y-4">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="withdrawal-type" class="mb-1 block text-xs font-semibold text-slate-700">Withdrawal pathway</label>
                                            <select id="withdrawal-type" wire:model.live="withdrawalType" class="h-11 w-full rounded-xl border-slate-200 text-sm">
                                                @if(Gate::allows('student-status.recommend', $selectedStudent)) <option value="academic">Academic withdrawal recommendation</option> @endif
                                                @if(Gate::allows('student-status.process-voluntary', $selectedStudent)) <option value="voluntary">Voluntary withdrawal</option> @endif
                                                @if(Gate::allows('student-status.process-medical', $selectedStudent)) <option value="medical">Medical withdrawal</option> @endif
                                            </select>
                                            @error('withdrawalType') <p class="mt-1 text-xs text-rose-600">Choose an authorized withdrawal pathway.</p> @enderror
                                        </div>
                                        <div>
                                            <label for="status-session" class="mb-1 block text-xs font-semibold text-slate-700">Academic session</label>
                                            <input id="status-session" list="student-status-sessions" wire:model="academicSession" placeholder="2025/2026" class="h-11 w-full rounded-xl border-slate-200 text-sm">
                                            <datalist id="student-status-sessions">@foreach($sessions as $session)<option value="{{ $session }}">@endforeach</datalist>
                                            @error('academicSession') <p class="mt-1 text-xs text-rose-600">Use a session in YYYY/YYYY format.</p> @enderror
                                        </div>
                                        @if($withdrawalType === 'academic')
                                            <div>
                                                <label for="reason-code" class="mb-1 block text-xs font-semibold text-slate-700">Reason code</label>
                                                <select id="reason-code" wire:model="reasonCode" class="h-11 w-full rounded-xl border-slate-200 text-sm">
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
                                                @error('reasonCode') <p class="mt-1 text-xs text-rose-600">Choose a reason code.</p> @enderror
                                            </div>
                                        @endif
                                        <div>
                                            <label for="status-semester" class="mb-1 block text-xs font-semibold text-slate-700">Semester <span class="font-normal text-slate-400">(optional)</span></label>
                                            <select id="status-semester" wire:model="semester" class="h-11 w-full rounded-xl border-slate-200 text-sm"><option value="">Entire session</option><option value="1">Semester 1</option><option value="2">Semester 2</option></select>
                                            @error('semester') <p class="mt-1 text-xs text-rose-600">Choose semester 1 or 2.</p> @enderror
                                        </div>
                                        <div>
                                            <label for="effective-date" class="mb-1 block text-xs font-semibold text-slate-700">Effective date</label>
                                            <input id="effective-date" type="date" wire:model="effectiveDate" class="h-11 w-full rounded-xl border-slate-200 text-sm">
                                            @error('effectiveDate') <p class="mt-1 text-xs text-rose-600">Enter a valid effective date.</p> @enderror
                                        </div>
                                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 md:col-span-2">
                                            <input type="checkbox" wire:model="reinstatementEligible" class="mt-0.5 rounded border-slate-300 text-violet-700 focus:ring-violet-500">
                                            <span><span class="block text-xs font-semibold text-slate-700">Allow future reinstatement request</span><span class="mt-1 block text-xs text-slate-500">Eligibility is recorded with this status decision.</span></span>
                                        </label>
                                        <div class="md:col-span-2">
                                            <label for="status-reason" class="mb-1 block text-xs font-semibold text-slate-700">Reason</label>
                                            <textarea id="status-reason" wire:model="reason" rows="3" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Record the reason in clear, factual terms."></textarea>
                                            @error('reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="status-notes" class="mb-1 block text-xs font-semibold text-slate-700">Supporting notes <span class="font-normal text-slate-400">(optional)</span></label>
                                            <textarea id="status-notes" wire:model="notes" rows="2" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Board notes or supporting information."></textarea>
                                            @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    @error('selectedStudentId') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                    <div class="flex justify-end"><button type="submit" class="rounded-lg bg-violet-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-violet-800" wire:loading.attr="disabled">Review action</button></div>
                                </form>
                                @if($showStatusConfirmation)
                                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4" role="alertdialog" aria-label="Confirm withdrawal record">
                                        <h3 class="text-sm font-bold text-amber-900">Confirm this status action</h3>
                                        <p class="mt-1 text-xs text-amber-800">This will create a {{ $withdrawalType }} withdrawal recommendation for Senate review for {{ $selectedStudent->surname }} {{ $selectedStudent->firstname }} ({{ $selectedStudent->academicDetail?->matric_no }}), effective {{ $effectiveDate }}. Senate decisions are recorded separately by an authorized officer. The action will be recorded in the immutable audit history.</p>
                                        <div class="mt-3 flex flex-wrap justify-end gap-2"><button type="button" wire:click="$set('showStatusConfirmation', false)" class="rounded-lg border border-amber-300 px-4 py-2 text-xs font-semibold text-amber-900">Go back</button><button type="button" wire:click="createStatusRecord" wire:loading.attr="disabled" class="rounded-lg bg-rose-700 px-4 py-2 text-xs font-bold text-white hover:bg-rose-800">Confirm and create record</button></div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

                <div x-show="tab === 'review'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex flex-col gap-2">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Due for withdrawal review</h2>
                            <p class="mt-1 text-xs text-slate-500">These students match the configured academic withdrawal rules and are ready for Senate review. No official withdrawal is created until a recommendation is approved.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select aria-label="Filter due review by academic session" wire:model.live="dueReviewSession" class="h-9 rounded-lg border-slate-200 text-xs"><option value="">All sessions</option>@foreach($sessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach</select>
                            <select aria-label="Filter due review by department" wire:model.live="dueReviewDepartment" class="h-9 rounded-lg border-slate-200 text-xs"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select>
                            <span wire:loading.delay wire:target="dueReviewSession,dueReviewDepartment" class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-violet-700">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                Loading
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto" wire:loading.class="opacity-70" wire:target="dueReviewSession,dueReviewDepartment">
                        <table class="w-full min-w-[680px] text-left text-xs text-slate-600">
                            <thead class="border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-400"><tr><th class="px-3 py-3">Student</th><th class="px-3 py-3">Eligibility rule</th><th class="px-3 py-3">Session / Department</th><th class="px-3 py-3">Action</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($dueForWithdrawalReview as $student)
                                    @php($eligibility = app(\App\Services\AcademicProgressionService::class)->evaluateWithdrawalEligibility($student))
                                    <tr>
                                        <td class="px-3 py-3"><span class="block font-semibold text-slate-800">{{ $student->surname }} {{ $student->firstname }}</span><span class="text-slate-400">{{ $student->academicDetail?->matric_no }}</span></td>
                                        <td class="px-3 py-3"><span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-1 font-bold text-rose-700">{{ $eligibility['reason_code'] ?? 'ELIGIBLE' }}</span><p class="mt-2 max-w-xs text-slate-500">{{ $eligibility['reason'] ?? 'Triggered by institutional withdrawal rules.' }}</p></td>
                                        <td class="px-3 py-3">{{ $student->academicDetail?->acad_session ?? 'N/A' }}<span class="block text-slate-400">{{ $student->academicDetail?->department?->name ?? 'Department unavailable' }}</span></td>
                                        <td class="px-3 py-3">
                                        <button type="button" wire:click="selectStudent('{{ $student->id }}')" x-data="{ loading: false }" x-on:click="loading = true" x-on:livewire:loadend.window="loading = false" class="inline-flex items-center gap-1 font-semibold text-violet-700 hover:underline">
                                            <span x-show="loading" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                            <span x-show="!loading">Open student</span>
                                            <span x-show="loading" class="text-[10px] uppercase tracking-wide">Loading</span>
                                        </button>
                                    </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">No students are currently due for withdrawal review.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="tab === 'senate'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div><h2 class="text-base font-bold text-slate-800">Pending Senate withdrawal decisions</h2><p class="mt-1 text-xs text-slate-500">Undergraduate recommendations awaiting an authorized Senate decision. Filter by session, department, type, or effective date. Historical results remain available.</p></div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select aria-label="Filter by academic session" wire:model.live="senateQueueSession" class="h-9 rounded-lg border-slate-200 text-xs"><option value="">All sessions</option>@foreach($sessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach</select>
                            <select aria-label="Filter by department" wire:model.live="senateQueueDepartment" class="h-9 rounded-lg border-slate-200 text-xs"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select>
                            <select aria-label="Filter by withdrawal type" wire:model.live="senateQueueType" class="h-9 rounded-lg border-slate-200 text-xs"><option value="">All types</option><option value="academic">Academic</option><option value="voluntary">Voluntary</option><option value="medical">Medical</option></select>
                            <input type="date" aria-label="Effective from" wire:model.live="senateQueueDateFrom" class="h-9 rounded-lg border-slate-200 text-xs">
                            <input type="date" aria-label="Effective to" wire:model.live="senateQueueDateTo" class="h-9 rounded-lg border-slate-200 text-xs">
                            <span wire:loading.delay wire:target="senateQueueSession,senateQueueDepartment,senateQueueType,senateQueueDateFrom,senateQueueDateTo" class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-violet-700">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                Loading
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto" wire:loading.class="opacity-70" wire:target="senateQueueSession,senateQueueDepartment,senateQueueType,senateQueueDateFrom,senateQueueDateTo">
                        <table class="w-full min-w-[760px] text-left text-xs text-slate-600">
                            <thead class="border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-400"><tr><th class="px-3 py-3">Student</th><th class="px-3 py-3">Withdrawal</th><th class="px-3 py-3">Session / Department</th><th class="px-3 py-3">Reason</th><th class="px-3 py-3">Action</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($pendingDecisions as $record)
                                    <tr>
                                        <td class="px-3 py-3"><span class="block font-semibold text-slate-800">{{ $record->user?->surname }} {{ $record->user?->firstname }}</span><span class="text-slate-400">{{ $record->user?->academicDetail?->matric_no }}</span></td>
                                        <td class="px-3 py-3"><span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-1 font-bold text-amber-800">{{ $record->status->label() }}</span></td>
                                        <td class="px-3 py-3">{{ $record->academic_session }}<span class="block text-slate-400">{{ $record->user?->academicDetail?->department?->name }}</span></td>
                                        <td class="max-w-48 px-3 py-3">{{ $record->reason }}</td>
                                        <td class="px-3 py-3">
                                            <button type="button" wire:click="selectStudent('{{ $record->user_id }}')" x-data="{ loading: false }" x-on:click="loading = true" x-on:livewire:loadend.window="loading = false" class="inline-flex items-center gap-1 font-semibold text-violet-700 hover:underline">
                                                <span x-show="loading" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                                <span x-show="!loading">Review history</span>
                                                <span x-show="loading" class="text-[10px] uppercase tracking-wide">Loading</span>
                                            </button>
                                            @can('student-status.decide-senate', $record->user)<div class="mt-2 flex gap-2"><button type="button" wire:click="beginSenateDecision({{ $record->id }}, true)" class="rounded bg-emerald-700 px-2 py-1 font-bold text-white">Approve</button><button type="button" wire:click="beginSenateDecision({{ $record->id }}, false)" class="rounded bg-rose-700 px-2 py-1 font-bold text-white">Reject</button></div>@endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">No Senate withdrawal decisions match these filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="tab === 'reinstatement'" x-transition class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft-xl sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div><h2 class="text-base font-bold text-slate-800">Reinstatement requests</h2><p class="mt-1 text-xs text-slate-500">Department and Faculty reviews happen in order. Senate approval appends a reinstated history event.</p></div>
                        <span wire:loading.delay class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-violet-700">
                            <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                            Loading
                        </span>
                    </div>
                    <div class="mt-4 space-y-3" wire:loading.class="opacity-70">
                        @forelse($reinstatementRequests as $request)
                            <article class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-800">{{ $request->user?->surname }} {{ $request->user?->firstname }} <span class="font-normal text-slate-500">· {{ $request->user?->academicDetail?->matric_no }}</span></h3>
                                        <p class="mt-1 text-xs text-slate-500">{{ $request->user?->academicDetail?->department?->name }} · Request session {{ $request->academic_session }}</p>
                                        @if($request->originalWithdrawal)<p class="mt-1 text-xs text-slate-600">Original status: {{ $request->originalWithdrawal->status->label() }} · {{ $request->originalWithdrawal->academic_session }} · Effective {{ $request->originalWithdrawal->effective_date?->format('d M Y') ?? 'date unavailable' }}@if($request->originalWithdrawal->senate_reference) · Senate Ref {{ $request->originalWithdrawal->senate_reference }}@endif</p>@endif
                                        <p class="mt-1 text-xs font-semibold text-violet-700">{{ str_replace('_', ' ', $request->senate_decision) }}</p>
                                        <button type="button" wire:click="selectStudent('{{ $request->user_id }}')" x-data="{ loading: false }" x-on:click="loading = true" x-on:livewire:loadend.window="loading = false" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-violet-700 hover:underline">
                                            <span x-show="loading" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700"></span>
                                            <span x-show="!loading">View full status, progression and registration history</span>
                                            <span x-show="loading" class="text-[10px] uppercase tracking-wide">Loading</span>
                                        </button>
                                        @if($request->notes)<p class="mt-2 text-xs text-slate-600">{{ $request->notes }}</p>@endif
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <div class="w-full min-w-[220px] lg:w-64"><label class="sr-only" for="review-notes-{{ $request->id }}">Review notes</label><textarea id="review-notes-{{ $request->id }}" wire:model.defer="reviewNotesByRequest.{{ $request->id }}" rows="2" class="w-full rounded-lg border-slate-200 text-xs" placeholder="Optional review notes"></textarea></div>
                                        @if($request->senate_decision === \App\Services\StudentStatusService::REINSTATEMENT_REQUESTED)
                                            @can('student-status.review-department-reinstatement', $request->user)<button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'DEPARTMENT', true)" wire:confirm="Approve the Department review for this reinstatement request?" class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white">Department approve</button><button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'DEPARTMENT', false)" wire:confirm="Reject the Department review for this reinstatement request?" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">Department reject</button>@endcan
                                        @elseif($request->senate_decision === \App\Services\StudentStatusService::REINSTATEMENT_DEPARTMENT_APPROVED)
                                            @can('student-status.review-faculty-reinstatement', $request->user)<button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'FACULTY', true)" wire:confirm="Approve the Faculty review for this reinstatement request?" class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white">Faculty approve</button><button type="button" wire:click="recordReinstatementReview({{ $request->id }}, 'FACULTY', false)" wire:confirm="Reject the Faculty review for this reinstatement request?" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">Faculty reject</button>@endcan
                                        @elseif($request->senate_decision === \App\Services\StudentStatusService::REINSTATEMENT_FACULTY_APPROVED)
                                            @can('student-status.decide-senate', $request->user)<button type="button" wire:click="beginReinstatementDecision({{ $request->id }}, true)" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white">Senate approve</button><button type="button" wire:click="beginReinstatementDecision({{ $request->id }}, false)" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">Senate reject</button>@endcan
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-xs text-slate-500">No reinstatement requests are currently awaiting review.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if($showDecisionModal)
        <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="senate-decision-title" x-data x-on:keydown.escape.window="$wire.closeDecisionModal()">
            <div class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-soft-xl sm:p-6" x-on:click.outside="$wire.closeDecisionModal()">
                <h2 id="senate-decision-title" class="text-lg font-bold text-slate-800">{{ $approveDecision ? 'Confirm Senate approval' : 'Record Senate rejection' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $approveDecision ? 'Approval changes the authoritative status and disables new UG academic actions.' : 'Rejection leaves the student active and preserves the recommendation history.' }}</p>
                <div class="mt-4 space-y-4">
                    <div><label for="decision-ref" class="mb-1 block text-xs font-semibold text-slate-700">Senate reference {{ $approveDecision ? '*' : '(optional)' }}</label><input id="decision-ref" wire:model="senateReference" class="h-11 w-full rounded-xl border-slate-200 text-sm" placeholder="SEN-2026-104">@error('senateReference')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                    <div><label for="decision-date" class="mb-1 block text-xs font-semibold text-slate-700">Decision date *</label><input id="decision-date" type="date" wire:model="senateDecisionDate" class="h-11 w-full rounded-xl border-slate-200 text-sm">@error('senateDecisionDate')<p class="mt-1 text-xs text-rose-600">Enter a valid decision date.</p>@enderror</div>
                    <div><label for="decision-notes" class="mb-1 block text-xs font-semibold text-slate-700">{{ $approveDecision ? 'Decision notes' : 'Reason for rejection *' }}</label><textarea id="decision-notes" wire:model="decisionNotes" rows="3" class="w-full rounded-xl border-slate-200 text-sm"></textarea>@error('decisionNotes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" wire:click="closeDecisionModal" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
                        <button type="button" wire:click="{{ $decisionIsReinstatement ? 'decideReinstatement' : 'decideWithdrawal' }}" wire:loading.attr="disabled" x-data="{ submitting: false }" x-on:click="submitting = true" x-on:livewire:loadend.window="submitting = false" class="inline-flex items-center gap-2 rounded-lg {{ $approveDecision ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-rose-700 hover:bg-rose-800' }} px-4 py-2 text-sm font-bold text-white">
                            <span x-show="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                            <span x-show="!submitting">{{ $approveDecision ? 'Confirm approval' : 'Confirm rejection' }}</span>
                            <span x-show="submitting" class="text-[10px] uppercase tracking-wide">Working</span>
                        </button>
                    </div>
            </div>
        </div>
    @endif
</div>
