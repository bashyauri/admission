<div class="mx-auto max-w-6xl space-y-6 p-4 sm:p-6">
    <div class="border-b border-slate-200 pb-5">
        <p class="text-xs font-black uppercase tracking-wide text-sky-700">Academic Records</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Admission Session Review</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
            Review students whose admission cohort is missing. The proposed value is derived from the first two digits of the matric number; only selected records are updated after confirmation.
        </p>
    </div>

    @if(session()->has('success'))
        <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-sm font-black text-slate-900">Pending Review</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $academicDetails->total() }} record(s) have a proposed session.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <label class="text-xs font-bold text-slate-700">
                    Rows per page
                    <select wire:model.live="perPage" class="ml-2 rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm font-semibold text-slate-700 outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>

                <button
                    type="button"
                    wire:click="openConfirmationModal"
                    @disabled($selectedAcademicDetailIds === [])
                    class="inline-flex items-center justify-center rounded-lg bg-sky-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-sky-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Synchronize Selected ({{ count($selectedAcademicDetailIds) }})
                </button>
            </div>
        </div>

        @error('selectedAcademicDetailIds')
            <p class="border-b border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 sm:px-6">{{ $message }}</p>
        @enderror

        <div class="grid grid-cols-1 gap-3 border-b border-slate-200 px-4 py-4 sm:grid-cols-3 sm:px-6">
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Search Student</label>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Matric number or name"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100"
                >
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Department</label>
                <select wire:model.live="filterDepartmentId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Proposed Admission Session</label>
                <select wire:model.live="filterAdmissionSession" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
                    <option value="">All Proposed Sessions</option>
                    @foreach($availableAdmissionSessions as $session)
                        <option value="{{ $session }}">{{ $session }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            @php
                $currentPageIds = $academicDetails->pluck('id')->map(fn ($id) => (string) $id)->all();
                $selectedIds = array_map('strval', $selectedAcademicDetailIds);
                $isCurrentPageSelected = $currentPageIds !== [] && array_diff($currentPageIds, $selectedIds) === [];
            @endphp
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th class="w-12 px-4 py-3 text-left sm:px-6">
                            <input
                                type="checkbox"
                                wire:click="toggleCurrentPageSelection(@js($currentPageIds))"
                                @checked($isCurrentPageSelected)
                                aria-label="Select all students on this page"
                                class="h-4 w-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600"
                            >
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Matric Number</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Student</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Department</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Current Academic Session</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Admission Session to Set</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($academicDetails as $academicDetail)
                        <tr wire:key="admission-session-{{ $academicDetail->id }}" class="hover:bg-slate-50">
                            <td class="px-4 py-4 sm:px-6">
                                <input
                                    type="checkbox"
                                    value="{{ $academicDetail->id }}"
                                    wire:model.live="selectedAcademicDetailIds"
                                    class="h-4 w-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600"
                                >
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-bold text-slate-900">
                                {{ $academicDetail->matric_no }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                {{ trim(($academicDetail->user?->surname ?? '') . ' ' . ($academicDetail->user?->firstname ?? '') . ' ' . ($academicDetail->user?->m_name ?? '')) ?: 'Unknown Student' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                {{ $academicDetail->department?->name ?? 'Unassigned' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-700">
                                {{ $academicDetail->acad_session }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="inline-flex rounded-md bg-sky-50 px-2.5 py-1 text-sm font-black text-sky-800">
                                    {{ $academicDetail->proposed_admission_session }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">
                                No students match the current review filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($academicDetails->hasPages())
            <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                {{ $academicDetails->links() }}
            </div>
        @endif
    </div>

    @if($showConfirmationModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/60" wire:click="closeConfirmationModal"></div>
            <div class="relative w-full max-w-md border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-black text-slate-900">Confirm Synchronization</h2>
                    <p class="mt-1 text-sm leading-5 text-slate-600">
                        Set the admission session for {{ count($selectedAcademicDetailIds) }} selected student record(s) to the displayed matric-number-derived values?
                    </p>
                </div>
                <div class="flex justify-end gap-3 bg-slate-50 px-5 py-4">
                    <button type="button" wire:click="closeConfirmationModal" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="button" wire:click="synchronizeSelected" wire:loading.attr="disabled" class="rounded-lg bg-sky-700 px-4 py-2 text-sm font-bold text-white hover:bg-sky-800 disabled:opacity-50">
                        <span wire:loading.remove wire:target="synchronizeSelected">Approve & Synchronize</span>
                        <span wire:loading wire:target="synchronizeSelected">Synchronizing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
