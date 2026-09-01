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
                    <div class="mt-auto">
                        <span class="text-xs text-slate-400">
                            Showing <strong class="text-slate-700">{{ $students->count() }}</strong> student(s)
                        </span>
                    </div>
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
                    <div class="p-0 overflow-x-auto">
                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                            <thead class="align-bottom">
                                <tr>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Student</th>
                                    <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Matric No</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">CA Score (40)</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Exam Score (60)</th>
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
                                        $hasInvalidCa = $ca !== null && $ca !== '' && (!is_numeric($ca) || (float) $ca < 0 || (float) $ca > 40);
                                        $hasInvalidExam = $exam !== null && $exam !== '' && (!is_numeric($exam) || (float) $exam < 0 || (float) $exam > 60);
                                        $hasInvalidScore = $hasInvalidCa || $hasInvalidExam;
                                        $hasScore = ($ca !== null && $ca !== '') || ($exam !== null && $exam !== '');
                                        $hasCompleteScore = $isAbsent || ($ca !== null && $ca !== '' && $exam !== null && $exam !== '');
                                        $total = $isAbsent ? 0 : ($hasInvalidScore || !$hasCompleteScore ? null : (float) $ca + (float) $exam);
                                        $grade = $isAbsent ? 'F' : (!$hasCompleteScore || $hasInvalidScore ? '-' : match(true) {
                                            $total >= 70 => 'A',
                                            $total >= 60 => 'B',
                                            $total >= 50 => 'C',
                                            $total >= 45 => 'D',
                                            default => 'F',
                                        });
                                        $status = $results[$userId]['status'] ?? 'pending';
                                        $isPending = $status === 'pending';
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <div class="flex px-2 py-1">
                                                <div class="flex flex-col justify-center">
                                                    <h6 class="mb-0 text-sm leading-normal font-semibold">
                                                         {{ $student->academicDetail->user->surname ?? '' }}
                                                        {{ $student->academicDetail->user->firstname ?? '' }}
                                                        {{ $student->academicDetail->user->m_name ?? '' }}
                                                       
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <p class="mb-0 text-xs font-semibold leading-tight font-mono">{{ $student->academicDetail->matric_no ?? 'N/A' }}</p>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <input type="number" step="0.01" min="0" max="40"
                                                wire:model.live.debounce.300ms="results.{{ $userId }}.ca"
                                                aria-invalid="{{ $hasInvalidCa ? 'true' : 'false' }}"
                                                class="text-sm px-2 py-1 border rounded w-20 text-center focus:ring-2 {{ $hasInvalidCa ? 'border-red-500 bg-red-50 text-red-700 focus:ring-red-200' : 'focus:ring-fuchsia-400' }} {{ !$isPending ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ !$isPending || $isAbsent ? 'disabled' : '' }}>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <input type="number" step="0.01" min="0" max="60"
                                                wire:model.live.debounce.300ms="results.{{ $userId }}.exam"
                                                aria-invalid="{{ $hasInvalidExam ? 'true' : 'false' }}"
                                                class="text-sm px-2 py-1 border rounded w-20 text-center focus:ring-2 {{ $hasInvalidExam ? 'border-red-500 bg-red-50 text-red-700 focus:ring-red-200' : 'focus:ring-fuchsia-400' }} {{ !$isPending ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ !$isPending || $isAbsent ? 'disabled' : '' }}>
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
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            @if($isPending)
                                                <label class="mb-2 flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-600">
                                                    <input type="checkbox" wire:model.live="results.{{ $userId }}.is_absent" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                                    Absent
                                                </label>
                                                <button wire:click="saveScore('{{ $userId }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="saveScore('{{ $userId }}')"
                                                    class="inline-flex min-w-[72px] items-center justify-center text-xs font-bold text-white bg-slate-800 px-3 py-2 rounded-lg hover:bg-fuchsia-600 transition disabled:cursor-not-allowed disabled:opacity-50">
                                                    <span wire:loading.remove wire:target="saveScore('{{ $userId }}')">Save score</span>
                                                    <span wire:loading wire:target="saveScore('{{ $userId }}')">Saving...</span>
                                                </button>
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
