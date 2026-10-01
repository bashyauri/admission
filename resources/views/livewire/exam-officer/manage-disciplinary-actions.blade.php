<div class="space-y-6">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Exam Officer</p>
                <h1 class="mt-2 text-2xl font-bold text-slate-900">Disciplinary Sanctions</h1>
            </div>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                wire:click="searchStudents"
            >
                Refresh list
            </button>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-4 lg:grid-cols-4">
            <label class="block text-sm font-medium text-slate-700">
                Search student
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                    placeholder="Matric no or student's name"
                >
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Academic session
                <select wire:model="selectedSession" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <option value="all">All sessions</option>
                    @foreach($availableSessions as $session)
                        <option value="{{ $session }}">{{ $session }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Status
                <select wire:model="statusFilter" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Lifted</option>
                </select>
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Sanction type
                <select wire:model="sanctionFilter" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <option value="all">All</option>
                    <option value="repeat_session">Repeat Session</option>
                    <option value="course_cancellation">Course Cancellation</option>
                    <option value="suspension">Suspension</option>
                    <option value="expulsion">Expulsion</option>
                </select>
            </label>
        </div>
    </div>

    @if(! empty($searchResults))
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Student Search Results</h2>
                <span class="text-xs uppercase tracking-wide text-slate-500">{{ count($searchResults) }} match(es)</span>
            </div>

            <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                @foreach($searchResults as $result)
                    <div class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $result['name'] }}</p>
                            <p class="text-sm text-slate-600">{{ $result['matric_no'] }} • {{ $result['department'] }}</p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-100"
                            wire:click="openApplyModal('{{ $result['id'] }}')"
                        >
                            Add sanction
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Student</th>
                        <th class="px-4 py-3 font-semibold">Sanction</th>
                        <th class="px-4 py-3 font-semibold">Session</th>
                        <th class="px-4 py-3 font-semibold">Ref</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($actions as $action)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $action->user?->firstname ?? '' }} {{ $action->user?->surname ?? '' }}</div>
                                <div class="text-xs text-slate-500">{{ $action->academicDetail?->matric_no ?? 'No matric number' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ str_replace('_', ' ', $action->sanction_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $action->academic_session ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $action->senate_ref_no ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @if($action->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">Lifted</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if($action->is_active)
                                        <button type="button" class="rounded-lg border border-amber-200 bg-amber-50 px-2 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100" wire:click="liftSanction({{ $action->id }})">
                                            Lift sanction
                                        </button>
                                    @endif
                                    @if(! $action->is_appealed)
                                        <button type="button" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100" wire:click="resolveAppeal({{ $action->id }}, 'quashed')">
                                            Appeal quashed
                                        </button>
                                        <button type="button" class="rounded-lg border border-sky-200 bg-sky-50 px-2 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100" wire:click="resolveAppeal({{ $action->id }}, 'upheld')">
                                            Appeal upheld
                                        </button>
                                    @endif
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100" wire:click="viewDetail({{ $action->id }})">
                                        View detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="mx-auto max-w-md">
                                    <p class="text-lg font-semibold text-slate-800">No disciplinary records found</p>
                                    <p class="mt-2 text-sm text-slate-500">Search for a student or apply a new sanction to begin.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($actions->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                {{ $actions->links() }}
            </div>
        @endif
    </div>

    @if($showDetailModal && $selectedAction)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Disciplinary record</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-900">{{ trim(($selectedAction->user?->firstname ?? '') . ' ' . ($selectedAction->user?->surname ?? '')) }}</h3>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600" wire:click="closeDetailModal">✕</button>
                </div>

                <div class="grid gap-4 md:grid-cols-2 text-sm">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Student</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ trim(($selectedAction->user?->firstname ?? '') . ' ' . ($selectedAction->user?->surname ?? '')) }}</div>
                        <div class="text-slate-600">{{ $selectedAction->academicDetail?->matric_no ?? 'No matric number' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Sanction</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ str_replace('_', ' ', $selectedAction->sanction_type) }}</div>
                        <div class="text-slate-600">{{ $selectedAction->academic_session ?? 'N/A' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Senate reference</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $selectedAction->senate_ref_no ?? 'N/A' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Status</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $selectedAction->is_active ? 'Active' : 'Lifted' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Remarks</div>
                        <div class="mt-1 whitespace-pre-line text-slate-700">{{ $selectedAction->remarks ?: 'No remarks recorded.' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Verdict date</div>
                        <div class="mt-1 text-slate-900">{{ $selectedAction->verdict_date?->format('d M Y') ?? 'N/A' }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Sanctioned by</div>
                        <div class="mt-1 text-slate-900">{{ trim(($selectedAction->sanctionedBy?->firstname ?? '') . ' ' . ($selectedAction->sanctionedBy?->surname ?? '')) ?: 'N/A' }}</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" wire:click="closeDetailModal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showApplyModal && $selectedStudent)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Record sanction</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-900">{{ trim(($selectedStudent->firstname ?? '') . ' ' . ($selectedStudent->surname ?? '')) }}</h3>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600" wire:click="closeApplyModal">✕</button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700">
                        Sanction type
                        <select wire:model="sanctionType" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="repeat_session">Repeat session</option>
                            <option value="course_cancellation">Course cancellation</option>
                            <option value="suspension">Suspension</option>
                            <option value="expulsion">Expulsion</option>
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-slate-700">
                        Academic session
                        <input type="text" wire:model="academicSession" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="2025/2026">
                    </label>

                    <label class="block text-sm font-medium text-slate-700">
                        Effective session
                        <input type="text" wire:model="effectiveSession" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="2025/2026">
                    </label>

                    <label class="block text-sm font-medium text-slate-700">
                        Semester
                        <select wire:model="semester" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="first">First semester</option>
                            <option value="second">Second semester</option>
                            <option value="summer">Summer</option>
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-slate-700">
                        Verdict date
                        <input type="date" wire:model="verdictDate" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </label>

                    <label class="block text-sm font-medium text-slate-700">
                        Effective date
                        <input type="date" wire:model="effectiveDate" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </label>

                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                        Senate reference
                        <input type="text" wire:model="senateRefNo" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="SEN-2026-001">
                    </label>

                    @if($sanctionType === 'course_cancellation' && ! empty($studentResults))
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                            Related result
                            <select wire:model="resultId" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                                <option value="">Select a result</option>
                                @foreach($studentResults as $result)
                                    <option value="{{ $result['id'] }}">{{ $result['course'] }} ({{ $result['session'] }})</option>
                                @endforeach
                            </select>
                        </label>
                    @endif

                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                        Remarks
                        <textarea wire:model="remarks" rows="4" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Record the disciplinary rationale and supporting details."></textarea>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" wire:click="closeApplyModal">
                        Cancel
                    </button>
                    <button type="button" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700" wire:click="applySanction">
                        Save sanction
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
