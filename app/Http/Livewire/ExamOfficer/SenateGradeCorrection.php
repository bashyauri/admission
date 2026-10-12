<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Enums\ProgrammesEnum;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Result;
use App\Models\SenateGradeCorrection as CorrectionRecord;
use App\Models\User;
use App\Services\SenateGradeCorrectionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SenateGradeCorrection extends Component
{
    use LivewireAlert;
    use WithFileUploads;
    use WithPagination;

    public string $matricSearch = '';
    public string $selectedDepartmentId = '';
    public string $selectedSession = '';
    public string $selectedSemester = '';
    public string $selectedLevel = '';
    public string $selectedDepartmentCourse = '';
    public string $ledgerSearch = '';
    public string $ledgerSession = '';
    public ?int $selectedResultId = null;
    public bool $cohortPreviewReady = false;
    public bool $bulkOnly = false;
    public string $cohortScope = 'all';
    /** @var array<int, int> */
    public array $cohortResultIds = [];

    public string $senateReference = '';
    public string $senateDecisionDate = '';
    public string $senateMinutesReference = '';
    public string $verificationMethod = '';
    public bool $decisionVerified = false;
    public string $correctionType = 'total_mark_adjustment';
    public string $adjustmentAmount = '';
    public string $correctedCa = '';
    public string $correctedExam = '';
    public string $reason = '';
    public $evidence;

    /** @var array<int, int> */
    private array $departmentScopeIds = [];
    private bool $hasGlobalCapability = false;

    public function boot(): void
    {
        $requiredCapability = $this->requiredCapability();
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);
        $capabilities = $actor->capabilities()
            ->where('capability', $requiredCapability)
            ->where('is_active', true)
            ->get(['department_id']);
        abort_if($capabilities->isEmpty(), 403);
        $this->hasGlobalCapability = $capabilities->contains(fn ($capability) => $capability->department_id === null);
        $this->departmentScopeIds = $capabilities->pluck('department_id')->filter()->map(fn ($id) => (int) $id)->all();
    }

    public function mount(): void
    {
        $this->bulkOnly = $this->requiredCapability() === SenateGradeCorrectionService::BULK_CAPABILITY;
        $this->selectedSession = (string) Result::query()
            ->where('status', 'released')
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->when(!$this->hasGlobalCapability, fn ($query) => $this->applyDepartmentScope($query))
            ->orderByDesc('academic_session')
            ->value('academic_session');
    }

    public function updatedMatricSearch(): void { $this->clearCohortPreview(); $this->resetPage(); }
    public function updatedSelectedDepartmentId(): void
    {
        $this->clearCohortPreview();
        $this->selectedResultId = null;
        $this->selectedDepartmentCourse = '';
        $this->resetCorrectionForm();
        $this->resetPage();
    }
    public function updatedSelectedSession(): void { $this->clearCohortPreview(); $this->resetPage(); }
    public function updatedSelectedSemester(): void { $this->clearCohortPreview(); $this->resetPage(); }
    public function updatedSelectedLevel(): void
    {
        $this->clearCohortPreview();
        $this->selectedResultId = null;
        $this->resetCorrectionForm();
        $this->resetPage();
    }
    public function updatedSelectedDepartmentCourse(): void { $this->clearCohortPreview(); $this->resetPage(); }
    public function updatedCohortScope(): void { $this->clearCohortPreview(); }
    public function updatedLedgerSearch(): void { $this->resetPage('ledgerPage'); }
    public function updatedLedgerSession(): void { $this->resetPage('ledgerPage'); }

    public function selectResult(int $resultId): void
    {
        $result = $this->accessibleResults()->whereKey($resultId)->firstOrFail();
        $this->selectedResultId = $result->id;
        $this->resetCorrectionForm();
        $this->correctedCa = (string) $result->ca_score;
        $this->correctedExam = (string) $result->exam_score;
    }

    public function applyCorrection(SenateGradeCorrectionService $service): void
    {
        $this->validate([
            'selectedResultId' => ['required', 'integer'],
            'senateReference' => ['required', 'string', 'max:100'],
            'senateDecisionDate' => ['required', 'date'],
            'senateMinutesReference' => ['required', 'string', 'max:255'],
            'verificationMethod' => ['required', 'string', 'min:10', 'max:255'],
            'decisionVerified' => ['accepted'],
            'correctionType' => ['required', Rule::in(['total_mark_adjustment', 'component_correction'])],
            'adjustmentAmount' => [Rule::requiredIf($this->correctionType === 'total_mark_adjustment'), 'nullable', 'numeric'],
            'correctedCa' => [Rule::requiredIf($this->correctionType === 'component_correction'), 'nullable', 'numeric', 'between:0,100'],
            'correctedExam' => [Rule::requiredIf($this->correctionType === 'component_correction'), 'nullable', 'numeric', 'between:0,100'],
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
            'evidence' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'decisionVerified.accepted' => 'Confirm that you checked the decision against the official Senate minutes or decision register.',
            'evidence.required' => 'Attach the Senate decision or minutes extract as supporting evidence.',
        ]);

        $this->accessibleResults()->whereKey($this->selectedResultId)->firstOrFail();

        try {
            $correction = $service->apply([
                'result_id' => $this->selectedResultId,
                'senate_reference' => $this->senateReference,
                'senate_decision_date' => $this->senateDecisionDate,
                'senate_minutes_reference' => $this->senateMinutesReference,
                'verification_method' => $this->verificationMethod,
                'decision_verified' => $this->decisionVerified,
                'correction_type' => $this->correctionType,
                'adjustment_amount' => $this->adjustmentAmount,
                'corrected_ca' => $this->correctedCa,
                'corrected_exam' => $this->correctedExam,
                'reason' => $this->reason,
            ], auth()->user(), $this->evidence);

            $this->alert('success', "Senate correction applied. The corrected result is {$correction->corrected_total} ({$correction->corrected_grade}).", ['position' => 'top-end', 'timer' => 5000, 'toast' => true]);
            $this->resetCorrectionForm();
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            throw $exception;
        } catch (\InvalidArgumentException $exception) {
            $this->alert('error', $exception->getMessage(), ['position' => 'top-end', 'timer' => 6000, 'toast' => true]);
        } catch (Throwable $exception) {
            report($exception);
            $this->alert('error', 'The correction could not be applied. No correction was committed; review the result and try again or contact support.', ['position' => 'top-end', 'timer' => 6000, 'toast' => true]);
        }
    }

    public function previewCohort(): void
    {
        abort_unless($this->requiredCapability() === SenateGradeCorrectionService::BULK_CAPABILITY, 403);
        $this->validate([
            'selectedDepartmentId' => ['required', 'integer'],
            'selectedSession' => ['required', 'string'],
            'selectedSemester' => ['required', Rule::in(['first', 'second'])],
            'selectedLevel' => ['required', 'integer'],
            'selectedDepartmentCourse' => ['required', 'integer'],
            'cohortScope' => ['required', Rule::in(['all', 'failed'])],
        ]);
        if (trim($this->matricSearch) !== '') {
            $this->addError('matricSearch', 'Clear the student search to preview the full selected cohort.');
            return;
        }

        $this->cohortResultIds = $this->cohortQuery()->orderBy('results.id')->limit(251)->pluck('results.id')->map(fn ($id) => (int) $id)->all();
        if (count($this->cohortResultIds) > 250) {
            $this->clearCohortPreview();
            $this->addError('selectedDepartmentCourse', 'This cohort has more than 250 failures. Narrow the filters and preview smaller cohorts.');
            return;
        }
        if ($this->cohortResultIds === []) {
            $this->clearCohortPreview();
            $this->addError('selectedDepartmentCourse', 'No failed results match this department, level, course, session, and semester.');
            return;
        }
        $this->cohortPreviewReady = true;
        $this->selectedResultId = null;
        $this->resetValidation();
    }

    public function applyCohortCorrection(SenateGradeCorrectionService $service): void
    {
        abort_unless($this->requiredCapability() === SenateGradeCorrectionService::BULK_CAPABILITY, 403);
        if (!$this->cohortPreviewReady || $this->cohortResultIds === []) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Preview the cohort before applying a correction.');
        }
        $this->validate([
            'senateReference' => ['required', 'string', 'max:100'],
            'senateDecisionDate' => ['required', 'date'],
            'senateMinutesReference' => ['required', 'string', 'max:255'],
            'verificationMethod' => ['required', 'string', 'min:10', 'max:255'],
            'decisionVerified' => ['accepted'],
            'adjustmentAmount' => ['required', 'numeric', 'gt:0', 'max:100'],
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
            'evidence' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        // Rebuild from the server-side filters and refuse stale or tampered previews.
        $currentIds = $this->cohortQuery()->orderBy('results.id')->limit(251)->pluck('results.id')->map(fn ($id) => (int) $id)->all();
        if ($currentIds !== $this->cohortResultIds) {
            $this->clearCohortPreview();
            $this->addError('selectedDepartmentCourse', 'The cohort changed since preview. Review the updated results and preview again.');
            return;
        }

        try {
            $count = $service->applyBatch([
                'result_ids' => $this->cohortResultIds,
                'department_id' => $this->selectedDepartmentId,
                'level' => $this->selectedLevel,
                'department_course_id' => $this->selectedDepartmentCourse,
                'academic_session' => $this->selectedSession,
                'semester' => $this->selectedSemester,
                'failure_scope' => $this->cohortScope,
                'senate_reference' => $this->senateReference,
                'senate_decision_date' => $this->senateDecisionDate,
                'senate_minutes_reference' => $this->senateMinutesReference,
                'verification_method' => $this->verificationMethod,
                'decision_verified' => $this->decisionVerified,
                'correction_type' => 'total_mark_adjustment',
                'adjustment_amount' => $this->adjustmentAmount,
                'reason' => $this->reason,
            ], auth()->user(), $this->evidence);
            $this->alert('success', "Senate correction applied to {$count} failed results.", ['position' => 'top-end', 'timer' => 6000, 'toast' => true]);
            $this->clearCohortPreview();
            $this->resetCorrectionForm();
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            throw $exception;
        } catch (\InvalidArgumentException $exception) {
            $this->alert('error', $exception->getMessage(), ['position' => 'top-end', 'timer' => 6000, 'toast' => true]);
        } catch (Throwable $exception) {
            report($exception);
            $this->alert('error', 'The cohort correction was not committed. Review the cohort and try again or contact support.', ['position' => 'top-end', 'timer' => 6000, 'toast' => true]);
        }
    }

    private function clearCohortPreview(): void
    {
        $this->cohortPreviewReady = false;
        $this->cohortResultIds = [];
    }

    protected function requiredCapability(): string
    {
        return SenateGradeCorrectionService::CAPABILITY;
    }

    private function cohortQuery(): Builder
    {
        if ($this->selectedDepartmentId === '' || $this->selectedSession === '' || $this->selectedSemester === '' || $this->selectedLevel === '' || $this->selectedDepartmentCourse === '') {
            return $this->accessibleResults()->whereRaw('1 = 0');
        }
        return $this->accessibleResults()
            ->where('academic_detail_id', '>', 0)
            ->whereHas('academicDetail', fn (Builder $query) => $query->where('department_id', (int) $this->selectedDepartmentId))
            ->where('academic_session', $this->selectedSession)
            ->where('semester', $this->selectedSemester)
            ->where('department_course_id', (int) $this->selectedDepartmentCourse)
            ->where(function (Builder $query): void {
                $query->where('results.level_snapshot', (int) $this->selectedLevel)
                    ->orWhere(function (Builder $legacy): void {
                        $legacy->whereNull('results.level_snapshot')->whereHas('registeredCourse', fn (Builder $registration) => $registration->where('level_snapshot', (int) $this->selectedLevel));
                    });
            })
            ->when($this->cohortScope === 'failed', fn (Builder $query) => $query->where('total_score', '<', 40))
            ->whereNotNull('ca_score')->whereNotNull('exam_score')->whereNotNull('total_score');
    }

    public function downloadEvidence(int $correctionId): StreamedResponse
    {
        $correction = $this->accessibleCorrections()->findOrFail($correctionId);
        $filename = 'senate-decision-' . $correction->id . '.' . pathinfo($correction->evidence_path, PATHINFO_EXTENSION);
        return Storage::disk('local')->download($correction->evidence_path, $filename);
    }

    private function resetCorrectionForm(): void
    {
        $this->reset([
            'senateReference', 'senateDecisionDate', 'senateMinutesReference', 'verificationMethod',
            'decisionVerified', 'correctionType', 'adjustmentAmount', 'reason', 'evidence',
        ]);
        $this->correctionType = 'total_mark_adjustment';
        $this->resetValidation();
    }

    private function applyDepartmentScope(Builder $query): Builder
    {
        return $query->whereHas('academicDetail', fn ($academicDetail) => $academicDetail->whereIn('department_id', $this->departmentScopeIds));
    }

    private function accessibleResults(): Builder
    {
        return Result::query()
            ->where('status', 'released')
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->when($this->selectedDepartmentId !== '', fn (Builder $query) => $query->whereHas(
                'academicDetail',
                fn (Builder $academicDetail) => $academicDetail->where('department_id', (int) $this->selectedDepartmentId),
            ))
            ->when($this->selectedLevel !== '', fn (Builder $query) => $query->where(function (Builder $levelQuery): void {
                $levelQuery->where('results.level_snapshot', (int) $this->selectedLevel)
                    ->orWhere(function (Builder $legacyQuery): void {
                        $legacyQuery->whereNull('results.level_snapshot')
                            ->whereHas('registeredCourse', fn (Builder $registration) => $registration->where('level_snapshot', (int) $this->selectedLevel));
                    });
            }))
            ->when(!$this->hasGlobalCapability, fn (Builder $query) => $this->applyDepartmentScope($query));
    }

    private function accessibleCorrections(): Builder
    {
        $query = CorrectionRecord::query();
        if (!$this->hasGlobalCapability) {
            $query->whereHas('result.academicDetail', fn ($academicDetail) => $academicDetail->whereIn('department_id', $this->departmentScopeIds));
        }
        return $query;
    }

    public function render()
    {
        $accessibleCourseIds = $this->accessibleResults()
            ->whereNotNull('department_course_id')
            ->select('department_course_id')
            ->distinct();

        $courses = DepartmentCourse::query()
            ->with('studentCourse:id,code,title')
            ->whereIn('id', $accessibleCourseIds)
            ->orderBy('student_course_id')
            ->get();

        $departments = Department::query()
            ->when(!$this->hasGlobalCapability, fn (Builder $query) => $query->whereIn('id', $this->departmentScopeIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        $levels = $this->accessibleResults()
            ->leftJoin('registered_courses', 'registered_courses.id', '=', 'results.registered_course_id')
            ->selectRaw('COALESCE(results.level_snapshot, registered_courses.level_snapshot) as result_level')
            ->distinct()
            ->orderBy('result_level')
            ->pluck('result_level')
            ->filter(fn ($level) => $level !== null)
            ->values();

        $sessions = $this->accessibleResults()->select('academic_session')->distinct()->orderByDesc('academic_session')->pluck('academic_session');
        if ($this->bulkOnly) {
            $cohortPreview = $this->cohortPreviewReady
                ? $this->accessibleResults()->with(['user:id,surname,firstname,m_name', 'academicDetail:id,user_id,matric_no', 'departmentCourse.studentCourse'])
                    ->whereIn('results.id', $this->cohortResultIds)->orderBy('results.id')->get()
                : collect();
            return view('livewire.exam-officer.senate-grade-correction-bulk', compact('departments', 'levels', 'courses', 'sessions', 'cohortPreview'));
        }

        $results = $this->accessibleResults()
            ->with(['user:id,surname,firstname,m_name', 'academicDetail:id,user_id,matric_no,department_id', 'departmentCourse.studentCourse'])
            ->when($this->selectedSession !== '', fn ($query) => $query->where('academic_session', $this->selectedSession))
            ->when($this->selectedSemester !== '', fn ($query) => $query->where('semester', $this->selectedSemester))
            ->when($this->selectedDepartmentCourse !== '', fn ($query) => $query->where('department_course_id', $this->selectedDepartmentCourse))
            ->when(trim($this->matricSearch) !== '', fn ($query) => $query->whereHas('academicDetail', fn ($detail) => $detail->where('matric_no', 'like', '%' . trim($this->matricSearch) . '%')))
            ->orderBy('academic_session', 'desc')->orderBy('semester')->orderBy('department_course_id')->orderBy('user_id')
            ->paginate(15);

        $selectedResult = $this->selectedResultId
            ? $this->accessibleResults()->with([
                'user:id,surname,firstname,m_name',
                'academicDetail:id,user_id,matric_no,department_id',
                'departmentCourse.studentCourse',
                'senateGradeCorrections.implementer:id,surname,firstname',
                'senateGradeCorrections.verifier:id,surname,firstname',
            ])->find($this->selectedResultId)
            : null;

        $ledger = $this->accessibleCorrections()
            ->with(['result.user:id,surname,firstname,m_name', 'result.academicDetail:id,user_id,matric_no', 'result.departmentCourse.studentCourse:id,code,title', 'implementer:id,surname,firstname'])
            ->when($this->selectedDepartmentId !== '', fn ($query) => $query->whereHas('result.academicDetail', fn (Builder $academicDetail) => $academicDetail->where('department_id', (int) $this->selectedDepartmentId)))
            ->when($this->ledgerSession !== '', fn ($query) => $query->whereHas('result', fn ($result) => $result->where('academic_session', $this->ledgerSession)))
            ->when($this->selectedDepartmentCourse !== '', fn ($query) => $query->whereHas('result', fn ($result) => $result->where('department_course_id', $this->selectedDepartmentCourse)))
            ->when(trim($this->ledgerSearch) !== '', function ($query): void {
                $search = trim($this->ledgerSearch);
                $query->where(function ($filter) use ($search): void {
                    $filter->where('senate_reference', 'like', "%{$search}%")
                        ->orWhereHas('result.academicDetail', fn ($detail) => $detail->where('matric_no', 'like', "%{$search}%"))
                        ->orWhereHas('result.departmentCourse.studentCourse', fn ($course) => $course->where('code', 'like', "%{$search}%"))
                        ->orWhereHas('implementer', fn ($officer) => $officer->where('surname', 'like', "%{$search}%")->orWhere('firstname', 'like', "%{$search}%"));
                });
            })
            ->latest('implemented_at')->paginate(10, ['*'], 'ledgerPage');

        $previewTotal = null;
        if ($selectedResult) {
            $previewTotal = $this->correctionType === 'component_correction'
                ? (is_numeric($this->correctedCa) && is_numeric($this->correctedExam) ? round((float) $this->correctedCa + (float) $this->correctedExam, 2) : null)
                : (is_numeric($this->adjustmentAmount) ? round((float) $selectedResult->total_score + (float) $this->adjustmentAmount, 2) : (float) $selectedResult->total_score);
        }

        $previewGrade = $previewTotal !== null && $previewTotal >= 0 && $previewTotal <= 100
            ? app(\App\Services\GradeCalculationService::class)->getGradeAndPoint((float) $previewTotal)
            : null;
        $ledgerSessions = Result::query()
            ->whereIn('id', $this->accessibleCorrections()->select('result_id'))
            ->select('academic_session')->distinct()->orderByDesc('academic_session')->pluck('academic_session');

        return view('livewire.exam-officer.senate-grade-correction', compact('departments', 'levels', 'courses', 'sessions', 'results', 'selectedResult', 'ledger', 'previewTotal', 'previewGrade', 'ledgerSessions'));
    }
}
