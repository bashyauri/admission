<div>
    <div class="flex flex-wrap -mx-3 mb-5">
        <div class="w-full max-w-full px-3 mb-6 mx-auto">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">

                {{-- Header --}}
                <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-gradient-fuchsia text-white rounded-lg text-lg leading-none shadow-soft-md">
                                📜
                            </span>
                            <div>
                                <h6 class="dark:text-white font-bold text-lg leading-tight mb-0">
                                    Degree Certificate Generation &amp; Collection Tracking
                                </h6>
                                <p class="text-xs text-slate-500 mb-0">
                                    Generate official certificate serial numbers, track print production, and record collection audits.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Top Action Buttons --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button wire:click="batchIssueCertificates"
                                onclick="confirm('Generate certificate serial numbers for all unissued graduands in this session?') || event.stopImmediatePropagation()"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-xs font-bold text-white uppercase bg-gradient-fuchsia rounded-lg shadow-soft-md hover:scale-102 transition transform active:opacity-85 inline-flex items-center gap-1.5 disabled:opacity-50">
                            <span wire:loading.remove wire:target="batchIssueCertificates">⚡ Batch Generate All</span>
                            <span wire:loading wire:target="batchIssueCertificates">⏳ Generating...</span>
                        </button>

                        <a href="{{ route('exam-officer.graduation-audit') }}"
                           class="px-4 py-2 text-xs font-bold text-slate-700 uppercase bg-white border border-slate-300 rounded-lg shadow-soft-md hover:bg-slate-50 transition inline-flex items-center gap-1.5">
                            <span>🎓 Graduation Audit</span>
                        </a>

                        <a href="{{ route('exam-officer.senate-pass-list', ['session' => str_replace('/', '-', $selectedSession)]) }}"
                           target="_blank"
                           class="px-4 py-2 text-xs font-bold text-indigo-700 uppercase bg-indigo-50 border border-indigo-200 rounded-lg shadow-soft-md hover:bg-indigo-100 transition inline-flex items-center gap-1.5">
                            <span>📜 Senate Pass List</span>
                        </a>
                    </div>
                </div>

                {{-- Metric Cards --}}
                <div class="p-6 pt-4 pb-2">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Issued</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-slate-800">{{ $totalIssued }}</span>
                                <span class="text-xs text-slate-400">All time</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Pending Collection</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-amber-800">{{ $totalPending }}</span>
                                <span class="text-xs text-amber-600">In registry</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Collected</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-emerald-800">{{ $totalCollected }}</span>
                                <span class="text-xs text-emerald-600">Delivered</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-purple-50/60 rounded-xl border border-purple-100 flex flex-col justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Awaiting Issuance</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-purple-800">{{ $unissuedItems->count() }}</span>
                                <span class="text-xs text-purple-600">Staged</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filter & Search Toolbar --}}
                <div class="px-6 py-3 border-y border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Session</label>
                            <select wire:model.live="selectedSession"
                                    class="text-xs bg-white border border-slate-200 text-slate-800 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500 px-3 py-1.5 font-medium shadow-soft-xs">
                                @foreach($availableSessions as $sess)
                                    <option value="{{ $sess }}">{{ $sess }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Department</label>
                            <select wire:model.live="selectedDepartment"
                                    class="text-xs bg-white border border-slate-200 text-slate-800 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500 px-3 py-1.5 font-medium shadow-soft-xs">
                                <option value="all">All Departments</option>
                                @foreach($availableDepartments as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status</label>
                            <select wire:model.live="statusFilter"
                                    class="text-xs bg-white border border-slate-200 text-slate-800 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500 px-3 py-1.5 font-medium shadow-soft-xs">
                                <option value="all">All Records</option>
                                <option value="pending">Pending Collection</option>
                                <option value="collected">Collected</option>
                                <option value="printed">Printed</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search</label>
                            <input type="text"
                                   wire:model.live.debounce.400ms="searchQuery"
                                   placeholder="Cert No, Name, Matric..."
                                   class="text-xs bg-white border border-slate-200 text-slate-800 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500 pl-3 pr-8 py-1.5 font-medium shadow-soft-xs w-56">
                            @if($searchQuery)
                                <button wire:click="$set('searchQuery', '')" class="absolute right-2 top-6 text-slate-400 hover:text-slate-600 text-xs">✕</button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Staged Graduands Awaiting Certificate Section (if any) --}}
                @if($unissuedItems->isNotEmpty())
                    <div class="p-6 pb-2" x-data="{ openUnissued: true }">
                        <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-4">
                            <div class="flex items-center justify-between cursor-pointer" @click="openUnissued = !openUnissued">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-purple-900">
                                        ⚡ Staged Graduands Awaiting Certificate Issuance ({{ $unissuedItems->count() }})
                                    </span>
                                    <span class="text-xs text-purple-600">
                                        Cleared on the official graduation list but without a generated certificate serial number.
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-purple-700 hover:underline" x-text="openUnissued ? 'Hide ▲' : 'Show ▼'"></span>
                            </div>

                            <div x-show="openUnissued" class="mt-3 overflow-x-auto">
                                <table class="items-center w-full mb-0 align-top border-collapse text-slate-500">
                                    <thead class="align-bottom">
                                        <tr class="border-b border-purple-200 text-[10px] font-bold uppercase tracking-wider text-purple-800 text-left">
                                            <th class="py-2 px-3">Student Name</th>
                                            <th class="py-2 px-3">Matric No</th>
                                            <th class="py-2 px-3">Department</th>
                                            <th class="py-2 px-3">Degree Class</th>
                                            <th class="py-2 px-3 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-purple-100 text-xs">
                                        @foreach($unissuedItems as $item)
                                            <tr class="hover:bg-purple-50/60 transition">
                                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                                    {{ $item->full_name ?: ($item->user?->surname . ' ' . $item->user?->firstname) }}
                                                </td>
                                                <td class="py-2.5 px-3 font-mono text-slate-700">
                                                    {{ $item->matric_no ?: ($item->academicDetail?->matric_no ?? '—') }}
                                                </td>
                                                <td class="py-2.5 px-3 text-slate-600">
                                                    {{ $item->department ?: ($item->academicDetail?->department?->name ?? '—') }}
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-100 text-purple-800">
                                                        {{ $item->class_of_degree ?? '—' }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    <button wire:click="openIssueModal({{ $item->id }})"
                                                            class="px-3 py-1 text-[11px] font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-md shadow-soft-xs transition">
                                                        Generate Serial
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Issued Certificates Table --}}
                <div class="flex-auto px-0 pt-0 pb-2">
                    <div class="p-0 overflow-x-auto">
                        <table class="items-center w-full mb-0 align-top border-collapse text-slate-500">
                            <thead class="align-bottom">
                                <tr class="border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-left bg-slate-50/75">
                                    <th class="py-3 px-4">Certificate No</th>
                                    <th class="py-3 px-4">Student</th>
                                    <th class="py-3 px-4">Programme & Dept</th>
                                    <th class="py-3 px-4">Class of Degree</th>
                                    <th class="py-3 px-4 text-center">Print Status</th>
                                    <th class="py-3 px-4 text-center">Collection Status</th>
                                    <th class="py-3 px-4">Recipient / Date</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse($certificates as $cert)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        {{-- Certificate Number --}}
                                        <td class="py-3 px-4">
                                            <span class="font-mono font-bold text-purple-900 bg-purple-50 px-2 py-1 rounded border border-purple-200">
                                                {{ $cert->certificate_number }}
                                            </span>
                                            <p class="text-[10px] text-slate-400 mt-1">
                                                Issued: {{ $cert->issue_date ? $cert->issue_date->format('d M Y') : '—' }}
                                            </p>
                                        </td>

                                        {{-- Student Details --}}
                                        <td class="py-3 px-4">
                                            <p class="font-bold text-slate-800 mb-0">
                                                {{ $cert->user?->surname }} {{ $cert->user?->firstname }} {{ $cert->user?->m_name }}
                                            </p>
                                            <p class="text-[11px] font-mono text-slate-500 mb-0">
                                                {{ $cert->academicDetail?->matric_no ?? '—' }}
                                            </p>
                                        </td>

                                        {{-- Programme & Department --}}
                                        <td class="py-3 px-4">
                                            <p class="text-slate-700 font-medium mb-0">
                                                {{ $cert->academicDetail?->department?->name ?? '—' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 mb-0">
                                                {{ $cert->graduationList?->academic_session ?? '—' }}
                                            </p>
                                        </td>

                                        {{-- Class of Degree --}}
                                        <td class="py-3 px-4">
                                            <span class="inline-block px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 text-slate-800">
                                                {{ $cert->class_of_degree }}
                                            </span>
                                        </td>

                                        {{-- Print Status --}}
                                        <td class="py-3 px-4 text-center">
                                            <button wire:click="togglePrinted({{ $cert->id }})"
                                                    title="Click to toggle print status"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full transition {{ $cert->is_printed ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                                <span>{{ $cert->is_printed ? '🖨️ Printed' : '⏳ Unprinted' }}</span>
                                            </button>
                                        </td>

                                        {{-- Collection Status --}}
                                        <td class="py-3 px-4 text-center">
                                            @if($cert->is_collected)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                    ✅ Collected
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">
                                                    📦 In Custody
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Recipient & Date Collected --}}
                                        <td class="py-3 px-4">
                                            @if($cert->is_collected)
                                                <p class="font-semibold text-slate-800 mb-0">
                                                    {{ $cert->recipient_name ?: ($cert->user?->surname . ' ' . $cert->user?->firstname) }}
                                                </p>
                                                <p class="text-[10px] text-slate-500 mb-0">
                                                    {{ $cert->collected_at ? $cert->collected_at->format('d M Y, h:i A') : '—' }}
                                                </p>
                                                @if($cert->collectedBy)
                                                    <p class="text-[9px] text-slate-400 mb-0">
                                                        Officer: {{ $cert->collectedBy->surname }}
                                                    </p>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400 italic">Not yet collected</span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="py-3 px-4 text-right">
                                            @if(!$cert->is_collected)
                                                <button wire:click="openCollectModal({{ $cert->id }})"
                                                        class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-soft-xs transition inline-flex items-center gap-1">
                                                    <span>Log Collection</span>
                                                </button>
                                            @else
                                                <span class="text-xs text-emerald-700 font-medium">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10 text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <span class="text-4xl mb-2">📜</span>
                                                <p class="font-bold text-sm text-slate-600">No Degree Certificates Found</p>
                                                <p class="text-xs text-slate-400 mt-1 max-w-sm">
                                                    @if($searchQuery || $statusFilter !== 'all')
                                                        No certificates match your search filters. Try clearing search or status options.
                                                    @else
                                                        Graduands must first be audited and staged into the Graduation List from the Graduation Audit screen.
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($certificates->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">
                            {{ $certificates->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Issue Certificate Modal --}}
    @if($showIssueModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             x-data
             @keydown.escape.window="$wire.closeIssueModal()">
            <div class="bg-white rounded-2xl shadow-soft-2xl max-w-md w-full p-6 relative">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📜</span>
                        <h6 class="font-bold text-slate-800 mb-0">Generate Degree Certificate</h6>
                    </div>
                    <button wire:click="closeIssueModal" class="text-slate-400 hover:text-slate-600 text-lg leading-none">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-slate-500 mb-1 font-semibold uppercase text-[10px]">Student Information</p>
                        <p class="font-bold text-slate-800 text-sm mb-0">{{ $issueStudentName }}</p>
                        <p class="font-mono text-slate-600 text-xs mb-1">Matric No: {{ $issueMatricNo }}</p>
                        <p class="text-purple-800 font-semibold mb-0">Class: {{ $issueClassOfDegree }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Remarks / Reference (Optional)</label>
                        <textarea wire:model.defer="issueRemarks"
                                  rows="2"
                                  placeholder="e.g. Senate approval Ref No..."
                                  class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-fuchsia-500 focus:border-fuchsia-500"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button wire:click="closeIssueModal"
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button wire:click="confirmIssueCertificate"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-bold text-white bg-gradient-fuchsia rounded-lg shadow-soft-md hover:scale-102 transition transform disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmIssueCertificate">Generate & Issue</span>
                        <span wire:loading wire:target="confirmIssueCertificate">Generating...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Log Collection Modal --}}
    @if($showCollectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             x-data
             @keydown.escape.window="$wire.closeCollectModal()">
            <div class="bg-white rounded-2xl shadow-soft-2xl max-w-md w-full p-6 relative">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📦</span>
                        <h6 class="font-bold text-slate-800 mb-0">Log Certificate Collection</h6>
                    </div>
                    <button wire:click="closeCollectModal" class="text-slate-400 hover:text-slate-600 text-lg leading-none">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-slate-500 mb-1 font-semibold uppercase text-[10px]">Graduand</p>
                        <p class="font-bold text-slate-800 text-sm mb-0">{{ $collectStudentName }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">
                            Recipient Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="collectRecipientName"
                               placeholder="Full name of person receiving the certificate"
                               class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-fuchsia-500 focus:border-fuchsia-500">
                        @error('collectRecipientName')
                            <p class="text-rose-600 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Collection Remarks (Optional)</label>
                        <textarea wire:model.defer="collectRemarks"
                                  rows="2"
                                  placeholder="ID presented, power of attorney, signature sheet ref..."
                                  class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-fuchsia-500 focus:border-fuchsia-500"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button wire:click="closeCollectModal"
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button wire:click="confirmLogCollection"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-soft-md transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmLogCollection">Confirm Collection</span>
                        <span wire:loading wire:target="confirmLogCollection">Logging...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
