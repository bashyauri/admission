<div class="min-h-screen bg-slate-50 space-y-6">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-slate-900 flex items-center justify-center shadow-sm text-white flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        Staff Capabilities & Multi-Roles
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Grant and manage academic roles (HOD, Exam Officer, Lecturer, Coordinator) to staff without changing their primary account.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    wire:click="openAssignModal"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider shadow-sm transition hover:shadow active:scale-95 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Assign Role Capability
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         METRICS OVERVIEW
    ============================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Active HODs --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Active HODs
                </p>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    {{ $activeHodCount }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Head of Departments</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>

        {{-- Active Exam Officers --}}
        <div class="bg-white rounded-2xl border border-purple-100 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-purple-600">
                    Active Exam Officers
                </p>
                <p class="text-2xl font-black text-purple-700 mt-1">
                    {{ $activeExamOfficersCount }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Vet & approve results</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Active Lecturers --}}
        <div class="bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">
                    Active Lecturer Caps
                </p>
                <p class="text-2xl font-black text-emerald-700 mt-1">
                    {{ $activeLecturersCount }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Course result entry</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </div>

        {{-- Total Assignments --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Total Assignments
                </p>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    {{ $totalAssignmentsCount }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">All capabilities recorded</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

    </div>

    {{-- ============================================================
         FILTER BAR
    ============================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Search Bar --}}
            <div class="relative">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Search Staff
                </label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.400ms="searchQuery"
                        placeholder="Name, email, phone..."
                        autocomplete="off"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-300 transition"
                    />
                </div>
            </div>

            {{-- Capability Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Role Capability
                </label>
                <select
                    wire:model.live="filterCapability"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm py-2.5 focus:border-slate-400 focus:ring-slate-300"
                >
                    <option value="">All Capabilities</option>
                    <option value="hod">HOD (Head of Dept)</option>
                    <option value="exam_officer">Exam Officer</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="coordinator">Coordinator</option>
                    <option value="cit">CIT Officer</option>
                    <option value="idcard_officer">ID Card Officer</option>
                </select>
            </div>

            {{-- Department Filter --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Department Scope
                </label>
                <select
                    wire:model.live="filterDepartment"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm py-2.5 focus:border-slate-400 focus:ring-slate-300"
                >
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-600">
                        Status
                    </label>
                    @if($searchQuery || $filterCapability || $filterDepartment || $filterStatus !== '')
                        <button
                            wire:click="resetFilters"
                            type="button"
                            class="text-xs text-rose-500 hover:text-rose-700 font-semibold"
                        >
                            Reset
                        </button>
                    @endif
                </div>
                <select
                    wire:model.live="filterStatus"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm py-2.5 focus:border-slate-400 focus:ring-slate-300"
                >
                    <option value="">All Statuses</option>
                    <option value="1">Active Only</option>
                    <option value="0">Inactive / Revoked</option>
                </select>
            </div>

        </div>
    </div>

    {{-- ============================================================
         CAPABILITIES TABLE
    ============================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900">
                    Assigned Staff Capabilities
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Showing {{ $capabilities->total() }} total assignment records.
                </p>
            </div>

            <div wire:loading class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                <svg class="animate-spin w-3.5 h-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Updating list...
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Staff Member</th>
                        <th class="px-6 py-3.5">Capability Role</th>
                        <th class="px-6 py-3.5">Department Scope</th>
                        <th class="px-6 py-3.5">Tenure / Notes</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($capabilities as $cap)
                        <tr class="hover:bg-slate-50/60 transition">

                            {{-- Staff Info --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700 flex-shrink-0">
                                        {{ strtoupper(substr($cap->user?->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($cap->user?->surname ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 truncate">
                                            {{ $cap->user?->surname }} {{ $cap->user?->firstname }} {{ $cap->user?->m_name }}
                                        </p>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                            <span>{{ $cap->user?->email }}</span>
                                            <span class="inline-block w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="capitalize px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 border border-slate-200">
                                                {{ $cap->user?->role ?? 'Staff' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Capability Role Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cap->capability === 'hod')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-white bg-slate-900 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                                        </svg>
                                        HOD
                                    </span>
                                @elseif($cap->capability === 'exam_officer')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Exam Officer
                                    </span>
                                @elseif($cap->capability === 'lecturer')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13" />
                                        </svg>
                                        Lecturer
                                    </span>
                                @elseif($cap->capability === 'coordinator')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-sky-700 bg-sky-50 border border-sky-200 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7" />
                                        </svg>
                                        Coordinator
                                    </span>
                                @elseif($cap->capability === 'cit')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 shadow-sm">
                                        CIT Officer
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-slate-700 bg-slate-100 border border-slate-200 shadow-sm capitalize">
                                        {{ str_replace('_', ' ', $cap->capability) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Department Scope --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cap->department)
                                    <span class="text-xs font-semibold text-slate-800 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $cap->department->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Institution-Wide (All Departments)
                                    </span>
                                @endif
                            </td>

                            {{-- Notes / Granted Info --}}
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-700 font-medium line-clamp-1">
                                    {{ $cap->reason ?: 'Standard appointment' }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Granted: {{ $cap->granted_at ? $cap->granted_at->format('M d, Y') : 'N/A' }}
                                    @if($cap->grantedBy)
                                        by {{ $cap->grantedBy->firstname }} {{ $cap->grantedBy->surname }}
                                    @endif
                                </p>
                            </td>

                            {{-- Status Pill --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($cap->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Inactive / Revoked
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        wire:click="toggleStatus({{ $cap->id }})"
                                        wire:loading.attr="disabled"
                                        type="button"
                                        class="text-xs font-bold px-3 py-1.5 rounded-lg border transition {{ $cap->is_active ? 'border-amber-200 bg-amber-50/60 text-amber-700 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50/60 text-emerald-700 hover:bg-emerald-100' }}"
                                    >
                                        {{ $cap->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>

                                    <button
                                        wire:click="revokeCapability({{ $cap->id }})"
                                        wire:loading.attr="disabled"
                                        onclick="return confirm('Are you sure you want to permanently revoke and remove this capability record?') || event.stopImmediatePropagation()"
                                        type="button"
                                        class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                        title="Revoke & Delete"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">No capability assignments found</h3>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    No staff member matches your current search or filter criteria. Try clearing the filters or assigning a new capability.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $capabilities->links() }}
        </div>

    </div>


    {{-- ============================================================
         ASSIGN CAPABILITY MODAL (COURSE ALLOCATION UI/UX STYLE)
    ============================================================= --}}
    @if($showAssignModal)
        <div
            class="fixed inset-0 z-[9999] overflow-y-auto"
            x-data
            x-on:keydown.escape.window="$wire.closeAssignModal()"
        >
            {{-- Backdrop with blur --}}
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                wire:click="closeAssignModal"
            ></div>

            {{-- Modal Dialog --}}
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    wire:click.stop
                    class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden my-6 border border-slate-100"
                >

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-sm flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">
                                    Assign Role Capability
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Grant temporary or tenured academic roles to existing staff.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeAssignModal"
                            class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:text-slate-700 hover:bg-slate-200 flex items-center justify-center transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-5 max-h-[calc(100vh-210px)] overflow-y-auto">

                        {{-- Step 1: Staff Selection --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Select Staff Member <span class="text-rose-500">*</span>
                                </label>
                                @if($selectedUser)
                                    <button
                                        type="button"
                                        wire:click="clearSelectedStaff"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Change Staff Member
                                    </button>
                                @endif
                            </div>

                            {{-- Selected Staff Card (When a user is picked) --}}
                            @if($selectedUser)
                                <div class="rounded-xl border-2 border-emerald-500/40 bg-emerald-50/30 p-4 relative">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shadow-sm flex-shrink-0">
                                            {{ strtoupper(substr($selectedUser->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($selectedUser->surname ?? 'S', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-bold text-slate-900 truncate">
                                                    {{ $selectedUser->surname }} {{ $selectedUser->firstname }} {{ $selectedUser->m_name }}
                                                </h4>
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wider">
                                                    Selected
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-600 truncate mt-0.5">
                                                {{ $selectedUser->email }} &bull; {{ $selectedUser->phone ?: 'No phone' }}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-600">
                                                    Primary: {{ ucfirst($selectedUser->role) }}
                                                </span>
                                                @forelse($selectedUser->capabilities as $activeCap)
                                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-purple-50 border border-purple-200 text-purple-700">
                                                        Active: {{ strtoupper(str_replace('_', ' ', $activeCap->capability)) }}
                                                        @if($activeCap->department)
                                                            ({{ $activeCap->department->name }})
                                                        @endif
                                                    </span>
                                                @empty
                                                    <span class="text-[10px] text-slate-400 italic">No previous active capabilities</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Live Search Input --}}
                                <div class="relative mb-2">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input
                                        type="search"
                                        wire:model.live.debounce.300ms="staffSearch"
                                        placeholder="Search lecturer or staff by name, email, or phone..."
                                        autocomplete="off"
                                        class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-200 text-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-300 transition"
                                    />
                                    <div wire:loading wire:target="staffSearch" class="absolute right-3.5 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Search Results / Staff List --}}
                                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-2">
                                    <div class="flex items-center justify-between px-2 py-1 mb-1.5 text-[11px] font-semibold text-slate-500">
                                        <span>Click any staff member to select:</span>
                                        <span>Showing {{ count($staffList) }} staff</span>
                                    </div>

                                    <div class="max-h-[220px] overflow-y-auto space-y-1.5 pr-1">
                                        @forelse($staffList as $staff)
                                            <button
                                                type="button"
                                                wire:key="select-staff-{{ $staff->id }}"
                                                wire:click="selectStaff('{{ $staff->id }}')"
                                                class="w-full text-left p-2.5 rounded-xl border bg-white hover:bg-slate-50 hover:border-slate-300 transition flex items-center gap-3 cursor-pointer group {{ $selectedUserId === $staff->id ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }}"
                                            >
                                                {{-- Initials Avatar --}}
                                                <div class="w-9 h-9 rounded-full bg-slate-100 group-hover:bg-white border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700 flex-shrink-0">
                                                    {{ strtoupper(substr($staff->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($staff->surname ?? 'S', 0, 1)) }}
                                                </div>

                                                {{-- Staff Info --}}
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <p class="text-xs font-bold text-slate-800 truncate group-hover:text-slate-900">
                                                            {{ $staff->surname }} {{ $staff->firstname }} {{ $staff->m_name }}
                                                        </p>
                                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-500 border border-slate-200 capitalize">
                                                            {{ $staff->role }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 truncate mt-0.5">
                                                        <span>{{ $staff->email }}</span>
                                                        @if($staff->phone)
                                                            <span>&bull; {{ $staff->phone }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Arrow / Select Icon --}}
                                                <div class="text-slate-300 group-hover:text-slate-600 flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="py-8 text-center">
                                                <p class="text-xs font-bold text-slate-600">No staff members found</p>
                                                <p class="text-[11px] text-slate-400 mt-0.5">
                                                    @if(!empty($staffSearch))
                                                        No matches for "{{ $staffSearch }}". Try searching by surname, first name, or email.
                                                    @else
                                                        No eligible staff members available.
                                                    @endif
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            @error('selectedUserId')
                                <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Step 2: Capability Role Selection --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Capability Role <span class="text-rose-500">*</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">

                                {{-- HOD --}}
                                <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition {{ $capability === 'hod' ? 'border-slate-900 bg-slate-50 ring-1 ring-slate-900' : 'border-slate-200 hover:bg-slate-50/60' }}">
                                    <input type="radio" wire:model="capability" value="hod" class="mt-0.5 mr-3 text-slate-900 focus:ring-slate-900" />
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">HOD</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Head of Dept / Endorse results</span>
                                    </div>
                                </label>

                                {{-- Exam Officer --}}
                                <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition {{ $capability === 'exam_officer' ? 'border-purple-600 bg-purple-50/50 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50/60' }}">
                                    <input type="radio" wire:model="capability" value="exam_officer" class="mt-0.5 mr-3 text-purple-600 focus:ring-purple-500" />
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">Exam Officer</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Vet & approve department results</span>
                                    </div>
                                </label>

                                {{-- Lecturer --}}
                                <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition {{ $capability === 'lecturer' ? 'border-emerald-600 bg-emerald-50/50 ring-1 ring-emerald-600' : 'border-slate-200 hover:bg-slate-50/60' }}">
                                    <input type="radio" wire:model="capability" value="lecturer" class="mt-0.5 mr-3 text-emerald-600 focus:ring-emerald-500" />
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">Lecturer</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Enter results & manage courses</span>
                                    </div>
                                </label>

                                {{-- Coordinator --}}
                                <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition {{ $capability === 'coordinator' ? 'border-sky-600 bg-sky-50/50 ring-1 ring-sky-600' : 'border-slate-200 hover:bg-slate-50/60' }}">
                                    <input type="radio" wire:model="capability" value="coordinator" class="mt-0.5 mr-3 text-sky-600 focus:ring-sky-500" />
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">Coordinator</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Review assigned course cohorts</span>
                                    </div>
                                </label>

                                {{-- CIT --}}
                                <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition {{ $capability === 'cit' ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-600' : 'border-slate-200 hover:bg-slate-50/60' }}">
                                    <input type="radio" wire:model="capability" value="cit" class="mt-0.5 mr-3 text-indigo-600 focus:ring-indigo-500" />
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">CIT Officer</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">IT operations & admin roles</span>
                                    </div>
                                </label>

                                {{-- ID Card Officer --}}
                                <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition {{ $capability === 'idcard_officer' ? 'border-amber-600 bg-amber-50/50 ring-1 ring-amber-600' : 'border-slate-200 hover:bg-slate-50/60' }}">
                                    <input type="radio" wire:model="capability" value="idcard_officer" class="mt-0.5 mr-3 text-amber-600 focus:ring-amber-500" />
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">ID Card Officer</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Process student ID cards</span>
                                    </div>
                                </label>

                            </div>

                            @if($capability === 'hod')
                                <div class="mt-2.5 rounded-lg bg-amber-50 border border-amber-200 p-2.5 text-[11px] text-amber-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Selecting a department below will sync this user as the official Head of Department for that department.</span>
                                </div>
                            @elseif($capability === 'coordinator')
                                <div class="mt-2.5 rounded-lg bg-sky-50 border border-sky-200 p-2.5 text-[11px] text-sky-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Grants coordinator access. You can assign specific level and admission cohorts in the Coordinator Manager.</span>
                                </div>
                            @endif

                            @error('capability')
                                <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Step 3: Department Scope --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Department Scope
                            </label>
                            <select
                                wire:model="departmentId"
                                class="w-full rounded-xl border-slate-200 bg-white text-sm py-2.5 focus:border-slate-400 focus:ring-slate-300"
                            >
                                <option value="">Institution-Wide (All Departments)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Leave unselected if capability applies across the entire institution.
                            </p>
                            @error('departmentId')
                                <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Step 4: Reason / Notes --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Reason / Appointment Notes
                            </label>
                            <textarea
                                wire:model="reason"
                                rows="2"
                                placeholder="e.g. Appointed Departmental Exam Officer for 2025/2026 Academic Session"
                                class="w-full rounded-xl border-slate-200 text-sm p-3 focus:border-slate-400 focus:ring-slate-300"
                            ></textarea>
                            @error('reason')
                                <p class="text-xs text-rose-500 font-medium mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/50">
                        <button
                            type="button"
                            wire:click="closeAssignModal"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="assignCapability"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-95 disabled:opacity-60 disabled:cursor-wait cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="assignCapability">
                                Assign Capability
                            </span>
                            <span wire:loading wire:target="assignCapability" class="flex items-center gap-1.5">
                                <svg class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Assigning...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
