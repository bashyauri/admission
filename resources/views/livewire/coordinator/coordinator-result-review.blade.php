<div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- ================================================================
         FLASH MESSAGES
    ================================================================= --}}
    @if (session()->has('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
            {{ session('warning') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('info') }}
        </div>
    @endif


    {{-- ================================================================
         HEADER
    ================================================================= --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="p-5 sm:p-6">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <div class="mb-2 flex flex-wrap items-center gap-2">

                        <span class="inline-flex items-center rounded-lg bg-fuchsia-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-fuchsia-700">
                            Department Coordinator
                        </span>

                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                            Result Review
                        </span>

                    </div>


                    <h1 class="text-xl font-black text-slate-900 sm:text-2xl">
                        {{ $inspectingDepartment?->name ?? 'Department' }}
                    </h1>


                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500 sm:text-sm">

                        <span>
                            <span class="font-bold text-slate-700">
                                Session:
                            </span>

                            {{ $selectedSession }}
                        </span>


                        <span>
                            <span class="font-bold text-slate-700">
                                Semester:
                            </span>

                            {{ ucfirst($selectedSemester) }}
                        </span>

                    </div>

                </div>


                <div class="inline-flex w-fit items-center gap-2 rounded-xl border border-fuchsia-200 bg-fuchsia-50 px-4 py-2.5">

                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-fuchsia-600"></span>

                    </span>

                    <span class="text-xs font-extrabold text-fuchsia-700">
                        Coordinator Review
                    </span>

                </div>

            </div>

        </div>


        {{-- Workflow --}}
        <div class="border-t border-slate-100 bg-slate-50 px-5 py-3 sm:px-6">

            <div class="flex flex-wrap items-center gap-2 text-xs">

                <span class="font-bold text-slate-700">
                    Workflow:
                </span>

                <span class="text-slate-600">
                    Lecturer submits
                </span>

                <span class="text-slate-300">
                    →
                </span>

                <span class="font-black text-fuchsia-700">
                    Coordinator reviews
                </span>

                <span class="text-slate-300">
                    →
                </span>

                <span class="text-slate-600">
                    Exam Officer approves
                </span>

                <span class="text-slate-300">
                    →
                </span>

                <span class="text-slate-600">
                    Results released
                </span>

            </div>

        </div>

    </div>


    {{-- ================================================================
         FILTERS
    ================================================================= --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">

        <div class="mb-4">

            <h2 class="text-sm font-black text-slate-900 sm:text-base">
                Result Filters
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Choose a level and academic period, then open a course queue.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            {{-- Academic Session --}}
            <div>

                <label class="mb-1.5 block text-xs font-bold text-slate-700">
                    Academic Session
                </label>

                <select
                    wire:model.live="selectedSession"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 outline-none transition focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500"
                >

                    @forelse($availableSessions as $session)

                        <option value="{{ $session }}">
                            {{ $session }}
                        </option>

                    @empty

                        <option value="">
                            No session available
                        </option>

                    @endforelse

                </select>

            </div>


            {{-- Semester --}}
            <div>

                <label class="mb-1.5 block text-xs font-bold text-slate-700">
                    Semester
                </label>

                <select
                    wire:model.live="selectedSemester"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 outline-none transition focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500"
                >

                    <option value="first">
                        First Semester
                    </option>

                    <option value="second">
                        Second Semester
                    </option>

                </select>

            </div>


            {{-- Assigned Level --}}
            <div>

                <label class="mb-1.5 block text-xs font-bold text-slate-700">
                    Assigned Level
                </label>

                <select
                    wire:model.live="selectedLevelId"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 outline-none transition focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500"
                >

                    @foreach($availableLevels as $level)
                        <option value="{{ $level['id'] }}">
                            {{ $level['label'] }}
                        </option>
                    @endforeach

                </select>

            </div>


            {{-- Status --}}
            <div>

                <label class="mb-1.5 block text-xs font-bold text-slate-700">
                    Result Status
                </label>

                <select
                    wire:model.live="statusFilter"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 outline-none transition focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500"
                >

                    <option value="all">
                        All Results
                    </option>

                    <option value="submitted">
                        Submitted
                    </option>

                    <option value="coordinator_approved">
                        Coordinator Approved
                    </option>

                    <option value="released">
                        Released
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                </select>

            </div>

        </div>

    </div>


    {{-- ================================================================
         SUMMARY CARDS
    ================================================================= --}}
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5 sm:gap-4">

        {{-- Submitted --}}
        <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">

            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                Submitted
            </p>

            <p class="mt-1 text-2xl font-black text-blue-600 sm:text-3xl">
                {{ $this->submittedCount }}
            </p>

            <p class="mt-1 text-[11px] text-slate-400">
                Awaiting review
            </p>

        </div>


        {{-- Coordinator Approved --}}
        <div class="rounded-2xl border border-fuchsia-100 bg-white p-4 shadow-sm">

            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                Coordinator
            </p>

            <p class="mt-1 text-2xl font-black text-fuchsia-600 sm:text-3xl">
                {{ $this->coordinatorApprovedCount }}
            </p>

            <p class="mt-1 text-[11px] text-slate-400">
                Approved
            </p>

        </div>


        {{-- Exam Officer --}}
        <div class="rounded-2xl border border-green-100 bg-white p-4 shadow-sm">

            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                Exam Officer
            </p>

            <p class="mt-1 text-2xl font-black text-green-600 sm:text-3xl">
                {{ $this->examOfficerApprovedCount }}
            </p>

            <p class="mt-1 text-[11px] text-slate-400">
                Approved
            </p>

        </div>


        {{-- Released --}}
        <div class="rounded-2xl border border-purple-100 bg-white p-4 shadow-sm">

            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                Released
            </p>

            <p class="mt-1 text-2xl font-black text-purple-600 sm:text-3xl">
                {{ $this->releasedCount }}
            </p>

            <p class="mt-1 text-[11px] text-slate-400">
                Published
            </p>

        </div>


        {{-- Pending --}}
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">

            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                Pending
            </p>

            <p class="mt-1 text-2xl font-black text-amber-600 sm:text-3xl">
                {{ $this->pendingCount }}
            </p>

            <p class="mt-1 text-[11px] text-slate-400">
                Returned / pending
            </p>

        </div>

    </div>


    {{-- ================================================================
         COURSE LIST
    ================================================================= --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-4 py-4 sm:px-6">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="text-base font-black text-slate-900 sm:text-lg">
                        Department Courses
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Select a course to review its submitted results.
                    </p>

                </div>


                <span class="w-fit rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                    {{ count($courseSummaries) }} course(s)
                </span>

            </div>

        </div>


        <div class="divide-y divide-slate-100">

            @forelse($courseSummaries as $course)

                @php

                    $isSelected =
                        (int) $selectedDepartmentCourseId ===
                        (int) $course['department_course_id'];

                    $hasSubmitted =
                        $course['submitted'] > 0;

                    $statusText = 'No Pending Submission';

                    $statusClasses =
                        'border-slate-200 bg-slate-50 text-slate-600';

                    if ($course['submitted'] > 0) {

                        $statusText = 'Submitted';

                        $statusClasses =
                            'border-blue-200 bg-blue-50 text-blue-700';

                    } elseif ($course['coordinator_approved'] > 0) {

                        $statusText = 'Coordinator Approved';

                        $statusClasses =
                            'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700';

                    } elseif ($course['exam_officer_approved'] > 0) {

                        $statusText = 'Exam Officer Approved';

                        $statusClasses =
                            'border-green-200 bg-green-50 text-green-700';

                    } elseif ($course['released'] > 0) {

                        $statusText = 'Released';

                        $statusClasses =
                            'border-purple-200 bg-purple-50 text-purple-700';

                    } elseif ($course['pending'] > 0) {

                        $statusText = 'Pending';

                        $statusClasses =
                            'border-amber-200 bg-amber-50 text-amber-700';

                    }

                @endphp


                <div
                    wire:key="department-course-{{ $course['department_course_id'] }}"
                    class="p-4 transition sm:px-6 sm:py-5 {{ $isSelected ? 'bg-fuchsia-50/40' : 'hover:bg-slate-50' }}"
                >

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">

                        {{-- Course Information --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-black text-white">
                                    {{ $course['course_code'] }}
                                </span>


                                <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[10px] font-extrabold {{ $statusClasses }}">
                                    {{ $statusText }}
                                </span>

                            </div>


                            <h3 class="mt-2 text-sm font-black text-slate-900 sm:text-base">
                                {{ $course['course_title'] }}
                            </h3>


                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">

                                <span>
                                    <strong class="text-slate-700">
                                        Units:
                                    </strong>

                                    {{ $course['units'] }}
                                </span>


                                <span>
                                    <strong class="text-slate-700">
                                        Students:
                                    </strong>

                                    {{ $course['student_count'] }}
                                </span>

                            </div>

                        </div>


                        {{-- Result Counts --}}
                        <div class="grid grid-cols-3 gap-2 lg:w-[330px]">

                            <div class="rounded-xl bg-blue-50 px-3 py-2 text-center">

                                <p class="text-[9px] font-black uppercase tracking-wide text-blue-500">
                                    Submitted
                                </p>

                                <p class="text-sm font-black text-blue-700">
                                    {{ $course['submitted'] }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-fuchsia-50 px-3 py-2 text-center">

                                <p class="text-[9px] font-black uppercase tracking-wide text-fuchsia-500">
                                    Coordinator
                                </p>

                                <p class="text-sm font-black text-fuchsia-700">
                                    {{ $course['coordinator_approved'] }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-green-50 px-3 py-2 text-center">

                                <p class="text-[9px] font-black uppercase tracking-wide text-green-500">
                                    Exam Officer
                                </p>

                                <p class="text-sm font-black text-green-700">
                                    {{ $course['exam_officer_approved'] }}
                                </p>

                            </div>

                        </div>


                        {{-- Action --}}
                        <div class="shrink-0">

                            <button
                                type="button"
                                wire:click="selectCourse({{ $course['department_course_id'] }})"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold transition lg:w-auto
                                    {{ $isSelected
                                        ? 'bg-fuchsia-600 text-white hover:bg-fuchsia-700'
                                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
                                    }}"
                            >

                                @if($isSelected)
                                    Reviewing
                                @else
                                    Review Results
                                @endif

                            </button>

                        </div>

                    </div>

                </div>

            @empty

                <div class="px-6 py-14 text-center">

                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">

                        <svg
                            class="h-6 w-6 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l4.414 4.414A1 1 0 0 1 18 8.414V19a2 2 0 0 1-2 2Z"
                            />
                        </svg>

                    </div>


                    <h3 class="text-sm font-black text-slate-800">
                        No courses found
                    </h3>

                    <p class="mt-1 text-xs text-slate-500">
                        No courses match the selected session, semester or status.
                    </p>

                </div>

            @endforelse

        </div>

    </div>


    {{-- ================================================================
         SELECTED COURSE RESULTS
    ================================================================= --}}
    @if($selectedDepartmentCourseId && $this->selectedCourse)

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Selected Course Header --}}
            <div class="border-b border-slate-200 px-4 py-5 sm:px-6">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="min-w-0">

                        <div class="mb-2 flex flex-wrap items-center gap-2">

                            <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-black text-white">
                                {{ $this->selectedCourse['course_code'] }}
                            </span>


                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                {{ $this->selectedCourse['units'] }} Units
                            </span>

                        </div>


                        <h2 class="text-lg font-black text-slate-900 sm:text-xl">
                            {{ $this->selectedCourse['course_title'] }}
                        </h2>


                        <p class="mt-1 text-xs text-slate-500">
                            {{ $selectedSession }}
                            ·
                            {{ ucfirst($selectedSemester) }} Semester
                            ·
                            {{ $totalStudentResults }} result(s)
                        </p>

                    </div>


                    {{-- Review Decision --}}
                    @if($this->selectedCourse['submitted'] > 0)

                        <div class="w-full border-t border-slate-100 pt-4 lg:w-auto lg:min-w-[350px] lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">

                            <div class="flex items-start gap-3">

                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-sm font-black text-blue-700">
                                    {{ $this->selectedCourse['submitted'] }}
                                </div>

                                <div class="min-w-0">
                                    <p class="text-xs font-black text-slate-900">
                                        Results awaiting your decision
                                    </p>

                                    <p class="mt-0.5 text-xs leading-5 text-slate-500">
                                        Forward the submitted batch to the Exam Officer, or return the batch with guidance.
                                    </p>
                                </div>

                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">

                            <button
                                type="button"
                                wire:click="openApprovalModal"
                                class="inline-flex items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-green-700 disabled:opacity-50"
                            >
                                Review & Forward
                            </button>


                            <button
                                type="button"
                                wire:click="openRejectModal"
                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-50"
                            >
                                Return to Lecturer
                            </button>

                        </div>

                        </div>

                    @endif

                </div>

            </div>


            {{-- Search --}}
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-4 sm:px-6">

                <div>

                    <div>

                        <label class="mb-1.5 block text-xs font-bold text-slate-700">
                            Search Student
                        </label>

                        <div class="relative">

                            <input
                                type="text"
                                wire:model.live.debounce.400ms="searchQuery"
                                placeholder="Search name or matric number..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pl-10 text-sm outline-none transition focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500"
                            >


                            <svg
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                                />
                            </svg>

                        </div>

                    </div>


                </div>

            </div>


            {{-- ========================================================
                 DESKTOP TABLE
            ========================================================= --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wide text-slate-500">
                                #
                            </th>

                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Course Code
                            </th>

                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Matric No
                            </th>

                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Student
                            </th>

                            <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-wide text-slate-500">
                                CA
                            </th>

                            <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Exam
                            </th>

                            <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Total
                            </th>

                            <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Grade
                            </th>

                            <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse($studentsWithResults as $index => $student)

                            @php

                                $studentStatusClasses = match ($student['status']) {

                                    'submitted' =>
                                        'border-blue-200 bg-blue-50 text-blue-700',

                                    'coordinator_approved' =>
                                        'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700',

                                    'exam_officer_approved' =>
                                        'border-green-200 bg-green-50 text-green-700',

                                    'released' =>
                                        'border-purple-200 bg-purple-50 text-purple-700',

                                    default =>
                                        'border-amber-200 bg-amber-50 text-amber-700',
                                };


                                $studentStatusLabel = match ($student['status']) {

                                    'submitted' =>
                                        'Submitted',

                                    'coordinator_approved' =>
                                        'Coordinator Approved',

                                    'exam_officer_approved' =>
                                        'Exam Officer Approved',

                                    'released' =>
                                        'Released',

                                    default =>
                                        'Pending',
                                };

                            @endphp


                            <tr
                                wire:key="result-row-{{ $student['result_id'] }}"
                                class="transition hover:bg-slate-50"
                            >

                                {{-- # --}}
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>


                                {{-- Course Code --}}
                                <td class="whitespace-nowrap px-5 py-4">

                                    <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-black text-white">
                                        {{ $student['course_code'] }}
                                    </span>

                                </td>


                                {{-- Matric --}}
                                <td class="whitespace-nowrap px-5 py-4">

                                    <span class="text-sm font-bold text-slate-800">
                                        {{ $student['matric_no'] }}
                                    </span>

                                </td>


                                {{-- Student --}}
                                <td class="px-5 py-4">

                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $student['name'] }}
                                    </div>

                                </td>


                                {{-- CA --}}
                                <td class="whitespace-nowrap px-5 py-4 text-center text-sm font-semibold text-slate-700">
                                    {{ $student['ca_score'] ?? '-' }}
                                </td>


                                {{-- Exam --}}
                                <td class="whitespace-nowrap px-5 py-4 text-center text-sm font-semibold text-slate-700">
                                    {{ $student['exam_score'] ?? '-' }}
                                </td>


                                {{-- Total --}}
                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <span class="text-sm font-black text-slate-900">
                                        {{ $student['total_score'] ?? '-' }}
                                    </span>

                                </td>


                                {{-- Grade --}}
                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <span class="inline-flex min-w-[36px] items-center justify-center rounded-lg bg-slate-100 px-2 py-1 text-sm font-black text-slate-800">
                                        {{ $student['grade'] }}
                                    </span>

                                </td>


                                {{-- Status --}}
                                <td class="whitespace-nowrap px-5 py-4 text-center">

                                    <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[10px] font-extrabold {{ $studentStatusClasses }}">
                                        {{ $studentStatusLabel }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="px-6 py-14 text-center">

                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">

                                        <svg
                                            class="h-6 w-6 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l4.414 4.414A1 1 0 0 1 18 8.414V19a2 2 0 0 1-2 2Z"
                                            />
                                        </svg>

                                    </div>


                                    <h3 class="text-sm font-black text-slate-800">
                                        No results found
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        No results match the selected filters.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ========================================================
                 MOBILE RESULTS
            ========================================================= --}}
            <div class="divide-y divide-slate-100 md:hidden">

                @forelse($studentsWithResults as $index => $student)

                    @php

                        $studentStatusClasses = match ($student['status']) {

                            'submitted' =>
                                'border-blue-200 bg-blue-50 text-blue-700',

                            'coordinator_approved' =>
                                'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700',

                            'exam_officer_approved' =>
                                'border-green-200 bg-green-50 text-green-700',

                            'released' =>
                                'border-purple-200 bg-purple-50 text-purple-700',

                            default =>
                                'border-amber-200 bg-amber-50 text-amber-700',
                        };


                        $studentStatusLabel = match ($student['status']) {

                            'submitted' =>
                                'Submitted',

                            'coordinator_approved' =>
                                'Coordinator Approved',

                            'exam_officer_approved' =>
                                'Exam Officer Approved',

                            'released' =>
                                'Released',

                            default =>
                                'Pending',
                        };

                    @endphp


                    <div
                        wire:key="mobile-result-{{ $student['result_id'] }}"
                        class="p-4"
                    >

                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0">

                                <p class="text-[10px] font-bold text-slate-400">
                                    #{{ $index + 1 }}
                                </p>


                                <h3 class="mt-0.5 text-sm font-black text-slate-900">
                                    {{ $student['name'] }}
                                </h3>


                                <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                    {{ $student['matric_no'] }}
                                </p>


                                <span class="mt-2 inline-flex rounded-lg bg-slate-900 px-2.5 py-1 text-[10px] font-black text-white">
                                    {{ $student['course_code'] }}
                                </span>

                            </div>


                            <span class="shrink-0 rounded-lg border px-2 py-1 text-[9px] font-extrabold {{ $studentStatusClasses }}">
                                {{ $studentStatusLabel }}
                            </span>

                        </div>


                        <div class="mt-4 grid grid-cols-4 gap-2">

                            <div class="rounded-xl bg-slate-50 p-2.5 text-center">

                                <p class="text-[9px] font-bold uppercase text-slate-400">
                                    CA
                                </p>

                                <p class="text-sm font-black text-slate-800">
                                    {{ $student['ca_score'] ?? '-' }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-slate-50 p-2.5 text-center">

                                <p class="text-[9px] font-bold uppercase text-slate-400">
                                    Exam
                                </p>

                                <p class="text-sm font-black text-slate-800">
                                    {{ $student['exam_score'] ?? '-' }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-slate-50 p-2.5 text-center">

                                <p class="text-[9px] font-bold uppercase text-slate-400">
                                    Total
                                </p>

                                <p class="text-sm font-black text-slate-800">
                                    {{ $student['total_score'] ?? '-' }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-fuchsia-50 p-2.5 text-center">

                                <p class="text-[9px] font-bold uppercase text-fuchsia-500">
                                    Grade
                                </p>

                                <p class="text-sm font-black text-fuchsia-700">
                                    {{ $student['grade'] }}
                                </p>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-14 text-center">

                        <h3 class="text-sm font-black text-slate-800">
                            No results found
                        </h3>

                        <p class="mt-1 text-xs text-slate-500">
                            No results match the selected filters.
                        </p>

                    </div>

                @endforelse

            </div>

            @if($lastResultPage > 1)
                <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                    <p class="text-xs font-semibold text-slate-500">
                        Page {{ $currentResultPage }} of {{ $lastResultPage }}
                    </p>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="gotoResultPage({{ $currentResultPage - 1 }})"
                            @disabled($currentResultPage === 1)
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        <button
                            type="button"
                            wire:click="gotoResultPage({{ $currentResultPage + 1 }})"
                            @disabled($currentResultPage === $lastResultPage)
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            @endif

        </div>

    @endif


    {{-- ================================================================
         APPROVAL MODAL
    ================================================================= --}}
    @if($showApprovalModal && $this->selectedCourse)

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                wire:click="closeApprovalModal"
            ></div>

            <div class="relative w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl">

                <div class="border-b border-slate-200 px-5 py-4">
                    <p class="text-xs font-black uppercase tracking-wide text-green-700">
                        Confirm forwarding
                    </p>

                    <h3 class="mt-1 text-lg font-black text-slate-900">
                        Forward {{ $this->selectedCourse['submitted'] }} submitted result(s)?
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        This sends the reviewed batch to the Exam Officer for final release.
                    </p>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-1 divide-y divide-slate-100 rounded-lg border border-slate-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        <div class="px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Course</p>
                            <p class="mt-1 text-sm font-black text-slate-900">{{ $this->selectedCourse['course_code'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $this->selectedCourse['course_title'] }}</p>
                        </div>
                        <div class="px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Academic period</p>
                            <p class="mt-1 text-sm font-black text-slate-900">{{ $selectedSession }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ ucfirst($selectedSemester) }} Semester</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-5 text-amber-900">
                        Returned results must be corrected and resubmitted by the lecturer. Check the scores above before forwarding this batch.
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        wire:click="closeApprovalModal"
                        wire:loading.attr="disabled"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
                    >
                        Continue Reviewing
                    </button>

                    <button
                        type="button"
                        wire:click="approveStudentResults"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-green-700 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="approveStudentResults">
                            Forward to Exam Officer
                        </span>
                        <span wire:loading wire:target="approveStudentResults">
                            Forwarding...
                        </span>
                    </button>
                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         REJECTION MODAL
    ================================================================= --}}
    @if($showRejectModal)

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                wire:click="closeRejectModal"
            ></div>


            {{-- Modal --}}
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

                {{-- Header --}}
                <div class="border-b border-slate-200 px-5 py-4">

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <h3 class="text-base font-black text-slate-900">
                                Return Results to Lecturer
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Provide a clear reason for returning these results.
                            </p>

                        </div>


                        <button
                            type="button"
                            wire:click="closeRejectModal"
                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        >

                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>

                        </button>

                    </div>

                </div>


                {{-- Body --}}
                <div class="p-5">

                    <label class="mb-1.5 block text-xs font-bold text-slate-700">
                        Reason for Return
                    </label>


                    <textarea
                        wire:model="rejectionReason"
                        rows="5"
                        maxlength="500"
                        placeholder="Example: Please correct the CA scores for the affected students and resubmit."
                        class="w-full resize-none rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500"
                    ></textarea>


                    @error('rejectionReason')

                        <p class="mt-1.5 text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>

                    @enderror


                    <p class="mt-1.5 text-[11px] text-slate-400">
                        Maximum 500 characters.
                    </p>

                </div>


                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        wire:click="closeRejectModal"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
                    >
                        Cancel
                    </button>


                    <button
                        type="button"
                        wire:click="rejectStudentResults"
                        wire:loading.attr="disabled"
                        class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 disabled:opacity-50"
                    >

                        <span wire:loading.remove wire:target="rejectStudentResults">
                            Return to Lecturer
                        </span>

                        <span wire:loading wire:target="rejectStudentResults">
                            Returning...
                        </span>

                    </button>

                </div>

            </div>

        </div>

    @endif

</div>
