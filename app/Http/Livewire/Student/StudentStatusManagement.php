<?php

declare(strict_types=1);

namespace App\Http\Livewire\Student;

use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Models\Department;
use App\Models\RegisteredCourse;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Services\AcademicProgressionService;
use App\Services\AcademicSessionService;
use App\Services\StudentStatusService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class StudentStatusManagement extends Component
{
    use LivewireAlert;

    public string $studentSearch = '';
    public ?string $selectedStudentId = null;
    public string $withdrawalType = 'academic';
    public string $reasonCode = 'CONSECUTIVE_PROBATION';
    public string $reason = '';
    public string $academicSession = '';
    public string $semester = '';
    public string $effectiveDate = '';
    public bool $reinstatementEligible = true;
    public string $notes = '';
    public string $queueSession = '';
    public string $queueDepartment = '';
    public string $queueType = '';
    public string $queueDateFrom = '';
    public string $queueDateTo = '';
    public ?int $selectedDecisionRecordId = null;
    public bool $showDecisionModal = false;
    public bool $approveDecision = true;
    public bool $decisionIsReinstatement = false;
    public string $senateReference = '';
    public string $senateDecisionDate = '';
    public string $decisionNotes = '';
    public string $reviewNotes = '';
    public bool $showStatusConfirmation = false;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        Gate::authorize('student-status.view-any');
        $this->academicSession = app(AcademicSessionService::class)->getAcademicSession(Auth::user());
        $this->effectiveDate = now()->toDateString();
        $this->senateDecisionDate = now()->toDateString();
    }

    public function updatedStudentSearch(): void
    {
        $this->selectedStudentId = null;
    }

    public function selectStudent(string $studentId): void
    {
        $student = $this->visibleStudentsQuery()->findOrFail($studentId);
        Gate::authorize('student-status.view', $student);
        $this->selectedStudentId = (string) $student->id;
        $this->withdrawalType = Gate::allows('student-status.recommend', $student)
            ? 'academic'
            : (Gate::allows('student-status.process-voluntary', $student) ? 'voluntary' : 'medical');
    }

    public function clearSelectedStudent(): void
    {
        $this->selectedStudentId = null;
    }

    public function createStatusRecord(): void
    {
        $this->validate([
            'selectedStudentId' => ['required', 'exists:users,id'],
            'withdrawalType' => ['required', 'in:academic,voluntary,medical'],
            'reasonCode' => ['required_if:withdrawalType,academic', 'nullable', 'string', 'max:80'],
            'reason' => ['required', 'string', 'max:2000'],
            'academicSession' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['nullable', 'in:1,2'],
            'effectiveDate' => ['required', 'date'],
            'reinstatementEligible' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $student = $this->visibleStudentsQuery()->findOrFail($this->selectedStudentId);
        Gate::authorize('student-status.view', $student);

        if (!app(StudentStatusService::class)->isAcademicallyActive($student)) {
            $this->addError('selectedStudentId', 'This student already has a current inactive status. Review the status history before taking another action.');
            return;
        }

        try {
            $semester = $this->semester === '' ? null : (int) $this->semester;
            $service = app(StudentStatusService::class);

            $record = match ($this->withdrawalType) {
                'academic' => (function () use ($service, $student, $semester) {
                    Gate::authorize('student-status.recommend', $student);
                    return $service->createWithdrawalRecommendation(
                        user: $student,
                        reasonCode: $this->reasonCode,
                        reason: $this->reason,
                        academicSession: $this->academicSession,
                        semester: $semester,
                        notes: $this->notes ?: null,
                        effectiveDate: $this->effectiveDate,
                        reinstatementEligible: $this->reinstatementEligible,
                    );
                })(),
                'voluntary' => (function () use ($service, $student, $semester) {
                    Gate::authorize('student-status.process-voluntary', $student);
                    return $service->processVoluntaryWithdrawal(
                        user: $student,
                        reason: $this->reason,
                        academicSession: $this->academicSession,
                        semester: $semester,
                        effectiveDate: $this->effectiveDate,
                        processedBy: Auth::user(),
                        reinstatementEligible: $this->reinstatementEligible,
                    );
                })(),
                'medical' => (function () use ($service, $student, $semester) {
                    Gate::authorize('student-status.process-medical', $student);
                    return $service->processMedicalWithdrawal(
                        user: $student,
                        reason: $this->reason,
                        academicSession: $this->academicSession,
                        semester: $semester,
                        effectiveDate: $this->effectiveDate,
                        processedBy: Auth::user(),
                        reinstatementEligible: $this->reinstatementEligible,
                    );
                })(),
            };

            $this->reset(['reason', 'notes']);
            $this->effectiveDate = now()->toDateString();
            $this->alert('success', 'Withdrawal record created. Review its workflow status before submitting it to Senate.');
            $this->selectedStudentId = (string) $student->id;
            $this->dispatch('student-status-record-created', recordId: $record->id);
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (\InvalidArgumentException $exception) {
            $this->addError('reason', $exception->getMessage());
        }
    }

    public function submitRecommendation(int $recordId): void
    {
        $record = $this->findVisibleRecord($recordId);
        Gate::authorize('student-status.submit-for-senate', $record->user);

        try {
            app(StudentStatusService::class)->submitForSenate($record, Auth::user());
            $this->alert('success', 'Withdrawal recommendation submitted to Senate.');
        } catch (\InvalidArgumentException $exception) {
            $this->alert('error', $exception->getMessage());
        }
    }

    public function beginSenateDecision(int $recordId, bool $approved): void
    {
        $record = $this->findVisibleRecord($recordId);
        Gate::authorize('student-status.decide-senate', $record->user);

        $this->selectedDecisionRecordId = $record->id;
        $this->approveDecision = $approved;
        $this->decisionIsReinstatement = false;
        $this->senateReference = '';
        $this->senateDecisionDate = now()->toDateString();
        $this->decisionNotes = '';
        $this->resetValidation(['senateReference', 'senateDecisionDate', 'decisionNotes']);
        $this->showDecisionModal = true;
    }

    public function decideWithdrawal(): void
    {
        $this->validate([
            'selectedDecisionRecordId' => ['required', 'exists:student_status_records,id'],
            'senateReference' => [$this->approveDecision ? 'required' : 'nullable', 'string', 'max:100'],
            'senateDecisionDate' => ['required', 'date'],
            'decisionNotes' => [$this->approveDecision ? 'nullable' : 'required', 'string', 'max:2000'],
        ]);

        $record = $this->findVisibleRecord($this->selectedDecisionRecordId);
        Gate::authorize('student-status.decide-senate', $record->user);

        try {
            $service = app(StudentStatusService::class);
            if ($this->approveDecision) {
                $service->approveWithdrawal(
                    recommendation: $record,
                    senateReference: $this->senateReference,
                    senateDecisionDate: $this->senateDecisionDate,
                    senateDecisionDetails: $this->decisionNotes ?: null,
                    approvedBy: Auth::user(),
                );
                $message = 'Senate approved the withdrawal decision.';
            } else {
                $service->rejectWithdrawal(
                    recommendation: $record,
                    rejectionReason: $this->decisionNotes,
                    senateDecisionDate: $this->senateDecisionDate,
                    rejectedBy: Auth::user(),
                    senateReference: $this->senateReference ?: null,
                );
                $message = 'Senate rejected the withdrawal recommendation. The student remains active.';
            }

            $this->showDecisionModal = false;
            $this->selectedDecisionRecordId = null;
            $this->alert('success', $message);
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (\InvalidArgumentException $exception) {
            $this->addError('senateReference', $exception->getMessage());
        }
    }

    public function recordReinstatementReview(int $recordId, string $stage, bool $approved): void
    {
        $record = $this->findVisibleRecord($recordId);
        $ability = strtoupper($stage) === 'DEPARTMENT'
            ? 'student-status.review-department-reinstatement'
            : 'student-status.review-faculty-reinstatement';
        Gate::authorize($ability, $record->user);

        try {
            app(StudentStatusService::class)->reviewReinstatement(
                request: $record,
                stage: $stage,
                approved: $approved,
                reviewedBy: Auth::user(),
                notes: $this->reviewNotes ?: null,
            );
            $this->reviewNotes = '';
            $this->alert('success', strtoupper($stage) . ' reinstatement review recorded.');
        } catch (\InvalidArgumentException $exception) {
            $this->alert('error', $exception->getMessage());
        }
    }

    public function beginReinstatementDecision(int $recordId, bool $approved): void
    {
        $record = $this->findVisibleRecord($recordId);
        Gate::authorize('student-status.decide-senate', $record->user);
        $this->selectedDecisionRecordId = $record->id;
        $this->approveDecision = $approved;
        $this->decisionIsReinstatement = true;
        $this->senateReference = '';
        $this->senateDecisionDate = now()->toDateString();
        $this->decisionNotes = '';
        $this->resetValidation(['senateReference', 'senateDecisionDate', 'decisionNotes']);
        $this->showDecisionModal = true;
    }

    public function decideReinstatement(): void
    {
        $this->validate([
            'selectedDecisionRecordId' => ['required', 'exists:student_status_records,id'],
            'senateReference' => ['required', 'string', 'max:100'],
            'senateDecisionDate' => ['required', 'date'],
            'decisionNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $record = $this->findVisibleRecord($this->selectedDecisionRecordId);
        Gate::authorize('student-status.decide-senate', $record->user);

        try {
            app(StudentStatusService::class)->processReinstatement(
                request: $record,
                approved: $this->approveDecision,
                senateReference: $this->senateReference,
                senateDecisionDate: $this->senateDecisionDate,
                processedBy: Auth::user(),
                decisionNotes: $this->decisionNotes ?: null,
            );
            $this->showDecisionModal = false;
            $this->selectedDecisionRecordId = null;
            $this->alert('success', $this->approveDecision
                ? 'Senate approved reinstatement. A new reinstated history event was created.'
                : 'Senate rejected the reinstatement request. The withdrawal history remains authoritative.');
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (\InvalidArgumentException $exception) {
            $this->addError('senateReference', $exception->getMessage());
        }
    }

    public function closeDecisionModal(): void
    {
        $this->showDecisionModal = false;
        $this->selectedDecisionRecordId = null;
        $this->resetValidation();
    }

    private function visibleStudentsQuery(): Builder
    {
        $actor = Auth::user();
        $query = User::query()
            ->where('role', 'student')
            ->where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->with(['academicDetail.department', 'academicDetail.course', 'academicDetail.studentLevel', 'academicDetail.programme']);

        if (!$actor->isAdmin()) {
            $scopes = $actor->capabilities()
                ->where('capability', 'student_status.view')
                ->pluck('department_id');

            if (!$scopes->contains(null)) {
                $departmentIds = $scopes->filter()->values();
                $query->whereHas('academicDetail', fn (Builder $academic) => $academic->whereIn('department_id', $departmentIds));
            }
        }

        if (trim($this->studentSearch) !== '') {
            $term = trim($this->studentSearch);
            $like = '%' . $term . '%';
            $normalizedTerm = preg_replace('/[^A-Za-z0-9]/u', '', $term) ?: $term;
            $normalizedLike = '%' . $normalizedTerm . '%';

            $query->where(function (Builder $student) use ($like, $normalizedLike) {
                $student->whereHas('academicDetail', function (Builder $academic) use ($like, $normalizedLike) {
                        $academic->where('matric_no', 'like', $like)
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(LOWER(matric_no), '/', ''), '-', ''), ' ', ''), '.', '') LIKE ?", [strtolower($normalizedLike)]);
                    })
                    ->orWhere('firstname', 'like', $like)
                    ->orWhere('surname', 'like', $like)
                    ->orWhere('m_name', 'like', $like);
            });
        }

        return $query;
    }

    private function findVisibleRecord(int $recordId): StudentStatusRecord
    {
        $record = StudentStatusRecord::with('user.academicDetail')
            ->whereKey($recordId)
            ->whereHas('user', fn (Builder $user) => $user->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->firstOrFail();

        Gate::authorize('student-status.view', $record->user);
        return $record;
    }

    private function scopedWorkflowRecords(array $decisions, array $statuses): Builder
    {
        $query = StudentStatusRecord::query()
            ->with(['user.academicDetail.department', 'user.academicDetail.studentLevel', 'processedBy'])
            ->whereIn('senate_decision', $decisions)
            ->whereIn('status', $statuses)
            ->whereHas('user', fn (Builder $user) => $user->where('programme_id', ProgrammesEnum::Undergraduate->value));

        $actor = Auth::user();
        if (!$actor->isAdmin()) {
            $scopes = $actor->capabilities()->where('capability', 'student_status.view')->pluck('department_id');
            if (!$scopes->contains(null)) {
                $departmentIds = $scopes->filter()->values();
                $query->whereHas('user.academicDetail', fn (Builder $academic) => $academic->whereIn('department_id', $departmentIds));
            }
        }

        if ($this->queueSession !== '') {
            $query->where('academic_session', $this->queueSession);
        }
        if ($this->queueDepartment !== '') {
            $query->whereHas('user.academicDetail', fn (Builder $academic) => $academic->where('department_id', $this->queueDepartment));
        }
        $withdrawalStatuses = [
            StudentStatus::ACADEMIC_WITHDRAWAL->value,
            StudentStatus::VOLUNTARY_WITHDRAWAL->value,
            StudentStatus::MEDICAL_WITHDRAWAL->value,
        ];
        if (count(array_intersect($statuses, $withdrawalStatuses)) > 0 && $this->queueType !== '') {
            $query->where('status_type', $this->queueType);
        }
        if ($this->queueDateFrom !== '') {
            $query->whereDate('effective_date', '>=', $this->queueDateFrom);
        }
        if ($this->queueDateTo !== '') {
            $query->whereDate('effective_date', '<=', $this->queueDateTo);
        }

        return $query->latest('updated_at');
    }

    public function render()
    {
        $students = $this->studentSearch === '' ? collect() : $this->visibleStudentsQuery()->limit(12)->get();
        $selectedStudent = null;
        $currentStatus = null;
        $history = collect();
        $auditEntries = collect();
        $registrationHistory = collect();
        $progression = null;

        if ($this->selectedStudentId) {
            $selectedStudent = $this->visibleStudentsQuery()->find($this->selectedStudentId);
            if ($selectedStudent) {
                Gate::authorize('student-status.view', $selectedStudent);
                $statusService = app(StudentStatusService::class);
                $currentStatus = $statusService->getCurrentStatus($selectedStudent);
                $history = $statusService->getStatusHistory($selectedStudent);
                if ($selectedStudent->academicDetail) {
                    $registrationHistory = RegisteredCourse::query()
                        ->with('departmentCourse.studentCourse')
                        ->where('academic_detail_id', $selectedStudent->academicDetail->id)
                        ->orderByDesc('academic_session')
                        ->orderByDesc('id')
                        ->limit(12)
                        ->get();
                }
                $progression = app(AcademicProgressionService::class)->determineAcademicStanding($selectedStudent);
                if (Gate::allows('student-status.view-audit')) {
                    $auditEntries = $selectedStudent->studentStatusAuditEntries()->with('actor')->latest('occurred_at')->limit(20)->get();
                }
            }
        }

        $pendingDecisions = $this->scopedWorkflowRecords(
            [StudentStatusService::WORKFLOW_PENDING_SENATE],
            [StudentStatus::ACADEMIC_WITHDRAWAL->value, StudentStatus::VOLUNTARY_WITHDRAWAL->value, StudentStatus::MEDICAL_WITHDRAWAL->value],
        )->limit(25)->get();

        $reinstatementRequests = $this->scopedWorkflowRecords(
            [
                StudentStatusService::REINSTATEMENT_REQUESTED,
                StudentStatusService::REINSTATEMENT_DEPARTMENT_APPROVED,
                StudentStatusService::REINSTATEMENT_FACULTY_APPROVED,
            ],
            [StudentStatus::REINSTATED->value],
        )->limit(25)->get();
        $originalWithdrawals = app(StudentStatusService::class)->getCurrentStatuses($reinstatementRequests->pluck('user_id'));
        $reinstatementRequests->each(function (StudentStatusRecord $request) use ($originalWithdrawals) {
            $request->setRelation('originalWithdrawal', $originalWithdrawals->get($request->user_id));
        });

        $sessions = RegisteredCourse::query()->whereNotNull('academic_session')->distinct()->orderByDesc('academic_session')->pluck('academic_session');
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('livewire.student.student-status-management', compact(
            'students', 'selectedStudent', 'currentStatus', 'history', 'auditEntries', 'registrationHistory',
            'progression', 'pendingDecisions', 'reinstatementRequests', 'sessions', 'departments',
        ))->layout('layouts.app');
    }
}
