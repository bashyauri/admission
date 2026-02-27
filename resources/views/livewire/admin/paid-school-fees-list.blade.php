<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">

            <!-- Header -->
            <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                <div class="lg:flex items-center justify-between">
                    <div>
                        <h5 class="mb-0 dark:text-white">UG Paid School Fees</h5>
                        <div class="flex items-center gap-4 mt-1">
                            <p class="text-sm text-gray-600">Undergraduate students who have paid school fees</p>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                Total: {{ $payments->total() }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 lg:mt-0">
                        <!-- Export button can stay here -->
                        <button class="px-6 py-2 font-bold text-xs uppercase rounded-lg border border-fuchsia-500 text-fuchsia-500 hover:bg-fuchsia-500 hover:text-white transition">
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 pb-0">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            wire:model.live.debounce.500ms="search"
                            type="text"
                            placeholder="Name, RRR, matric no, dept, level..."
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-fuchsia-300 focus:outline-none dark:bg-gray-950 dark:border-gray-700 dark:text-white"
                        >
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select
                            wire:model.live="department"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-fuchsia-300 dark:bg-gray-950 dark:border-gray-700 dark:text-white"
                        >
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select
                            wire:model.live="level"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-fuchsia-300 dark:bg-gray-950 dark:border-gray-700 dark:text-white"
                        >
                            <option value="">All Levels</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl }}">{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                        <select
                            wire:model.live="perPage"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-fuchsia-300 dark:bg-gray-950 dark:border-gray-700 dark:text-white"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- Table -->
            <div class="flex-auto p-6 px-0 pb-0 overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-4 cursor-pointer" wire:click="sort('surname')">
                                S/N
                                @if($sortBy === 'surname')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="px-6 py-4 cursor-pointer" wire:click="sort('surname')">Full Name</th>
                            <th class="px-6 py-4 cursor-pointer" wire:click="sort('matric_no')">Matric No</th>
                            <th class="px-6 py-4 cursor-pointer" wire:click="sort('department_name')">Department</th>
                            <th class="px-6 py-4 cursor-pointer" wire:click="sort('level_name')">Level</th>
                            <th class="px-6 py-4 cursor-pointer" wire:click="sort('RRR')">RRR</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $index => $payment)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <td class="px-6 py-4 text-center font-medium">
                                    {{ ($payments->currentPage() - 1) * $payments->perPage() + $index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-fuchsia-500 to-purple-600 text-white flex items-center justify-center font-semibold">
                                            {{ strtoupper(substr($payment->firstname ?? '', 0, 1) . substr($payment->surname ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium">
                                                {{ $payment->surname }} {{ $payment->firstname }} {{ $payment->m_name ?? '' }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $payment->phone ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">{{ $payment->matric_no ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $payment->department_name ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $payment->level_name ?? '—' }}</td>
                                <td class="px-6 py-4 font-mono">{{ $payment->RRR ?? '—' }}</td>
                                <td class="px-6 py-4">School Fees Payment</td>
                               <td class="px-6 py-4 whitespace-nowrap">
    <span class="px-3 py-1 text-xs font-semibold uppercase rounded-full bg-green-100 text-green-800">
        Approved
    </span>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-gray-500">
                                    No matching records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 flex items-center justify-between border-t dark:border-gray-800">
                <div class="text-sm text-gray-600">
                    Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
                </div>

                <div>
                  {{ $payments->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
