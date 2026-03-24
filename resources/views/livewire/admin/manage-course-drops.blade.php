<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3">
        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                <h5 class="mb-1 dark:text-white">Manage Course Drops</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Drop course registrations in bulk with a preview-first flow and saved audit trail.
                </p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Academic Session</label>
                        <input type="text" wire:model.live="academicSession"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-3 py-2"
                            placeholder="e.g. 2025/2026">
                        @error('academicSession')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Course Codes</label>
                        <input type="text" wire:model.live="courseCodes"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-3 py-2"
                            placeholder="FUBK_ARC108, FUBK_QTS104">
                        <p class="text-xs text-gray-500 mt-1">Comma-separated values. Use the exact stored course code.</p>
                        @error('courseCodes')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Department (Optional)</label>
                        <select wire:model.live="departmentId"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-3 py-2">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('departmentId')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Programme (Optional)</label>
                        <select wire:model.live="programmeId"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-3 py-2">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                                <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                            @endforeach
                        </select>
                        @error('programmeId')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Level (Optional)</label>
                        <select wire:model.live="studentLevelId"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-3 py-2">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->level }}</option>
                            @endforeach
                        </select>
                        @error('studentLevelId')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Admin Note (Optional)</label>
                        <input type="text" wire:model.live="note"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white px-3 py-2"
                            placeholder="Reason for this bulk drop">
                        @error('note')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <p class="text-sm text-amber-800 dark:text-amber-300 font-semibold">Safety Step</p>
                    <p class="text-xs text-amber-700 dark:text-amber-200 mt-1">
                        Preview first. To execute drop, type <span class="font-semibold">DROP</span> in the confirmation box.
                    </p>
                    <div class="mt-3">
                        <input type="text" wire:model.live="confirmationText"
                            class="w-full md:w-80 rounded-lg border border-amber-300 dark:border-amber-700 dark:bg-gray-900 dark:text-white px-3 py-2"
                            placeholder="Type DROP to confirm execution">
                        @error('confirmationText')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button wire:click="previewDrop"
                        class="px-5 py-2.5 text-xs font-bold uppercase rounded-lg border border-fuchsia-500 text-fuchsia-600 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-900/30 transition-colors">
                        Preview Drop
                    </button>

                    <button wire:click="executeDrop"
                        class="px-5 py-2.5 text-xs font-bold uppercase rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                        Execute Drop
                    </button>

                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Preview Matches: <span class="font-semibold">{{ $previewMatchedCount }}</span>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Last Dropped: <span class="font-semibold">{{ $lastDroppedCount }}</span>
                    </div>
                </div>

                @if($previewMatchedCount > 0)
                    <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-800">
                            <h6 class="dark:text-white mb-1">Preview Sample</h6>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Showing up to 12 matched registrations before execution.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                                <thead class="bg-white dark:bg-gray-950/40">
                                    <tr>
                                        <th class="px-4 py-3">Student</th>
                                        <th class="px-4 py-3">Matric No</th>
                                        <th class="px-4 py-3">Department</th>
                                        <th class="px-4 py-3">Course Code</th>
                                        <th class="px-4 py-3">Course Title</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewRows as $previewRow)
                                        <tr class="border-t border-gray-100 dark:border-gray-800">
                                            <td class="px-4 py-3">{{ $previewRow['student_name'] }}</td>
                                            <td class="px-4 py-3">{{ $previewRow['matric_no'] }}</td>
                                            <td class="px-4 py-3">{{ $previewRow['department_name'] ?? '-' }}</td>
                                            <td class="px-4 py-3 font-semibold">{{ $previewRow['course_code'] }}</td>
                                            <td class="px-4 py-3">{{ $previewRow['course_title'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="w-full max-w-full px-3 mt-6">
        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                <h6 class="dark:text-white">Recent Course Drop Audits</h6>
            </div>

            <div class="flex-auto p-0 overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Admin</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Session</th>
                            <th class="px-4 py-3">Courses</th>
                            <th class="px-4 py-3">Matched</th>
                            <th class="px-4 py-3">Dropped</th>
                            <th class="px-4 py-3">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $audit->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ trim(($audit->adminUser->firstname ?? '') . ' ' . ($audit->adminUser->surname ?? '')) ?: 'Unknown' }}</td>
                                <td class="px-4 py-3 uppercase">{{ $audit->action_type }}</td>
                                <td class="px-4 py-3">{{ $audit->academic_session }}</td>
                                <td class="px-4 py-3">{{ implode(', ', $audit->course_codes ?? []) }}</td>
                                <td class="px-4 py-3">{{ $audit->matched_count }}</td>
                                <td class="px-4 py-3">{{ $audit->dropped_count }}</td>
                                <td class="px-4 py-3">{{ $audit->note ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No audit records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
