<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">

            <!-- Warning Banner -->
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-0 rounded-t-2xl">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <strong class="font-medium">Warning:</strong> This tool permanently deletes applicant records and all related data. This action cannot be undone. Always export a backup before deleting.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                <div class="lg:flex justify-between items-center">
                    <div>
                        <h5 class="mb-0 dark:text-white">Manage Postgraduate Applicants</h5>
                        <p class="text-sm text-gray-600 mt-1">
                            Postgraduate applicants who were not admitted (Role: Applicant, Programme: PG)
                        </p>
                        <div class="mt-2">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                Total Applicants: {{ $totalCount }}
                            </span>
                        @if(count($selectedApplicants) > 0 || $selectAll)
                            <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium ml-2">
                                Selected: {{ count($selectedApplicants) }} {{ $selectAll ? '(All)' : '' }}
                            </span>
                            @if(!$selectAll && count($selectedApplicants) < $totalCount)
                                <button wire:click="selectAllApplicants" class="ml-2 text-xs text-blue-600 hover:text-blue-800 underline">
                                    Select all {{ $totalCount }} applicants
                                </button>
                            @endif
                            <button wire:click="clearSelection" class="ml-2 text-xs text-red-600 hover:text-red-800 underline">
                                Clear
                            </button>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if($successMessage)
                <div class="p-6 pb-0">
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ $successMessage }}</p>
                                    @if($exportPath)
                                        <p class="text-sm text-green-600 mt-1">
                                            Backup saved: {{ $exportPath }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="dismissMessages" class="text-green-400 hover:text-green-600">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($errorMessage)
                <div class="p-6 pb-0">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                                </div>
                            </div>
                            <button wire:click="dismissMessages" class="text-red-400 hover:text-red-600">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters and Actions -->
            <div class="p-6 pb-0">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-[300px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Applicants</label>
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.debounce.300ms="search"
                                placeholder="Search by name, email, or JAMB number..."
                                class="dark:bg-gray-950 focus:shadow-soft-primary-outline dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none pl-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-[140px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rows Per Page</label>
                        <select
                            wire:model.live="perPage"
                            class="dark:bg-gray-950 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all focus:border-fuchsia-300 focus:outline-none">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="flex gap-2">
                        <button
                            wire:click="exportSelected"
                            @if(empty($selectedApplicants)) disabled @endif
                            class="inline-flex items-center px-4 py-2 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-size-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-blue-500 text-blue-500 hover:text-blue-500 hover:opacity-75 hover:shadow-none active:scale-100 active:border-blue-500 active:bg-blue-500 active:text-white hover:active:border-blue-500 hover:active:bg-transparent hover:active:text-blue-500 hover:active:opacity-75 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Selected
                        </button>

                        <button
                            wire:click="openPreviewModal"
                            @if(empty($selectedApplicants)) disabled @endif
                            class="inline-flex items-center px-4 py-2 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-size-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-red-500 text-red-500 hover:text-red-500 hover:opacity-75 hover:shadow-none active:scale-100 active:border-red-500 active:bg-red-500 active:text-white hover:active:border-red-500 hover:active:bg-transparent hover:active:text-red-500 hover:active:opacity-75 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Review for Deletion
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="flex-auto p-6 px-0 pb-0">
                <div class="overflow-x-auto table-responsive">
                    <table class="table" id="postgraduate-applicants-table">
                        <thead class="thead-light">
                            <tr>
                        <th class="w-12 px-4 py-3">
                            <input
                                type="checkbox"
                                wire:model.live="selectAllOnPage"
                                class="w-4 h-4 text-fuchsia-600 bg-gray-100 border-gray-300 rounded focus:ring-fuchsia-500 cursor-pointer"
                                title="Select all on this page">
                        </th>
                                <th>Applicant</th>
                                <th>JAMB No</th>
                                <th>Email</th>
                                <th>Records</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applicants as $applicant)
                                @php
                                    $recordCount = 0;
                                    $recordCount += $applicant->proposedCourse ? 1 : 0;
                                    $recordCount += $applicant->academicDetail ? 1 : 0;
                                    $recordCount += $applicant->transactions->count();
                                    $recordCount += $applicant->studentTransactions->count();
                                    $recordCount += $applicant->olevelExams->count();
                                    $recordCount += $applicant->certificateUploads->count();
                                    $recordCount += $applicant->schools->count();
                                    $recordCount += $applicant->postUtmeUpload ? 1 : 0;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            wire:model.live="selectedApplicants"
                                            value="{{ $applicant->id }}"
                                            class="w-4 h-4 text-fuchsia-600 bg-gray-100 border-gray-300 rounded focus:ring-fuchsia-500 cursor-pointer applicant-checkbox">
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($applicant->firstname, 0, 1) . substr($applicant->surname, 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <h6 class="my-auto dark:text-white font-medium">
                                                    {{ $applicant->full_name }}
                                                </h6>
                                                <p class="text-xs text-gray-500">{{ $applicant->phone ?? 'N/A' }}</p>
                                            </div>
                                    </td>
                                    <td class="text-sm font-mono">{{ $applicant->jamb_no ?? 'N/A' }}</td>
                                    <td class="text-sm">{{ $applicant->email }}</td>
                                    <td>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" title="Related records count">
                                            {{ $recordCount }} records
                                        </span>
                                    </td>
                                    <td class="text-sm text-gray-500">{{ $applicant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <button
                                            wire:click="reviewSingleApplicant('{{ $applicant->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                            Review
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-lg font-medium">No applicants found</p>
                                            <p class="text-sm">No postgraduate applicants with "applicant" role found in the system.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($totalCount > $perPage)
                    <div class="p-6 pt-4">
                        {{ $applicants->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    @if($showPreviewModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closePreviewModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <!-- Modal header -->
                    <div class="bg-red-50 px-4 py-3 border-b border-red-200">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <h3 class="text-lg font-medium text-red-900" id="modal-title">Review Deletion Preview</h3>
                        </div>
                    </div>

                    <!-- Modal body -->
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        @if(!empty($previewData))
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">
                                    You are about to permanently delete <strong class="text-red-600">{{ $previewData['total_users'] }} applicants</strong>
                                    with <strong class="text-red-600">{{ $previewData['total_records'] }} related records</strong>.
                                </p>
                                <p class="text-xs text-red-600">
                                    ⚠️ This action is irreversible. Make sure you have exported a backup if needed.
                                </p>
                            </div>

                            <!-- Selected Users List -->
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Selected Applicants:</h4>
                                <div class="max-h-40 overflow-y-auto bg-gray-50 rounded-lg p-3">
                                    @foreach($previewData['users'] as $user)
                                        <div class="flex items-center py-1">
                                            <div class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-xs text-white mr-2">
                                                {{ strtoupper(substr($user['name'], 0, 1)) }}
                                            </div>
                                            <span class="text-sm">{{ $user['name'] }} ({{ $user['email'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Record Counts by Table -->
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($previewData['record_counts'] as $table => $count)
                                    @if($count > 0)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                                            <span class="text-sm text-gray-600">{{ str_replace('_', ' ', $table) }}:</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                {{ $count }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Modal footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            wire:click="proceedToConfirmation"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Proceed to Confirmation
                        </button>
                        <button
                            type="button"
                            wire:click="closePreviewModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fuchsia-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal -->
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-80 transition-opacity" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal header -->
                    <div class="bg-red-600 px-4 py-3">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-lg font-medium text-white" id="modal-title">⚠️ Final Confirmation Required</h3>
                        </div>
                    </div>

                    <!-- Modal body -->
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                            <p class="text-sm text-red-700">
                                <strong>This action cannot be undone.</strong> All selected applicants and their related records will be permanently deleted from the database.
                            </p>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">
                            You are about to delete <strong class="text-red-600 text-lg">{{ count($selectedApplicants) }} applicants</strong>.
                        </p>

                        @if($exportPath)
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                                <p class="text-sm text-blue-700">
                                    ✅ Backup exported: {{ $exportPath }}
                                </p>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="confirm-text" class="block text-sm font-medium text-gray-700 mb-2">
                                To confirm, type: <code class="bg-gray-100 px-2 py-1 rounded text-red-600 font-bold">DELETE {{ count($selectedApplicants) }}</code>
                            </label>
                            <input
                                type="text"
                                id="confirm-text"
                                wire:model.live="confirmText"
                                placeholder="DELETE {{ count($selectedApplicants) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border px-3 py-2"
                                @if($isDeleting) disabled @endif>
                        </div>

                        @if($isDeleting)
                            <div class="mt-4">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div class="bg-red-600 h-2 rounded-full animate-pulse" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <span class="ml-3 text-sm text-gray-600">Deleting...</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Modal footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            wire:click="deleteApplicants"
                            @if($isDeleting || trim($confirmText) !== 'DELETE ' . count($selectedApplicants)) disabled @endif
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            @if($isDeleting)
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Deleting...
                            @else
                                Delete Permanently
                            @endif
                        </button>
                        <button
                            type="button"
                            wire:click="closeConfirmModal"
                            @if($isDeleting) disabled @endif
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fuchsia-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
