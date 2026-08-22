<div>
    {{-- Top Metrics Cards --}}
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full max-w-full px-3 mb-6 sm:w-1/3 sm:flex-none xl:mb-0">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal uppercase text-size-sm">Active Exam Officers</p>
                                <h5 class="mb-0 font-bold">{{ $activeExamOfficersCount }}</h5>
                            </div>
                        </div>
                        <div class="max-w-full px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-fuchsia shadow-soft-2xl flex items-center justify-center">
                                <svg width="20px" height="20px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <path fill="#FFFFFF" d="M20,2 L4,9 L4,20 C4,29.4 11.2,38.2 20,40 C28.8,38.2 36,29.4 36,20 L36,9 L20,2 Z" opacity="0.6"/>
                                        <path fill="#FFFFFF" d="M20,6 L7,12 L7,20 C7,27.8 12.8,35 20,37 C27.2,35 33,27.8 33,20 L33,12 L20,6 Z"/>
                                        <polyline points="13,20 18,25 28,15" stroke="#7928CA" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full max-w-full px-3 mb-6 sm:w-1/3 sm:flex-none xl:mb-0">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal uppercase text-size-sm">Active Lecturer Caps</p>
                                <h5 class="mb-0 font-bold">{{ $activeLecturersCount }}</h5>
                            </div>
                        </div>
                        <div class="max-w-full px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-lime shadow-soft-2xl flex items-center justify-center">
                                <svg width="20px" height="20px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect fill="#FFFFFF" x="0" y="0" width="40" height="28" rx="2"/>
                                        <rect fill="#17c1e8" x="2" y="2" width="36" height="24" rx="1"/>
                                        <rect fill="#FFFFFF" x="17" y="28" width="6" height="6"/>
                                        <rect fill="#FFFFFF" x="10" y="34" width="20" height="3" rx="1"/>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full max-w-full px-3 mb-6 sm:w-1/3 sm:flex-none xl:mb-0">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal uppercase text-size-sm">Total Assignments</p>
                                <h5 class="mb-0 font-bold">{{ $totalAssignmentsCount }}</h5>
                            </div>
                        </div>
                        <div class="max-w-full px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-dark shadow-soft-2xl flex items-center justify-center">
                                <svg width="20px" height="20px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect fill="#FFFFFF" x="14" y="2" width="12" height="8" rx="2"/>
                                        <rect fill="#FFFFFF" x="2" y="18" width="10" height="8" rx="2" opacity="0.8"/>
                                        <rect fill="#FFFFFF" x="15" y="18" width="10" height="8" rx="2" opacity="0.8"/>
                                        <rect fill="#FFFFFF" x="28" y="18" width="10" height="8" rx="2" opacity="0.8"/>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Table Card --}}
    <div class="flex flex-wrap -mx-3">
        <div class="w-full max-w-full px-3">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                
                {{-- Card Header & Filter Bar --}}
                <div class="p-6 pb-0 mb-0 bg-white border-b-0 rounded-t-2xl">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h6 class="mb-0 font-bold text-slate-700">Staff Capabilities & Role Assignments</h6>
                            <p class="mb-0 text-sm leading-normal text-slate-500">
                                Assign multi-role capabilities (e.g. Exam Officer, Lecturer) to academic staff without altering their primary account.
                            </p>
                        </div>
                        <div>
                            <button wire:click="openAssignModal" type="button" class="inline-block px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all bg-gradient-fuchsia border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs text-size-xs shadow-soft-md">
                                + Assign New Capability
                            </button>
                        </div>
                    </div>

                    {{-- Filters Row --}}
                    <div class="flex flex-wrap items-center gap-3 mt-4 pb-4">
                        <div class="flex-1 min-w-[200px]">
                            <input wire:model.debounce.300ms="searchQuery" type="text" placeholder="Search staff name, email, phone..." class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:outline-none focus:transition-shadow" />
                        </div>
                        <div class="min-w-[160px]">
                            <select wire:model="filterCapability" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-2 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">All Capabilities</option>
                                <option value="exam_officer">Exam Officer</option>
                                <option value="lecturer">Lecturer</option>
                            </select>
                        </div>
                        <div class="min-w-[180px]">
                            <select wire:model="filterDepartment" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-2 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[130px]">
                            <select wire:model="filterStatus" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-2 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">All Statuses</option>
                                <option value="1">Active Only</option>
                                <option value="0">Inactive Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Table Body --}}
                <div class="flex-auto px-0 pt-0 pb-2">
                    <div class="p-0 overflow-x-auto">
                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                            <thead class="align-bottom">
                                <tr>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Staff Member
                                    </th>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Assigned Capability
                                    </th>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Department Scope
                                    </th>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Tenure / Reason
                                    </th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70 text-center">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($capabilities as $cap)
                                    <tr>
                                        {{-- Staff Member --}}
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <div class="flex px-4 py-1">
                                                <div class="flex flex-col justify-center">
                                                    <h6 class="mb-0 text-sm font-semibold leading-normal text-slate-700">
                                                        {{ $cap->user?->surname }} {{ $cap->user?->firstname }} {{ $cap->user?->m_name }}
                                                    </h6>
                                                    <p class="mb-0 text-xs leading-tight text-slate-400">
                                                        {{ $cap->user?->email }} &bull; <span class="capitalize">{{ $cap->user?->role }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Capability Badge --}}
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            @if($cap->capability === 'exam_officer')
                                                <span class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white bg-gradient-fuchsia">
                                                    Exam Officer
                                                </span>
                                            @elseif($cap->capability === 'lecturer')
                                                <span class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white bg-gradient-lime">
                                                    Lecturer
                                                </span>
                                            @else
                                                <span class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white bg-slate-500">
                                                    {{ $cap->capability }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Department --}}
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <span class="text-xs font-semibold leading-tight text-slate-700">
                                                {{ $cap->department?->name ?? 'Institution-Wide (All Departments)' }}
                                            </span>
                                        </td>

                                        {{-- Tenure / Reason --}}
                                        <td class="p-2 align-middle bg-transparent border-b shadow-transparent">
                                            <p class="mb-0 text-xs text-slate-600 font-medium line-clamp-1">
                                                {{ $cap->reason ?? 'Standard departmental assignment' }}
                                            </p>
                                            <p class="mb-0 text-xxs text-slate-400">
                                                Granted: {{ $cap->granted_at?->format('M d, Y') ?? 'N/A' }}
                                                @if($cap->grantedBy)
                                                    by {{ $cap->grantedBy->firstname }}
                                                @endif
                                            </p>
                                        </td>

                                        {{-- Status --}}
                                        <td class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            @if($cap->is_active)
                                                <span class="px-3.6 text-xxs rounded-1.8 py-1.5 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white bg-lime-500">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-3.6 text-xxs rounded-1.8 py-1.5 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white bg-slate-400">
                                                    Inactive / Revoked
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-center">
                                            <button wire:click="toggleStatus({{ $cap->id }})" type="button" class="text-xs font-semibold px-3 py-1.5 rounded-lg border {{ $cap->is_active ? 'border-yellow-400 text-yellow-600 hover:bg-yellow-50' : 'border-lime-500 text-lime-600 hover:bg-lime-50' }} transition-colors mr-2">
                                                {{ $cap->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>

                                            <button wire:click="revokeCapability({{ $cap->id }})" onclick="return confirm('Are you sure you want to permanently remove this capability record?') || event.stopImmediatePropagation()" type="button" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-400 text-red-500 hover:bg-red-50 transition-colors">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-6 text-center text-sm text-slate-400">
                                            No capability assignments found matching your search criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $capabilities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Capability Modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 z-sticky flex items-center justify-center overflow-x-hidden overflow-y-auto bg-black bg-opacity-50 p-4 transition-all">
            <div class="relative w-full max-w-2xl mx-auto bg-white rounded-2xl shadow-soft-2xl border-0 overflow-hidden">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-6 pb-4 border-b border-gray-100">
                    <div>
                        <h6 class="mb-0 font-bold text-slate-700">Assign Role Capability</h6>
                        <p class="mb-0 text-xs text-slate-400">Grant temporary or tenured academic roles to existing staff.</p>
                    </div>
                    <button wire:click="closeAssignModal" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                        &times;
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-4">
                    
                    {{-- Staff Selection --}}
                    <div>
                        <label class="block mb-2 text-xs font-bold uppercase text-slate-700">Select Staff Member <span class="text-red-500">*</span></label>
                        <div class="mb-2">
                            <input wire:model.debounce.300ms="staffSearch" type="text" placeholder="Search staff by name or email..." class="text-xs focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-1.5 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none" />
                        </div>
                        <select wire:model="selectedUserId" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-2 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none">
                            <option value="">-- Choose Staff Member --</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->surname }} {{ $staff->firstname }} ({{ $staff->email }}) - Primary Role: {{ ucfirst($staff->role) }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedUserId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Capability Type --}}
                    <div>
                        <label class="block mb-2 text-xs font-bold uppercase text-slate-700">Capability Role <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative flex items-center p-3 border rounded-xl cursor-pointer hover:bg-slate-50 {{ $capability === 'exam_officer' ? 'border-fuchsia-500 bg-fuchsia-50/30' : 'border-gray-200' }}">
                                <input type="radio" wire:model="capability" value="exam_officer" class="mr-3 text-fuchsia-600 focus:ring-fuchsia-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-slate-700">Exam Officer</span>
                                    <span class="block text-xxs text-slate-500">Vet & approve department results</span>
                                </div>
                            </label>

                            <label class="relative flex items-center p-3 border rounded-xl cursor-pointer hover:bg-slate-50 {{ $capability === 'lecturer' ? 'border-lime-500 bg-lime-50/30' : 'border-gray-200' }}">
                                <input type="radio" wire:model="capability" value="lecturer" class="mr-3 text-lime-600 focus:ring-lime-500" />
                                <div>
                                    <span class="block text-sm font-semibold text-slate-700">Lecturer</span>
                                    <span class="block text-xxs text-slate-500">Enter results & manage courses</span>
                                </div>
                            </label>
                        </div>
                        @error('capability') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Department Scope --}}
                    <div>
                        <label class="block mb-2 text-xs font-bold uppercase text-slate-700">Department Scope</label>
                        <select wire:model="departmentId" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-2 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none">
                            <option value="">Institution-Wide (All Departments)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xxs text-slate-400">Leave unselected if capability applies across the entire institution.</p>
                        @error('departmentId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Reason / Tenure Note --}}
                    <div>
                        <label class="block mb-2 text-xs font-bold uppercase text-slate-700">Reason / Appointment Notes</label>
                        <textarea wire:model="reason" rows="2" placeholder="e.g. Appointed Departmental Exam Officer for 2025/2026 Academic Session" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full rounded-lg border border-solid border-gray-300 bg-white py-2 px-3 font-normal text-gray-700 focus:border-fuchsia-300 focus:outline-none"></textarea>
                        @error('reason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end p-6 pt-4 border-t border-gray-100 gap-3">
                    <button wire:click="closeAssignModal" type="button" class="px-6 py-2.5 text-xs font-bold text-slate-700 uppercase bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="assignCapability" type="button" class="px-6 py-2.5 text-xs font-bold text-white uppercase bg-gradient-fuchsia rounded-lg shadow-soft-md hover:scale-102 transition-all">
                        Assign Capability
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
