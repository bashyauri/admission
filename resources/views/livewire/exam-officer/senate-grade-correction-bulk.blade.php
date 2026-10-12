<div class="mx-auto w-full max-w-7xl px-3 py-4 sm:px-6">
    <header class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-fuchsia-700">Senate decisions</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800 dark:text-white">Bulk result correction</h1>
            <p class="mt-1 max-w-3xl text-sm text-slate-500">Apply one verified mark adjustment to a precisely selected course-of-study cohort. Each included result is reviewed below and receives its own audit record.</p>
        </div>
        @if(auth()->user()?->hasCapability('senate_grade_correction'))
            <a href="{{ route('senate-corrections') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Individual correction</a>
        @endif
    </header>

    <section class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4" aria-label="Governance notice">
        <p class="font-semibold text-amber-950">Official academic record change</p>
        <p class="mt-1 text-sm text-amber-900">Use only a decision verified against official Senate minutes or the decision register. Corrections update released results and recalculate academic records. The student result display shows the corrected result without a Senate-correction label.</p>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-2xl bg-white p-5 shadow-soft-xl dark:bg-gray-950 xl:col-span-4" aria-labelledby="cohort-filters-heading">
            <h2 id="cohort-filters-heading" class="text-lg font-bold text-slate-800 dark:text-white">1. Define the cohort</h2>
            <p class="mt-1 text-sm text-slate-500">Every filter is required so the same Senate ruling cannot reach a different session or course by mistake.</p>
            <div class="mt-4 space-y-3">
                <label class="block text-xs font-semibold text-slate-600">Course of study / Department *
                    <select wire:model.live="selectedDepartmentId" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm"><option value="">Select department</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select>
                </label>
                <label class="block text-xs font-semibold text-slate-600">Result level *
                    <select wire:model.live="selectedLevel" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm"><option value="">Select level</option>@foreach($levels as $level)<option value="{{ $level }}">{{ $level }} Level</option>@endforeach</select>
                </label>
                <label class="block text-xs font-semibold text-slate-600">Course offering *
                    <select wire:model.live="selectedDepartmentCourse" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm"><option value="">Select course</option>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->studentCourse?->code }} — {{ $course->studentCourse?->title }}</option>@endforeach</select>
                </label>
                <label class="block text-xs font-semibold text-slate-600">Academic session *
                    <select wire:model.live="selectedSession" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm"><option value="">Select session</option>@foreach($sessions as $session)<option value="{{ $session }}">{{ $session }}</option>@endforeach</select>
                </label>
                <label class="block text-xs font-semibold text-slate-600">Semester *
                    <select wire:model.live="selectedSemester" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm"><option value="">Select semester</option><option value="first">First</option><option value="second">Second</option></select>
                </label>
                <fieldset class="rounded-xl border border-slate-200 p-3">
                    <legend class="px-1 text-xs font-semibold text-slate-600">Apply adjustment to *</legend>
                    <label class="flex items-start gap-2 py-1 text-sm"><input type="radio" wire:model.live="cohortScope" value="all" class="mt-1"><span><strong>All released results</strong><span class="block text-xs text-slate-500">Includes passes and failures. Recommended when Senate decision covers the full class.</span></span></label>
                    <label class="flex items-start gap-2 py-1 text-sm"><input type="radio" wire:model.live="cohortScope" value="failed" class="mt-1"><span><strong>Failed results only</strong><span class="block text-xs text-slate-500">Only results below 40 marks.</span></span></label>
                </fieldset>
                <button type="button" wire:click="previewCohort" wire:loading.attr="disabled" wire:target="previewCohort" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-fuchsia-600 px-4 py-2 text-sm font-bold text-white hover:bg-fuchsia-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="previewCohort">Preview cohort</span><span wire:loading wire:target="previewCohort">Loading preview…</span>
                </button>
                @foreach(['selectedDepartmentId','selectedLevel','selectedDepartmentCourse','selectedSession','selectedSemester','cohortScope'] as $field)
                    @error($field)<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @endforeach
            </div>
            <p class="mt-3 text-xs text-slate-500">Maximum 250 results per operation. Larger cohorts must be divided by an approved filter such as level or semester.</p>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-soft-xl dark:bg-gray-950 xl:col-span-8" aria-labelledby="cohort-preview-heading">
            <h2 id="cohort-preview-heading" class="text-lg font-bold text-slate-800 dark:text-white">2. Review impact and Senate decision</h2>
            @if(!$cohortPreviewReady)
                <div class="mt-4 rounded-xl border border-dashed border-slate-300 p-8 text-center">
                    <p class="font-semibold text-slate-700">No cohort preview yet</p>
                    <p class="mt-1 text-sm text-slate-500">Complete all five cohort filters, then select <strong>Preview cohort</strong>.</p>
                </div>
            @else
                @php($invalidCount = $cohortPreview->filter(fn ($row) => (float) $row->total_score + (is_numeric($adjustmentAmount) ? (float) $adjustmentAmount : 0) > 100)->count())
                <div class="mt-4 flex flex-col gap-2 rounded-xl border border-fuchsia-200 bg-fuchsia-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div><p class="font-bold text-fuchsia-950">{{ $departments->firstWhere('id', (int) $selectedDepartmentId)?->name }} · {{ $selectedLevel }} Level</p><p class="text-sm text-fuchsia-900">{{ $courses->firstWhere('id', (int) $selectedDepartmentCourse)?->studentCourse?->code }} · {{ $selectedSession }} · {{ ucfirst($selectedSemester) }} Semester</p></div>
                    <div class="text-sm font-semibold text-fuchsia-950">{{ $cohortPreview->count() }} result(s) · {{ $cohortScope === 'all' ? 'all released' : 'failed only' }}</div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
                    <label class="text-xs font-semibold text-slate-600">Add marks to each result *
                        <input wire:model.live="adjustmentAmount" type="number" min="0.01" max="100" step="0.01" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm" placeholder="10">
                        <span class="mt-1 block font-normal text-slate-500">Example: 48 + 10 = 58. Original CA and exam components stay unchanged.</span>
                        @error('adjustmentAmount')<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700"><strong>Check the Senate ruling:</strong> Is this adjustment authorized for every listed student in this exact course, level, session, and semester? Results that would exceed 100 marks are highlighted and prevent submission.</div>
                </div>

                <div class="mt-4 max-h-[32rem] overflow-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-3">Student</th><th class="p-3">Current</th><th class="p-3">Revised</th><th class="p-3">Change</th></tr></thead>
                        <tbody>
                        @foreach($cohortPreview as $candidate)
                            @php($after = (float) $candidate->total_score + (is_numeric($adjustmentAmount) ? (float) $adjustmentAmount : 0))
                            @php($afterGrade = $after <= 100 ? app(\App\Services\GradeCalculationService::class)->getGradeAndPoint($after) : null)
                            <tr wire:key="cohort-result-{{ $candidate->id }}" class="border-t {{ $after > 100 ? 'bg-red-50' : '' }}">
                                <td class="p-3"><span class="block font-semibold">{{ $candidate->user?->full_name }}</span><span class="font-mono text-xs text-slate-500">{{ $candidate->academicDetail?->matric_no }}</span></td>
                                <td class="p-3 whitespace-nowrap">{{ number_format((float) $candidate->total_score, 2) }} · {{ $candidate->grade }}</td>
                                <td class="p-3 whitespace-nowrap font-semibold {{ $after > 100 ? 'text-red-700' : 'text-slate-800' }}">{{ number_format($after, 2) }} · {{ $afterGrade['grade'] ?? 'Exceeds 100' }}</td>
                                <td class="p-3 whitespace-nowrap">+{{ is_numeric($adjustmentAmount) ? number_format((float) $adjustmentAmount, 2) : '0.00' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($invalidCount > 0)
                    <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm font-semibold text-red-800" role="alert">{{ $invalidCount }} result(s) would exceed 100. Reduce the adjustment or revise the cohort filters before applying.</p>
                @endif

                <form wire:submit="applyCohortCorrection" class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                    <h3 class="font-bold text-slate-800">3. Verify and apply the Senate decision</h3>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-xs font-semibold text-slate-600">Senate reference *<input wire:model="senateReference" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm" placeholder="SEN-2026-104"></label>
                        <label class="text-xs font-semibold text-slate-600">Decision date *<input wire:model="senateDecisionDate" type="date" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm"></label>
                        <label class="text-xs font-semibold text-slate-600 sm:col-span-2">Minutes / decision reference *<input wire:model="senateMinutesReference" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm" placeholder="Meeting date, minute number, or resolution item"></label>
                        <label class="text-xs font-semibold text-slate-600 sm:col-span-2">How was this decision verified? *<input wire:model="verificationMethod" class="mt-1 block h-11 w-full rounded-lg border-slate-200 text-sm" placeholder="Checked against the official Senate minutes..."></label>
                    </div>
                    <label class="block text-xs font-semibold text-slate-600">Reason for this correction *<textarea wire:model="reason" rows="3" class="mt-1 block w-full rounded-lg border-slate-200 text-sm" placeholder="Describe the Senate-authorized correction and the affected examination issue."></textarea></label>
                    <label class="block text-xs font-semibold text-slate-600">Senate decision evidence *<input wire:model="evidence" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full rounded-lg border border-slate-200 p-2 text-sm"><span class="mt-1 block font-normal text-slate-500">PDF or image, maximum 10 MB. Stored privately.</span></label>
                    <label class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950"><input wire:model="decisionVerified" type="checkbox" class="mt-1 rounded"><span>I verified this decision against the official Senate minutes or decision register. This records me as the verifier.</span></label>
                    @foreach(['senateReference','senateDecisionDate','senateMinutesReference','verificationMethod','reason','evidence','decisionVerified'] as $field)
                        @error($field)<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @endforeach
                    <button type="submit" wire:loading.attr="disabled" wire:target="applyCohortCorrection,evidence" @disabled($invalidCount > 0 || !is_numeric($adjustmentAmount) || (float) $adjustmentAmount <= 0) wire:confirm="Apply Senate decision {{ $senateReference }}: add {{ $adjustmentAmount ?: '0' }} marks to {{ $cohortPreview->count() }} {{ $cohortScope === 'all' ? 'released' : 'failed' }} results for {{ $selectedLevel }} Level {{ $selectedSession }} {{ $selectedSemester }}? The operation is all-or-nothing." class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-fuchsia-600 px-4 py-3 text-sm font-bold text-white hover:bg-fuchsia-700 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="applyCohortCorrection">Apply to {{ $cohortPreview->count() }} results</span><span wire:loading wire:target="applyCohortCorrection">Applying correction to cohort…</span>
                    </button>
                </form>
            @endif
        </section>
    </div>
</div>
