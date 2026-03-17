<div>
    <div class="flex flex-wrap -mx-3 mt-6">
        <div class="w-full max-w-full px-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Course Registration History</h2>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Select an academic session to view and print your registered courses.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('student.course-registration') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back to Course Registration
                        </a>

                        <label for="session-select" class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Academic Session:</label>
                        <select id="session-select" wire:model.live="selectedSession"
                            class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                            @forelse ($sessions as $session)
                                <option value="{{ $session }}">{{ $session }}</option>
                            @empty
                                <option value="">No sessions available</option>
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>

            @if ($selectedSession && $courses->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $selectedSession }} &mdash; {{ $courses->count() }} course(s) &middot; {{ $totalUnits }} unit(s)
                            </h3>
                        </div>
                        <a href="{{ route('student.print-course-form-session', ['user' => auth()->id(), 'session' => str_replace('/', '-', $selectedSession)]) }}"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-fuchsia-600 hover:bg-fuchsia-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 border-b dark:border-gray-600">#</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 border-b dark:border-gray-600">Code</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 border-b dark:border-gray-600">Title</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 border-b dark:border-gray-600">Semester</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 border-b dark:border-gray-600 text-center">Units</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courses as $index => $course)
                                    <tr wire:key="course-{{ $course->id }}" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-mono text-gray-800 dark:text-gray-200">
                                            {{ $course->departmentCourse->studentCourse->code }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                            {{ $course->departmentCourse->studentCourse->title }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                            {{ $course->departmentCourse->studentCourse->semester == 1 ? 'First' : 'Second' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $course->units }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="4" class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">Total Units:</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-900 dark:text-fuchsia-200">
                                            {{ $totalUnits }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif ($selectedSession && $courses->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-10 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No registered courses found for <strong>{{ $selectedSession }}</strong>.</p>
                </div>
            @endif
        </div>
    </div>
</div>
