<div class="min-h-screen bg-slate-50">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <div class="mb-6">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div class="flex items-center gap-3">

                <div class="w-11 h-11 rounded-xl bg-slate-900 flex items-center justify-center shadow-sm">

                    <svg
                        class="w-5 h-5 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"
                        />
                    </svg>

                </div>

                <div>

                    <h1 class="text-xl font-bold text-slate-900">
                        Course Allocation
                    </h1>

                    <p class="text-sm text-slate-500">
                        Assign courses to lecturers for the selected academic context.
                    </p>

                </div>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <span class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-600">
                    {{ $selectedSession }}
                </span>

                <span class="px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-xs font-bold text-blue-700">
                    {{ $selectedSemester === 'first' ? 'Harmattan' : 'Rain' }}
                </span>

            </div>

        </div>

    </div>


    {{-- ============================================================
         ACADEMIC CONTEXT
    ============================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6">

        <div class="px-5 py-4 border-b border-slate-100">

            <h2 class="text-sm font-bold text-slate-800">
                Academic Context
            </h2>

            <p class="text-xs text-slate-400 mt-1">
                Choose the session, semester and department.
            </p>

        </div>


        <div class="p-5">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Session --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Academic Session
                    </label>

                    <select
                        wire:model.live="selectedSession"
                        class="w-full rounded-xl border-slate-200 bg-white text-sm focus:border-slate-400 focus:ring-slate-300"
                    >

                        @foreach($availableSessions as $session)

                            <option value="{{ $session }}">
                                {{ $session }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Semester --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Semester
                    </label>

                    <select
                        wire:model.live="selectedSemester"
                        class="w-full rounded-xl border-slate-200 bg-white text-sm focus:border-slate-400 focus:ring-slate-300"
                    >

                        <option value="first">
                            Harmattan — First Semester
                        </option>

                        <option value="second">
                            Rain — Second Semester
                        </option>

                    </select>

                </div>


                {{-- Department --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Department
                    </label>

                    <select
                        wire:model.live="selectedDepartmentId"
                        class="w-full rounded-xl border-slate-200 bg-white text-sm focus:border-slate-400 focus:ring-slate-300"
                    >

                        <option value="">
                            Select Department
                        </option>

                        @foreach($departments as $department)

                            <option value="{{ $department->id }}">
                                {{ $department->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         STATISTICS
    ============================================================= --}}
    @if($selectedDepartmentId)

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

            {{-- Total --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">

                <p class="text-xs font-semibold text-slate-400 uppercase">
                    Total Courses
                </p>

                <p class="text-2xl font-bold text-slate-900 mt-1">
                    {{ $totalCourses }}
                </p>

            </div>


            {{-- Allocated --}}
            <div class="bg-white rounded-2xl border border-emerald-100 p-4 shadow-sm">

                <p class="text-xs font-semibold text-emerald-600 uppercase">
                    Allocated
                </p>

                <p class="text-2xl font-bold text-emerald-600 mt-1">
                    {{ $allocatedCourses }}
                </p>

            </div>


            {{-- Unallocated --}}
            <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-sm">

                <p class="text-xs font-semibold text-amber-600 uppercase">
                    Unallocated
                </p>

                <p class="text-2xl font-bold text-amber-600 mt-1">
                    {{ $unallocatedCourses }}
                </p>

            </div>

        </div>

    @endif


    {{-- ============================================================
         COURSE BROWSER
    ============================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">

        <div class="p-5 border-b border-slate-100">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                <div>

                    <h2 class="text-sm font-bold text-slate-800">
                        Courses
                    </h2>

                    @if($selectedDepartmentId)

                        <p class="text-xs text-slate-500 mt-1">
                            Click an unallocated course to assign a lecturer.
                        </p>

                    @else

                        <p class="text-xs text-slate-500 mt-1">
                            Select a department to view courses.
                        </p>

                    @endif

                </div>


                @if($selectedDepartmentId)

                    <div class="flex flex-wrap gap-2">

                        <button
                            type="button"
                            wire:click="$set('courseStatus', 'all')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold
                            {{ $courseStatus === 'all'
                                ? 'bg-slate-900 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }}"
                        >
                            All
                        </button>


                        <button
                            type="button"
                            wire:click="$set('courseStatus', 'unallocated')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold
                            {{ $courseStatus === 'unallocated'
                                ? 'bg-amber-600 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }}"
                        >
                            Unallocated
                        </button>


                        <button
                            type="button"
                            wire:click="$set('courseStatus', 'allocated')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold
                            {{ $courseStatus === 'allocated'
                                ? 'bg-emerald-600 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }}"
                        >
                            Allocated
                        </button>

                    </div>

                @endif

            </div>


            {{-- Search --}}
            @if($selectedDepartmentId)

                <div class="relative mt-4">

                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z"
                        />
                    </svg>


                    <input
                        type="search"
                        wire:model.live.debounce.300ms="courseSearch"
                        placeholder="Search course code or title..."
                        autocomplete="off"
                        class="w-full pl-10 pr-10 py-3 rounded-xl border-slate-200 text-sm focus:border-slate-400 focus:ring-slate-300"
                    />


                    <div
                        wire:loading
                        wire:target="courseSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2"
                    >

                        <svg
                            class="animate-spin w-4 h-4 text-slate-500"
                            fill="none"
                            viewBox="0 0 24 24"
                        >

                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            />

                        </svg>

                    </div>

                </div>

            @endif

        </div>


        {{-- Course List --}}
        <div class="p-4">

            @if(!$selectedDepartmentId)

                <div class="py-16 text-center">

                    <p class="text-sm font-semibold text-slate-600">
                        Select a department
                    </p>

                    <p class="text-xs text-slate-400 mt-1">
                        Courses will appear here.
                    </p>

                </div>


            @elseif($departmentCourses->isEmpty())

                <div class="py-16 text-center">

                    <p class="text-sm font-semibold text-slate-600">
                        No courses found
                    </p>

                    <p class="text-xs text-slate-400 mt-1">
                        Try another search or filter.
                    </p>

                </div>


            @else

                <div class="space-y-2">

                    @foreach($departmentCourses as $course)

                        @php
                            $studentCourse = $course->studentCourse;

                            $allocation = $allocations->firstWhere(
                                'department_course_id',
                                $course->id
                            );

                            $isAllocated = $allocation !== null;
                        @endphp


                        <div
                            wire:key="course-{{ $course->id }}"
                            @if(!$isAllocated)
                                wire:click="openAllocationModal({{ $course->id }})"
                            @endif
                            class="group border rounded-xl transition
                            {{ $isAllocated
                                ? 'border-emerald-100 bg-emerald-50/30'
                                : 'border-slate-200 bg-white hover:border-slate-400 hover:bg-slate-50 cursor-pointer'
                            }}"
                        >

                            <div class="p-4">

                                <div class="flex items-center gap-4">

                                    {{-- Course Icon --}}
                                    <div
                                        class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0
                                        {{ $isAllocated
                                            ? 'bg-emerald-100 text-emerald-600'
                                            : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'
                                        }}"
                                    >

                                        @if($isAllocated)

                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m5 12 4 4L19 7"
                                                />
                                            </svg>

                                        @else

                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"
                                                />
                                            </svg>

                                        @endif

                                    </div>


                                    {{-- Course Information --}}
                                    <div class="flex-1 min-w-0">

                                        <div class="flex flex-wrap items-center gap-2">

                                            <span class="text-sm font-bold text-slate-900">
                                                {{ $studentCourse->code ?? 'N/A' }}
                                            </span>

                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-500">
                                                {{ $studentCourse->units ?? '-' }}
                                                {{ ($studentCourse->units ?? 0) == 1 ? 'Unit' : 'Units' }}
                                            </span>

                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 text-[10px] font-bold">
                                                {{ $studentCourse->semester == 1 ? 'Harmattan' : 'Rain' }}
                                            </span>

                                        </div>


                                        <p class="text-sm text-slate-600 mt-1 truncate">
                                            {{ $studentCourse->title ?? 'Untitled Course' }}
                                        </p>


                                        @if($isAllocated)

                                            <p class="text-xs text-emerald-600 font-semibold mt-1">

                                                Assigned to:

                                                {{ $allocation->lecturer->surname ?? '' }}
                                                {{ $allocation->lecturer->firstname ?? '' }}

                                            </p>

                                        @else

                                            <p class="text-xs text-slate-400 group-hover:text-slate-600 mt-1">
                                                Click to assign lecturer
                                            </p>

                                        @endif

                                    </div>


                                    {{-- Action --}}
                                    <div class="flex-shrink-0">

                                       @if($isAllocated)

    <div class="flex items-center gap-2">

        {{-- Status --}}
        <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">
            
            <svg
                class="w-3.5 h-3.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m5 12 4 4L19 7"
                />
            </svg>

            Allocated

        </span>


        {{-- Remove --}}
        <button
            type="button"
            wire:click.stop="removeAllocation('{{ $allocation->id }}')"
            wire:confirm="Remove {{ $allocation->lecturer->email ?? 'this lecturer' }} from {{ $studentCourse->code ?? 'this course' }}?"
            wire:loading.attr="disabled"
            wire:target="removeAllocation('{{ $allocation->id }}')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-bold hover:bg-red-100 transition disabled:opacity-50"
        >

            <span
                wire:loading.remove
                wire:target="removeAllocation('{{ $allocation->id }}')"
            >
                <svg
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h14"
                    />
                </svg>

                Remove
            </span>


            <span
                wire:loading
                wire:target="removeAllocation('{{ $allocation->id }}')"
            >
                Removing...
            </span>

        </button>

    </div>

@else

    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-bold">

        Allocate

        <svg
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
            />
        </svg>

    </span>

@endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>


    {{-- ============================================================
         CURRENT ALLOCATIONS
    ============================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="p-5 border-b border-slate-100">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-sm font-bold text-slate-800">
                        Current Allocations
                    </h2>

                    <p class="text-xs text-slate-500 mt-1">
                        Courses already assigned to lecturers.
                    </p>

                </div>

                <span class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                    {{ $allocations->count() }}
                </span>

            </div>

        </div>


        {{-- Desktop --}}
        <div class="hidden md:block overflow-x-auto">

            <table class="w-full">

                <thead class="bg-slate-50 border-b border-slate-200">

                    <tr>

                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-wide font-bold text-slate-400">
                            Course
                        </th>

                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-wide font-bold text-slate-400">
                            Lecturer
                        </th>

                        <th class="px-5 py-3 text-center text-[10px] uppercase tracking-wide font-bold text-slate-400">
                            Units
                        </th>

                        <th class="px-5 py-3 text-right text-[10px] uppercase tracking-wide font-bold text-slate-400">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse($allocations as $allocation)

                        <tr
                            wire:key="allocation-{{ $allocation->id }}"
                            class="hover:bg-slate-50"
                        >

                            <td class="px-5 py-4">

                                <p class="text-sm font-bold text-slate-800">
                                    {{ $allocation->departmentCourse->studentCourse->code ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    {{ $allocation->departmentCourse->studentCourse->title ?? '' }}
                                </p>

                            </td>


                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">

                                        {{ strtoupper(substr($allocation->lecturer->firstname ?? '', 0, 1)) }}{{ strtoupper(substr($allocation->lecturer->surname ?? '', 0, 1)) }}

                                    </div>

                                    <div>

                                        <p class="text-sm font-semibold text-slate-800">

                                            {{ $allocation->lecturer->surname ?? '' }}
                                            {{ $allocation->lecturer->firstname ?? '' }}

                                        </p>

                                        <p class="text-xs text-slate-400">
                                            {{ $allocation->lecturer->email ?? '' }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            <td class="px-5 py-4 text-center">

                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-bold text-slate-600">

                                    {{ $allocation->assigned_units ?? $allocation->departmentCourse->studentCourse->units ?? '-' }}

                                </span>

                            </td>


                            <td class="px-5 py-4 text-right">

                                <button
                                    type="button"
                                    wire:click="removeAllocation({{ $allocation->id }})"
                                    wire:confirm="Are you sure you want to remove this course allocation?"
                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-bold hover:bg-red-100"
                                >
                                    Remove
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="px-5 py-14 text-center">

                                <p class="text-sm font-semibold text-slate-600">
                                    No allocations yet
                                </p>

                                <p class="text-xs text-slate-400 mt-1">
                                    Select an unallocated course above to get started.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Mobile --}}
        <div class="md:hidden divide-y divide-slate-100">

            @forelse($allocations as $allocation)

                <div
                    wire:key="mobile-allocation-{{ $allocation->id }}"
                    class="p-4"
                >

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <p class="text-sm font-bold text-slate-800">

                                {{ $allocation->departmentCourse->studentCourse->code ?? 'N/A' }}

                            </p>

                            <p class="text-xs text-slate-500 mt-1">

                                {{ $allocation->departmentCourse->studentCourse->title ?? '' }}

                            </p>

                        </div>

                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-xs font-bold text-slate-600">

                            {{ $allocation->assigned_units ?? $allocation->departmentCourse->studentCourse->units ?? '-' }}
                            Units

                        </span>

                    </div>


                    <div class="mt-4 flex items-center gap-3">

                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">

                            {{ strtoupper(substr($allocation->lecturer->firstname ?? '', 0, 1)) }}{{ strtoupper(substr($allocation->lecturer->surname ?? '', 0, 1)) }}

                        </div>


                        <div class="flex-1">

                            <p class="text-xs font-bold text-slate-700">

                                {{ $allocation->lecturer->surname ?? '' }}
                                {{ $allocation->lecturer->firstname ?? '' }}

                            </p>

                            <p class="text-[11px] text-slate-400">

                                {{ $allocation->lecturer->email ?? '' }}

                            </p>

                        </div>

                    </div>


                    <button
                        type="button"
                        wire:click="removeAllocation({{ $allocation->id }})"
                        wire:confirm="Are you sure you want to remove this course allocation?"
                        class="w-full mt-4 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-bold"
                    >
                        Remove Allocation
                    </button>

                </div>

            @empty

                <div class="p-10 text-center">

                    <p class="text-sm font-semibold text-slate-600">
                        No allocations yet
                    </p>

                </div>

            @endforelse

        </div>

    </div>


    {{-- ============================================================
         ALLOCATION MODAL
    ============================================================= --}}
    @if($showAllocationModal)

        <div
            class="fixed inset-0 z-[9999] overflow-y-auto"
            x-data
            x-on:keydown.escape.window="$wire.closeAllocationModal()"
        >

            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                wire:click="closeAllocationModal"
            ></div>


            {{-- Modal --}}
            <div class="relative min-h-screen flex items-center justify-center p-4">

                <div
                    wire:click.stop
                    class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden"
                >

                    {{-- Header --}}
                    <div class="px-5 py-4 border-b border-slate-100">

                        <div class="flex items-center justify-between gap-4">

                            <div>

                                <h2 class="text-base font-bold text-slate-900">
                                    Assign Lecturer
                                </h2>

                                <p class="text-xs text-slate-500 mt-1">
                                    Select a lecturer to assign to this course.
                                </p>

                            </div>


                            <button
                                type="button"
                                wire:click="closeAllocationModal"
                                class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center"
                            >

                                <svg
                                    class="w-4 h-4"
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

                        {{-- Selected Course --}}
                        @if($selectedCourse && $selectedCourse->studentCourse)

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 mb-5">

                                <div class="flex items-center gap-3">

                                    <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center">

                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >

                                            <path
                                                stroke-linecap="round"
                                                stroke-width="1.8"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18 5.754 18 7.5 18s3.332 1.253 4.5 1.253"
                                            />

                                        </svg>

                                    </div>


                                    <div class="min-w-0">

                                        <div class="flex items-center gap-2">

                                            <span class="text-base font-bold text-slate-900">

                                                {{ $selectedCourse->studentCourse->code }}

                                            </span>

                                            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-bold text-slate-500">

                                                {{ $selectedCourse->studentCourse->units }}
                                                Units

                                            </span>

                                        </div>

                                        <p class="text-sm text-slate-600 mt-1">

                                            {{ $selectedCourse->studentCourse->title }}

                                        </p>

                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- Search --}}
                        <div class="relative mb-4">

                            <svg
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z"
                                />

                            </svg>


                            <input
                                type="search"
                                wire:model.live.debounce.300ms="lecturerSearch"
                                placeholder="Search lecturer..."
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 text-sm focus:border-slate-400 focus:ring-slate-300"
                            />

                        </div>


                        {{-- Lecturer List --}}
                        <div class="max-h-[360px] overflow-y-auto space-y-2">

                            @forelse($lecturers as $lecturer)

                                <button
                                    type="button"
                                    wire:key="allocate-lecturer-{{ $lecturer->id }}"
                                    wire:click="assignCourse('{{ $lecturer->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="assignCourse('{{ $lecturer->id }}')"
                                    class="w-full text-left p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-400 transition disabled:opacity-60 disabled:cursor-wait"
                                >

                                    <div class="flex items-center gap-3">

                                        {{-- Avatar --}}
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 flex-shrink-0">

                                            {{ strtoupper(substr($lecturer->firstname ?? '', 0, 1)) }}{{ strtoupper(substr($lecturer->surname ?? '', 0, 1)) }}

                                        </div>


                                        {{-- Lecturer --}}
                                        <div class="flex-1 min-w-0">

                                            <p class="text-sm font-semibold text-slate-800 truncate">

                                                {{ $lecturer->surname }}
                                                {{ $lecturer->firstname }}

                                            </p>

                                            <p class="text-xs text-slate-400 truncate">

                                                {{ $lecturer->email }}

                                            </p>

                                        </div>


                                        {{-- Loading / Arrow --}}
                                        <div class="flex-shrink-0">

                                            <span
                                                wire:loading.remove
                                                wire:target="assignCourse('{{ $lecturer->id }}')"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-500"
                                            >

                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >

                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="m9 5 7 7-7 7"
                                                    />

                                                </svg>

                                            </span>


                                            <span
                                                wire:loading
                                                wire:target="assignCourse('{{ $lecturer->id }}')"
                                            >

                                                <svg
                                                    class="w-5 h-5 animate-spin text-slate-600"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >

                                                    <circle
                                                        class="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        stroke-width="4"
                                                    />

                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                                    />

                                                </svg>

                                            </span>

                                        </div>

                                    </div>

                                </button>

                            @empty

                                <div class="py-10 text-center">

                                    <p class="text-sm font-semibold text-slate-500">
                                        No lecturers found
                                    </p>

                                    <p class="text-xs text-slate-400 mt-1">
                                        Try another name or email.
                                    </p>

                                </div>

                            @endforelse

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">

                        <button
                            type="button"
                            wire:click="closeAllocationModal"
                            class="w-full py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>