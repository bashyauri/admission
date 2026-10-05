@use('App\Models\User')
<div>
    {{-- Session/Cohort Selector --}}
    @if($coordinatorAssignments->count() > 1)
        <div class="mb-4 rounded-xl bg-white/80 border border-slate-200 shadow-soft-sm p-4">
            <h6 class="dark:text-white mb-3 text-sm font-bold">Select Assignment Cohort</h6>
            <div class="flex flex-wrap gap-2">
                @foreach($coordinatorAssignments as $assignment)
                    <button wire:click="selectAssignment({{ $assignment->id }})"
                            class="px-3 py-2 text-xs font-bold rounded-lg border transition-all
                                   {{ $selectedAssignmentId === $assignment->id
                                        ? 'bg-fuchsia-500 text-white border-fuchsia-500 shadow-soft-md'
                                        : 'bg-white text-slate-600 border-slate-200 hover:border-fuchsia-300 hover:bg-fuchsia-50' }}">
                        @if($assignment->course)
                            {{ $assignment->course->name }}
                        @elseif($assignment->department)
                            {{ $assignment->department->name }}
                        @endif
                        — {{ $assignment->studentLevel?->name ?? $assignment->student_level_id }}L
                        — {{ $assignment->academic_session }}
                    </button>
                @endforeach
            </div>
        </div>
    @elseif($coordinatorAssignments->count() === 1)
        {{-- Show single assignment info --}}
        @php
            $singleAssignment = $coordinatorAssignments->first();
        @endphp
        <div class="mb-4 rounded-xl bg-fuchsia-50 border border-fuchsia-200 shadow-soft-sm p-4">
            <h6 class="dark:text-white mb-2 text-sm font-bold">Current Assignment</h6>
            <p class="text-xs text-fuchsia-700">
                @if($singleAssignment->course)
                    {{ $singleAssignment->course->name }}
                @elseif($singleAssignment->department)
                    {{ $singleAssignment->department->name }}
                @endif
                — {{ $singleAssignment->studentLevel?->name ?? $singleAssignment->student_level_id }}L
                — {{ $singleAssignment->academic_session }}
            </p>
        </div>
    @endif

    <div class="flex flex-wrap -mx-3 mt-6">

        {{-- ─────────────────────────────────────────────────────────────
             LEFT PANEL  |  Student search + PIN generation
        ───────────────────────────────────────────────────────────────── --}}
        <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
            <div class="relative flex flex-col min-w-0 mb-6 overflow-auto overflow-x-hidden break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">

                {{-- Search header --}}
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">
                    <h6 class="dark:text-white mb-2">Find Student</h6>
                    @if($academicSession)
                        <p class="text-xs text-slate-400 mb-2">
                            Session: <span class="font-bold text-fuchsia-600">{{ $academicSession }}</span>
                            @if($studentLevelId)
                                | Level: <span class="font-bold text-fuchsia-600">{{ $studentLevelId }}L</span>
                            @endif
                        </p>
                    @endif
                    <input type="text"
                           id="coordinator-student-search"
                           placeholder="Matric / Registration number"
                           class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                           wire:model.live="search" />
                </div>

                {{-- Loading spinner --}}
                <div class="flex justify-center items-center p-4" wire:loading wire:target="search">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-fuchsia-500 dark:border-fuchsia-300"></div>
                    <span class="ml-2 text-fuchsia-500 dark:text-fuchsia-300">Searching...</span>
                </div>

                {{-- Generated PIN banner --}}
                @if($generatedPin && $search !== '')
                    <div class="mx-4 mb-2 rounded-xl bg-gradient-to-br from-lime-50 to-green-50 border border-lime-200 p-3 text-center shadow-soft-sm">
                        <p class="text-xs font-bold uppercase tracking-widest text-lime-700 mb-1">📋 Registration PIN</p>
                        <p class="text-3xl font-black tracking-[0.4em] text-lime-700">{{ $generatedPin }}</p>
                        <p class="text-xs text-lime-600 mt-1 leading-snug">Hand this PIN to the student to unlock course registration.</p>
                    </div>
                @endif

                {{-- Student list --}}
                <div class="flex-auto p-2">
                    @if ($search !== '')
                        @forelse ($students as $student)
                            @php
                                $isApproved = $student->approval?->isApproved();
                                $isPinUsed  = $student->approval?->isPinUsed();
                                $isSelected = $selectedStudentId === $student->id;
                            @endphp
                            <div id="student-card-{{ $student->id }}"
                                 class="block p-3 rounded-xl mb-2 border transition-all cursor-pointer
                                        {{ $isSelected
                                            ? 'bg-fuchsia-50 border-fuchsia-200 shadow-soft-sm'
                                            : 'bg-white/60 border-transparent hover:bg-slate-50 hover:border-slate-200' }}"
                                 wire:click="selectStudent({{ $student->id }})">
                                <div class="flex gap-3 items-start">
                                    <img src="{{ asset('storage/' . ($student->user?->picture ?? 'avatars/default.png')) }}"
                                         alt="{{ $student->user?->surname ?? 'Student' }}"
                                         class="w-10 h-10 rounded-xl object-cover shadow-soft-sm flex-shrink-0 border border-white">
                                    <div class="flex-1 min-w-0">
                                        <h6 class="mb-0 text-sm font-semibold dark:text-white truncate leading-snug">
                                            {{ $student->user?->surname }} {{ $student->user?->firstname }}
                                            @if($student->user?->m_name) {{ $student->user->m_name }} @endif
                                        </h6>
                                        <p class="mb-1.5 text-xs font-mono text-slate-400 leading-none">{{ $student->matric_no }}</p>

                                        {{-- Status badge --}}
                                        @if($isApproved)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-green-100 text-green-700 border border-green-200">
                                                ✅ Approved &amp; Locked
                                            </span>
                                        @elseif($isPinUsed)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-700 border border-blue-200">
                                                📌 PIN Used — Pending Approval
                                            </span>
                                        @elseif($student->approval)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 border border-amber-200">
                                                🔑 PIN Generated
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200">
                                                No PIN Yet
                                            </span>
                                        @endif

                                        {{-- Action buttons --}}
                                        <div class="mt-2 flex flex-wrap gap-1" wire:click.stop>
                                            <button id="generate-pin-btn-{{ $student->id }}"
                                                    wire:click="generatePin({{ $student->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="generatePin({{ $student->id }})"
                                                    class="inline-block px-2.5 py-1 text-xs font-bold text-slate-800 align-middle transition-all border-0 rounded-lg cursor-pointer bg-gradient-lime shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">
                                                <span wire:loading.remove wire:target="generatePin({{ $student->id }})">
                                                    {{ $student->approval ? '🔄 Re-generate PIN' : '🔑 Generate PIN' }}
                                                </span>
                                                <span wire:loading wire:target="generatePin({{ $student->id }})">Generating...</span>
                                            </button>

                                            <button type="button"
                                                    wire:click="close"
                                                    class="inline-block px-2.5 py-1 text-xs font-bold text-center align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 shadow-soft-md text-slate-700">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center">
                                <p class="text-slate-400 text-sm font-semibold">No student found.</p>
                                <p class="text-slate-300 text-xs mt-1">Check the matric number and try again.</p>
                            </div>
                        @endforelse
                    @else
                        <div class="py-10 text-center text-slate-400">
                            <p class="text-3xl mb-2">🔍</p>
                            <p class="text-sm font-semibold">Enter a matric number</p>
                            <p class="text-xs mt-1">to search for a student.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────────
             RIGHT PANEL  |  Course review + approval
        ───────────────────────────────────────────────────────────────── --}}
        <div class="w-full max-w-full px-3 flex-0 lg:w-8/12">
            @if($selectedAcademicDetail)
                @php
                    $isApproved = $selectedAcademicDetail->approval?->isApproved();
                    $isPinUsed  = $selectedAcademicDetail->approval?->isPinUsed();
                    $totalUnits = $registeredCourses->sum(fn($rc) => (int) ($rc->credit_units_snapshot ?? $rc->units ?? $rc->departmentCourse?->units ?? 0));
                @endphp

                <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">

                    {{-- ── Card header ── --}}
                    <div class="border-black/12.5 rounded-t-2xl border-b border-solid border-slate-100 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">

                            {{-- Student info --}}
                            <div>
                                <h6 class="mb-0.5 dark:text-white font-bold text-base leading-snug">
                                    {{ $selectedAcademicDetail->user?->surname }}
                                    {{ $selectedAcademicDetail->user?->firstname }}
                                    @if($selectedAcademicDetail->user?->m_name)
                                        {{ $selectedAcademicDetail->user->m_name }}
                                    @endif
                                </h6>
                                <p class="text-xs font-mono text-slate-400 mb-2">{{ $selectedAcademicDetail->matric_no }}</p>

                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Registration status --}}
                                    @if($isApproved)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-green-100 text-green-700 border border-green-200">
                                            ✅ Registration Approved &amp; Locked
                                        </span>
                                    @elseif($isPinUsed)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 border border-blue-200">
                                            📌 PIN Used — Awaiting Approval
                                        </span>
                                    @elseif($selectedAcademicDetail->approval)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-amber-100 text-amber-700 border border-amber-200">
                                            🔑 PIN Generated — Not Yet Used
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 border border-slate-200">
                                            No PIN Generated
                                        </span>
                                    @endif

                                    {{-- Course / unit summary --}}
                                    <span class="text-xs text-slate-500">
                                        <strong class="text-slate-700">{{ $registeredCourses->count() }}</strong> course(s) &mdash;
                                        <strong class="text-slate-700">{{ $totalUnits }}</strong> total unit(s)
                                    </span>
                                </div>
                            </div>

                            {{-- Primary action button --}}
                            <div class="flex flex-wrap gap-2 items-start">
                                @if($isApproved)
                                    <button id="unlock-registration-btn"
                                            wire:click="unlockRegistration({{ $selectedAcademicDetail->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="unlockRegistration"
                                            onclick="return confirm('Unlock this student\'s registration so they can add or remove courses?')"
                                            class="inline-block px-5 py-2.5 text-xs font-bold text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer shadow-soft-md hover:scale-102 active:opacity-85 bg-gradient-to-r from-amber-400 to-orange-400 hover:from-amber-500 hover:to-orange-500 disabled:opacity-60 disabled:cursor-not-allowed disabled:scale-100">
                                        <span wire:loading.remove wire:target="unlockRegistration">🔓 Unlock Registration</span>
                                        <span wire:loading wire:target="unlockRegistration">Unlocking...</span>
                                    </button>
                                @else
                                    <button id="approve-registration-btn"
                                            wire:click="approveRegistration({{ $selectedAcademicDetail->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="approveRegistration"
                                            onclick="return confirm('Approve and lock this student\'s course registration?\n\nThey will NOT be able to add or remove courses after this.')"
                                            {{ $registeredCourses->isEmpty() ? 'disabled' : '' }}
                                            class="inline-block px-5 py-2.5 text-xs font-bold text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85
                                                   {{ $registeredCourses->isEmpty() ? 'bg-gradient-gray text-slate-400 cursor-not-allowed scale-100 opacity-60' : 'bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600' }}
                                                   disabled:opacity-60 disabled:cursor-not-allowed disabled:scale-100">
                                        <span wire:loading.remove wire:target="approveRegistration">✅ Approve &amp; Lock Registration</span>
                                        <span wire:loading wire:target="approveRegistration">Approving...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Approved notice banner ── --}}
                    @if($isApproved)
                        <div class="mx-5 mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 flex items-start gap-2">
                            <span class="text-green-600 text-lg mt-0.5 flex-shrink-0">🔒</span>
                            <div>
                                <p class="text-sm font-bold text-green-800 leading-snug">Registration is approved and locked.</p>
                                <p class="text-xs text-green-700 mt-0.5">This student cannot add or remove courses. Use the <strong>Unlock Registration</strong> button above if changes are needed.</p>
                            </div>
                        </div>
                    @endif

                    {{-- ── Registered courses table ── --}}
                    <div class="flex-auto p-4 pt-3">
                        @if($registeredCourses->isEmpty())
                            <div class="py-12 text-center text-slate-400">
                                <p class="text-4xl mb-2">📋</p>
                                <p class="text-sm font-semibold text-slate-500">No registered courses found for the current session.</p>
                                <p class="text-xs mt-1 text-slate-400">The student must use their PIN to register before you can approve.</p>
                            </div>
                        @else
                            <p class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Registered Courses</p>
                            <div class="overflow-x-auto rounded-xl border border-slate-100">
                                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                    <thead class="align-bottom">
                                        <tr>
                                            <th class="px-4 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">#</th>
                                            <th class="px-4 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Code</th>
                                            <th class="px-4 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Title</th>
                                            <th class="px-4 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Semester</th>
                                            <th class="px-4 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Units</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registeredCourses as $i => $rc)
                                            @php
                                                $code  = $rc->course_code_snapshot  ?? $rc->departmentCourse?->studentCourse?->code  ?? '—';
                                                $title = $rc->course_title_snapshot  ?? $rc->departmentCourse?->studentCourse?->title ?? '—';
                                                $rawSem = $rc->semester_snapshot ?? $rc->departmentCourse?->studentCourse?->semester ?? '';
                                                $semLabel = match(true) {
                                                    in_array((string)$rawSem, ['1', 'first', 'First'])   => 'Harmattan',
                                                    in_array((string)$rawSem, ['2', 'second', 'Second']) => 'Rain',
                                                    default => $rawSem ?: '—',
                                                };
                                                $units = $rc->credit_units_snapshot ?? $rc->units ?? $rc->departmentCourse?->units ?? '—';
                                            @endphp
                                            <tr class="hover:bg-slate-50/70 transition">
                                                <td class="p-2 px-4 align-middle bg-transparent border-b whitespace-nowrap text-xs text-slate-400">
                                                    {{ $i + 1 }}
                                                </td>
                                                <td class="p-2 px-4 align-middle bg-transparent border-b whitespace-nowrap">
                                                    <span class="font-mono font-bold text-sm text-slate-800">{{ $code }}</span>
                                                </td>
                                                <td class="p-2 px-4 align-middle bg-transparent border-b">
                                                    <span class="text-sm text-slate-600 leading-snug">{{ $title }}</span>
                                                </td>
                                                <td class="p-2 px-4 text-center align-middle bg-transparent border-b whitespace-nowrap">
                                                    <span class="text-xs font-semibold text-slate-500">{{ $semLabel }}</span>
                                                </td>
                                                <td class="p-2 px-4 text-center align-middle bg-transparent border-b whitespace-nowrap">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-100">
                                                        {{ $units }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-slate-50/80">
                                            <td colspan="4" class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-400 tracking-wider">
                                                Total Units
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center justify-center w-10 h-8 rounded-lg text-sm font-black bg-fuchsia-600 text-white shadow-soft-sm">
                                                    {{ $totalUnits }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if(!$isApproved && !$registeredCourses->isEmpty())
                                <p class="text-xs text-slate-400 mt-3 text-center">
                                    Review the courses above, then click <strong class="text-green-700">Approve &amp; Lock Registration</strong> when satisfied.
                                </p>
                            @endif
                        @endif
                    </div>

                </div>

            @else
                {{-- Empty state: no student selected yet --}}
                <div class="relative flex flex-col items-center justify-center min-h-64 min-w-0 break-words bg-white/60 border-0 dark:bg-gray-950 shadow-soft-xl rounded-2xl bg-clip-border p-10 text-center">
                    <p class="text-5xl mb-3">👈</p>
                    <h6 class="dark:text-white mb-1">Select a student</h6>
                    <p class="text-sm text-slate-400 max-w-xs">
                        Search for a student on the left using their matric number, then click on their card to review their registered courses and approve or lock their registration.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-6 text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200">No PIN Yet</span>
                            <span>= PIN not generated</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 border border-amber-200">🔑 PIN Generated</span>
                            <span>= Student can register</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-700 border border-blue-200">📌 PIN Used</span>
                            <span>= Ready for approval</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-green-100 text-green-700 border border-green-200">✅ Approved</span>
                            <span>= Locked</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>