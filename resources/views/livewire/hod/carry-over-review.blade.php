<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="text-xl font-semibold text-slate-800 dark:text-white">Carry-over Department Review</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Review unmatched undergraduate carry-overs, select an approved offering, and record the department's decision.</p>
    </div>

    @if($carryOvers->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">No carry-overs need review</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Unmatched or over-limit retakes for your department will appear here.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($carryOvers as $carryOver)
                @php
                    $student = $carryOver->user;
                    $failedRegistration = $carryOver->registeredCourse;
                    $departmentId = $student?->academicDetail?->department_id;
                @endphp
                <section wire:key="carry-over-review-{{ $carryOver->id }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">{{ $student?->name ?? 'Student' }} · {{ $student?->academicDetail?->matric_no ?? 'Matric no. unavailable' }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                Failed {{ $carryOver->failed_session }} {{ ucfirst($carryOver->failed_semester) }}:
                                {{ $failedRegistration?->course_code_snapshot ?? $carryOver->departmentCourse?->studentCourse?->code ?? 'Course' }}
                                {{ $failedRegistration?->course_title_snapshot ?? $carryOver->departmentCourse?->studentCourse?->title ?? '' }}
                            </p>
                            <p class="mt-2 text-sm text-amber-700 dark:text-amber-300">{{ $carryOver->review_reason }}</p>
                            @if($carryOver->review_note)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Previous review: {{ $carryOver->review_note }}</p>
                            @endif
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Review required</span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="successor-{{ $carryOver->id }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Department-approved successor offering</label>
                            <select id="successor-{{ $carryOver->id }}" wire:model="successorDepartmentCourseIds.{{ $carryOver->id }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-fuchsia-500 focus:outline-none focus:ring-2 focus:ring-fuchsia-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Select an offering</option>
                                @foreach($successorsByDepartment->get($departmentId, collect()) as $offering)
                                    <option value="{{ $offering->id }}">{{ $offering->studentCourse?->code }} · {{ $offering->studentCourse?->title }} ({{ $offering->units }} units)</option>
                                @endforeach
                            </select>
                            @error("successorDepartmentCourseIds.{$carryOver->id}") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="review-note-{{ $carryOver->id }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Department review note</label>
                            <textarea id="review-note-{{ $carryOver->id }}" wire:model="reviewNotes.{{ $carryOver->id }}" rows="2" maxlength="2000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-fuchsia-500 focus:outline-none focus:ring-2 focus:ring-fuchsia-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Explain the approved course equivalency"></textarea>
                            @error("reviewNotes.{$carryOver->id}") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button" wire:click="approveSuccessor({{ $carryOver->id }})" wire:confirm="Approve this successor offering for {{ $student?->academicDetail?->matric_no ?? 'this student' }}? The retake will be registered in the active session when the student opens course registration." wire:loading.attr="disabled" class="inline-flex items-center rounded-lg bg-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-fuchsia-700 disabled:opacity-50">
                            Approve successor
                        </button>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</div>
