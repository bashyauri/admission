<div class="mx-auto w-full max-w-7xl px-3 py-4 sm:px-6">
    <header class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
        <div><h1 class="text-2xl font-bold text-slate-800 dark:text-white">Senate Grade Correction</h1>
        <p class="mt-1 text-sm text-slate-500">Search and review one released undergraduate result for an individual Senate-authorized correction.</p></div>
        @if(auth()->user()?->hasCapability('senate_bulk_grade_correction'))
            <a href="{{ route('senate-corrections.bulk') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-fuchsia-600 px-4 py-2 text-sm font-semibold text-white hover:bg-fuchsia-700">Bulk correction</a>
        @endif
    </header>

    <section class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4" role="note">
        <h2 class="text-sm font-bold text-amber-900">Official result change</h2>
        <p class="mt-1 text-sm text-amber-800">Attach the Senate decision evidence and verify it against the official minutes or decision register. Each correction updates the released result and recalculates the student's academic record.</p>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="xl:col-span-7 rounded-2xl bg-white p-5 shadow-soft-xl dark:bg-gray-950" aria-labelledby="find-result-heading">
            <h2 id="find-result-heading" class="text-lg font-bold text-slate-800 dark:text-white">Find a released result</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <label class="text-xs font-semibold text-slate-600">Course of study / Department
                    <select wire:model.live="selectedDepartmentId" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm">
                        <option value="">All departments in my scope</option>
                        @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Academic session
                    <select wire:model.live="selectedSession" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm">
                        <option value="">All sessions</option>
                        @foreach($sessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Semester
                    <select wire:model.live="selectedSemester" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm">
                        <option value="">All semesters</option><option value="first">First</option><option value="second">Second</option>
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Result level
                    <select wire:model.live="selectedLevel" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm">
                        <option value="">All levels</option>
                        @foreach($levels as $level)<option value="{{ $level }}">{{ $level }} Level</option>@endforeach
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Course offering
                    <select wire:model.live="selectedDepartmentCourse" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm">
                        <option value="">All courses</option>
                        @foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->studentCourse?->code }} — {{ $course->studentCourse?->title }}</option>@endforeach
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Matric number
                    <input wire:model.live.debounce.400ms="matricSearch" type="search" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm" placeholder="Search student">
                </label>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-3">Student</th><th class="p-3">Course</th><th class="p-3">Session / Semester</th><th class="p-3">Result</th><th class="p-3"><span class="sr-only">Action</span></th></tr></thead>
                    <tbody>
                        @forelse($results as $result)
                            <tr wire:key="released-result-{{ $result->id }}" class="border-t border-slate-100 {{ $selectedResultId === $result->id ? 'bg-fuchsia-50' : '' }}">
                                <td class="p-3"><span class="block font-semibold text-slate-800">{{ $result->user?->full_name }}</span><span class="font-mono text-xs text-slate-500">{{ $result->academicDetail?->matric_no }}</span></td>
                                <td class="p-3"><span class="font-semibold">{{ $result->course_code_snapshot ?: $result->departmentCourse?->studentCourse?->code }}</span><span class="block max-w-48 truncate text-xs text-slate-500">{{ $result->course_title_snapshot ?: $result->departmentCourse?->studentCourse?->title }}</span></td>
                                <td class="p-3 whitespace-nowrap">{{ $result->academic_session }}<span class="block text-xs text-slate-500">{{ ucfirst($result->semester) }}</span></td>
                                <td class="p-3 whitespace-nowrap">{{ number_format((float) $result->total_score, 2) }} · {{ $result->grade }}</td>
                                <td class="p-3"><button type="button" wire:click="selectResult({{ $result->id }})" class="rounded-lg bg-fuchsia-600 px-3 py-2 text-xs font-semibold text-white hover:bg-fuchsia-700 focus:outline-none focus:ring-2 focus:ring-fuchsia-400">Review</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-sm text-slate-500">No released undergraduate results match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $results->links() }}</div>
        </section>

        <section class="xl:col-span-5 rounded-2xl bg-white p-5 shadow-soft-xl dark:bg-gray-950" aria-labelledby="correction-heading">
            <h2 id="correction-heading" class="text-lg font-bold text-slate-800 dark:text-white">Correction details</h2>
            @if(!$selectedResult)
                <div class="mt-4 rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">Choose one released result to inspect its marks and enter the Senate decision.</div>
            @else
                <div class="mt-4 rounded-xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-800">{{ $selectedResult->user?->full_name }} <span class="font-mono text-xs text-slate-500">{{ $selectedResult->academicDetail?->matric_no }}</span></p>
                    <p class="mt-1 text-sm text-slate-600">{{ $selectedResult->course_code_snapshot ?: $selectedResult->departmentCourse?->studentCourse?->code }} — {{ $selectedResult->course_title_snapshot ?: $selectedResult->departmentCourse?->studentCourse?->title }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $selectedResult->academic_session }} · {{ ucfirst($selectedResult->semester) }} Semester</p>
                    <div class="mt-3 grid grid-cols-4 gap-2 text-center text-xs">
                        <div class="rounded-lg bg-white p-2"><span class="block text-slate-500">CA</span><strong>{{ number_format((float) $selectedResult->ca_score, 2) }}</strong></div>
                        <div class="rounded-lg bg-white p-2"><span class="block text-slate-500">Exam</span><strong>{{ number_format((float) $selectedResult->exam_score, 2) }}</strong></div>
                        <div class="rounded-lg bg-white p-2"><span class="block text-slate-500">Total</span><strong>{{ number_format((float) $selectedResult->total_score, 2) }}</strong></div>
                        <div class="rounded-lg bg-white p-2"><span class="block text-slate-500">Grade</span><strong>{{ $selectedResult->grade }} · {{ $selectedResult->grade_point }}</strong></div>
                    </div>
                </div>

                <form wire:submit="applyCorrection" class="mt-4 space-y-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-xs font-semibold text-slate-600">Senate reference *<input wire:model="senateReference" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm" placeholder="SEN-2026-104"></label>
                        <label class="text-xs font-semibold text-slate-600">Decision date *<input wire:model="senateDecisionDate" type="date" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm"></label>
                    </div>
                    @error('senateReference')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('senateDecisionDate')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="block text-xs font-semibold text-slate-600">Senate minutes / decision reference *<input wire:model="senateMinutesReference" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm" placeholder="Meeting date, minute number, or resolution item"></label>
                    @error('senateMinutesReference')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="block text-xs font-semibold text-slate-600">How did you verify this decision? *<input wire:model="verificationMethod" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm" placeholder="Checked against Senate minute ..."></label>
                    @error('verificationMethod')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                    <fieldset>
                        <legend class="mb-1 text-xs font-semibold text-slate-600">Correction method *</legend>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <label class="inline-flex items-center gap-2"><input type="radio" value="total_mark_adjustment" wire:model.live="correctionType"> Adjust total mark</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" value="component_correction" wire:model.live="correctionType"> Correct CA / exam</label>
                        </div>
                    </fieldset>

                    @if($correctionType === 'total_mark_adjustment')
                        <label class="block text-xs font-semibold text-slate-600">Senate-authorized total adjustment *<input wire:model.live="adjustmentAmount" type="number" step="0.01" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm" placeholder="2"></label>
                        <p class="text-xs text-slate-500">Example: 48 + 2 = 50. CA and exam remain as originally recorded; the total adjustment is shown separately in the audit and student result.</p>
                        @error('adjustmentAmount')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @else
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="text-xs font-semibold text-slate-600">Corrected CA *<input wire:model.live="correctedCa" type="number" min="0" max="100" step="0.01" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm"></label>
                            <label class="text-xs font-semibold text-slate-600">Corrected exam *<input wire:model.live="correctedExam" type="number" min="0" max="100" step="0.01" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm"></label>
                        </div>
                        @error('correctedCa')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('correctedExam')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif

                    <div class="rounded-xl border border-fuchsia-200 bg-fuchsia-50 p-3 text-sm text-fuchsia-950">
                        <strong>Preview:</strong> {{ $selectedResult->total_score }} → {{ $previewTotal ?? 'Enter correction' }}
                        @if($previewTotal !== null && $previewTotal >= 0 && $previewTotal <= 100)
                            <span> · {{ $previewGrade['grade'] }} ({{ $previewGrade['grade_point'] }} points)</span>
                        @endif
                    </div>

                    <label class="block text-xs font-semibold text-slate-600">Reason for correction *<textarea wire:model="reason" rows="3" class="mt-1 block w-full rounded-lg border-slate-200 text-sm" placeholder="Describe the Senate-authorized correction and affected examination issue."></textarea></label>
                    @error('reason')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="block text-xs font-semibold text-slate-600">Attach Senate decision / minutes extract *<input wire:model="evidence" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full rounded-lg border border-slate-200 p-2 text-sm"></label>
                    <p class="text-xs text-slate-500">PDF or image, maximum 10 MB. Stored privately and available only to authorized Senate correction staff.</p>
                    @error('evidence')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950"><input wire:model="decisionVerified" type="checkbox" class="mt-0.5 rounded border-amber-400"><span>I verified the attached decision against the official Senate minutes or decision register. This action records me as the verifier.</span></label>
                    @error('decisionVerified')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                    <button type="submit" wire:loading.attr="disabled" wire:target="applyCorrection,evidence" wire:confirm="Apply Senate decision {{ $senateReference }} to {{ $selectedResult->academicDetail?->matric_no }} for {{ $selectedResult->course_code_snapshot ?: $selectedResult->departmentCourse?->studentCourse?->code }}? Original: {{ $selectedResult->total_score }} ({{ $selectedResult->grade }}). Revised total: {{ $previewTotal ?? 'not entered' }} ({{ $previewGrade['grade'] ?? 'not calculated' }}). The released result and academic records will be updated." class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-fuchsia-600 px-4 py-2 text-sm font-bold text-white hover:bg-fuchsia-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="applyCorrection">Apply Senate Correction</span>
                        <span wire:loading wire:target="applyCorrection">Applying correction…</span>
                    </button>
                </form>

                @if($selectedResult->senateGradeCorrections->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <h3 class="font-bold text-slate-800">Correction history for this result</h3>
                        @foreach($selectedResult->senateGradeCorrections as $correction)
                            <article class="mt-3 rounded-xl border border-slate-200 p-3 text-xs">
                                <p class="font-semibold">{{ $correction->senate_reference }} · {{ $correction->senate_decision_date?->format('d M Y') }}</p>
                                <p class="mt-1">{{ $correction->original_total }} ({{ $correction->original_grade }}) → {{ $correction->corrected_total }} ({{ $correction->corrected_grade }}) · {{ $correction->adjustment_amount >= 0 ? '+' : '' }}{{ $correction->adjustment_amount }}</p>
                                <p class="mt-1 text-slate-600">{{ $correction->reason }}</p>
                                <p class="mt-1 text-slate-500">Verified by {{ $correction->verifier?->surname }} {{ $correction->verifier?->firstname }}; applied by {{ $correction->implementer?->surname }} {{ $correction->implementer?->firstname }} on {{ $correction->implemented_at?->format('d M Y H:i') }}</p>
                                <button type="button" wire:click="downloadEvidence({{ $correction->id }})" class="mt-2 font-semibold text-fuchsia-700 underline">Download Senate evidence</button>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endif
        </section>
    </div>

    <section class="mt-6 rounded-2xl bg-white p-5 shadow-soft-xl dark:bg-gray-950" aria-labelledby="correction-ledger-heading">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div><h2 id="correction-ledger-heading" class="text-lg font-bold text-slate-800 dark:text-white">Senate correction ledger</h2><p class="text-sm text-slate-500">Search applied corrections by Senate reference, student, or course.</p></div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <label class="text-xs font-semibold text-slate-600">Session
                    <select wire:model.live="ledgerSession" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm sm:w-36"><option value="">All sessions</option>@foreach($ledgerSessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach</select>
                </label>
                <label class="text-xs font-semibold text-slate-600">Search ledger<input wire:model.live.debounce.400ms="ledgerSearch" type="search" class="mt-1 block h-10 w-full rounded-lg border-slate-200 text-sm sm:w-72" placeholder="Reference, matric, course, officer"></label>
            </div>
        </div>
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-3">Implemented</th><th class="p-3">Senate reference</th><th class="p-3">Student / course</th><th class="p-3">Original</th><th class="p-3">Adjustment</th><th class="p-3">Corrected</th><th class="p-3">Officer</th></tr></thead>
                <tbody>
                    @forelse($ledger as $entry)
                        <tr class="border-t border-slate-100">
                            <td class="p-3 whitespace-nowrap">{{ $entry->implemented_at?->format('d M Y H:i') }}</td>
                            <td class="p-3 font-mono text-xs">{{ $entry->senate_reference }}</td>
                            <td class="p-3"><span class="block font-medium">{{ $entry->result?->academicDetail?->matric_no }} · {{ $entry->result?->user?->full_name }}</span><span class="text-xs text-slate-500">{{ $entry->result?->course_code_snapshot ?: $entry->result?->departmentCourse?->studentCourse?->code }} · {{ $entry->result?->academic_session }} {{ ucfirst($entry->result?->semester ?? '') }}</span></td>
                            <td class="p-3">{{ $entry->original_total }} · {{ $entry->original_grade }}</td>
                            <td class="p-3">{{ $entry->adjustment_amount >= 0 ? '+' : '' }}{{ $entry->adjustment_amount }}</td>
                            <td class="p-3 font-semibold">{{ $entry->corrected_total }} · {{ $entry->corrected_grade }}</td>
                            <td class="p-3">{{ $entry->implementer?->surname }} {{ $entry->implementer?->firstname }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-slate-500">No Senate grade corrections recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $ledger->links() }}</div>
    </section>
</div>
