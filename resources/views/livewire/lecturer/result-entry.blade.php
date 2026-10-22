<div>
    <div class="flex flex-wrap -mx-3 mb-5">
        <div class="w-full max-w-full px-3 mb-6 mx-auto">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                
                {{-- Header --}}
                <div class="p-6 pb-0 mb-0 bg-white border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex flex-wrap justify-between items-start gap-4">
                    <div>
                        <h6 class="dark:text-white font-bold text-lg">
                            Result Entry: {{ $allocation->departmentCourse->studentCourse->code ?? 'Course' }}
                        </h6>
                        <p class="text-sm text-slate-500">{{ $allocation->departmentCourse->studentCourse->title ?? $allocation->departmentCourse->studentCourse->name ?? '' }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button wire:click="downloadTemplate" class="px-4 py-2 text-xs font-bold text-white uppercase bg-slate-700 rounded-lg shadow hover:bg-slate-600 transition">
                            ⬇ Download Template
                        </button>

                        <div class="flex items-center gap-2">
                            <label class="px-4 py-2 text-xs font-bold text-white uppercase bg-blue-600 rounded-lg shadow hover:bg-blue-500 cursor-pointer transition">
                                Choose CSV/XLSX
                                <input type="file" wire:model="file" class="sr-only" accept=".csv,.xlsx">
                            </label>

                            <span wire:loading wire:target="file" class="text-xs font-semibold text-blue-700">
                                Uploading file...
                            </span>

                            @if($file)
                                <span wire:loading.remove wire:target="file" class="max-w-[180px] truncate text-xs font-semibold text-slate-600" title="{{ $file->getClientOriginalName() }}">
                                    {{ $file->getClientOriginalName() }} ready
                                </span>
                            @endif
                        </div>

                        @if($file)
                            <button wire:click="previewResults" wire:loading.attr="disabled" wire:target="file,previewResults" class="px-4 py-2 text-xs font-bold text-white uppercase bg-indigo-600 rounded-lg shadow hover:bg-indigo-500 transition disabled:opacity-50">
                                <span wire:loading.remove wire:target="file,previewResults">Preview Upload</span>
                                <span wire:loading wire:target="file,previewResults">Preparing Preview...</span>
                            </button>
                        @endif

                        <button wire:click="submitAll"
                            onclick="confirm('Submit all results to coordinators? This locks them from further editing.') || event.stopImmediatePropagation()"
                            class="px-4 py-2 text-xs font-bold text-white uppercase bg-green-600 rounded-lg shadow hover:bg-green-500 transition">
                            ✅ Submit to Coordinators
                        </button>
                    </div>
                </div>

                @error('file')
                    <div class="mx-6 mb-4 border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                {{-- Allocation Context --}}
                <div class="px-6 py-4 border-b bg-slate-50 flex flex-wrap items-center gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Academic Session</label>
                        <span class="inline-flex text-sm border border-gray-200 bg-white rounded-lg px-3 py-1.5 text-slate-700">
                            {{ $allocation->academic_session }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Semester</label>
                        <span class="inline-flex text-sm border border-gray-200 bg-white rounded-lg px-3 py-1.5 text-slate-700">
                            {{ ($allocation->semester ?? 'first') === 'second' ? 'Rain (Second)' : 'Harmattan (First)' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Assigned Coordinator</label>
                        @if($this->assignedCoordinator && $this->assignedCoordinator->user)
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold border border-indigo-200 bg-indigo-50 rounded-lg px-3 py-1.5 text-indigo-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $this->assignedCoordinator->user->firstname }} {{ $this->assignedCoordinator->user->surname }}
                            </span>
                        @else
                            <span class="inline-flex text-sm border border-amber-200 bg-amber-50 rounded-lg px-3 py-1.5 text-amber-700">
                                No coordinator assigned
                            </span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Scoring Weight</label>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold border border-fuchsia-200 bg-fuchsia-50 rounded-lg px-3 py-1.5 text-fuchsia-800">
                            <span>CA: {{ $maxCa }}%</span>
                            <span class="text-fuchsia-400">/</span>
                            <span>Exam: {{ $maxExam }}%</span>
                            @if($maxCa !== 40 || $maxExam !== 60)
                                <span class="ml-1 text-[10px] bg-fuchsia-600 text-white px-1.5 py-0.5 rounded font-black tracking-wide uppercase">Practical / Custom</span>
                            @endif
                        </span>
                    </div>
                    <div class="mt-auto ml-auto">
                        <span class="text-xs text-slate-400">
                            Showing <strong class="text-slate-700">{{ $students->count() }}</strong> student(s)
                        </span>
                    </div>
                </div>

                <div class="mx-4 mt-4 flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 sm:mx-6">
                    <span aria-hidden="true" class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700">i</span>
                    <p class="mb-0 text-sm leading-5 text-sky-900">
                        Save each student’s scores with <strong>Save score</strong>. When the course results are complete, use <strong>Submit to Coordinators</strong> to send the pending results for review.
                    </p>
                </div>

                @if($uploadPreviewRows)
                    <div class="border-b border-slate-200 bg-white px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Upload Preview</h2>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $previewValidCount }} valid row(s), {{ count($uploadErrors) }} row(s) requiring attention. No results have been saved yet.
                                </p>
                            </div>
                            @if($previewValidCount > 0)
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="discardUploadPreview" type="button" class="px-4 py-2 text-xs font-bold text-slate-700 uppercase border border-slate-300 bg-white rounded-lg hover:bg-slate-100 transition">
                                        Discard Preview
                                    </button>
                                    <button wire:click="importResults" wire:loading.attr="disabled" wire:target="importResults" class="px-4 py-2 text-xs font-bold text-white uppercase bg-green-600 rounded-lg shadow hover:bg-green-500 transition disabled:opacity-50">
                                        <span wire:loading.remove wire:target="importResults">Confirm Import {{ $previewValidCount }} Result(s)</span>
                                        <span wire:loading wire:target="importResults">Importing...</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 max-h-80 overflow-auto rounded-lg border border-slate-200">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-slate-50 text-left text-[11px] font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2">Row</th>
                                        <th class="px-3 py-2">Matric No</th>
                                        <th class="px-3 py-2 text-center">CA</th>
                                        <th class="px-3 py-2 text-center">Exam</th>
                                        <th class="px-3 py-2 text-center">Total</th>
                                        <th class="px-3 py-2 text-center">Grade</th>
                                        <th class="px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($uploadPreviewRows as $previewRow)
                                        <tr class="{{ $previewRow['is_valid'] ? 'bg-white' : 'bg-red-50/50' }}">
                                            <td class="px-3 py-2 text-slate-500">{{ $previewRow['row'] }}</td>
                                            <td class="px-3 py-2 font-mono font-semibold text-slate-800">{{ $previewRow['matric_no'] }}</td>
                                            <td class="px-3 py-2 text-center">{{ $previewRow['ca_score'] === null ? '-' : $previewRow['ca_score'] }}</td>
                                            <td class="px-3 py-2 text-center">{{ $previewRow['exam_score'] === null ? '-' : $previewRow['exam_score'] }}</td>
                                            <td class="px-3 py-2 text-center font-semibold">{{ $previewRow['total_score'] === null ? '-' : number_format($previewRow['total_score'], 2) }}</td>
                                            <td class="px-3 py-2 text-center font-bold">{{ $previewRow['grade'] }}</td>
                                            <td class="px-3 py-2">
                                                <span class="text-xs font-semibold {{ $previewRow['is_valid'] ? 'text-green-700' : 'text-red-700' }}">{{ $previewRow['message'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Student Results Table --}}
                <div class="flex-auto px-0 pt-0 pb-2">
                    {{-- Compact student cards keep scores and the save action visible on phones/tablets. --}}
                    <div class="space-y-3 px-4 py-4 2xl:hidden">
                        @forelse($students as $student)
                            @php
                                $userId = $student->academicDetail->user_id ?? null;
                                $ca = $results[$userId]['ca'] ?? null;
                                $exam = $results[$userId]['exam'] ?? null;
                                $isAbsent = (bool) ($results[$userId]['is_absent'] ?? false);
                                $hasInvalidCa = $ca !== null && $ca !== '' && (!is_numeric($ca) || (float) $ca < 0 || (float) $ca > $maxCa);
                                $hasInvalidExam = $exam !== null && $exam !== '' && (!is_numeric($exam) || (float) $exam < 0 || (float) $exam > $maxExam);
                                $hasInvalidScore = $hasInvalidCa || $hasInvalidExam;
                                $hasScore = ($ca !== null && $ca !== '') || ($exam !== null && $exam !== '');
                                $hasCompleteScore = $isAbsent || ($ca !== null && $ca !== '' && $exam !== null && $exam !== '');
                                $total = $isAbsent ? 0 : ($hasInvalidScore || !$hasCompleteScore ? null : (float) $ca + (float) $exam);
                                $grade = $isAbsent ? 'F' : (!$hasCompleteScore || $hasInvalidScore ? '-' : match(true) {
                                    $total >= 70 => 'A',
                                    $total >= 60 => 'B',
                                    $total >= 50 => 'C',
                                    $total >= 45 => 'D',
                                    $total >= 40 => 'E',
                                    default => 'F',
                                });
                                $status = $results[$userId]['status'] ?? 'pending';
                                $isPending = $status === 'pending';
                                $canEnterNewResult = $results[$userId]['can_enter_new_result'] ?? true;
                                $institutionalStatus = $results[$userId]['institutional_status'] ?? null;
                            @endphp
                            <article wire:key="result-entry-mobile-{{ $userId }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="break-words text-sm font-bold text-slate-900">
                                            {{ format_user_name($student->academicDetail->user) }}
                                        </h3>
                                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $student->academicDetail->matric_no ?? 'N/A' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase
                                        @if($status === 'pending') bg-amber-100 text-amber-800
                                        @elseif($status === 'submitted') bg-blue-100 text-blue-800
                                        @else bg-emerald-100 text-emerald-800 @endif">
                                        {{ $status }}
                                    </span>
                                </div>

                                @if($institutionalStatus)
                                    <p class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800">{{ $institutionalStatus }}</p>
                                @endif

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <label class="block text-xs font-semibold text-slate-600">
                                        CA score <span class="font-normal">(max {{ $maxCa }})</span>
                                        <input type="number" step="0.01" min="0" max="{{ $maxCa }}"
                                            wire:model.live.debounce.300ms="results.{{ $userId }}.ca"
                                            aria-label="CA score for {{ $student->academicDetail->matric_no ?? 'student' }}"
                                            aria-invalid="{{ $hasInvalidCa ? 'true' : 'false' }}"
                                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 {{ $hasInvalidCa ? 'border-red-500 bg-red-50 focus:ring-red-200' : 'border-slate-300 bg-white focus:ring-fuchsia-200' }}"
                                            {{ !$isPending || $isAbsent || !$canEnterNewResult ? 'disabled' : '' }}>
                                        @if($hasInvalidCa)
                                            <span class="mt-1 block text-xs font-medium text-red-700">Enter a value from 0 to {{ $maxCa }}.</span>
                                        @endif
                                    </label>
                                    <label class="block text-xs font-semibold text-slate-600">
                                        Exam score <span class="font-normal">(max {{ $maxExam }})</span>
                                        <input type="number" step="0.01" min="0" max="{{ $maxExam }}"
                                            wire:model.live.debounce.300ms="results.{{ $userId }}.exam"
                                            aria-label="Exam score for {{ $student->academicDetail->matric_no ?? 'student' }}"
                                            aria-invalid="{{ $hasInvalidExam ? 'true' : 'false' }}"
                                            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 {{ $hasInvalidExam ? 'border-red-500 bg-red-50 focus:ring-red-200' : 'border-slate-300 bg-white focus:ring-fuchsia-200' }}"
                                            {{ !$isPending || $isAbsent || !$canEnterNewResult ? 'disabled' : '' }}>
                                        @if($hasInvalidExam)
                                            <span class="mt-1 block text-xs font-medium text-red-700">Enter a value from 0 to {{ $maxExam }}.</span>
                                        @endif
                                    </label>
                                </div>

                                <div class="mt-3 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-xs">
                                    <span class="text-slate-600">Total
                                        <strong class="ml-1 {{ $hasInvalidScore ? 'text-red-700' : 'text-slate-900' }}">
                                            {{ $isAbsent ? 'Absent' : ($hasInvalidScore ? 'Fix score' : ($hasCompleteScore ? number_format($total, 1) : ($hasScore ? 'Incomplete' : '-'))) }}
                                        </strong>
                                    </span>
                                    <span class="text-slate-600">Grade <strong class="ml-1 text-sm text-slate-900">{{ $grade }}</strong></span>
                                </div>

                                @if($isPending && $canEnterNewResult)
                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                        <label class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-slate-700">
                                            <input type="checkbox" wire:model.live="results.{{ $userId }}.is_absent" class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                                            Mark absent
                                        </label>
                                        <button type="button" wire:click="saveScore('{{ $userId }}')"
                                            wire:loading.attr="disabled" wire:target="saveScore('{{ $userId }}')"
                                            class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white transition hover:bg-fuchsia-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                            <span wire:loading.remove wire:target="saveScore('{{ $userId }}')">Save score</span>
                                            <span wire:loading wire:target="saveScore('{{ $userId }}')">Saving...</span>
                                        </button>
                                    </div>
                                @elseif($isPending)
                                    <p class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800">New result entry is blocked by the student’s institutional status. Previously saved results remain in the record.</p>
                                @else
                                    <p class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">Editing is locked because this result has been {{ $status }}.</p>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                No students registered for this course in <strong>{{ $selectedSession }}</strong> ({{ ucfirst($selectedSemester) }} Semester).
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden p-0 overflow-x-auto 2xl:block">
                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                            <thead class="align-bottom">
                                <tr>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Student</th>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Matric No</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">CA Score ({{ $maxCa }})</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Exam Score ({{ $maxExam }})</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Total</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Grade</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Status</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    @php
                                        $userId = $student->academicDetail->user_id ?? null;
                                        $ca = $results[$userId]['ca'] ?? null;
                                        $exam = $results[$userId]['exam'] ?? null;
                                        $isAbsent = (bool) ($results[$userId]['is_absent'] ?? false);
                                        $hasInvalidCa = $ca !== null && $ca !== '' && (!is_numeric($ca) || (float) $ca < 0 || (float) $ca > $maxCa);
                                        $hasInvalidExam = $exam !== null && $exam !== '' && (!is_numeric($exam) || (float) $exam < 0 || (float) $exam > $maxExam);
                                        $hasInvalidScore = $hasInvalidCa || $hasInvalidExam;
                                        $hasScore = ($ca !== null && $ca !== '') || ($exam !== null && $exam !== '');
                                        $hasCompleteScore = $isAbsent || ($ca !== null && $ca !== '' && $exam !== null && $exam !== '');
                                        $total = $isAbsent ? 0 : ($hasInvalidScore || !$hasCompleteScore ? null : (float) $ca + (float) $exam);
                                        $grade = $isAbsent ? 'F' : (!$hasCompleteScore || $hasInvalidScore ? '-' : match(true) {
                                            $total >= 70 => 'A',
                                            $total >= 60 => 'B',
                                            $total >= 50 => 'C',
                                            $total >= 45 => 'D',
                                            $total >= 40 => 'E',
                                            default => 'F',
                                        });
                                        $status = $results[$userId]['status'] ?? 'pending';
                                        $isPending = $status === 'pending';
                                        $canEnterNewResult = $results[$userId]['can_enter_new_result'] ?? true;
                                        $institutionalStatus = $results[$userId]['institutional_status'] ?? null;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <div class="flex px-2 py-1">
                                                <div class="flex flex-col justify-center">
                                                    <h6 class="mb-0 text-sm leading-normal font-semibold">
                                                        {{ format_user_name($student->academicDetail->user) }}
                                                       
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <p class="mb-0 text-xs font-semibold leading-tight font-mono">{{ $student->academicDetail->matric_no ?? 'N/A' }}</p>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <input type="number" step="0.01" min="0" max="{{ $maxCa }}"
                                                wire:model.live.debounce.300ms="results.{{ $userId }}.ca"
                                                aria-invalid="{{ $hasInvalidCa ? 'true' : 'false' }}"
                                                class="text-sm px-2 py-1 border rounded w-20 text-center focus:ring-2 {{ $hasInvalidCa ? 'border-red-500 bg-red-50 text-red-700 focus:ring-red-200' : 'focus:ring-fuchsia-400' }} {{ !$isPending ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ !$isPending || $isAbsent || !$canEnterNewResult ? 'disabled' : '' }}>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <input type="number" step="0.01" min="0" max="{{ $maxExam }}"
                                                wire:model.live.debounce.300ms="results.{{ $userId }}.exam"
                                                aria-invalid="{{ $hasInvalidExam ? 'true' : 'false' }}"
                                                class="text-sm px-2 py-1 border rounded w-20 text-center focus:ring-2 {{ $hasInvalidExam ? 'border-red-500 bg-red-50 text-red-700 focus:ring-red-200' : 'focus:ring-fuchsia-400' }} {{ !$isPending ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ !$isPending || $isAbsent || !$canEnterNewResult ? 'disabled' : '' }}>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <span class="text-sm font-bold {{ $isAbsent ? 'text-red-600' : ($hasInvalidScore ? 'text-red-600' : ($hasCompleteScore ? 'text-slate-800' : 'text-amber-600')) }}">
                                                {{ $isAbsent ? 'Absent' : ($hasInvalidScore ? 'Fix score' : ($hasCompleteScore ? number_format($total, 1) : ($hasScore ? 'Incomplete' : '-'))) }}
                                            </span>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <span class="text-sm font-bold {{ $grade === 'A' ? 'text-green-600' : ($grade === 'F' ? 'text-red-600' : 'text-slate-700') }}">
                                                {{ $grade }}
                                            </span>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <span class="text-xs font-semibold uppercase px-2 py-1 rounded
                                                @if($status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($status === 'submitted') bg-blue-100 text-blue-800
                                                @else bg-green-100 text-green-800 @endif">
                                                {{ $status }}
                                            </span>
                                            @if($institutionalStatus)
                                                <span class="mt-1 block rounded border border-rose-200 bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-800">{{ $institutionalStatus }}</span>
                                            @endif
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-normal shadow-transparent">
                                            @if($isPending)
                                                @if($canEnterNewResult)
                                                <label class="mb-2 flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-600">
                                                    <input type="checkbox" wire:model.live="results.{{ $userId }}.is_absent" class="rounded border-slate-300 text-red-600 focus:ring-red-500" {{ !$canEnterNewResult ? 'disabled' : '' }}>
                                                    Absent
                                                </label>
                                                <button wire:click="saveScore('{{ $userId }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="saveScore('{{ $userId }}')"
                                                    class="inline-flex min-w-[72px] items-center justify-center text-xs font-bold text-white bg-slate-800 px-3 py-2 rounded-lg hover:bg-fuchsia-600 transition disabled:cursor-not-allowed disabled:opacity-50">
                                                    <span wire:loading.remove wire:target="saveScore('{{ $userId }}')">Save score</span>
                                                    <span wire:loading wire:target="saveScore('{{ $userId }}')">Saving...</span>
                                                </button>
                                                @else
                                                    <span class="block max-w-[180px] break-words text-left text-[10px] font-semibold leading-snug text-rose-700">New result entry is blocked. Previously saved results remain in the record.</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-6 text-center align-middle bg-transparent border-b">
                                            <p class="mb-0 text-sm font-semibold text-slate-400">
                                                No students registered for this course in <strong>{{ $selectedSession }}</strong> ({{ ucfirst($selectedSemester) }} Semester).
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
