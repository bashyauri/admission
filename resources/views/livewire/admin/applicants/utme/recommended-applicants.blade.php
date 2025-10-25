<div class="flex flex-wrap -mx-3" x-data="applicantsTable({
        applicants: {{ $recommendedApplicants->toJson() }},
        perPage: 10
    })">

    <div class="w-full max-w-full px-3">
        <div class="relative flex flex-col min-w-0 break-words bg-white dark:bg-gray-950 rounded-2xl shadow-soft-xl">

            <!-- Header -->
            <div
                class="border-black/12.5 rounded-t-2xl border-b p-6 pb-0 flex flex-col lg:flex-row justify-between gap-3">
                <h5 class="mb-0 dark:text-white">Recommended UTME Applicants</h5>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search -->
                    <input type="text" x-model="search" placeholder="Search applicants..."
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-400">

                    <!-- Per Page -->
                    <select x-model.number="perPage"
                        class="border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-400">
                        <option value="10">10 / page</option>
                        <option value="20">20 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>

                    <!-- Export Button -->
                    <a href="{{ route('admin.export-recommended-pdf') }}" target="_blank"
                        class="px-6 py-2 text-sm font-semibold text-fuchsia-500 border border-fuchsia-500 rounded-lg hover:bg-fuchsia-500 hover:text-white transition">
                        Export PDF
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="flex-auto p-6 px-0 pb-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="p-3">Full Name</th>
                                <th class="p-3">Course</th>
                                <th class="p-3">JAMB No</th>
                                <th class="p-3">Phone</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-center">Shortlist</th>
                                <th class="p-3 text-center">Drop</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="applicant in paginatedApplicants" :key="applicant.id">
                                <tr x-data="{ processing: false, dropped: false }" x-show="!dropped"
                                    x-transition.opacity.duration.400ms
                                    class="border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition">

                                    <td class="p-3">
                                        <span class="font-semibold text-gray-700 dark:text-white"
                                            x-text="applicant.surname + ' ' + applicant.firstname + ' ' + (applicant.middlename ?? '')"></span>
                                    </td>

                                    <td class="p-3" x-text="applicant.course_name"></td>
                                    <td class="p-3" x-text="applicant.jamb_no ?? '—'"></td>
                                    <td class="p-3" x-text="applicant.phone ?? '—'"></td>

                                    <td class="p-3">
                                        <span class="px-3 py-1 text-xs font-bold uppercase rounded-full" :class="{
                                                'bg-green-100 text-green-700': applicant.status === 'shortlisted',
                                                'bg-gray-200 text-gray-700': applicant.status !== 'shortlisted'
                                            }" x-text="applicant.status ?? 'Pending'">
                                        </span>
                                    </td>

                                    <!-- ✅ Shortlist -->
                                    <td class="text-center">
                                        <template x-if="!processing">
                                            <input type="checkbox" @click="processing = true;
                                                    const checkbox = $event.target;
                                                    $wire.shortlist(applicant.id)
                                                        .then(() => {
                                                            applicant.status = 'shortlisted';
                                                            processing = false;
                                                        })
                                                        .catch(() => {
                                                            checkbox.checked = false; // revert checkbox
                                                            processing = false;
                                                            window.dispatchEvent(new CustomEvent('alert', {
                                                                detail: { type: 'error', message: 'Shortlisting failed. Try again.' }
                                                            }));
                                                        });" class="rounded-lg cursor-pointer accent-green-600">
                                        </template>

                                        <template x-if="processing">
                                            <span class="text-green-600 font-medium">Processing...</span>
                                        </template>
                                    </td>

                                    <!-- Drop -->
                                    <td class="text-center">
                                        <template x-if="!processing">
                                            <button @click="processing = true;
                                                    $wire.drop(applicant.id)
                                                        .then(() => dropped = true)
                                                        .catch(() => {
                                                            processing = false;
                                                            window.dispatchEvent(new CustomEvent('alert', {
                                                                detail: { type: 'error', message: 'Failed to drop applicant.' }
                                                            }));
                                                        });"
                                                class="p-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition">
                                                ❌
                                            </button>
                                        </template>

                                        <template x-if="processing">
                                            <span class="text-red-600 font-medium">Processing...</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="paginatedApplicants.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center p-6 text-gray-500 dark:text-gray-300">
                                        No applicants found.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex flex-wrap justify-between items-center p-4 border-t mt-4 text-sm text-gray-600">
                    <span
                        x-text="`Showing ${startIndex + 1} - ${Math.min(endIndex, filteredApplicants.length)} of ${filteredApplicants.length}`"></span>

                    <div class="flex items-center gap-2 mt-2 lg:mt-0">
                        <button @click="prevPage" :disabled="page === 1"
                            class="px-3 py-1 border rounded-lg disabled:opacity-50 hover:bg-gray-100">
                            Prev
                        </button>
                        <span class="font-semibold" x-text="page"></span>
                        <button @click="nextPage" :disabled="endIndex >= filteredApplicants.length"
                            class="px-3 py-1 border rounded-lg disabled:opacity-50 hover:bg-gray-100">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Table Logic -->
<script>
    function applicantsTable({ applicants, perPage }) {
        return {
            search: '',
            applicants,
            perPage,
            page: 1,

            get filteredApplicants() {
                if (!this.search) return this.applicants;
                return this.applicants.filter(a =>
                    (a.surname + ' ' + a.firstname + ' ' + (a.middlename ?? '') + ' ' + (a.course_name ?? ''))
                        .toLowerCase()
                        .includes(this.search.toLowerCase())
                );
            },

            get startIndex() {
                return (this.page - 1) * this.perPage;
            },

            get endIndex() {
                return this.page * this.perPage;
            },

            get paginatedApplicants() {
                return this.filteredApplicants.slice(this.startIndex, this.endIndex);
            },

            nextPage() {
                if (this.endIndex < this.filteredApplicants.length) this.page++;
            },

            prevPage() {
                if (this.page > 1) this.page--;
            },

            init() {
                this.$watch('perPage', () => this.page = 1);
                this.$watch('search', () => this.page = 1);
            }
        }
    }

    // Optional: basic alert system using browser alert or your LivewireAlert
    window.addEventListener('alert', e => {
        const { type, message } = e.detail;
        if (type === 'error') {
            alert(message);
        }
    });
</script>