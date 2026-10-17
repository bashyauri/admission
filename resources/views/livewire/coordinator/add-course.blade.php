<div class="mt-6 space-y-5">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Department courses</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/60">
            Add courses from the shared catalog and review the courses assigned to your department.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
        <section aria-labelledby="available-courses-heading"
            class="min-w-0 rounded-2xl bg-white shadow-soft-xl dark:bg-gray-950 dark:shadow-soft-dark-xl lg:col-span-2">
            <div class="border-b border-slate-100 p-5 dark:border-white/10">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="available-courses-heading" class="font-semibold text-slate-800 dark:text-white">Available courses</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-white/60">Select a course to add it to this department.</p>
                    </div>
                    <span class="rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-semibold text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-200">
                        {{ $courses->total() }} available
                    </span>
                </div>

                <div class="relative mt-4">
                    <label class="sr-only" for="course-search">Search course catalog</label>
                    <input id="course-search" type="search" placeholder="Search by course code or title"
                        wire:model.live.debounce.300ms="search"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-20 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-200 dark:border-slate-600 dark:bg-gray-900 dark:text-white dark:focus:ring-fuchsia-900"
                        aria-describedby="course-search-help">
                    <p id="course-search-help" class="mt-1 text-xs text-slate-500 dark:text-white/50">Search updates as you type.</p>
                    @if (trim((string) $search) !== '')
                        <button type="button" wire:click="clearSearch"
                            class="absolute right-2 top-2 rounded-md px-2 py-1 text-xs font-semibold text-fuchsia-700 hover:bg-fuchsia-50 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 dark:text-fuchsia-200 dark:hover:bg-gray-800"
                            aria-label="Clear course search">Clear</button>
                    @endif
                </div>
            </div>

            <div class="min-h-48 p-4">
                <div wire:loading wire:target="search" class="mb-3 flex items-center gap-2 text-sm text-fuchsia-700 dark:text-fuchsia-200" role="status">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-fuchsia-500 border-t-transparent" aria-hidden="true"></span>
                    Searching the catalog…
                </div>

                @forelse ($courses as $course)
                    <article wire:key="available-course-{{ $course->id }}"
                        class="mb-3 rounded-xl border border-slate-200 p-4 transition hover:border-fuchsia-300 hover:shadow-soft-md dark:border-slate-700 dark:hover:border-fuchsia-700">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-bold tracking-wide text-slate-800 dark:text-white">{{ $course->code }}</p>
                                <h3 class="mt-1 break-words text-sm text-slate-600 dark:text-white/75">{{ $course->title }}</h3>
                                <dl class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500 dark:text-white/60">
                                    <div><dt class="inline font-semibold">Level:</dt> <dd class="inline">{{ $course->studentLevel?->level ?? 'Not set' }}</dd></div>
                                    <div><dt class="inline font-semibold">Semester:</dt> <dd class="inline">{{ (int) $course->semester === 1 ? 'First' : ((int) $course->semester === 2 ? 'Second' : 'Not set') }}</dd></div>
                                    <div><dt class="inline font-semibold">Units:</dt> <dd class="inline">{{ $course->units }}</dd></div>
                                </dl>
                            </div>
                            <button type="button" wire:click="addCourse({{ $course->id }})"
                                wire:loading.attr="disabled" wire:target="addCourse"
                                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-gradient-fuchsia px-4 py-2 text-xs font-bold text-white shadow-soft-md transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                aria-label="Add {{ $course->code }} to this department">
                                <span wire:loading.remove wire:target="addCourse">Add course</span>
                                <span wire:loading wire:target="addCourse">Adding…</span>
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 px-5 py-8 text-center dark:border-slate-700" role="status">
                        <h3 class="font-semibold text-slate-700 dark:text-white">
                            {{ trim((string) $search) !== '' ? 'No matching courses' : 'All catalog courses are assigned' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-white/60">
                            {{ trim((string) $search) !== '' ? 'Try another course code or title.' : 'New catalog courses will appear here when available.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($courses->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-white/10">
                    {{ $courses->links() }}
                </div>
            @endif
        </section>

        <section aria-labelledby="department-courses-heading"
            class="min-w-0 rounded-2xl bg-white shadow-soft-xl dark:bg-gray-950 dark:shadow-soft-dark-xl lg:col-span-3">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-white/10">
                <div>
                    <h2 id="department-courses-heading" class="font-semibold text-slate-800 dark:text-white">Assigned to your department</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-white/60">Edit the department unit value or remove an assignment.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-white/80">
                    {{ $selectedCourses->total() }} assigned
                </span>
            </div>

            <div class="p-4">
                <div wire:loading wire:target="deleteCourse,saveUnit" class="mb-3 flex items-center gap-2 text-sm text-fuchsia-700 dark:text-fuchsia-200" role="status">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-fuchsia-500 border-t-transparent" aria-hidden="true"></span>
                    Updating department courses…
                </div>

                @forelse ($selectedCourses as $pickedCourse)
                    @php($course = $pickedCourse->studentCourse)
                    <article wire:key="department-course-{{ $pickedCourse->id }}"
                        class="mb-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-bold tracking-wide text-slate-800 dark:text-white">{{ $course?->code ?? 'Unavailable course' }}</p>
                                <h3 class="mt-1 break-words text-sm text-slate-600 dark:text-white/75">{{ $course?->title ?? 'This catalog course is no longer available.' }}</h3>
                                <dl class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500 dark:text-white/60">
                                    <div><dt class="inline font-semibold">Level:</dt> <dd class="inline">{{ $course?->studentLevel?->level ?? 'Not set' }}</dd></div>
                                    <div><dt class="inline font-semibold">Semester:</dt> <dd class="inline">{{ (int) $course?->semester === 1 ? 'First' : ((int) $course?->semester === 2 ? 'Second' : 'Not set') }}</dd></div>
                                </dl>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-3 sm:justify-end">
                                @if ($editingCourseId === $pickedCourse->id)
                                    <form wire:submit="saveUnit" class="flex items-end gap-2">
                                        <div>
                                            <label for="unit-{{ $pickedCourse->id }}" class="mb-1 block text-xs font-semibold text-slate-600 dark:text-white/70">Units</label>
                                            <input id="unit-{{ $pickedCourse->id }}" wire:model="form.unit" type="number" min="1" step="1" required
                                                class="w-24 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-200 dark:border-slate-600 dark:bg-gray-900 dark:text-white">
                                            @error('form.unit') <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="saveUnit"
                                            class="rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-400 disabled:opacity-50"
                                            aria-label="Save units for {{ $course?->code ?? 'course' }}">Save</button>
                                        <button type="button" wire:click="cancelEdit"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-600 dark:text-white dark:hover:bg-slate-800">Cancel</button>
                                    </form>
                                @else
                                    <span class="rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-semibold text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-200">
                                        {{ $pickedCourse->units }} units
                                    </span>
                                    <button type="button" wire:click="editCourse({{ $pickedCourse->id }})"
                                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:border-fuchsia-300 hover:text-fuchsia-700 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 dark:border-slate-600 dark:text-white dark:hover:text-fuchsia-200"
                                        aria-label="Edit units for {{ $course?->code ?? 'course' }}">Edit units</button>
                                    <button type="button" wire:click="deleteCourse({{ $pickedCourse->id }})"
                                        wire:confirm="Remove {{ $course?->code ?? 'this course' }} from your department? The shared catalog course will remain."
                                        wire:loading.attr="disabled" wire:target="deleteCourse"
                                        class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-400 disabled:opacity-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950/40"
                                        aria-label="Remove {{ $course?->code ?? 'course' }} from this department">Remove</button>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 px-5 py-8 text-center dark:border-slate-700" role="status">
                        <h3 class="font-semibold text-slate-700 dark:text-white">No courses assigned yet</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-white/60">Choose a course from the catalog to add the first department course.</p>
                    </div>
                @endforelse
            </div>

            @if ($selectedCourses->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-white/10">
                    {{ $selectedCourses->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
