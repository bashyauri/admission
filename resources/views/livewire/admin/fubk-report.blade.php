<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
        <div class="relative overflow-hidden break-words rounded-2xl border-0 bg-white shadow-soft-xl dark:bg-gray-950 dark:shadow-soft-dark-xl">
            <div class="border-b border-gray-200 bg-gradient-to-r from-cyan-600 via-sky-600 to-blue-700 p-6 text-white dark:border-gray-800">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-100">FUBK Reports</p>
                        <h4 class="mt-2 text-white">Student movement and course-registration exports</h4>
                        <p class="mt-2 text-sm text-cyan-50/90">
                            Review fresh and returning students for the active academic session, inspect biodata and registered courses, and export the active view as CSV.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <button wire:click="showFresh"
                            class="rounded-2xl border px-5 py-4 text-left transition {{ $reportType === 'fresh' ? 'border-white/70 bg-white text-sky-700 shadow-soft-md' : 'border-white/20 bg-white/10 text-white hover:bg-white/20' }}">
                            <p class="text-xs font-bold uppercase tracking-[0.2em]">Fresh Students</p>
                            <p class="mt-1 text-sm {{ $reportType === 'fresh' ? 'text-slate-600' : 'text-cyan-100' }}">New intakes in this session with their registered courses.</p>
                        </button>

                        <button wire:click="showReturning"
                            class="rounded-2xl border px-5 py-4 text-left transition {{ $reportType === 'returning' ? 'border-white/70 bg-white text-sky-700 shadow-soft-md' : 'border-white/20 bg-white/10 text-white hover:bg-white/20' }}">
                            <p class="text-xs font-bold uppercase tracking-[0.2em]">Returning Students</p>
                            <p class="mt-1 text-sm {{ $reportType === 'returning' ? 'text-slate-600' : 'text-cyan-100' }}">Continuing students and the courses they registered.</p>
                        </button>

                        <button wire:click="showAll"
                            class="rounded-2xl border px-5 py-4 text-left transition {{ $reportType === 'all' ? 'border-white/70 bg-white text-sky-700 shadow-soft-md' : 'border-white/20 bg-white/10 text-white hover:bg-white/20' }}">
                            <p class="text-xs font-bold uppercase tracking-[0.2em]">All Students</p>
                            <p class="mt-1 text-sm {{ $reportType === 'all' ? 'text-slate-600' : 'text-cyan-100' }}">Fresh and returning students in one report.</p>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid gap-4 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Search students or courses</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-cyan-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="Name, email, phone, matric no, JAMB no, course code...">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Department</label>
                        <select wire:model.live="departmentId"
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-cyan-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Programme</label>
                        <select wire:model.live="programmeId"
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-cyan-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">All programmes</option>
                            @foreach ($programmes as $programme)
                                <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Academic session</label>
                        <input type="text" value="{{ $academicSession }}" disabled
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Rows per page</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-cyan-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <div>
                        <p class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Select biodata columns</p>
                        <div class="grid grid-cols-2 gap-2 rounded-2xl border border-gray-200 p-3 dark:border-gray-700 md:grid-cols-3 lg:grid-cols-4">
                            @foreach ($columnOptions as $columnKey => $columnLabel)
                                <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" wire:model.live="selectedColumns" value="{{ $columnKey }}"
                                        class="h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                                    <span>{{ $columnLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,420px)]">
                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 p-4 dark:border-cyan-900/40 dark:bg-cyan-900/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Current view</p>
                        <p class="mt-2 text-lg font-semibold text-slate-800 dark:text-white">
                            {{ $reportType === 'fresh' ? 'Fresh students' : ($reportType === 'returning' ? 'Returning students' : 'All students') }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                            {{ $students->total() }} student record{{ $students->total() === 1 ? '' : 's' }} found for {{ $academicSession }}.
                        </p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Returning reports are best exported with Course Registration to include level, semester, and credit units.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <a href="{{ $this->exportUrl }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-slate-900 to-slate-700 px-5 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:scale-[1.01] dark:from-slate-100 dark:to-white dark:text-slate-900">
                            Biodata CSV
                        </a>
                        <a href="{{ $this->pdfUrl }}" target="_blank"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-700 transition hover:scale-[1.01] dark:border-slate-700 dark:bg-gray-900 dark:text-slate-100">
                            Biodata PDF
                        </a>

                        <a href="{{ $this->courseCsvUrl }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-cyan-700 to-blue-700 px-5 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:scale-[1.01]">
                            Course Reg CSV
                        </a>
                        <a href="{{ $this->coursePdfUrl }}" target="_blank"
                            class="inline-flex items-center justify-center rounded-2xl border border-cyan-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.18em] text-cyan-700 transition hover:scale-[1.01] dark:border-cyan-700 dark:bg-gray-900 dark:text-cyan-300">
                            Course Reg PDF (Queue)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 w-full max-w-full px-3">
        <div class="relative break-words rounded-2xl border-0 bg-white shadow-soft-xl dark:bg-gray-950 dark:shadow-soft-dark-xl">
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                <h6 class="mb-1 dark:text-white">{{ $reportType === 'fresh' ? 'Fresh student list' : 'Returning student list' }}</h6>
                <p class="text-sm text-gray-600 dark:text-gray-400">Each row includes biodata, admission context, and the courses registered in the selected session.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left text-sm text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            @foreach ($selectedColumns as $selectedColumn)
                                <th class="px-4 py-3">{{ $columnOptions[$selectedColumn] ?? ucfirst(str_replace('_', ' ', $selectedColumn)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr class="border-t border-gray-100 align-top dark:border-gray-800">
                                @foreach ($selectedColumns as $selectedColumn)
                                    <td class="px-4 py-4">
                                        @if ($selectedColumn === 'registered_courses')
                                            <div class="flex max-w-md flex-wrap gap-2">
                                                @forelse ($student['registered_courses'] as $course)
                                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $course }}</span>
                                                @empty
                                                    <span class="text-xs text-slate-500">No registered courses</span>
                                                @endforelse
                                            </div>
                                        @elseif ($selectedColumn === 'department_name')
                                            <span class="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                                                {{ $student[$selectedColumn] ?: 'N/A' }}
                                            </span>
                                        @elseif ($selectedColumn === 'programme_name')
                                            <span class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ $student[$selectedColumn] ?: 'N/A' }}</span>
                                        @else
                                            <span>{{ $student[$selectedColumn] ?: 'N/A' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($selectedColumns) }}" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    No student records matched this report.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
