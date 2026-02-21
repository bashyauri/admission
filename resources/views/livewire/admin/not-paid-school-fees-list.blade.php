<div x-data="{
    search: '{{ $search }}',
    departmentFilter: '{{ $departmentFilter }}',
    levelFilter: '{{ $levelFilter }}',
    statusFilter: '{{ $statusFilter }}',
    sortBy: '{{ $sortBy }}',
    sortDirection: '{{ $sortDirection }}',
    perPage: '{{ $perPage }}',
    currentPage: 1,

    // URL generation function
    generateInvoiceUrl(studentId) {
        return '/admin/transactions/generate-invoice/' + studentId;
    },

    get allStudents() {
        return @js($students);
    },

    get filteredStudents() {
        let query = this.allStudents;

        // Search filter
        if (this.search) {
            query = query.filter(student => {
                const searchTerm = this.search.toLowerCase();
                return (
                    (student.surname + ' ' + student.firstname + ' ' + student.m_name).toLowerCase().includes(searchTerm) ||
                    (student.email && student.email.toLowerCase().includes(searchTerm)) ||
                    (student.phone && student.phone.toLowerCase().includes(searchTerm)) ||
                    (student.matric_no && student.matric_no.toLowerCase().includes(searchTerm))
                );
            });
        }

        // Department filter
        if (this.departmentFilter) {
            query = query.filter(student =>
                student.department_name &&
                student.department_name.toLowerCase() === this.departmentFilter.toLowerCase()
            );
        }

        // Level filter
        if (this.levelFilter) {
            query = query.filter(student =>
                student.level_name && student.level_name.toLowerCase() === this.levelFilter.toLowerCase()
            );
        }

        // Status filter
        if (this.statusFilter) {
            query = query.filter(student =>
                student.payment_status &&
                student.payment_status.toLowerCase() === this.statusFilter.toLowerCase()
            );
        }

        // Sorting
        query = query.sort((a, b) => {
            let aVal = a[this.sortBy];
            let bVal = b[this.sortBy];

            // Handle null/undefined values - put them at the end
            if (aVal === null || aVal === undefined) { aVal = ''; }
            if (bVal === null || bVal === undefined) { bVal = ''; }

            if (typeof aVal === 'string') { aVal = aVal.toLowerCase(); }
            if (typeof bVal === 'string') { bVal = bVal.toLowerCase(); }

            if (this.sortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });

        return query;
    },

    get paginatedStudents() {
        const start = (this.currentPage - 1) * parseInt(this.perPage);
        const end = start + parseInt(this.perPage);
        return this.filteredStudents.slice(start, end);
    },

    get totalPages() {
        return Math.ceil(this.filteredStudents.length / parseInt(this.perPage));
    },

    get hasNextPage() {
        return this.currentPage < this.totalPages;
    },

    get hasPrevPage() {
        return this.currentPage > 1;
    },

    nextPage() {
        if (this.hasNextPage) { this.currentPage++; }
    },

    prevPage() {
        if (this.hasPrevPage) { this.currentPage--; }
    },

    goToPage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
        }
    },

    sort(field) {
        this.sortBy = field;
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        this.currentPage = 1;
    },

    getPageNumbers() {
        const total = this.totalPages;
        const current = this.currentPage;
        const delta = 2; // Number of pages to show around current page

        let pages = [];

        // Always show first page
        if (total > 0) {
            pages.push(1);
        }

        // Show pages around current page
        for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
            if (i > 1 && i < total) {
                pages.push(i);
            }
        }

        // Always show last page if different from first
        if (total > 1) {
            pages.push(total);
        }

        // Add ellipsis for gaps
        let result = [];
        for (let i = 0; i < pages.length; i++) {
            if (i > 0 && pages[i] - pages[i-1] > 1) {
                result.push('...');
            }
            result.push(pages[i]);
        }

        return result;
    },

    init() {
        this.$watch('search', () => this.currentPage = 1);
        this.$watch('departmentFilter', () => this.currentPage = 1);
        this.$watch('levelFilter', () => this.currentPage = 1);
        this.$watch('statusFilter', () => this.currentPage = 1);
        this.$watch('perPage', () => this.currentPage = 1);
    }
}" class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <!-- Header -->
            <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                <div class="lg:flex">
                    <div>
                        <h5 class="mb-0 dark:text-white">UG Not Paid School Fees</h5>
                        <div class="flex items-center gap-4">
                            <p class="text-sm text-gray-600 mt-1">Undergraduate students who have not paid school fees</p>
                            <div class="flex gap-2 text-sm">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                    Total: <span x-text="allStudents.length"></span>
                                </span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium" x-show="filteredStudents.length < allStudents.length">
                                    Filtered: <span x-text="filteredStudents.length"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="my-auto mt-6 ml-auto lg:mt-0">
                        <div class="my-auto ml-auto flex gap-2">
                            <button export-button-products data-type="csv"
                                class="inline-block px-6 py-2 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-size-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-fuchsia-500 text-fuchsia-500 hover:text-fuchsia-500 hover:opacity-75 hover:shadow-none active:scale-100 active:border-fuchsia-500 active:bg-fuchsia-500 active:text-white hover:active:border-fuchsia-500 hover:active:bg-transparent hover:active:text-fuchsia-500 hover:active:opacity-75">
                                Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 pb-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                type="text"
                                x-model="search"
                                placeholder="Search by name, email, matric no, dept, level..."
                                class="dark:bg-gray-950 focus:shadow-soft-primary-outline dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                        </div>

                        <!-- Department Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select
                                x-model="departmentFilter"
                                class="dark:bg-gray-950 focus:shadow-soft-primary-outline dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}">{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Second Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Level Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                            <select
                                x-model="levelFilter"
                                class="dark:bg-gray-950 focus:shadow-soft-primary-outline dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">All Levels</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                x-model="statusFilter"
                                class="dark:bg-gray-950 focus:shadow-soft-primary-outline dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="">All Status</option>
                                <option value="Not Paid">Not Paid</option>
                            </select>
                        </div>

                        <!-- Per Page -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                            <select
                                x-model="perPage"
                                class="dark:bg-gray-950 focus:shadow-soft-primary-outline dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="flex-auto p-6 px-0 pb-0">
                <div class="overflow-x-auto table-responsive">
                    <table class="table" id="not-paid-school-fees-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="cursor-pointer hover:bg-gray-50" @click="sort('surname')">
                                    <div class="flex items-center">
                                        Full Name
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-gray-50" @click="sort('matric_no')">
                                    <div class="flex items-center">
                                        Matric No
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-gray-50" @click="sort('department_name')">
                                    <div class="flex items-center">
                                        Department
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-gray-50" @click="sort('level_name')">
                                    <div class="flex items-center">
                                        Level
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-gray-50" @click="sort('RRR')">
                                    <div class="flex items-center">
                                        RRR
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </th>
                                <th>Description</th>
                                <th class="cursor-pointer hover:bg-gray-50" @click="sort('status')">
                                    <div class="flex items-center">
                                        Status
                                        <svg class="w-4 h-4 ml-1" column="fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="student in paginatedStudents" :key="student.id">
                                <tr class="hover:bg-gray-50">
                                    <td>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center text-white font-semibold text-sm">
                                                <span x-text="student.firstname.charAt(0) + student.surname.charAt(0)"></span>
                                            </div>
                                            <div class="ml-3">
                                                <h6 class="my-auto dark:text-white font-medium">
                                                    <span x-text="student.surname + ' ' + student.firstname + ' ' + (student.m_name || '')"></span>
                                                </h6>
                                                <p class="text-xs text-gray-500" x-text="student.phone || 'N/A'"></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm">
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded" x-text="student.matric_no || 'N/A'"></span>
                                    </td>
                                    <td class="text-sm" x-text="student.department_name || 'N/A'"></td>
                                    <td class="text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700" x-text="student.level_name || 'N/A'"></span>
                                    </td>
                                    <td class="text-sm font-mono" x-text="student.RRR || 'N/A'"></td>
                                    <td class="text-sm" x-text="student.RRR ? 'School Fees Payment' : 'N/A'"></td>
                                    <td>
                                        <span class="py-1.8 px-3 text-xs rounded-full inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none"
                                            :class="student.payment_status === 'Not Paid' ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100'">
                                            <span x-text="student.payment_status || 'Not Paid'"></span>
                                        </span>
                                        @if(isset($student) && isset($student['payment_status']))
                                            <span @class([
                                                'py-1.8 px-3 text-xs rounded-full inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none',
                                                'text-red-600 bg-red-100' => $student['payment_status'] === 'Not Paid',
                                                'text-green-600 bg-green-100' => $student['payment_status'] !== 'Not Paid',
                                            ])>
                                                {{ $student['payment_status'] ?? 'Not Paid' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                </tr>
                            </template>
                            <tr x-show="paginatedStudents.length === 0">
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-lg font-medium">No records found</p>
                                        <p class="text-sm">All undergraduate students have paid their school fees</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-6 pt-0" x-show="totalPages > 1">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span x-text="(currentPage - 1) * perPage + 1"></span> to
                            <span x-text="Math.min(currentPage * perPage, filteredStudents.length)"></span> of
                            <span x-text="filteredStudents.length"></span> results
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Previous -->
                            <button @click="prevPage()"
                                    :disabled="!hasPrevPage"
                                    class="px-3 py-1 text-sm border rounded-lg"
                                    :class="hasPrevPage ? 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed'">
                                Previous
                            </button>

                            <!-- Page Numbers -->
                            <template x-for="page in getPageNumbers()" :key="page">
                                <template x-if="page === '...'">
                                    <span class="px-3 py-1 text-sm text-gray-500">...</span>
                                </template>
                                <template x-if="page !== '...'">
                                    <button @click="goToPage(page)"
                                            class="px-3 py-1 text-sm border rounded-lg"
                                            :class="page === currentPage ? 'bg-fuchsia-500 border-fuchsia-500 text-white' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'">
                                        <span x-text="page"></span>
                                    </button>
                                </template>
                            </template>

                            <!-- Next -->
                            <button @click="nextPage()"
                                    :disabled="!hasNextPage"
                                    class="px-3 py-1 text-sm border rounded-lg"
                                    :class="hasNextPage ? 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed'">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/datatables.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
