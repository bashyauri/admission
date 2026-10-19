<div>
    {{-- ================================================================ --}}
    {{-- 🏛️ HERO HEADER & CONTEXT SELECTOR --}}
    {{-- ================================================================ --}}
    <div class="relative flex items-center justify-between flex-wrap gap-4 px-6 py-5 mb-6
                bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900
                rounded-2xl shadow-soft-xl overflow-hidden">
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-indigo-500 opacity-10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 border border-white/20 shadow">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-white font-bold text-xl tracking-tight">Exam Officer Command Center</h1>
                    <p class="text-slate-300 text-xs mt-0.5">Central Academic Affairs, Senate Broadsheets &amp; Result Governance</p>
                </div>
            </div>
        </div>

        {{-- Academic Session & Semester Dropdowns --}}
        <div class="relative z-10 flex items-end gap-3 flex-wrap">
            <div>
                <label class="block text-xxs font-semibold text-slate-400 uppercase mb-1">Academic Session</label>
                <select wire:model.live="selectedSession"
                        class="text-xs bg-white/10 border border-white/20 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none backdrop-blur-sm">
                    @forelse($availableSessions as $session)
                        <option value="{{ $session }}" class="text-slate-900">{{ $session }}</option>
                    @empty
                        <option value="" class="text-slate-900">No registered sessions</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="block text-xxs font-semibold text-slate-400 uppercase mb-1">Semester</label>
                <select wire:model.live="selectedSemester"
                        class="text-xs bg-white/10 border border-white/20 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none backdrop-blur-sm">
                    <option value="first" class="text-slate-900">Harmattan (First)</option>
                    <option value="second" class="text-slate-900">Rain (Second)</option>
                </select>
            </div>
        </div>
    </div>

    @if(empty($selectedSession))
        <div class="bg-white rounded-2xl shadow-soft-xl border border-slate-100 p-12 text-center my-6">
            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 22h17.6a2 2 0 001.73-3.14l-8.82-15a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-700">No Student Registrations Found</h3>
            <p class="text-sm text-slate-400 mt-1">There are currently no active course registrations recorded in the system.</p>
        </div>
    @else

        {{-- ================================================================ --}}
        {{-- 📊 INSTITUTIONAL PULSE (KPI METRICS) --}}
        {{-- ================================================================ --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            {{-- Total Results --}}
            <div class="flex flex-col bg-white rounded-2xl shadow-soft-xl border border-slate-100 p-4 hover:shadow-soft-2xl transition">
                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <span class="text-2xl font-extrabold text-slate-800">{{ number_format($totalResultsInPipeline) }}</span>
                <span class="text-xxs font-bold text-slate-400 uppercase tracking-wide mt-1">Total Pipeline</span>
                <span class="text-xxs text-slate-400">{{ $selectedSession }}</span>
            </div>

            {{-- Ready for Senate Release --}}
            <div class="flex flex-col bg-white rounded-2xl shadow-soft-xl border-l-4 border-l-green-500 border border-slate-100 p-4 hover:shadow-soft-2xl transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    @if($coordinatorApproved > 0)
                        <span class="text-xxs font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full animate-pulse">Action Required</span>
                    @endif
                </div>
                <span class="text-2xl font-extrabold text-green-700">{{ number_format($coordinatorApproved) }}</span>
                <span class="text-xxs font-bold text-green-800 uppercase tracking-wide mt-1">Ready for Release</span>
                <span class="text-xxs text-slate-400">Coordinator Approved</span>
            </div>

            {{-- Released to Students --}}
            <div class="flex flex-col bg-white rounded-2xl shadow-soft-xl border-l-4 border-l-blue-500 border border-slate-100 p-4 hover:shadow-soft-2xl transition">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-2xl font-extrabold text-blue-700">{{ number_format($releasedThisSemester) }}</span>
                <span class="text-xxs font-bold text-blue-800 uppercase tracking-wide mt-1">Released</span>
                <span class="text-xxs text-slate-400">Visible to Students</span>
            </div>

            {{-- With Coordinator --}}
            <div class="flex flex-col bg-white rounded-2xl shadow-soft-xl border-l-4 border-l-amber-400 border border-slate-100 p-4 hover:shadow-soft-2xl transition">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-2xl font-extrabold text-amber-700">{{ number_format($withCoordinator) }}</span>
                <span class="text-xxs font-bold text-amber-800 uppercase tracking-wide mt-1">With Coordinator</span>
                <span class="text-xxs text-slate-400">Under Review</span>
            </div>

            {{-- With Lecturer --}}
            <div class="flex flex-col bg-white rounded-2xl shadow-soft-xl border-l-4 border-l-slate-400 border border-slate-100 p-4 hover:shadow-soft-2xl transition">
                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <span class="text-2xl font-extrabold text-slate-600">{{ number_format($withLecturer) }}</span>
                <span class="text-xxs font-bold text-slate-500 uppercase tracking-wide mt-1">With Lecturer</span>
                <span class="text-xxs text-slate-400">Draft / Pending</span>
            </div>

            {{-- Graduation Eligible --}}
            <div class="flex flex-col bg-white rounded-2xl shadow-soft-xl border-l-4 border-l-purple-500 border border-slate-100 p-4 hover:shadow-soft-2xl transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                        </svg>
                    </div>
                    @if($graduationEligibleCount > 0)
                        <span class="text-xxs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">Pending</span>
                    @endif
                </div>
                <span class="text-2xl font-extrabold text-purple-700">{{ number_format($graduationEligibleCount) }}</span>
                <span class="text-xxs font-bold text-purple-800 uppercase tracking-wide mt-1">Grad Candidates</span>
                <span class="text-xxs text-slate-400">Awaiting Clearance</span>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- 📌 OPERATIONAL HUBS (DIRECT LAUNCHPADS) --}}
        {{-- ================================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

            {{-- HUB 1: Result Auditing & Release Workstation --}}
            <div class="flex flex-col justify-between bg-white rounded-2xl p-5 border border-slate-100 shadow-soft-xl hover:shadow-soft-2xl transition relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-green-500/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        @if($coordinatorApproved > 0)
                            <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-green-100 text-green-800 animate-pulse">
                                {{ $coordinatorApproved }} Ready
                            </span>
                        @endif
                    </div>
                    <h5 class="text-sm font-bold text-slate-800">Result Auditing &amp; Release</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Audit Coordinator-approved score sheets, return flagged results for revision, and publish official grades to students.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('exam-officer.results-review') }}"
                       class="inline-flex items-center justify-between w-full text-xs font-bold text-green-700 bg-green-50 hover:bg-green-100 px-3.5 py-2.5 rounded-xl transition">
                        <span>Open Audit Workstation</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- HUB 2: Graduation Audit & Senate Clearance --}}
            <div class="flex flex-col justify-between bg-white rounded-2xl p-5 border border-slate-100 shadow-soft-xl hover:shadow-soft-2xl transition relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                            </svg>
                        </div>
                        @if($graduationEligibleCount > 0)
                            <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-purple-100 text-purple-800">
                                {{ $graduationEligibleCount }} Candidates
                            </span>
                        @endif
                    </div>
                    <h5 class="text-sm font-bold text-slate-800">Graduation &amp; Senate Clearance</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Verify credit unit benchmarks, compute graduating CGPA classifications, resolve carry-overs, and stage graduands for Senate approval.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('exam-officer.graduation-audit') }}"
                       class="inline-flex items-center justify-between w-full text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 px-3.5 py-2.5 rounded-xl transition">
                        <span>Open Graduation Audit</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- HUB 3: Senate Broadsheets & Reports --}}
            <div class="flex flex-col justify-between bg-white rounded-2xl p-5 border border-slate-100 shadow-soft-xl hover:shadow-soft-2xl transition relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                        </div>
                        <span class="px-2 py-0.5 text-xxs font-bold uppercase rounded bg-blue-50 text-blue-700">NUC Format</span>
                    </div>
                    <h5 class="text-sm font-bold text-slate-800">Senate Broadsheet Hub</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Instant generation of Departmental Master Broadsheets and Graduation Broadsheets for Academic Board presentation.
                    </p>

                    {{-- Quick Department Selector --}}
                    @if(count($allDepartments) > 0)
                        <div class="mt-3">
                            <select wire:model.live="quickReportDeptId"
                                    class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-blue-400 focus:outline-none bg-slate-50 text-slate-700 font-medium">
                                @foreach($allDepartments as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                    @if($quickReportDeptId)
                        <a href="{{ route('exam-officer.senate-broadsheet', ['department' => $quickReportDeptId, 'session' => str_replace('/', '-', $selectedSession), 'semester' => $selectedSemester]) }}"
                           target="_blank"
                           class="flex-1 inline-flex items-center justify-center text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 py-2 rounded-xl transition">
                            Semester Broadsheet
                        </a>
                        <a href="{{ route('exam-officer.cohort-progression-broadsheet', ['department' => $quickReportDeptId, 'admissionSession' => 'all']) }}"
                           target="_blank"
                           title="View Complete Multi-Session Master Broadsheet (All Historical Sessions, Courses & Carry-Overs)"
                           class="inline-flex items-center justify-center text-xs font-bold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 px-3 py-2 rounded-xl transition border border-emerald-300 shadow-sm">
                            🎓 All-Sessions Master
                        </a>
                    @endif
                    <a href="{{ route('exam-officer.senate-graduation-broadsheet', ['session' => str_replace('/', '-', $selectedSession)]) }}"
                       target="_blank"
                       title="Print Senate Graduation Broadsheet"
                       class="inline-flex items-center justify-center text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 px-2.5 py-2 rounded-xl transition">
                        Grad Broadsheet
                    </a>
                </div>
            </div>

            {{-- HUB 4: Academic Standards & Disciplinary Status --}}
            <div class="flex flex-col justify-between bg-white rounded-2xl p-5 border border-slate-100 shadow-soft-xl hover:shadow-soft-2xl transition relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <span class="px-2 py-0.5 text-xxs font-bold uppercase rounded bg-green-50 text-green-700">Enforced</span>
                    </div>
                    <h5 class="text-sm font-bold text-slate-800">Academic Regulations</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Continuous Assessment (max 40) and Examination (max 60) benchmarks, 40% pass mark, and carry-over tracking active.
                    </p>
                    <div class="mt-3 space-y-1.5 text-xxs text-slate-500 font-medium">
                        <div class="flex items-center justify-between py-0.5 border-b border-slate-100">
                            <span>Grading Scale:</span>
                            <span class="font-bold text-slate-700">5.00 CGPA (NUC Standard)</span>
                        </div>
                        <div class="flex items-center justify-between py-0.5">
                            <span>Minimum Pass Score:</span>
                            <span class="font-bold text-slate-700">40% (Grade E)</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <span class="inline-flex items-center gap-1.5 text-xxs font-semibold text-slate-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        Grading Rules Compliant
                    </span>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- 📊 DEPARTMENTAL SENATE READINESS MATRIX (PAGINATED & SEARCHABLE) --}}
        {{-- ================================================================ --}}
        <div class="bg-white rounded-2xl shadow-soft-xl border border-slate-100 overflow-hidden mb-8">
            {{-- Table Control Bar --}}
            <div class="p-6 pb-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h6 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span>Departmental Senate Readiness Matrix</span>
                        <span class="px-2 py-0.5 text-xxs font-semibold rounded-full bg-slate-100 text-slate-600">
                            {{ $departments->total() }} Active Departments
                        </span>
                    </h6>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Approval pipeline and Academic Board readiness for {{ $selectedSession }}
                        &mdash; {{ $selectedSemester === 'first' ? 'Harmattan' : 'Rain' }} Semester
                    </p>
                </div>

                {{-- Search & Pagination Limit --}}
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Search Input --}}
                    <div class="relative min-w-[240px]">
                        <input type="text"
                               wire:model.live.debounce.300ms="deptSearch"
                               placeholder="Search department..."
                               class="w-full text-xs border border-slate-200 rounded-xl pl-9 pr-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-slate-50 transition">
                        <div class="absolute left-3 top-2.5 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        @if($deptSearch)
                            <button wire:click="$set('deptSearch', '')"
                                    class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">
                                &times;
                            </button>
                        @endif
                    </div>

                    {{-- Per Page Dropdown --}}
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="text-xxs uppercase font-semibold text-slate-400">Show</span>
                        <select wire:model.live="perPage"
                                class="text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-400">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="items-center w-full mb-0 align-top border-collapse text-slate-500">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="px-6 py-3 font-bold text-left uppercase align-middle text-xxs tracking-wide text-slate-400">Department</th>
                            <th class="px-6 py-3 font-bold text-center uppercase align-middle text-xxs tracking-wide text-slate-400">Registered Courses</th>
                            <th class="px-6 py-3 font-bold text-center uppercase align-middle text-xxs tracking-wide text-slate-400">Ready for Release</th>
                            <th class="px-6 py-3 font-bold text-center uppercase align-middle text-xxs tracking-wide text-slate-400">With Coordinator</th>
                            <th class="px-6 py-3 font-bold text-center uppercase align-middle text-xxs tracking-wide text-slate-400">Released</th>
                            <th class="px-6 py-3 font-bold text-left uppercase align-middle text-xxs tracking-wide text-slate-400" style="min-width: 160px;">Senate Readiness</th>
                            <th class="px-6 py-3 font-bold text-center uppercase align-middle text-xxs tracking-wide text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                            @php
                                $total = max(1, (int) $dept->total_results);
                                $ready = (int) $dept->ready_count;
                                $released = (int) $dept->released_count;
                                $withCoord = (int) $dept->with_coord_count;
                                $processed = $ready + $released;
                                $pct = round(($processed / $total) * 100);
                                $barColor = $pct >= 100 ? 'bg-blue-600' : ($pct > 50 ? 'bg-green-500' : ($pct > 0 ? 'bg-amber-400' : 'bg-slate-200'));
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition border-b border-slate-50">
                                {{-- Department --}}
                                <td class="px-6 py-4 align-middle whitespace-nowrap">
                                    <span class="text-sm font-bold text-slate-800 block">{{ $dept->department_name }}</span>
                                    <span class="text-xxs text-slate-400 font-medium">Undergraduate Programme</span>
                                </td>

                                {{-- Course Count --}}
                                <td class="px-6 py-4 text-center align-middle whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                                        {{ $dept->course_count }}
                                    </span>
                                </td>

                                {{-- Ready (Coordinator Approved) --}}
                                <td class="px-6 py-4 text-center align-middle whitespace-nowrap">
                                    @if($ready > 0)
                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-green-100 text-green-800 animate-pulse">
                                            {{ $ready }} Ready
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">&mdash;</span>
                                    @endif
                                </td>

                                {{-- With Coordinator --}}
                                <td class="px-6 py-4 text-center align-middle whitespace-nowrap">
                                    @if($withCoord > 0)
                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-amber-100 text-amber-800">
                                            {{ $withCoord }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Released --}}
                                <td class="px-6 py-4 text-center align-middle whitespace-nowrap">
                                    @if($released > 0)
                                        <span class="px-2.5 py-1 text-xxs font-bold uppercase rounded-full bg-blue-100 text-blue-800">
                                            {{ $released }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Progress Bar --}}
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xxs font-bold text-slate-600 w-9 text-right">{{ $pct }}%</span>
                                    </div>
                                    <span class="text-xxs text-slate-400 mt-0.5 block">
                                        {{ $processed }} of {{ $dept->total_results }} processed
                                    </span>
                                </td>

                                {{-- Action Links --}}
                                <td class="px-6 py-4 text-center align-middle whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('exam-officer.results-review', ['selectedDepartmentId' => $dept->department_id]) }}"
                                           title="Inspect in Result Auditing Workstation"
                                           class="px-2.5 py-1.5 text-xxs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                            Audit &rarr;
                                        </a>
                                        <a href="{{ route('exam-officer.senate-broadsheet', ['department' => $dept->department_id, 'session' => str_replace('/', '-', $selectedSession), 'semester' => $selectedSemester]) }}"
                                           target="_blank"
                                           title="Print Official Departmental Senate Broadsheet"
                                           class="px-2.5 py-1.5 text-xxs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition inline-flex items-center gap-1">
                                            <span>📄</span>
                                            <span>Broadsheet</span>
                                        </a>
                                        <a href="{{ route('exam-officer.cohort-progression-broadsheet', ['department' => $dept->department_id, 'admissionSession' => str_replace('/', '-', $selectedSession)]) }}"
                                           target="_blank"
                                           title="Print All-Sessions Cohort Progression Master Broadsheet with Carry-Over Audit"
                                           class="px-2.5 py-1.5 text-xxs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition inline-flex items-center gap-1">
                                            <span>🎓</span>
                                            <span>Cohort Master</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <p class="text-sm font-medium">No department matches found for the current search/filter.</p>
                                    <p class="text-xs text-slate-300 mt-1">Try clearing the search query or verifying student registrations in this session.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($departments->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $departments->links() }}
                </div>
            @endif
        </div>

        {{-- ================================================================ --}}
        {{-- 🕒 RECENT SENATE RELEASES & AUDIT LOG --}}
        {{-- ================================================================ --}}
        @if(count($recentlyReleased) > 0)
            <div class="bg-white rounded-2xl shadow-soft-xl border border-slate-100 p-6 mb-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                            Recent Senate Release Activity Log
                        </h6>
                        <p class="text-xs text-slate-400 mt-0.5">Chronological audit trail of semester results published to students</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($recentlyReleased as $release)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                    ✓
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $release['department_name'] }}</p>
                                    <p class="text-xxs text-slate-500 mt-0.5">{{ $release['comments'] ?? 'Results released and published.' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xxs font-semibold text-slate-500 block">Released by {{ $release['released_by'] }}</span>
                                <span class="text-xxs text-slate-400">
                                    {{ $release['approved_at'] ? \Carbon\Carbon::parse($release['approved_at'])->diffForHumans() : '' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif
</div>
