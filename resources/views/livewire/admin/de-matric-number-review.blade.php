<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <div class="border-b border-slate-200 pb-5">
        <p class="text-xs font-black uppercase tracking-wide text-violet-700">Direct Entry Review</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">DE Matric Number Audit</h1>
        <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">
            Reviewing {{ $currentSession }} only. DE matric numbers use the 20 entry code (for example, 2026/2027 numbers start with 2620). Review the suggested value before approving an update.
        </p>
    </div>

    @if (session()->has('success'))
        <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-sm font-black text-slate-900">Candidates needing a matric correction</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $academicDetails->total() }} record(s) flagged for review</p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <label class="text-xs font-bold text-slate-700">
                    Rows per page
                    <select wire:model.live="perPage" class="ml-2 rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm font-semibold text-slate-700 outline-none focus:border-violet-600 focus:ring-2 focus:ring-violet-100">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>

                <button
                    type="button"
                    wire:click="openConfirmationModal"
                    @disabled($selectedAcademicDetailIds === [])
                    class="inline-flex items-center justify-center rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Update Selected ({{ count($selectedAcademicDetailIds) }})
                </button>
            </div>
        </div>

        @error('selectedAcademicDetailIds')
            <p class="border-b border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 sm:px-6">{{ $message }}</p>
        @enderror

        <div class="grid grid-cols-1 gap-3 border-b border-slate-200 px-4 py-4 sm:grid-cols-2 sm:px-6">
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Search Student</label>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Matric or student name"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none focus:border-violet-600 focus:ring-2 focus:ring-violet-100"
                >
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-700">Department</label>
                <select wire:model.live="departmentId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-violet-600 focus:ring-2 focus:ring-violet-100">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
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
                                aria-label="Select all records on this page"
                                class="h-4 w-4 rounded border-slate-300 text-violet-700 focus:ring-violet-600"
                            >
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Student</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Department</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Session</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Current Matric</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Suggested</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($academicDetails as $academicDetail)
                        <tr wire:key="de-matric-{{ $academicDetail->id }}" class="hover:bg-slate-50">
                            <td class="px-4 py-4 sm:px-6">
                                <input
                                    type="checkbox"
                                    value="{{ $academicDetail->id }}"
                                    wire:model.live="selectedAcademicDetailIds"
                                    class="h-4 w-4 rounded border-slate-300 text-violet-700 focus:ring-violet-600"
                                >
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                <div class="font-bold text-slate-900">{{ format_user_name($academicDetail->user, 'Unknown Student') }}</div>
                                <div class="text-xs text-slate-500">{{ $academicDetail->user?->email ?? 'No email' }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                {{ $academicDetail->department?->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                {{ $academicDetail->admission_session ?? $academicDetail->acad_session ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-bold text-rose-700">
                                {{ $academicDetail->matric_no }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-black text-emerald-700">
                                {{ $academicDetail->suggested_matric_no ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4">
                                <button
                                    type="button"
                                    wire:click="updateSingleMatricNumber({{ $academicDetail->id }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-bold text-violet-800 transition hover:bg-violet-100"
                                >
                                    Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 sm:px-6">
                                No DE matric-number issues found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($academicDetails->hasPages())
            <div class="border-t border-slate-200 px-4 py-3 sm:px-6">
                {{ $academicDetails->links() }}
            </div>
        @endif
    </div>

    @if ($showConfirmationModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="text-xl font-black text-slate-900">Confirm DE matric update</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    You are about to update the selected DE records to their suggested sequence values. This action changes the student matric numbers and should be reviewed carefully.
                </p>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        wire:click="closeConfirmationModal"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="updateSelectedMatricNumbers"
                        class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-bold text-white hover:bg-violet-800"
                    >
                        Confirm update
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
