<div>
    <div class="flex flex-wrap -mx-3 mb-5">
        <div class="w-full max-w-full px-3 mb-6 mx-auto">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">

                {{-- Header --}}
                <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-gradient-fuchsia text-white rounded-lg text-lg leading-none shadow-soft-md">
                                🎓
                            </span>
                            <div>
                                <h6 class="dark:text-white font-bold text-lg leading-tight mb-0">
                                    Exam Officer Graduation Audit & Clearance
                                </h6>
                                <p class="text-xs text-slate-500 mb-0">
                                    Audit final-year candidates against NUC & Senate degree benchmarks, review deficiencies, and approve graduands for official pass lists.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Top Action Buttons --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button wire:click="runCohortAudit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-gradient-fuchsia rounded-lg shadow-soft-md hover:scale-102 transition transform active:opacity-85 inline-flex items-center gap-1.5 disabled:opacity-50">
                            <span wire:loading.remove wire:target="runCohortAudit">⚡ Run Cohort Audit</span>
                            <span wire:loading wire:target="runCohortAudit">⏳ Auditing Cohort...</span>
                        </button>

                        <button wire:click="batchClearEligible"
                                onclick="confirm('Batch clear all eligible candidates currently filtered?') || event.stopImmediatePropagation()"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-emerald-600 rounded-lg shadow-soft-md hover:bg-emerald-500 transition inline-flex items-center gap-1.5 disabled:opacity-50">
                            <span>✅ Batch Clear Eligible</span>
                        </button>

                        <button wire:click="batchStageToGraduationList"
                                onclick="confirm('Stage all cleared graduands into the official Graduation Pass List for this session?') || event.stopImmediatePropagation()"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-slate-800 rounded-lg shadow-soft-md hover:bg-slate-700 transition inline-flex items-center gap-1.5 disabled:opacity-50">
                            <span>📜 Stage to Pass List</span>
                        </button>
                    </div>
                </div>

                {{-- Metric Cards --}}
                <div class="p-6 pt-4 pb-2">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Candidates</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-slate-800">{{ $totalCandidates }}</span>
                                <span class="text-xs text-slate-400">Total</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-blue-50/60 rounded-xl border border-blue-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Audited</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-blue-800">{{ $auditedCount }}</span>
                                <span class="text-xs text-blue-600">{{ $totalCandidates > 0 ? round(($auditedCount / $totalCandidates) * 100) : 0 }}%</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Qualified</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-emerald-800">{{ $eligibleCount }}</span>
                                <span class="text-xs text-emerald-600">Eligible</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Deficient</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-amber-800">{{ $deficientCount }}</span>
                                <span class="text-xs text-amber-600">Pending</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-fuchsia-50/60 rounded-xl border border-fuchsia-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-fuchsia-700">Cleared</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-fuchsia-800">{{ $clearedCount }}</span>
                                <span class="text-xs text-fuchsia-600">Senate</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-indigo-50/60 rounded-xl border border-indigo-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Staged</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-indigo-800">{{ $stagedCount }}</span>
                                <span class="text-xs text-indigo-600">Pass List</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filter Toolbar --}}
                <div class="px-6 py-4 border-t border-b bg-slate-50/70 flex flex-wrap items-center gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Session</label>
                        <select wire:model.live="selectedSession" class="text-xs font-semibold bg-white border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-fuchsia-400">
                            @foreach($availableSessions as $sess)
                                <option value="{{ $sess }}">{{ $sess }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Department</label>
                        <select wire:model.live="selectedDepartmentId" class="text-xs font-semibold bg-white border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-fuchsia-400 max-w-xs">
                            <option value="all">All Departments</option>
                            @foreach($availableDepartments as $dept)
                                <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Level</label>
                        <select wire:model.live="selectedLevelId" class="text-xs font-semibold bg-white border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-fuchsia-400">
                            <option value="all">All Levels</option>
                            @foreach($availableLevels as $lvl)
                                <option value="{{ $lvl['id'] }}">{{ $lvl['level'] }} Level</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Filter</label>
                        <select wire:model.live="eligibilityFilter" class="text-xs font-semibold bg-white border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-fuchsia-400">
                            <option value="all">All Candidates</option>
                            <option value="eligible">Qualified Only</option>
                            <option value="deficient">Deficiencies Pending</option>
                            <option value="cleared">Officially Cleared</option>
                            <option value="uncleared">Qualified (Awaiting Clearance)</option>
                            <option value="staged">Staged in Pass List</option>
                            <option value="not_audited">Not Yet Audited</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Search Candidate</label>
                        <input type="text"
                               wire:model.debounce.300ms="searchQuery"
                               placeholder="Search by Matric No or Name..."
                               class="w-full text-xs bg-white border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-fuchsia-400">
                    </div>
                </div>

                {{-- Candidate Audit Grid --}}
                <div class="flex-auto px-0 pt-0 pb-2">
                    <div class="p-0 overflow-x-auto">
                        <table class="items-center w-full mb-0 align-top border-collapse text-slate-600">
                            <thead class="align-bottom bg-slate-50 text-[11px] uppercase font-bold text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input type="checkbox"
                                               wire:model.live="selectAll"
                                               class="rounded text-fuchsia-600 focus:ring-fuchsia-400">
                                    </th>
                                    <th class="px-4 py-3 text-left">Candidate Info</th>
                                    <th class="px-4 py-3 text-left">Programme & Level</th>
                                    <th class="px-4 py-3 text-center">CGPA / Class</th>
                                    <th class="px-4 py-3 text-center">Credit Units</th>
                                    <th class="px-4 py-3 text-center">Compulsory</th>
                                    <th class="px-4 py-3 text-center">Carry-Overs</th>
                                    <th class="px-4 py-3 text-center">Eligibility Status</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($candidates as $candidate)
                                    @php
                                        $eligibility = $candidate->graduationEligibilities->first();
                                        $isStaged = $candidate->graduationListItems->isNotEmpty();
                                        $fullName = trim(($candidate->user->surname ?? '') . ' ' . ($candidate->user->firstname ?? '') . ' ' . ($candidate->user->m_name ?? ''));
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors {{ in_array($candidate->id, $selectedAcademicDetailIds) ? 'bg-fuchsia-50/40' : '' }}">
                                        {{-- Checkbox --}}
                                        <td class="px-4 py-3 align-middle">
                                            <input type="checkbox"
                                                   wire:model.live="selectedAcademicDetailIds"
                                                   value="{{ $candidate->id }}"
                                                   class="rounded text-fuchsia-600 focus:ring-fuchsia-400">
                                        </td>

                                        {{-- Candidate Info --}}
                                        <td class="px-4 py-3 align-middle">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-800">{{ $candidate->matric_no ?? 'No Matric' }}</span>
                                                <span class="text-xs text-slate-600 font-medium">{{ $fullName ?: 'Unknown Name' }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $candidate->user->email ?? '' }}</span>
                                            </div>
                                        </td>

                                        {{-- Programme & Level --}}
                                        <td class="px-4 py-3 align-middle">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-semibold text-slate-700">{{ $candidate->programme->name ?? 'Undergraduate' }}</span>
                                                <span class="text-[11px] text-slate-500">{{ $candidate->department->name ?? 'Department' }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $candidate->studentLevel->level ?? '400' }} Level</span>
                                            </div>
                                        </td>

                                        {{-- CGPA / Class --}}
                                        <td class="px-4 py-3 align-middle text-center">
                                            @if($eligibility)
                                                <span class="text-xs font-extrabold {{ $eligibility->final_cgpa >= 1.00 ? 'text-emerald-700' : 'text-rose-600' }}">
                                                    {{ number_format((float) $eligibility->final_cgpa, 2) }}
                                                </span>
                                                <div class="text-[10px] text-slate-500 font-medium">
                                                    {{ $eligibility->class_of_degree ?? 'Pass' }}
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400 italic">Pending</span>
                                            @endif
                                        </td>

                                        {{-- Credit Units --}}
                                        <td class="px-4 py-3 align-middle text-center">
                                            @if($eligibility)
                                                <span class="text-xs font-bold {{ $eligibility->total_units_earned >= $eligibility->total_units_required ? 'text-emerald-700' : 'text-rose-600' }}">
                                                    {{ $eligibility->total_units_earned }} / {{ $eligibility->total_units_required }}
                                                </span>
                                                <div class="text-[10px] text-slate-400">
                                                    {{ $eligibility->total_units_earned >= $eligibility->total_units_required ? 'Satisfied' : 'Deficit' }}
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400 italic">—</span>
                                            @endif
                                        </td>

                                        {{-- Compulsory Checklist --}}
                                        <td class="px-4 py-3 align-middle text-center">
                                            @if($eligibility)
                                                <div class="inline-flex items-center gap-1 text-[11px] font-bold">
                                                    <span title="General Studies" class="px-1.5 py-0.5 rounded {{ $eligibility->general_studies_completed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                                        GST {{ $eligibility->general_studies_completed ? '✓' : '✗' }}
                                                    </span>
                                                    <span title="SIWES / Industrial Training" class="px-1.5 py-0.5 rounded {{ $eligibility->siwes_completed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                                        SWE {{ $eligibility->siwes_completed ? '✓' : '✗' }}
                                                    </span>
                                                    <span title="Entrepreneurship" class="px-1.5 py-0.5 rounded {{ $eligibility->entrepreneurship_completed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                                        ENT {{ $eligibility->entrepreneurship_completed ? '✓' : '✗' }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400 italic">—</span>
                                            @endif
                                        </td>

                                        {{-- Carry-Overs --}}
                                        <td class="px-4 py-3 align-middle text-center">
                                            @if($eligibility)
                                                @if(str_contains($eligibility->remarks ?? '', 'carry-over'))
                                                    <span class="px-2 py-0.5 text-[10px] font-bold text-rose-700 bg-rose-100 rounded-full">
                                                        Active F
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-bold text-emerald-700 bg-emerald-100 rounded-full">
                                                        Cleared ✓
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400 italic">—</span>
                                            @endif
                                        </td>

                                        {{-- Eligibility Status --}}
                                        <td class="px-4 py-3 align-middle text-center">
                                            @if(!$eligibility)
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">
                                                    Not Audited
                                                </span>
                                            @elseif($eligibility->is_cleared)
                                                <div class="flex flex-col items-center">
                                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-fuchsia-100 text-fuchsia-800">
                                                        Cleared for Senate
                                                    </span>
                                                    @if($isStaged)
                                                        <span class="text-[9px] font-semibold text-emerald-600 mt-0.5">Staged in Pass List</span>
                                                    @endif
                                                </div>
                                            @elseif($eligibility->meets_requirements)
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                    Qualified (Ready)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">
                                                    Deficiencies
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-4 py-3 align-middle text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                {{-- Single audit --}}
                                                <button wire:click="auditCandidate({{ $candidate->id }})"
                                                        title="Audit candidate"
                                                        class="p-1.5 text-xs text-slate-600 hover:text-fuchsia-600 hover:bg-slate-100 rounded-md transition">
                                                    ⚡
                                                </button>

                                                {{-- Breakdown inspection --}}
                                                <button wire:click="viewDeficiencies({{ $candidate->id }})"
                                                        title="View requirements breakdown"
                                                        class="px-2 py-1 text-[10px] font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition">
                                                    Breakdown
                                                </button>

                                                {{-- Clearance action --}}
                                                @if($eligibility && $eligibility->meets_requirements && !$eligibility->is_cleared)
                                                    <button wire:click="openClearModal({{ $eligibility->id }})"
                                                            title="Clear student for Senate degree approval"
                                                            class="px-2 py-1 text-[10px] font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-md transition shadow-sm">
                                                        Clear
                                                    </button>
                                                @endif

                                                {{-- Staging action --}}
                                                @if($eligibility && $eligibility->is_cleared && !$isStaged)
                                                    <button wire:click="stageToGraduationList({{ $eligibility->id }})"
                                                            title="Stage cleared graduand into official graduation pass list"
                                                            class="px-2 py-1 text-[10px] font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-md transition shadow-sm">
                                                        Stage
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="p-8 text-center text-slate-400">
                                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                                <span class="text-3xl mb-2">🎓</span>
                                                <span class="text-sm font-semibold text-slate-700">No Candidates Found</span>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Try adjusting your session, department, level, or search filters, or click "Run Cohort Audit" to evaluate final-year students.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $candidates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inspection & Deficiencies Modal --}}
    @if($showDeficiencyModal && $inspectedAuditResult)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-soft-2xl max-w-2xl w-full p-6 border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-base text-slate-800 mb-0">Graduation Audit Breakdown</h6>
                        <span class="text-xs text-slate-500">
                            {{ $inspectedStudent->matric_no ?? '' }} — {{ $inspectedStudent->user->firstname ?? '' }} {{ $inspectedStudent->user->surname ?? '' }}
                        </span>
                    </div>
                    <button wire:click="closeDeficiencyModal" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                        ✕
                    </button>
                </div>

                <div class="py-4 space-y-4">
                    {{-- Status Banner --}}
                    <div class="p-3.5 rounded-xl border {{ $inspectedAuditResult['eligible'] ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }} flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $inspectedAuditResult['eligible'] ? '✅' : '⚠️' }}</span>
                            <div>
                                <span class="text-xs font-black uppercase">
                                    {{ $inspectedAuditResult['eligible'] ? 'Candidate Meets All Graduation Benchmarks' : 'Candidate Has Outstanding Deficiencies' }}
                                </span>
                                <p class="text-[11px] mb-0 opacity-85">
                                    Final CGPA: <strong>{{ number_format($inspectedAuditResult['final_cgpa'], 2) }}</strong> ({{ $inspectedAuditResult['class_of_degree'] }})
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Requirements Checklist Grid --}}
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold">Cumulative GPA</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-slate-800">{{ number_format($inspectedAuditResult['final_cgpa'], 2) }} (Min 1.00)</span>
                                <span class="{{ $inspectedAuditResult['meets_cgpa'] ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                    {{ $inspectedAuditResult['meets_cgpa'] ? 'PASSED ✓' : 'FAILED ✗' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold">Total Credit Units Earned</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-slate-800">{{ $inspectedAuditResult['total_units_earned'] }} / {{ $inspectedAuditResult['total_units_required'] }}</span>
                                <span class="{{ $inspectedAuditResult['meets_units'] ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                    {{ $inspectedAuditResult['meets_units'] ? 'PASSED ✓' : 'DEFICIT ✗' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold">General Studies (GST/GNS)</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-slate-800">Compulsory GST</span>
                                <span class="{{ $inspectedAuditResult['general_studies_completed'] ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                    {{ $inspectedAuditResult['general_studies_completed'] ? 'CLEARED ✓' : 'PENDING ✗' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold">SIWES / Industrial Training</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-slate-800">Mandatory SIWES</span>
                                <span class="{{ $inspectedAuditResult['siwes_completed'] ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                    {{ $inspectedAuditResult['siwes_completed'] ? 'CLEARED ✓' : 'PENDING ✗' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold">Entrepreneurship (ENT)</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-slate-800">Compulsory ENT</span>
                                <span class="{{ $inspectedAuditResult['entrepreneurship_completed'] ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                    {{ $inspectedAuditResult['entrepreneurship_completed'] ? 'CLEARED ✓' : 'PENDING ✗' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[10px] uppercase font-bold">Carry-Over / Outstanding 'F'</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-slate-800">Uncleared Courses</span>
                                <span class="{{ $inspectedAuditResult['no_outstanding_courses'] ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                    {{ $inspectedAuditResult['no_outstanding_courses'] ? 'NONE ✓' : 'ACTIVE ✗' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Deficiencies List --}}
                    @if(!empty($inspectedAuditResult['deficiencies']))
                        <div class="p-3 bg-rose-50/70 border border-rose-200 rounded-xl">
                            <span class="text-[11px] font-bold text-rose-800 uppercase block mb-1.5">Identified Academic Deficiencies:</span>
                            <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                                @foreach($inspectedAuditResult['deficiencies'] as $deficiency)
                                    <li>{{ $deficiency }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button wire:click="closeDeficiencyModal" class="px-4 py-2 text-xs font-bold text-slate-700 uppercase bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Clearance Confirmation Modal --}}
    @if($showClearModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-soft-2xl max-w-md w-full p-6 border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h6 class="font-bold text-base text-slate-800 mb-0">Clear Candidate for Senate Degree</h6>
                    <button wire:click="closeClearModal" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
                </div>

                <div class="py-4 space-y-3">
                    <p class="text-xs text-slate-600">
                        You are approving official Exam Officer graduation clearance for:
                    </p>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 font-bold text-xs text-slate-800">
                        {{ $clearingStudentName }}
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Clearance Remarks</label>
                        <textarea wire:model="clearingRemarks"
                                  rows="3"
                                  class="w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-400"
                                  placeholder="Enter Senate clearance notes or remarks..."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button wire:click="closeClearModal" class="px-4 py-2 text-xs font-bold text-slate-600 uppercase bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Cancel
                    </button>
                    <button wire:click="confirmClearStudent"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-bold text-white uppercase bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition">
                        <span wire:loading.remove wire:target="confirmClearStudent">Confirm Clearance</span>
                        <span wire:loading wire:target="confirmClearStudent">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
