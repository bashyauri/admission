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

                        <label class="px-4 py-2 text-xs font-bold text-white uppercase bg-blue-600 rounded-lg shadow hover:bg-blue-500 cursor-pointer transition">
                            📁 Upload CSV
                            <input type="file" wire:model="file" class="hidden" accept=".csv,.xlsx">
                        </label>

                        @if($file)
                            <button wire:click="importResults" class="px-4 py-2 text-xs font-bold text-white uppercase bg-indigo-600 rounded-lg shadow hover:bg-indigo-500 transition">
                                ✔ Confirm Import
                            </button>
                        @endif

                        <button wire:click="submitAll"
                            onclick="confirm('Submit all results to HOD? This locks them from further editing.') || event.stopImmediatePropagation()"
                            class="px-4 py-2 text-xs font-bold text-white uppercase bg-green-600 rounded-lg shadow hover:bg-green-500 transition">
                            ✅ Submit to HOD
                        </button>
                    </div>
                </div>

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
                                        $total = (float)$ca + (float)$exam;
                                        $grade = match(true) {
                                            $total >= 70 => 'A',
                                            $total >= 60 => 'B',
                                            $total >= 50 => 'C',
                                            $total >= 45 => 'D',
                                            default => ($ca !== null || $exam !== null) ? 'F' : '-',
                                        };
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
                                                wire:model.defer="results.{{ $userId }}.ca"
                                                class="text-sm px-2 py-1 border rounded w-20 text-center focus:ring-2 focus:ring-fuchsia-400 {{ !$isPending ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ !$isPending ? 'disabled' : '' }}>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <input type="number" step="0.01" min="0" max="60"
                                                wire:model.defer="results.{{ $userId }}.exam"
                                                class="text-sm px-2 py-1 border rounded w-20 text-center focus:ring-2 focus:ring-fuchsia-400 {{ !$isPending ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ !$isPending ? 'disabled' : '' }}>
                                        </td>
                                        <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                            <span class="text-sm font-bold {{ ($ca !== null || $exam !== null) ? 'text-slate-800' : 'text-slate-300' }}">
                                                {{ ($ca !== null || $exam !== null) ? number_format($total, 1) : '-' }}
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
                                            <button wire:click="saveScore('{{ $userId }}')"
                                                class="text-xs font-semibold text-white bg-slate-800 px-3 py-1 rounded hover:bg-fuchsia-600 transition">
                                                Save
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
