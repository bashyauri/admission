<div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- ============================================================
         PAGE HEADER & STATS
    ============================================================= --}}
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">
                        Coordinator Management
                    </h1>
                    <p class="text-sm text-slate-500 font-medium">
                        Assign, reassign, and track course-level cohort coordinators across sessions.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white text-sm font-bold shadow-md shadow-fuchsia-500/20 hover:from-fuchsia-700 hover:to-indigo-700 transition duration-150 ease-in-out cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Assign Coordinator</span>
                </button>
            </div>
        </div>

        {{-- Top Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Coordinators</span>
                    <h3 class="text-2xl font-black text-slate-900">{{ $totalCoordinators }}</h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Course-Based (Active)</span>
                    <h3 class="text-2xl font-black text-emerald-600">{{ $courseBasedCoordinators }}</h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Legacy Dept-Based</span>
                    <h3 class="text-2xl font-black text-amber-600">{{ $deptBasedCoordinators }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         NAVIGATION TABS
    ============================================================= --}}
    <div class="flex items-center gap-2 border-b border-slate-200 mb-6">
        <button wire:click="setTab('coordinators')"
            class="px-4 py-3 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'coordinators' ? 'border-fuchsia-600 text-fuchsia-700 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Assigned Coordinators List
        </button>

        <button wire:click="setTab('unassigned_cohorts')"
            class="px-4 py-3 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'unassigned_cohorts' ? 'border-fuchsia-600 text-fuchsia-700 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Unassigned Student Cohorts (Auto-Detected)
        </button>
    </div>

    @if($activeTab === 'coordinators')
        {{-- ============================================================
             FILTERS & SEARCH BAR
        ============================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                {{-- Search Filter --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Search Coordinator / Program</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Name, email, course title, code..."
                            class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2 text-sm text-slate-800 focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-100" />
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Academic Session Filter --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Academic Session</label>
                    <select wire:model.live="selectedSession"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-100">
                        <option value="">All Sessions</option>
                        @foreach($availableSessions as $sess)
                            <option value="{{ $sess }}">{{ $sess }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Student Level Filter --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Student Level</label>
                    <select wire:model.live="selectedLevelId"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-100">
                        <option value="">All Levels</option>
                        @foreach($studentLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->level }} ({{ $level->level }}L)</option>
                        @endforeach
                    </select>
                </div>

                {{-- Coordinator Type Filter --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Assignment Type</label>
                    <select wire:model.live="coordinatorType"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-fuchsia-400 focus:ring-2 focus:ring-fuchsia-100">
                        <option value="all">All Types</option>
                        <option value="course">Course-Based (New)</option>
                        <option value="department">Department-Based (Legacy)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ============================================================
             COORDINATOR TABLE
        ============================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-black uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Coordinator (Staff)</th>
                            <th class="py-3.5 px-4">Assigned Program / Department</th>
                            <th class="py-3.5 px-4 text-center">Level</th>
                            <th class="py-3.5 px-4 text-center">Session Cohort</th>
                            <th class="py-3.5 px-4 text-center">Active Students</th>
                            <th class="py-3.5 px-4 text-center">Type</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($coordinators as $coordinator)
                            <tr class="hover:bg-slate-50/60 transition">
                                {{-- Staff Info --}}
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-fuchsia-100 text-fuchsia-700 flex items-center justify-center font-black text-sm shrink-0">
                                            {{ strtoupper(substr($coordinator->user->surname ?? 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">
                                                {{ $coordinator->user ? trim(($coordinator->user->surname ?? '') . ' ' . ($coordinator->user->firstname ?? '')) : 'N/A' }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $coordinator->user->email ?? 'No email' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Course / Dept --}}
                                <td class="py-4 px-4">
                                    @if($coordinator->course)
                                        <div class="font-bold text-slate-800">
                                            {{ $coordinator->course->name }}
                                        </div>
                                        <div class="text-xs text-slate-500 font-medium">
                                            Dept: {{ $coordinator->course->department->name ?? 'N/A' }}
                                        </div>
                                    @elseif($coordinator->department)
                                        <div class="font-bold text-amber-800">
                                            {{ $coordinator->department->name }}
                                        </div>
                                        <div class="text-xs text-amber-600 font-medium">
                                            Dept-Wide Assignment
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Unassigned</span>
                                    @endif
                                </td>

                                {{-- Level --}}
                                <td class="py-4 px-4 text-center">
                                    @if($coordinator->studentLevel)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $coordinator->studentLevel->level }}L
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">All Levels</span>
                                    @endif
                                </td>

                                {{-- Session --}}
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ $coordinator->academic_session ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- Students Count --}}
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $coordinatorStudentCounts[$coordinator->id] ?? 0 }} students
                                    </span>
                                </td>

                                {{-- Type --}}
                                <td class="py-4 px-4 text-center">
                                    @if($coordinator->course_id)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Course Cohort
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Legacy Dept
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Reassign / Edit Button --}}
                                        <button wire:click="openEditModal({{ $coordinator->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-200 hover:bg-fuchsia-100 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            Reassign
                                        </button>

                                        {{-- Delete Button --}}
                                        <button wire:click="deleteCoordinator({{ $coordinator->id }})"
                                            wire:confirm="Are you sure you want to remove this coordinator assignment?"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                                            title="Delete coordinator">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <div class="max-w-sm mx-auto flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <h4 class="font-bold text-slate-800 mb-1">No Coordinators Found</h4>
                                        <p class="text-xs text-slate-500 mb-4">No coordinator records match your current filter criteria.</p>
                                        <button wire:click="openCreateModal"
                                            class="px-4 py-2 rounded-xl bg-fuchsia-600 text-white text-xs font-bold shadow hover:bg-fuchsia-700">
                                            Assign New Coordinator
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($coordinators->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $coordinators->links() }}
                </div>
            @endif
        </div>

    @else
        {{-- ============================================================
             UNASSIGNED COHORTS DISCOVERY TAB
        ============================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Student Cohorts Awaiting Coordinator Assignment
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        These courses have admitted/registered students in the system but currently lack a dedicated course-level coordinator for their admission session.
                    </p>
                </div>
                <div class="text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
                    {{ count($unassignedCohorts) }} Cohort(s) Need Coordinators
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-black uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Program / Course</th>
                            <th class="py-3.5 px-4">Department</th>
                            <th class="py-3.5 px-4 text-center">Admission Level</th>
                            <th class="py-3.5 px-4 text-center">Admission Session</th>
                            <th class="py-3.5 px-4 text-center">Students in Cohort</th>
                            <th class="py-3.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($unassignedCohorts as $cohort)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-4 font-bold text-slate-900">
                                    {{ $cohort['course_name'] }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 text-xs">
                                    {{ $cohort['department_name'] }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $cohort['level_name'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ $cohort['academic_session'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $cohort['student_count'] }} students
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button wire:click="openAssignForCohort({{ $cohort['course_id'] }}, {{ $cohort['student_level_id'] }}, '{{ $cohort['academic_session'] }}')"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white shadow-sm hover:from-fuchsia-700 hover:to-indigo-700 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Assign Coordinator
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500">
                                    <div class="max-w-sm mx-auto flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <h4 class="font-bold text-slate-800 mb-1">All Cohorts Assigned!</h4>
                                        <p class="text-xs text-slate-500">Every active course cohort in the database currently has an assigned coordinator.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ============================================================
         ASSIGN / REASSIGN MODAL
    ============================================================= --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
            wire:click.self="closeModal">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg max-h-[88vh] flex flex-col overflow-hidden my-auto animate-fade-in-up">

                {{-- Modal Header (Sticky) --}}
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-fuchsia-600 to-indigo-600 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900">
                                {{ $isEditing ? 'Reassign Coordinator' : 'Assign Course Coordinator' }}
                            </h3>
                            <p class="text-[11px] text-slate-500">
                                Link staff to student cohort for result reviews.
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-4 sm:p-5 overflow-y-auto space-y-3 flex-1 text-xs">
                    {{-- Assignment Type Selector --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Assignment Mode</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition {{ $formAssignmentType === 'course' ? 'border-fuchsia-500 bg-fuchsia-50/40 ring-1 ring-fuchsia-400 text-fuchsia-950 font-bold' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium' }}">
                                <input type="radio" wire:model.live="formAssignmentType" value="course" class="text-fuchsia-600 focus:ring-fuchsia-400" />
                                <div>
                                    <div class="text-xs">Course Cohort (Standard)</div>
                                    <div class="text-[10px] text-slate-500 font-normal">Program specific (e.g. B.Sc)</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition {{ $formAssignmentType === 'department' ? 'border-amber-500 bg-amber-50/40 ring-1 ring-amber-400 text-amber-950 font-bold' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium' }}">
                                <input type="radio" wire:model.live="formAssignmentType" value="department" class="text-amber-600 focus:ring-amber-400" />
                                <div>
                                    <div class="text-xs">Department (Legacy)</div>
                                    <div class="text-[10px] text-slate-500 font-normal">Dept-wide fallback</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Department Selection (Filters Courses) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Department <span class="text-[11px] font-normal text-slate-400">({{ $formAssignmentType === 'course' ? 'Select to filter courses below' : 'Target department' }})</span>
                            @if($formAssignmentType === 'department') <span class="text-rose-500">*</span> @endif
                        </label>
                        <select wire:model.live="formDepartmentId"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-200">
                            <option value="">-- All Departments / Select Department --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('formDepartmentId') <span class="text-[11px] text-rose-600 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Course or Program Select --}}
                    @if($formAssignmentType === 'course')
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    Target Course / Program <span class="text-rose-500">*</span>
                                </label>
                                @if($formDepartmentId)
                                    <span class="text-[10px] font-semibold text-fuchsia-600">
                                        {{ count($this->formCourses) }} course(s) in dept
                                    </span>
                                @endif
                            </div>
                            <select wire:model.live="formCourseId"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-200">
                                <option value="">-- Select Course (e.g. B.Sc Computer Science) --</option>
                                @foreach($this->formCourses as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} @if($c->department) [{{ $c->department->name }}] @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('formCourseId') <span class="text-[11px] text-rose-600 font-medium">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        {{-- Admission Student Level --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Level <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="formLevelId"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-200">
                                <option value="">Select Level</option>
                                @foreach($studentLevels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->level }} ({{ $lvl->level }}L)</option>
                                @endforeach
                            </select>
                            @error('formLevelId') <span class="text-[11px] text-rose-600 font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Academic Session --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Cohort Session <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="formSession"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-200">
                                <option value="">Select Session</option>
                                @foreach($availableSessions as $sess)
                                    <option value="{{ $sess }}">{{ $sess }}</option>
                                @endforeach
                            </select>
                            @error('formSession') <span class="text-[11px] text-rose-600 font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Staff Selection (Searchable) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Assigned Staff / Coordinator <span class="text-rose-500">*</span>
                        </label>
                        <div class="mb-1.5">
                            <input type="text" wire:model.live.debounce.250ms="lecturerSearch"
                                placeholder="Search staff by name or email..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-800 focus:bg-white focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-200" />
                        </div>

                        <select wire:model="formUserId" size="4"
                            class="w-full rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs text-slate-900 focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-200 divide-y divide-slate-100">
                            <option value="" disabled>-- Select Staff Member ({{ count($staffMembers) }} listed) --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" class="py-1 px-2">
                                    {{ $staff->surname }} {{ $staff->firstname }} ({{ $staff->email }}) [{{ strtoupper($staff->role ?? 'Staff') }}]
                                </option>
                            @endforeach
                        </select>
                        @error('formUserId') <span class="text-[11px] text-rose-600 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Modal Footer (Sticky Bottom) --}}
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition cursor-pointer">
                        Cancel
                    </button>

                    <button type="button" wire:click="saveCoordinator"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white shadow-sm hover:from-fuchsia-700 hover:to-indigo-700 transition cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveCoordinator">
                            {{ $isEditing ? 'Save Reassignment' : 'Assign Coordinator' }}
                        </span>
                        <span wire:loading wire:target="saveCoordinator">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
