<div>
    <span class="sr-only">My Academic Results</span>

    {{-- Header & Academic Summary Card --}}
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full max-w-full px-3 mx-auto">
            <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    {{-- Student Details --}}
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white shadow-soft-md">
                            <i class="fas fa-graduation-cap text-2xl"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 font-bold text-slate-800">
                                {{ auth()->user()->firstname }} {{ auth()->user()->surname }}
                            </h5>
                            <p class="mb-0 text-sm text-slate-500">
                                <span class="font-semibold text-slate-700">Matric No:</span> {{ $academicDetail->matric_no ?? 'N/A' }}
                                <span class="mx-2">•</span>
                                <span class="font-semibold text-slate-700">Department:</span> {{ $academicDetail->department->name ?? 'N/A' }}
                                <span class="mx-2">•</span>
                                <span class="font-semibold text-slate-700">Level:</span> {{ $academicDetail->studentLevel->level ?? '100' }}L
                            </p>
                        </div>
                    </div>

                    {{-- Session Filter --}}
                    <div class="flex items-center gap-3">
                        <div>
                            <label for="session-filter" class="block text-xs font-bold uppercase text-slate-400 mb-1">
                                Filter by Session
                            </label>
                            <select
                                id="session-filter"
                                wire:model.live="selectedSession"
                                class="text-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500 block w-full p-2.5"
                            >
                                <option value="all">All Academic Sessions</option>
                                @foreach($availableSessions as $session)
                                    <option value="{{ $session }}">{{ $session }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="my-4 border-gray-100">

                {{-- Cumulative Stats Badges --}}
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Credits Registered</p>
                        <h4 class="text-xl font-bold text-slate-800">{{ $totalTcr }} <span class="text-xs text-slate-400 font-normal">units</span></h4>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Credits Earned (Passed)</p>
                        <h4 class="text-xl font-bold text-emerald-600">{{ $totalTcp }} <span class="text-xs text-slate-400 font-normal">units</span></h4>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Cumulative CGPA</p>
                        <h4 class="text-xl font-bold text-purple-700">{{ number_format($overallCgpa, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5.00</span></h4>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Academic Standing</p>
                        @php
                            $standing = $academicStanding['standing'] ?? 'PROMOTED';
                            $badgeColor = match($standing) {
                                'PROMOTED', 'GOOD STANDING' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                'PROBATION' => 'bg-amber-100 text-amber-800 border-amber-300',
                                'REPEAT', 'WITHDRAW' => 'bg-rose-100 text-rose-800 border-rose-300',
                                'SPILLOVER' => 'bg-blue-100 text-blue-800 border-blue-300',
                                default => 'bg-gray-100 text-gray-800 border-gray-300',
                            };
                        @endphp
                        <span class="inline-block px-3 py-1 text-xs font-bold rounded-full border {{ $badgeColor }}">
                            {{ $standing }}
                        </span>
                        @if($classOfDegree !== 'N/A')
                            <p class="text-xs text-slate-500 mt-1 font-medium">{{ $classOfDegree }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grouped Results by Session & Semester --}}
    @if(empty($groupedResults))
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full max-w-full px-3 mx-auto">
                <div class="relative flex flex-col items-center justify-center min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border p-12 text-center">
                    <div class="w-16 h-16 mb-4 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h5 class="text-lg font-bold text-slate-700 mb-1">No Released Results Found</h5>
                    <p class="text-sm text-slate-500 max-w-md">
                        There are no published results available for your account yet. Results will appear here once officially approved and released by the Senate / Exam Office.
                    </p>
                </div>
            </div>
        </div>
    @else
        @foreach($groupedResults as $session => $semesters)
            @foreach($semesters as $semesterName => $data)
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full max-w-full px-3 mx-auto">
                        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border overflow-hidden">
                            {{-- Semester Header --}}
                            <div class="p-6 bg-slate-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h6 class="font-bold text-slate-800 uppercase tracking-wide text-sm mb-0">
                                        <i class="far fa-calendar-alt text-purple-600 mr-2"></i>
                                        {{ $session }} Academic Session &mdash;
                                        <span class="capitalize">{{ $semesterName }} Semester</span>
                                    </h6>
                                </div>
                                <div class="flex items-center gap-3 text-xs font-semibold text-slate-600">
                                    <span class="bg-white px-3 py-1 rounded-lg border border-gray-200 shadow-sm">
                                        GPA: <strong class="text-purple-700 font-bold">{{ number_format($data['gpa'], 2) }}</strong>
                                    </span>
                                    @if($data['cgpa'] !== null)
                                        <span class="bg-white px-3 py-1 rounded-lg border border-gray-200 shadow-sm">
                                            CGPA: <strong class="text-emerald-700 font-bold">{{ number_format($data['cgpa'], 2) }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Results Table --}}
                            <div class="flex-auto px-0 pt-0 pb-2">
                                <div class="p-0 overflow-x-auto">
                                    <table class="items-center w-full mb-0 align-top border-collapse text-slate-500">
                                        <thead class="align-bottom bg-gray-50/50">
                                            <tr>
                                                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">#</th>
                                                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Course Code</th>
                                                <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Course Title</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Units</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">CA (40)</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Exam (60)</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Total (100)</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Grade</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Grade Point</th>
                                                <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs text-slate-400">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['courses'] as $index => $res)
                                                @php
                                                    $code = $res->course_code_snapshot 
                                                        ?? $res->departmentCourse?->studentCourse?->code 
                                                        ?? $res->registeredCourse?->departmentCourse?->studentCourse?->code 
                                                        ?? 'N/A';
                                                    $title = $res->course_title_snapshot 
                                                        ?? $res->departmentCourse?->studentCourse?->title 
                                                        ?? $res->registeredCourse?->departmentCourse?->studentCourse?->title 
                                                        ?? 'N/A';
                                                    $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $res->departmentCourse?->units ?? 0);
                                                    $ca = $res->ca_score !== null ? number_format((float)$res->ca_score, 1) : '-';
                                                    $exam = $res->exam_score !== null ? number_format((float)$res->exam_score, 1) : '-';
                                                    $total = $res->total_score !== null ? number_format((float)$res->total_score, 1) : '-';
                                                    $grade = strtoupper((string) ($res->grade ?? 'F'));
                                                    $gp = (int) ($res->grade_point ?? 0);
                                                    $qp = $gp * $units;

                                                    $gradeBadge = match($grade) {
                                                        'A' => 'bg-emerald-50 text-emerald-700 border-emerald-200 font-bold',
                                                        'B' => 'bg-blue-50 text-blue-700 border-blue-200 font-bold',
                                                        'C' => 'bg-cyan-50 text-cyan-700 border-cyan-200 font-bold',
                                                        'D' => 'bg-amber-50 text-amber-700 border-amber-200 font-bold',
                                                        'E' => 'bg-orange-50 text-orange-700 border-orange-200 font-bold',
                                                        default => 'bg-rose-50 text-rose-700 border-rose-200 font-bold',
                                                    };

                                                    $isPass = $grade !== 'F';
                                                @endphp
                                                <tr class="hover:bg-gray-50/60 transition-colors border-b border-gray-100">
                                                    <td class="px-6 py-3 text-xs text-slate-400">{{ $index + 1 }}</td>
                                                    <td class="px-6 py-3 text-xs font-bold text-slate-700 uppercase">{{ $code }}</td>
                                                    <td class="px-6 py-3 text-xs text-slate-600 font-medium">{{ $title }}</td>
                                                    <td class="px-6 py-3 text-xs text-center font-semibold text-slate-700">{{ $units }}</td>
                                                    <td class="px-6 py-3 text-xs text-center text-slate-600">{{ $ca }}</td>
                                                    <td class="px-6 py-3 text-xs text-center text-slate-600">{{ $exam }}</td>
                                                    <td class="px-6 py-3 text-xs text-center font-bold text-slate-800">{{ $total }}</td>
                                                    <td class="px-6 py-3 text-xs text-center">
                                                        <span class="inline-block px-2.5 py-0.5 text-xs rounded-lg border {{ $gradeBadge }}">
                                                            {{ $grade }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-3 text-xs text-center font-semibold text-slate-700">{{ $gp }} ({{ $qp }} pts)</td>
                                                    <td class="px-6 py-3 text-xs text-center">
                                                        @if($isPass)
                                                            <span class="text-emerald-600 font-semibold text-xs"><i class="fas fa-check-circle mr-1"></i>Passed</span>
                                                        @else
                                                            <span class="text-rose-600 font-semibold text-xs"><i class="fas fa-times-circle mr-1"></i>Failed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        {{-- Semester Footer Summary --}}
                                        <tfoot class="bg-gray-50 font-bold text-xs text-slate-700">
                                            <tr>
                                                <td colspan="3" class="px-6 py-3 text-right uppercase text-slate-500">
                                                    Semester Summary:
                                                </td>
                                                <td class="px-6 py-3 text-center text-purple-700">
                                                    TCR: {{ $data['tcr'] }}
                                                </td>
                                                <td colspan="3" class="px-6 py-3 text-center text-emerald-700">
                                                    TCP: {{ $data['tcp'] }}
                                                </td>
                                                <td colspan="2" class="px-6 py-3 text-center text-slate-800">
                                                    TQP: {{ $data['tqp'] }}
                                                </td>
                                                <td class="px-6 py-3 text-center text-purple-800">
                                                    GPA: {{ number_format($data['gpa'], 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    @endif
</div>
