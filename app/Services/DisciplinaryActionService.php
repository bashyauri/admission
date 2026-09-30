<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StudentStatus;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\CarryOverCourse;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryActionAudit;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use LogicException;

class DisciplinaryActionService
{
    private const SANCTION_TYPES = ['course_cancellation', 'repeat_session', 'suspension', 'expulsion'];

    public function __construct(
        private readonly StudentStatusService $studentStatusService,
        private readonly AcademicProgressionService $academicProgressionService,
        private readonly CarryOverRegistrationService $carryOverRegistrationService,
        private readonly GradeCalculationService $gradeCalculationService,
    ) {}

    /** Apply an approved Senate sanction to an undergraduate student. */
    public function applySanction(array $data): DisciplinaryAction
    {
        $actor = $this->authorizedActor($data['user_id'] ?? null);
        $student = User::with('academicDetail')->findOrFail($data['user_id'] ?? '');
        Gate::forUser($actor)->authorize('disciplinary-actions.manage', $student);

        $sanctionType = (string) ($data['sanction_type'] ?? '');
        if (!in_array($sanctionType, self::SANCTION_TYPES, true)) {
            throw new InvalidArgumentException('The selected disciplinary sanction type is invalid.');
        }
        if (!$student->isUndergraduate()) {
            throw new InvalidArgumentException('Disciplinary sanctions in this phase apply to undergraduate students only.');
        }

        $academicDetail = AcademicDetail::query()
            ->whereKey($data['academic_detail_id'] ?? null)
            ->where('user_id', $student->id)
            ->first();
        if (!$academicDetail) {
            throw new InvalidArgumentException('The selected academic record does not belong to this student.');
        }

        $academicSession = (string) ($data['academic_session'] ?? '');
        $effectiveSession = (string) ($data['effective_session'] ?? '');
        $senateReference = trim((string) ($data['senate_ref_no'] ?? ''));
        if (!$this->studentStatusService->isValidAcademicSession($academicSession)
            || !$this->studentStatusService->isValidAcademicSession($effectiveSession)) {
            throw new InvalidArgumentException('Academic sessions must use the YYYY/YYYY format.');
        }
        if (!$this->studentStatusService->isValidSenateReference($senateReference)) {
            throw new InvalidArgumentException('Enter a valid Senate reference number.');
        }
        $semester = $data['semester'] ?? null;
        if ($semester !== null && !in_array($semester, ['first', 'second'], true)) {
            throw new InvalidArgumentException('Semester must be first or second.');
        }

        $verdictDate = $this->validDate((string) ($data['verdict_date'] ?? ''));
        $result = null;
        if ($sanctionType === 'course_cancellation') {
            $result = Result::query()->with('registeredCourse.departmentCourse.studentCourse')
                ->whereKey($data['result_id'] ?? null)
                ->where('user_id', $student->id)
                ->where('academic_detail_id', $academicDetail->id)
                ->where('academic_session', $academicSession)
                ->when($semester !== null, fn ($query) => $query->where('semester', $semester))
                ->lockForUpdate()
                ->first();

            if (!$result || $result->status !== 'released') {
                throw new InvalidArgumentException('Course cancellation requires a released result attempt belonging to this student and session.');
            }
            if (!$result->department_course_id || !$result->registered_course_id) {
                throw new InvalidArgumentException('The selected result is missing its course registration reference.');
            }
            $snapshot = $this->courseSnapshot($result);
            if (!$snapshot['course_code'] && !$snapshot['course_title']) {
                throw new InvalidArgumentException('The selected result has no stored course snapshot to identify the affected course.');
            }
        }

        $effectiveDate = null;
        $endDate = null;
        if (in_array($sanctionType, ['suspension', 'expulsion'], true)) {
            $effectiveDate = $this->validDate((string) ($data['effective_date'] ?? $verdictDate));
            $resumptionSession = $data['resumption_session'] ?? null;
            if ($sanctionType === 'suspension') {
                $sameSessionOneSemester = (string) $resumptionSession === $effectiveSession && $semester === 'first';
                if (!$this->studentStatusService->isValidAcademicSession((string) $resumptionSession)
                    || (string) $resumptionSession < $effectiveSession
                    || ((string) $resumptionSession === $effectiveSession && !$sameSessionOneSemester)) {
                    throw new InvalidArgumentException('A suspension requires a valid resumption session after its effective period.');
                }
                $endDate = $this->validDate((string) ($data['end_date'] ?? ''));
                if ($endDate < $effectiveDate) {
                    throw new InvalidArgumentException('The suspension end date cannot be before its effective date.');
                }
            } elseif ($resumptionSession !== null && $resumptionSession !== '') {
                throw new InvalidArgumentException('An expulsion cannot have a resumption session.');
            }
        }

        return DB::transaction(function () use (
            $data, $actor, $student, $academicDetail, $sanctionType, $academicSession,
            $effectiveSession, $semester, $senateReference, $verdictDate, $result,
            $effectiveDate, $endDate
        ) {
            $action = DisciplinaryAction::create([
                'user_id' => $student->id,
                'academic_detail_id' => $academicDetail->id,
                'sanction_type' => $sanctionType,
                'result_id' => $result?->id,
                'academic_session' => $academicSession,
                'semester' => $semester,
                'senate_ref_no' => $senateReference,
                'verdict_date' => $verdictDate,
                'effective_session' => $effectiveSession,
                'resumption_session' => $data['resumption_session'] ?? null,
                'is_active' => true,
                'is_appealed' => false,
                'sanctioned_by' => $actor->id,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $effect = match ($sanctionType) {
                'course_cancellation' => $this->applyCourseCancellation($action, $result, $student),
                'repeat_session' => $this->academicProgressionService->applySanctionedRepeat(
                    $student,
                    $academicDetail,
                    $effectiveSession,
                    $semester === null ? null : ($semester === 'first' ? 1 : 2),
                    $senateReference,
                ),
                'suspension', 'expulsion' => $this->applyStatusSanction(
                    $action,
                    $student,
                    $sanctionType,
                    $academicSession,
                    $effectiveDate,
                    $endDate,
                    $senateReference,
                    $actor,
                    (string) ($data['remarks'] ?? 'Senate-approved disciplinary sanction.'),
                ),
            };

            $this->writeAudit($action, $student, $actor, 'SANCTION_APPLIED', [
                'effect' => $effect,
                'course_snapshot' => $result ? $this->courseSnapshot($result) : null,
                'effective_date' => $effectiveDate,
                'end_date' => $endDate,
            ]);

            return $action->refresh();
        });
    }

    /** Record an appeal decision. An upheld sanction remains in force; a quashed one is reversed. */
    public function recordAppealOutcome(
        int $actionId,
        string $outcome,
        string $resolutionRef,
        ?string $remarks = null
    ): DisciplinaryAction {
        if (!in_array($outcome, ['upheld', 'quashed'], true)) {
            throw new InvalidArgumentException('Appeal outcome must be upheld or quashed.');
        }

        if ($outcome === 'quashed') {
            return $this->liftSanction($actionId, $resolutionRef);
        }

        $actor = $this->authorizedActor();
        return DB::transaction(function () use ($actionId, $resolutionRef, $remarks, $actor) {
            $action = DisciplinaryAction::query()->with('user')->lockForUpdate()->findOrFail($actionId);
            Gate::forUser($actor)->authorize('disciplinary-actions.manage', $action->user);
            $this->validateResolutionReference($resolutionRef);
            if (!$action->is_active || $action->is_appealed) {
                throw new LogicException('Only an active sanction without a prior appeal can receive an appeal decision.');
            }

            $action->update(['is_appealed' => true, 'appeal_status' => 'upheld']);
            $this->writeAudit($action, $action->user, $actor, 'APPEAL_UPHELD', [
                'remarks' => $remarks,
            ], $resolutionRef);

            return $action->refresh();
        });
    }

    /** Quash a sanction and reverse its effects while preserving the original decision and audit trail. */
    public function liftSanction(int $actionId, string $resolutionRef): DisciplinaryAction
    {
        $actor = $this->authorizedActor();
        return DB::transaction(function () use ($actionId, $resolutionRef, $actor) {
            $action = DisciplinaryAction::query()->with(['user', 'result'])->lockForUpdate()->findOrFail($actionId);
            Gate::forUser($actor)->authorize('disciplinary-actions.manage', $action->user);
            $this->validateResolutionReference($resolutionRef);
            if (!$action->is_active) {
                throw new LogicException('This disciplinary sanction is already inactive.');
            }

            $applicationAudit = $action->auditEntries()->where('event', 'SANCTION_APPLIED')->oldest('id')->first();
            if (!$applicationAudit) {
                throw new LogicException('The sanction has no application audit snapshot and cannot be safely reversed.');
            }
            $metadata = $applicationAudit->metadata ?? [];
            $effect = $metadata['effect'] ?? [];

            if ($action->sanction_type === 'course_cancellation') {
                $this->reverseCourseCancellation($action, $effect, $action->user);
            } elseif ($action->sanction_type === 'repeat_session') {
                $this->academicProgressionService->restoreSanctionedRepeat($effect);
            } elseif (in_array($action->sanction_type, ['suspension', 'expulsion'], true)) {
                $statusRecord = StudentStatusRecord::find($action->student_status_record_id);
                if ($statusRecord) {
                    $this->studentStatusService->endDisciplinaryStatus(
                        $statusRecord,
                        now()->toDateString(),
                        $resolutionRef,
                        $actor,
                    );
                    $this->studentStatusService->restorePreDisciplinaryStatusEvents(
                        $action->user,
                        $effect['prior_status_records'] ?? [],
                        $metadata['effective_date'] ?? now()->toDateString(),
                        $resolutionRef,
                        $actor,
                    );
                }
            }

            $action->update([
                'is_active' => false,
                'is_appealed' => true,
                'appeal_status' => 'quashed',
            ]);
            $this->writeAudit($action, $action->user, $actor, 'SANCTION_QUASHED', [
                'reversed_effect' => $effect,
            ], $resolutionRef);

            return $action->refresh();
        });
    }

    private function applyCourseCancellation(DisciplinaryAction $action, Result $result, User $student): array
    {
        $result = Result::query()->with('registeredCourse.departmentCourse.studentCourse')->lockForUpdate()->findOrFail($result->id);
        $before = $result->only([
            'ca_score', 'exam_score', 'total_score', 'grade', 'grade_point', 'grade_point_total', 'remarks',
        ]);
        $carryOver = CarryOverCourse::query()
            ->where('user_id', $student->id)
            ->where('department_course_id', $result->department_course_id)
            ->where('failed_session', $result->academic_session)
            ->lockForUpdate()
            ->first();
        $carryOverBefore = $carryOver?->getAttributes();

        $referenceNote = 'MALPRACTICE (' . $action->senate_ref_no . ')';
        $result->update([
            'grade' => 'F',
            'grade_point' => 0,
            'grade_point_total' => 0,
            'remarks' => trim(implode(' | ', array_filter([$result->remarks, $referenceNote]))),
        ]);
        $carryOver = $this->carryOverRegistrationService->recordFailedCourse($result);
        if (!$carryOver) {
            throw new LogicException('The sanctioned result did not produce its required carry-over record.');
        }

        $gpaBefore = ResultGpaRecord::where('user_id', $student->id)->get()->map->getAttributes()->all();
        $this->recalculateGpaRecords($student);

        return [
            'result_id' => $result->id,
            'result_before' => $before,
            'result_after' => $result->only(['grade', 'grade_point', 'grade_point_total', 'remarks']),
            'carry_over_id' => $carryOver->id,
            'carry_over_created' => $carryOverBefore === null,
            'carry_over_before' => $carryOverBefore,
            'gpa_before' => $gpaBefore,
            'course_snapshot' => $this->courseSnapshot($result),
        ];
    }

    private function reverseCourseCancellation(DisciplinaryAction $action, array $effect, User $student): void
    {
        $result = Result::query()->lockForUpdate()->find($effect['result_id'] ?? null);
        if (!$result) {
            throw new LogicException('The sanctioned result no longer exists, so the appeal cannot be reversed automatically.');
        }
        foreach (($effect['result_after'] ?? []) as $field => $value) {
            if ($result->{$field} != $value) {
                throw new LogicException('The sanctioned result has changed since the decision; manual review is required before reversal.');
            }
        }
        $laterAttemptExists = Result::query()
            ->where('registered_course_id', $result->registered_course_id)
            ->where('id', '!=', $result->id)
            ->where('created_at', '>', $action->created_at)
            ->exists();
        if ($laterAttemptExists) {
            throw new LogicException('A later attempt exists for this course; manual review is required before reversal.');
        }

        $result->update($effect['result_before'] ?? []);
        $carryOver = CarryOverCourse::query()->lockForUpdate()->find($effect['carry_over_id'] ?? null);
        if ($carryOver) {
            if ($effect['carry_over_created'] ?? false) {
                if ($carryOver->is_cleared) {
                    throw new LogicException('The sanctioned carry-over has already been cleared; manual review is required before reversal.');
                }
                $carryOver->delete();
            } else {
                $before = $effect['carry_over_before'] ?? [];
                unset($before['id'], $before['created_at'], $before['updated_at']);
                $carryOver->update($before);
            }
        }
        $this->recalculateGpaRecords($student);
    }

    private function applyStatusSanction(
        DisciplinaryAction $action,
        User $student,
        string $sanctionType,
        string $academicSession,
        ?string $effectiveDate,
        ?string $endDate,
        string $senateReference,
        User $actor,
        string $remarks
    ): array {
        $priorStatuses = StudentStatusRecord::query()
            ->where('user_id', $student->id)
            ->whereIn('senate_decision', [StudentStatusService::WORKFLOW_SENATE_APPROVED, 'APPROVED'])
            ->whereNull('end_date')
            ->get(['id', 'end_date'])
            ->map(fn (StudentStatusRecord $record) => $record->getAttributes())
            ->all();

        $record = $this->studentStatusService->processDisciplinaryStatus(
            $student,
            $sanctionType === 'suspension' ? StudentStatus::SUSPENDED : StudentStatus::EXPELLED,
            $remarks,
            $academicSession,
            $action->semester === null ? null : ($action->semester === 'first' ? 1 : 2),
            $effectiveDate ?? throw new LogicException('A status sanction requires an effective date.'),
            $endDate,
            $senateReference,
            $actor,
        );
        $action->update(['student_status_record_id' => $record->id]);

        return [
            'student_status_record_id' => $record->id,
            'prior_status_records' => $priorStatuses,
        ];
    }

    private function recalculateGpaRecords(User $student): void
    {
        $periods = ResultGpaRecord::query()->where('user_id', $student->id)
            ->get(['academic_session', 'semester'])
            ->unique(fn (ResultGpaRecord $record) => $record->academic_session . '|' . $record->semester);

        foreach ($periods as $period) {
            $this->gradeCalculationService->processAndSaveGpaRecord($student, $period->academic_session, $period->semester);
        }
    }

    private function courseSnapshot(Result $result): array
    {
        $registeredCourse = $result->registeredCourse;
        $studentCourse = $registeredCourse?->departmentCourse?->studentCourse;

        return [
            'course_code' => $result->course_code_snapshot ?: $registeredCourse?->course_code_snapshot ?: $studentCourse?->code,
            'course_title' => $result->course_title_snapshot ?: $registeredCourse?->course_title_snapshot ?: $studentCourse?->title,
            'credit_units' => $result->credit_units_snapshot ?? $registeredCourse?->credit_units_snapshot,
            'semester' => $result->semester_snapshot ?: $registeredCourse?->semester_snapshot,
            'level' => $result->level_snapshot ?? $registeredCourse?->level_snapshot,
            'registered_course_id' => $result->registered_course_id,
        ];
    }

    private function validDate(string $date): string
    {
        try {
            return \Illuminate\Support\Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            throw new InvalidArgumentException('A valid calendar date is required.');
        }
    }

    private function validateResolutionReference(string $reference): void
    {
        if (!$this->studentStatusService->isValidSenateReference(trim($reference))) {
            throw new InvalidArgumentException('Enter a valid Senate resolution reference number.');
        }
    }

    private function authorizedActor(mixed $studentId = null): User
    {
        $actor = Auth::user();
        if (!$actor instanceof User) {
            throw new AuthorizationException('An authenticated disciplinary officer is required.');
        }
        if ($studentId !== null && (string) $studentId === (string) $actor->id) {
            throw new AuthorizationException('Staff cannot apply a disciplinary sanction to their own student account.');
        }

        return $actor;
    }

    private function writeAudit(
        DisciplinaryAction $action,
        User $student,
        User $actor,
        string $event,
        array $metadata = [],
        ?string $resolutionReference = null
    ): DisciplinaryActionAudit {
        return DisciplinaryActionAudit::create([
            'disciplinary_action_id' => $action->id,
            'student_id' => $student->id,
            'actor_id' => $actor->id,
            'event' => $event,
            'resolution_ref' => $resolutionReference,
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => now(),
        ]);
    }
}
