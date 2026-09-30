<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AcademicActivity;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\StudentStatusRecord;
use App\Models\StudentStatusAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class StudentStatusService
{
    // =========================================================================
    // Senate Workflow States
    // =========================================================================

    public const WORKFLOW_RECOMMENDED = 'WITHDRAWAL_RECOMMENDED';
    public const WORKFLOW_PENDING_SENATE = 'PENDING_SENATE';
    public const WORKFLOW_SENATE_APPROVED = 'SENATE_APPROVED';
    public const WORKFLOW_SENATE_REJECTED = 'SENATE_REJECTED';

    public const REINSTATEMENT_REQUESTED = 'REINSTATEMENT_REQUESTED';
    public const REINSTATEMENT_DEPARTMENT_APPROVED = 'REINSTATEMENT_DEPARTMENT_APPROVED';
    public const REINSTATEMENT_FACULTY_APPROVED = 'REINSTATEMENT_FACULTY_APPROVED';
    public const REINSTATEMENT_DEPARTMENT_REJECTED = 'REINSTATEMENT_DEPARTMENT_REJECTED';
    public const REINSTATEMENT_FACULTY_REJECTED = 'REINSTATEMENT_FACULTY_REJECTED';
    public const REINSTATEMENT_APPROVED = 'REINSTATEMENT_APPROVED';
    public const REINSTATEMENT_REJECTED = 'REINSTATEMENT_REJECTED';

    // =========================================================================
    // Assessment Gateway
    // =========================================================================

    /**
     * Evaluate academic withdrawal eligibility using the eligibility engine.
     *
     * This is the gateway method that coordinates with AcademicProgressionService
     * to determine if a student meets institutional withdrawal criteria.
     *
     * @return array{
     *     eligible: bool,
     *     reason_code: string|null,
     *     reason: string,
     *     standing: string,
     *     cgpa: float,
     *     triggered_rules: string[],
     * }
     */
    public function evaluateAcademicWithdrawal(User $user): array
    {
        return app(AcademicProgressionService::class)->evaluateWithdrawalEligibility($user);
    }

    // =========================================================================
    // Withdrawal Recommendation Creation
    // =========================================================================

    /**
     * Create a withdrawal recommendation record.
     *
     * This creates a recommendation status record that can be submitted to Senate.
     * A recommendation is NOT an official withdrawal until Senate approves it.
     *
     * @param User $user The student
     * @param string $reasonCode The institutional reason code (e.g., 'CONSECUTIVE_PROBATION')
     * @param string $reason Human-readable reason description
     * @param string $academicSession The academic session (e.g., '2025/2026')
     * @param int|null $semester The semester (1 or 2), null if session-wide
     * @param string|null $notes Additional context or supporting information
     * @param User|null $processedBy The user creating the recommendation
     * @param Carbon|string|null $effectiveDate Optional effective date (defaults to now)
     * @return StudentStatusRecord The created recommendation record
     */
    public function createWithdrawalRecommendation(
        User $user,
        string $reasonCode,
        string $reason,
        string $academicSession,
        ?int $semester = null,
        ?string $notes = null,
        ?User $processedBy = null,
        \DateTimeInterface|string|null $effectiveDate = null,
        bool $reinstatementEligible = true
    ): StudentStatusRecord {
        // Validate that user is undergraduate
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Withdrawal recommendations are only applicable to undergraduate students.');
        }

        $processedBy = $this->authorizeStatusAction('recommend', $processedBy, $user);

        // Validate academic session format
        if (!$this->isValidAcademicSession($academicSession)) {
            throw new \InvalidArgumentException("Invalid academic session format: {$academicSession}. Expected format: 'YYYY/YYYY'");
        }

        // Validate semester if provided
        if ($semester !== null && !in_array($semester, [1, 2], true)) {
            throw new \InvalidArgumentException('Semester must be 1 or 2.');
        }

        // Capture current academic progression for audit trail
        $progressionInfo = app(AcademicProgressionService::class)->determineAcademicStanding($user);

        $effDate = $effectiveDate instanceof Carbon
            ? $effectiveDate
            : ($effectiveDate ? Carbon::parse($effectiveDate) : now());

        return DB::transaction(function () use (
            $user,
            $reasonCode,
            $reason,
            $academicSession,
            $semester,
            $notes,
            $processedBy,
            $progressionInfo,
            $effDate,
            $reinstatementEligible
        ) {
            // Create the recommendation record
            $record = StudentStatusRecord::create([
                'user_id' => $user->id,
                'academic_detail_id' => $user->academicDetail?->id,
                'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
                'status_type' => StudentStatusType::ACADEMIC,
                'reason_code' => $reasonCode,
                'reason' => $reason,
                'academic_session' => $academicSession,
                'semester' => $semester,
                'effective_date' => $effDate->toDateString(),
                'senate_decision' => self::WORKFLOW_RECOMMENDED,
                'reinstatement_eligible' => $reinstatementEligible,
                'processed_by' => $processedBy?->id,
                'notes' => $notes,
            ]);

            // Update academic progression record to flag withdrawal recommendation
            $this->updateProgressionRecordWithRecommendation($user, $academicSession, $semester, $progressionInfo);
            $this->writeStatusAudit($user, $record, 'WITHDRAWAL_RECOMMENDED', $processedBy, [
                'new_status' => $record->status,
                'new_decision' => $record->senate_decision,
                'reason' => $record->reason,
                'reinstatement_eligible' => $record->reinstatement_eligible,
            ]);

            Log::info('Withdrawal recommendation created', [
                'user_id' => $user->id,
                'matric_no' => $user->academicDetail?->matric_no,
                'reason_code' => $reasonCode,
                'academic_session' => $academicSession,
                'processed_by' => $processedBy?->id,
            ]);

            return $record;
        });
    }

    // =========================================================================
    // Senate Submission
    // =========================================================================

    /**
     * Submit a withdrawal recommendation to Senate for approval.
     *
     * @param StudentStatusRecord $recommendation The recommendation record
     * @param User|null $submittedBy The user submitting to Senate
     * @return StudentStatusRecord The updated record
     */
    public function submitForSenate(StudentStatusRecord $recommendation, ?User $submittedBy = null): StudentStatusRecord
    {
        if ($recommendation->senate_decision !== self::WORKFLOW_RECOMMENDED) {
            throw new \InvalidArgumentException('Only recommendations can be submitted to Senate.');
        }

        $student = User::findOrFail($recommendation->user_id);
        $submittedBy = $this->authorizeStatusAction('submit-for-senate', $submittedBy, $student);
        return DB::transaction(function () use ($recommendation, $submittedBy) {
            $recommendation->update([
                'senate_decision' => self::WORKFLOW_PENDING_SENATE,
                'processed_by' => $submittedBy?->id ?? $recommendation->processed_by,
            ]);
            $this->writeStatusAudit($recommendation->user, $recommendation, 'WITHDRAWAL_SUBMITTED_FOR_SENATE', $submittedBy, [
                'old_status' => $recommendation->status,
                'new_status' => $recommendation->status,
                'old_decision' => self::WORKFLOW_RECOMMENDED,
                'new_decision' => self::WORKFLOW_PENDING_SENATE,
                'reason' => $recommendation->reason,
            ]);

            Log::info('Withdrawal recommendation submitted to Senate', [
                'status_record_id' => $recommendation->id,
                'user_id' => $recommendation->user_id,
                'submitted_by' => $submittedBy?->id,
            ]);

            return $recommendation->fresh();
        });
    }

    // =========================================================================
    // Senate Approval
    // =========================================================================

    /**
     * Approve a withdrawal recommendation on behalf of Senate.
     *
     * This is the point at which the withdrawal becomes official and affects
     * the student's academic activity permissions.
     *
     * @param StudentStatusRecord $recommendation The pending Senate record
     * @param string $senateReference The Senate reference number (e.g., 'SEN-2026-104')
     * @param Carbon|string|null $senateDecisionDate The date of Senate decision
     * @param string|null $senateDecisionDetails Additional Senate decision context
     * @param User|null $approvedBy The user recording the Senate approval
     * @param Carbon|string|null $effectiveDate Optional effective date override
     * @return StudentStatusRecord The approved record
     */
    public function approveWithdrawal(
        StudentStatusRecord $recommendation,
        string $senateReference,
        \DateTimeInterface|string|null $senateDecisionDate = null,
        ?string $senateDecisionDetails = null,
        ?User $approvedBy = null,
        \DateTimeInterface|string|null $effectiveDate = null
    ): StudentStatusRecord {
        if ($recommendation->senate_decision !== self::WORKFLOW_PENDING_SENATE) {
            throw new \InvalidArgumentException('Only pending Senate decisions can be approved.');
        }

        $student = User::findOrFail($recommendation->user_id);
        $approvedBy = $this->authorizeStatusAction('decide-senate', $approvedBy, $student);

        // Validate Senate reference format
        if (!$this->isValidSenateReference($senateReference)) {
            throw new \InvalidArgumentException("Invalid Senate reference format: {$senateReference}");
        }

        // Normalize date
        $decisionDate = $senateDecisionDate ? Carbon::parse($senateDecisionDate) : now();

        $effDate = $effectiveDate
            ? Carbon::parse($effectiveDate)
            : ($recommendation->effective_date ? Carbon::parse($recommendation->effective_date) : $decisionDate);

        return DB::transaction(function () use (
            $recommendation,
            $senateReference,
            $decisionDate,
            $senateDecisionDetails,
            $approvedBy,
            $effDate
        ) {
            // Close any previous active status for this student
            $this->closePreviousActiveStatus($recommendation->user_id, $effDate, $approvedBy, 'ACTIVE_STATUS_CLOSED_FOR_WITHDRAWAL');

            // Approve the withdrawal
            $recommendation->update([
                'senate_decision' => self::WORKFLOW_SENATE_APPROVED,
                'senate_reference' => $senateReference,
                'senate_decision_date' => $decisionDate->toDateString(),
                'effective_date' => $effDate instanceof Carbon ? $effDate->toDateString() : (string) $effDate,
                'notes' => $senateDecisionDetails ?? $recommendation->notes,
                'processed_by' => $approvedBy?->id ?? $recommendation->processed_by,
            ]);
            $this->writeStatusAudit($recommendation->user, $recommendation, 'WITHDRAWAL_APPROVED', $approvedBy, [
                'old_status' => $recommendation->status,
                'new_status' => $recommendation->status,
                'old_decision' => self::WORKFLOW_PENDING_SENATE,
                'new_decision' => self::WORKFLOW_SENATE_APPROVED,
                'reason' => $senateDecisionDetails ?? $recommendation->reason,
                'senate_reference' => $senateReference,
            ]);

            Log::info('Withdrawal approved by Senate', [
                'status_record_id' => $recommendation->id,
                'user_id' => $recommendation->user_id,
                'senate_reference' => $senateReference,
                'approved_by' => $approvedBy?->id,
            ]);

            return $recommendation->fresh();
        });
    }

    // =========================================================================
    // Senate Rejection
    // =========================================================================

    /**
     * Reject a withdrawal recommendation on behalf of Senate.
     *
     * The student remains active; the recommendation is marked as rejected.
     *
     * @param StudentStatusRecord $recommendation The pending Senate record
     * @param string $rejectionReason The reason for Senate rejection
     * @param Carbon|string|null $senateDecisionDate The date of Senate decision
     * @param User|null $rejectedBy The user recording the Senate rejection
     * @return StudentStatusRecord The rejected record
     */
    public function rejectWithdrawal(
        StudentStatusRecord $recommendation,
        string $rejectionReason,
        \DateTimeInterface|string|null $senateDecisionDate = null,
        ?User $rejectedBy = null,
        ?string $senateReference = null
    ): StudentStatusRecord {
        if ($recommendation->senate_decision !== self::WORKFLOW_PENDING_SENATE) {
            throw new \InvalidArgumentException('Only pending Senate decisions can be rejected.');
        }

        $student = User::findOrFail($recommendation->user_id);
        $rejectedBy = $this->authorizeStatusAction('decide-senate', $rejectedBy, $student);
        if ($senateReference !== null && !$this->isValidSenateReference($senateReference)) {
            throw new \InvalidArgumentException("Invalid Senate reference format: {$senateReference}");
        }

        // Normalize date
        $decisionDate = $senateDecisionDate ? Carbon::parse($senateDecisionDate) : now();

        return DB::transaction(function () use ($recommendation, $rejectionReason, $decisionDate, $rejectedBy, $senateReference) {
            $recommendation->update([
                'senate_decision' => self::WORKFLOW_SENATE_REJECTED,
                'senate_decision_date' => $decisionDate->toDateString(),
                'senate_reference' => $senateReference ?? $recommendation->senate_reference,
                'notes' => $rejectionReason,
                'processed_by' => $rejectedBy?->id ?? $recommendation->processed_by,
            ]);
            $this->writeStatusAudit($recommendation->user, $recommendation, 'WITHDRAWAL_REJECTED', $rejectedBy, [
                'old_status' => $recommendation->status,
                'new_status' => $recommendation->status,
                'old_decision' => self::WORKFLOW_PENDING_SENATE,
                'new_decision' => self::WORKFLOW_SENATE_REJECTED,
                'reason' => $rejectionReason,
                'senate_reference' => $senateReference,
            ]);

            // Clear withdrawal recommendation flag on academic progression record
            $progressionQuery = AcademicProgressionRecord::where('user_id', $recommendation->user_id)
                ->where('academic_session', $recommendation->academic_session);
            if ($recommendation->semester !== null) {
                $progressionQuery->where('semester', $recommendation->semester);
            }
            $progressionQuery->update(['withdrawal_recommended' => false]);

            Log::info('Withdrawal rejected by Senate', [
                'status_record_id' => $recommendation->id,
                'user_id' => $recommendation->user_id,
                'rejection_reason' => $rejectionReason,
                'rejected_by' => $rejectedBy?->id,
            ]);

            return $recommendation->fresh();
        });
    }

    // =========================================================================
    // Voluntary Withdrawal
    // =========================================================================

    /**
     * Process a voluntary withdrawal request.
     *
     * Voluntary withdrawals may be processed directly with Senate reference if already approved,
     * or created as recommendations pending Senate approval.
     *
     * @param User $user The student
     * @param string $reason The student's reason for voluntary withdrawal
     * @param string $academicSession The academic session
     * @param int|null $semester The semester (1 or 2), null if session-wide
     * @param \DateTimeInterface|string|null $effectiveDate When the withdrawal takes effect
     * @param string|null $senateReference Senate reference if Senate approval confirmed
     * @param User|null $processedBy The user processing the withdrawal
     * @return StudentStatusRecord The created voluntary withdrawal record
     */
    public function processVoluntaryWithdrawal(
        User $user,
        string $reason,
        string $academicSession,
        ?int $semester = null,
        \DateTimeInterface|string|null $effectiveDate = null,
        ?string $senateReference = null,
        ?User $processedBy = null,
        bool $reinstatementEligible = true
    ): StudentStatusRecord {
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Voluntary withdrawal is only applicable to undergraduate students.');
        }

        $processedBy = $this->authorizeStatusAction('process-voluntary', $processedBy, $user);

        if (!$this->isValidAcademicSession($academicSession)) {
            throw new \InvalidArgumentException("Invalid academic session format: {$academicSession}");
        }

        if ($semester !== null && !in_array($semester, [1, 2], true)) {
            throw new \InvalidArgumentException('Semester must be 1 or 2.');
        }

        $effDate = $effectiveDate ? Carbon::parse($effectiveDate) : now();

        // If Senate reference provided, validate it
        if ($senateReference !== null && !$this->isValidSenateReference($senateReference)) {
            throw new \InvalidArgumentException("Invalid Senate reference format: {$senateReference}");
        }

        return DB::transaction(function () use (
            $user,
            $reason,
            $academicSession,
            $semester,
            $effDate,
            $senateReference,
            $processedBy,
            $reinstatementEligible
        ) {
            $isApproved = $senateReference !== null;

            if ($isApproved) {
                // Close previous active status only if official Senate approval is attached
                $this->closePreviousActiveStatus($user->id, $effDate, $processedBy, 'ACTIVE_STATUS_CLOSED_FOR_VOLUNTARY_WITHDRAWAL');
            }

            $record = StudentStatusRecord::create([
                'user_id' => $user->id,
                'academic_detail_id' => $user->academicDetail?->id,
                'status' => StudentStatus::VOLUNTARY_WITHDRAWAL,
                'status_type' => StudentStatusType::VOLUNTARY,
                'reason_code' => 'VOLUNTARY',
                'reason' => $reason,
                'academic_session' => $academicSession,
                'semester' => $semester,
                'effective_date' => $effDate->toDateString(),
                'senate_decision' => $isApproved ? self::WORKFLOW_SENATE_APPROVED : self::WORKFLOW_RECOMMENDED,
                'senate_reference' => $senateReference,
                'senate_decision_date' => $isApproved ? $effDate->toDateString() : null,
                'reinstatement_eligible' => $reinstatementEligible,
                'processed_by' => $processedBy?->id,
            ]);
            $this->writeStatusAudit($user, $record, 'VOLUNTARY_WITHDRAWAL_PROCESSED', $processedBy, [
                'new_status' => $record->status,
                'new_decision' => $record->senate_decision,
                'reason' => $record->reason,
                'senate_reference' => $senateReference,
                'reinstatement_eligible' => $record->reinstatement_eligible,
            ]);

            Log::info('Voluntary withdrawal processed', [
                'user_id' => $user->id,
                'matric_no' => $user->academicDetail?->matric_no,
                'academic_session' => $academicSession,
                'senate_reference' => $senateReference,
                'processed_by' => $processedBy?->id,
            ]);

            return $record;
        });
    }

    // =========================================================================
    // Medical Withdrawal
    // =========================================================================

    /**
     * Process a medical withdrawal request.
     *
     * Medical withdrawals typically require documentation and may require
     * Senate approval depending on institutional policy.
     *
     * @param User $user The student
     * @param string $reason The medical reason
     * @param string $academicSession The academic session
     * @param int|null $semester The semester (1 or 2), null if session-wide
     * @param Carbon|string|null $effectiveDate When the withdrawal takes effect
     * @param string|null $senateReference Senate reference if Senate approval confirmed
     * @param User|null $processedBy The user processing the withdrawal
     * @return StudentStatusRecord The created medical withdrawal record
     */
    public function processMedicalWithdrawal(
        User $user,
        string $reason,
        string $academicSession,
        ?int $semester = null,
        \DateTimeInterface|string|null $effectiveDate = null,
        ?string $senateReference = null,
        ?User $processedBy = null,
        bool $reinstatementEligible = true
    ): StudentStatusRecord {
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Medical withdrawal is only applicable to undergraduate students.');
        }

        $processedBy = $this->authorizeStatusAction('process-medical', $processedBy, $user);

        if (!$this->isValidAcademicSession($academicSession)) {
            throw new \InvalidArgumentException("Invalid academic session format: {$academicSession}");
        }

        if ($semester !== null && !in_array($semester, [1, 2], true)) {
            throw new \InvalidArgumentException('Semester must be 1 or 2.');
        }

        $effDate = $effectiveDate ? Carbon::parse($effectiveDate) : now();

        // If Senate reference provided, validate it
        if ($senateReference !== null && !$this->isValidSenateReference($senateReference)) {
            throw new \InvalidArgumentException("Invalid Senate reference format: {$senateReference}");
        }

        return DB::transaction(function () use (
            $user,
            $reason,
            $academicSession,
            $semester,
            $effDate,
            $senateReference,
            $processedBy,
            $reinstatementEligible
        ) {
            $isApproved = $senateReference !== null;

            if ($isApproved) {
                // Close previous active status only if official Senate approval is attached
                $this->closePreviousActiveStatus($user->id, $effDate, $processedBy, 'ACTIVE_STATUS_CLOSED_FOR_MEDICAL_WITHDRAWAL');
            }

            $record = StudentStatusRecord::create([
                'user_id' => $user->id,
                'academic_detail_id' => $user->academicDetail?->id,
                'status' => StudentStatus::MEDICAL_WITHDRAWAL,
                'status_type' => StudentStatusType::MEDICAL,
                'reason_code' => 'MEDICAL',
                'reason' => $reason,
                'academic_session' => $academicSession,
                'semester' => $semester,
                'effective_date' => $effDate->toDateString(),
                'senate_decision' => $isApproved ? self::WORKFLOW_SENATE_APPROVED : self::WORKFLOW_RECOMMENDED,
                'senate_reference' => $senateReference,
                'senate_decision_date' => $isApproved ? $effDate->toDateString() : null,
                'reinstatement_eligible' => $reinstatementEligible,
                'processed_by' => $processedBy?->id,
            ]);
            $this->writeStatusAudit($user, $record, 'MEDICAL_WITHDRAWAL_PROCESSED', $processedBy, [
                'new_status' => $record->status,
                'new_decision' => $record->senate_decision,
                'reason' => $record->reason,
                'senate_reference' => $senateReference,
                'reinstatement_eligible' => $record->reinstatement_eligible,
            ]);

            Log::info('Medical withdrawal processed', [
                'user_id' => $user->id,
                'matric_no' => $user->academicDetail?->matric_no,
                'academic_session' => $academicSession,
                'senate_reference' => $senateReference,
                'processed_by' => $processedBy?->id,
            ]);

            return $record;
        });
    }

    // =========================================================================
    // Current Status Query
    // =========================================================================

    /**
     * Get the current authoritative status for a student.
     *
     * Returns the most recent active status record, or null if the student
     * has no status history (assumed ACTIVE).
     *
     * @param User $user The student
     * @param Carbon|string|null $date Optional date to check effective status at (defaults to now)
     * @return StudentStatusRecord|null The current status record, or null
     */
    public function getCurrentStatus(User $user, \DateTimeInterface|string|null $date = null): ?StudentStatusRecord
    {
        $targetDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        return StudentStatusRecord::where('user_id', $user->id)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_date')
                    ->orWhere('effective_date', '<=', $targetDate);
            })
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $targetDate);
            })
            ->whereIn('senate_decision', [self::WORKFLOW_SENATE_APPROVED, 'APPROVED'])
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Resolve authoritative current statuses for a group without issuing one
     * status query per student. The selection rules match getCurrentStatus().
     *
     * @param iterable<string> $userIds
     * @return Collection<string, StudentStatusRecord>
     */
    public function getCurrentStatuses(iterable $userIds, \DateTimeInterface|string|null $date = null): Collection
    {
        $ids = collect($userIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return new Collection();
        }

        $targetDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        return StudentStatusRecord::whereIn('user_id', $ids)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_date')->orWhere('effective_date', '<=', $targetDate);
            })
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $targetDate);
            })
            ->whereIn('senate_decision', [self::WORKFLOW_SENATE_APPROVED, 'APPROVED'])
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');
    }

    /**
     * Batch the activity gate for loaded students, for use in result tables
     * and imports where a per-student lookup would otherwise create N+1 queries.
     *
     * @param iterable<User> $students
     * @return \Illuminate\Support\Collection<string, bool>
     */
    public function getActivityEligibilityForStudents(
        iterable $students,
        AcademicActivity|string $activity,
        \DateTimeInterface|string|null $date = null
    ): \Illuminate\Support\Collection {
        $activityEnum = $activity instanceof AcademicActivity
            ? $activity
            : AcademicActivity::tryFrom((string) $activity);
        $users = collect($students)->filter(fn ($student) => $student instanceof User)->keyBy('id');

        if ($users->isEmpty()) {
            return collect();
        }

        if (!$activityEnum) {
            return $users->mapWithKeys(fn (User $student) => [$student->id => false]);
        }

        $statuses = $this->getCurrentStatuses($users->keys(), $date);

        return $users->mapWithKeys(function (User $student) use ($activityEnum, $statuses) {
            $status = $statuses->get($student->id);
            $allowed = !$student->isUndergraduate() || !$status || $status->status->isActive();

            return [$student->id => $allowed];
        });
    }

    /**
     * Get the student's status for a specific academic session (and optionally semester).
     *
     * @param User $user The student
     * @param string $academicSession The academic session
     * @param int|null $semester Optional semester filter
     * @return StudentStatusRecord|null The status record for that session, or null
     */
    public function getStatusForSession(User $user, string $academicSession, ?int $semester = null): ?StudentStatusRecord
    {
        $query = StudentStatusRecord::where('user_id', $user->id)
            ->where('academic_session', $academicSession)
            ->whereIn('senate_decision', [self::WORKFLOW_SENATE_APPROVED, 'APPROVED']);

        if ($semester !== null) {
            $query->where(function ($q) use ($semester) {
                $q->whereNull('semester')->orWhere('semester', $semester);
            });
        }

        return $query->orderByDesc('effective_date')->orderByDesc('id')->first();
    }

    /**
     * Get the latest Senate-approved status for each student in a session.
     * The keyed result supports report generation without one status query per row.
     *
     * @param iterable<string> $userIds
     * @return Collection<string, StudentStatusRecord>
     */
    public function getStatusesForSession(iterable $userIds, string $academicSession): Collection
    {
        return StudentStatusRecord::whereIn('user_id', $userIds)
            ->where('academic_session', $academicSession)
            ->whereIn('senate_decision', [self::WORKFLOW_SENATE_APPROVED, 'APPROVED'])
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');
    }

    /**
     * Get the complete status timeline for a student.
     *
     * Returns all status records in chronological order, providing a complete
     * audit trail of the student's institutional status history.
     *
     * @param User $user The student
     * @return Collection<int, StudentStatusRecord> The status history
     */
    public function getStatusHistory(User $user): Collection
    {
        return StudentStatusRecord::where('user_id', $user->id)
            ->orderBy('effective_date')
            ->orderBy('id')
            ->get();
    }

    // =========================================================================
    // Activity Status Checks
    // =========================================================================

    /**
     * Check if a student is academically active.
     *
     * A student is considered academically active if:
     * - They have no status history (assumed ACTIVE)
     * - Their current status is ACTIVE or REINSTATED
     * - Their current status is not withdrawn, suspended, or expelled
     *
     * @param User $user The student
     * @param \DateTimeInterface|string|null $date Optional date context
     * @return bool True if academically active
     */
    public function isAcademicallyActive(User $user, \DateTimeInterface|string|null $date = null): bool
    {
        $currentStatus = $this->getCurrentStatus($user, $date);

        // No status history implies active student
        if ($currentStatus === null) {
            return true;
        }

        return $currentStatus->status->isActive();
    }

    /**
     * Check if a student is authorized to perform a specific academic activity based on institutional status.
     *
     * @param User|string|int $student User instance, UUID, or ID
     * @param AcademicActivity|string $activity AcademicActivity enum case or string value
     * @param \DateTimeInterface|string|null $date Optional date context
     * @return bool True if authorized, false if blocked by student status
     */
    public function canPerformAcademicActivity(
        User|string|int $student,
        AcademicActivity|string $activity,
        \DateTimeInterface|string|null $date = null
    ): bool {
        $user = $student instanceof User ? $student : User::find($student);

        if (!$user) {
            return false;
        }

        $activityEnum = $activity instanceof AcademicActivity
            ? $activity
            : AcademicActivity::tryFrom((string) $activity);

        if (!$activityEnum) {
            return false;
        }

        // Phase 6.7 status rules are UG-only; leave PG academic workflows untouched.
        if (!$user->isUndergraduate()) {
            return true;
        }

        // Active students (or students with no status records) can perform all academic activities
        return $this->isAcademicallyActive($user, $date);
    }

    /**
     * Check if a student is eligible for reinstatement.
     *
     * Eligibility is determined by the reinstatement_eligible flag on the
     * most recent withdrawal/suspension record. An already-active student is not eligible.
     *
     * @param User $user The student
     * @return bool True if eligible for reinstatement
     */
    public function isEligibleForReinstatement(User $user): bool
    {
        $currentStatus = $this->getCurrentStatus($user);

        if ($currentStatus === null) {
            return false; // No withdrawal to reinstate from
        }

        // Active students do not need reinstatement
        if ($currentStatus->status->isActive()) {
            return false;
        }

        return (bool) $currentStatus->reinstatement_eligible;
    }

    /** Start an auditable reinstatement request for an eligible UG student. */
    public function requestReinstatement(
        User $user,
        ?User $requestedBy = null,
        ?string $notes = null
    ): StudentStatusRecord {
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Reinstatement is only applicable to undergraduate students.');
        }

        $requestedBy = $this->authorizeStatusAction('request-reinstatement', $requestedBy, $user);

        if (!$this->isEligibleForReinstatement($user)) {
            throw new \InvalidArgumentException('The student does not have an eligible active withdrawal to reinstate from.');
        }

        $withdrawal = $this->getCurrentStatus($user);
        if (!$withdrawal || !$withdrawal->status->isWithdrawn()) {
            throw new \InvalidArgumentException('Reinstatement can only be requested from an approved withdrawal status.');
        }

        return DB::transaction(function () use ($user, $withdrawal, $requestedBy, $notes) {
            $request = StudentStatusRecord::create([
                'user_id' => $user->id,
                'academic_detail_id' => $user->academicDetail?->id,
                'status' => StudentStatus::REINSTATED,
                'status_type' => $withdrawal->status_type,
                'reason_code' => 'REINSTATEMENT_REQUEST',
                'reason' => 'Reinstatement requested following ' . $withdrawal->status->label() . '.',
                'academic_session' => $withdrawal->academic_session,
                'semester' => $withdrawal->semester,
                'effective_date' => now()->toDateString(),
                'senate_decision' => self::REINSTATEMENT_REQUESTED,
                'reinstatement_eligible' => false,
                'processed_by' => $requestedBy->id,
                'notes' => $notes,
            ]);
            $this->writeStatusAudit($user, $request, 'REINSTATEMENT_REQUESTED', $requestedBy, [
                'old_status' => $withdrawal->status,
                'new_status' => $request->status,
                'old_decision' => $withdrawal->senate_decision,
                'new_decision' => $request->senate_decision,
                'reason' => $request->reason,
            ]);

            return $request;
        });
    }

    /** Record sequential Department and Faculty review of a reinstatement request. */
    public function reviewReinstatement(
        StudentStatusRecord $request,
        string $stage,
        bool $approved,
        ?User $reviewedBy = null,
        ?string $notes = null
    ): StudentStatusRecord {
        $stage = strtoupper($stage);
        $expectedDecision = match ($stage) {
            'DEPARTMENT' => self::REINSTATEMENT_REQUESTED,
            'FACULTY' => self::REINSTATEMENT_DEPARTMENT_APPROVED,
            default => throw new \InvalidArgumentException('Review stage must be DEPARTMENT or FACULTY.'),
        };

        if ($request->reason_code !== 'REINSTATEMENT_REQUEST' || $request->senate_decision !== $expectedDecision) {
            throw new \InvalidArgumentException("The reinstatement request is not ready for {$stage} review.");
        }

        $student = User::findOrFail($request->user_id);
        $ability = $stage === 'DEPARTMENT'
            ? 'review-department-reinstatement'
            : 'review-faculty-reinstatement';
        $reviewedBy = $this->authorizeStatusAction($ability, $reviewedBy, $student);

        $decision = $approved
            ? ($stage === 'DEPARTMENT' ? self::REINSTATEMENT_DEPARTMENT_APPROVED : self::REINSTATEMENT_FACULTY_APPROVED)
            : ($stage === 'DEPARTMENT' ? self::REINSTATEMENT_DEPARTMENT_REJECTED : self::REINSTATEMENT_FACULTY_REJECTED);

        $oldDecision = $request->senate_decision;

        return DB::transaction(function () use ($request, $decision, $reviewedBy, $notes, $student, $stage, $oldDecision) {
            $request->update([
                'senate_decision' => $decision,
                'senate_decision_date' => now()->toDateString(),
                'processed_by' => $reviewedBy->id,
                'notes' => $notes ?? $request->notes,
            ]);
            $this->writeStatusAudit($student, $request, "REINSTATEMENT_{$stage}_" . ($decision === self::REINSTATEMENT_DEPARTMENT_APPROVED || $decision === self::REINSTATEMENT_FACULTY_APPROVED ? 'APPROVED' : 'REJECTED'), $reviewedBy, [
                'old_status' => $request->status,
                'new_status' => $request->status,
                'old_decision' => $oldDecision,
                'new_decision' => $decision,
                'reason' => $notes,
            ]);

            return $request->fresh();
        });
    }

    /** Determine return placement from the existing UG progression rules. */
    public function determineReinstatementLevel(User $user, ?StudentStatusRecord $withdrawal = null): array
    {
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Reinstatement placement is only applicable to undergraduate students.');
        }

        $withdrawal ??= $this->getCurrentStatus($user);
        if (!$withdrawal
            || (string) $withdrawal->user_id !== (string) $user->id
            || !$withdrawal->status->isWithdrawn()
            || !in_array($withdrawal->senate_decision, [self::WORKFLOW_SENATE_APPROVED, 'APPROVED'], true)) {
            throw new \InvalidArgumentException('A current approved withdrawal is required to determine reinstatement placement.');
        }

        $levelId = app(AcademicProgressionService::class)->getNextEligibleLevel($user);
        $level = \App\Models\StudentLevel::find($levelId);
        $session = $this->nextAcademicSession($withdrawal->academic_session);

        return [
            'student_level_id' => $levelId,
            'level' => $level?->level ?? (string) $levelId,
            'academic_session' => $session,
        ];
    }

    /** Finalize the Senate decision; approval appends a distinct REINSTATED event. */
    public function processReinstatement(
        StudentStatusRecord $request,
        bool $approved,
        string $senateReference,
        \DateTimeInterface|string|null $senateDecisionDate = null,
        ?User $processedBy = null,
        ?string $decisionNotes = null
    ): StudentStatusRecord {
        if ($request->reason_code !== 'REINSTATEMENT_REQUEST'
            || $request->senate_decision !== self::REINSTATEMENT_FACULTY_APPROVED) {
            throw new \InvalidArgumentException('Reinstatement must pass Department and Faculty review before Senate processing.');
        }

        if (!$this->isValidSenateReference($senateReference)) {
            throw new \InvalidArgumentException("Invalid Senate reference format: {$senateReference}");
        }

        $student = User::findOrFail($request->user_id);
        $processedBy = $this->authorizeStatusAction('decide-senate', $processedBy, $student);
        $withdrawal = $this->getCurrentStatus($student);
        if (!$withdrawal || !$withdrawal->status->isWithdrawn() || !$withdrawal->reinstatement_eligible) {
            throw new \InvalidArgumentException('The student no longer has an eligible withdrawal to reinstate from.');
        }

        $decisionDate = $senateDecisionDate ? Carbon::parse($senateDecisionDate) : now();
        $placement = $approved ? $this->determineReinstatementLevel($student, $withdrawal) : null;

        return DB::transaction(function () use ($request, $student, $withdrawal, $approved, $senateReference, $decisionDate, $processedBy, $decisionNotes, $placement) {
            $decision = $approved ? self::REINSTATEMENT_APPROVED : self::REINSTATEMENT_REJECTED;
            $oldDecision = $request->senate_decision;

            // Keep the request as its own audit entry and append the final decision event.
            $request->update([
                'senate_decision' => $decision,
                'senate_reference' => $senateReference,
                'senate_decision_date' => $decisionDate->toDateString(),
                'processed_by' => $processedBy?->id ?? $request->processed_by,
                'notes' => $decisionNotes ?? $request->notes,
            ]);

            $event = StudentStatusRecord::create([
                'user_id' => $student->id,
                'academic_detail_id' => $student->academicDetail?->id,
                'status' => $approved ? StudentStatus::REINSTATED : $withdrawal->status,
                'status_type' => $withdrawal->status_type,
                'reason_code' => $approved ? 'SENATE_REINSTATEMENT' : 'REINSTATEMENT_REJECTED',
                'reason' => $approved ? 'Reinstatement approved by Senate.' : 'Reinstatement request rejected by Senate.',
                'academic_session' => $placement['academic_session'] ?? $withdrawal->academic_session,
                'semester' => null,
                'effective_date' => $decisionDate->toDateString(),
                'senate_reference' => $senateReference,
                'senate_decision_date' => $decisionDate->toDateString(),
                'senate_decision' => $approved ? self::WORKFLOW_SENATE_APPROVED : self::REINSTATEMENT_REJECTED,
                'reinstatement_eligible' => !$approved && (bool) $withdrawal->reinstatement_eligible,
                'processed_by' => $processedBy?->id ?? $request->processed_by,
                'notes' => $approved
                    ? trim(($decisionNotes ? $decisionNotes . "\n" : '') . 'Placement: ' . $placement['level'] . ' (level id ' . $placement['student_level_id'] . ').')
                    : $decisionNotes,
            ]);
            $this->writeStatusAudit($student, $event, $approved ? 'REINSTATEMENT_APPROVED' : 'REINSTATEMENT_REJECTED', $processedBy, [
                'old_status' => $withdrawal->status,
                'new_status' => $event->status,
                'old_decision' => $oldDecision,
                'new_decision' => $decision,
                'reason' => $decisionNotes ?? $event->reason,
                'senate_reference' => $senateReference,
                'request_record_id' => $request->id,
            ]);

            if ($approved) {
                $student->academicDetail?->update([
                    'student_level_id' => $placement['student_level_id'],
                    'acad_session' => $placement['academic_session'],
                ]);
            }

            Log::info('Reinstatement Senate decision recorded', [
                'user_id' => $student->id,
                'request_record_id' => $request->id,
                'decision_record_id' => $event->id,
                'approved' => $approved,
                'senate_reference' => $senateReference,
                'processed_by' => $processedBy?->id,
            ]);

            return $event;
        });
    }

    private function nextAcademicSession(string $session): string
    {
        if (!$this->isValidAcademicSession($session)) {
            throw new \InvalidArgumentException("Invalid academic session format: {$session}");
        }

        [$start] = array_map('intval', explode('/', $session));
        return ($start + 1) . '/' . ($start + 2);
    }

    // =========================================================================
    // Public Validation Methods
    // =========================================================================

    /**
     * Validate academic session format (YYYY/YYYY).
     */
    public function isValidAcademicSession(string $session): bool
    {
        return (bool) preg_match('/^\d{4}\/\d{4}$/', $session);
    }

    /**
     * Validate Senate reference format.
     *
     * Standard formats supported:
     * - SEN-YYYY-NNN (e.g. SEN-2026-104)
     * - SEN/YYYY/NNN (e.g. SEN/2026/104)
     * - SEN-YYYY/YYYY-NNN (e.g. SEN-2025/2026-001)
     */
    public function isValidSenateReference(string $reference): bool
    {
        return (bool) preg_match('/^SEN(?:-\d{4}-\d{1,6}|\/\d{4}\/\d{1,6}|-\d{4}\/\d{4}-\d{1,6})$/i', $reference);
    }

    // =========================================================================
    // Private Helper Methods
    // =========================================================================

    private function authorizeStatusAction(string $ability, ?User $actor, User $student): User
    {
        $authenticatedActor = auth()->user();
        if ($authenticatedActor instanceof User && $actor instanceof User
            && (string) $authenticatedActor->id !== (string) $actor->id) {
            throw new AuthorizationException('Student-status actions must be performed by the authenticated user.');
        }

        $actor ??= $authenticatedActor;
        if (!$actor instanceof User) {
            throw new AuthorizationException('An authenticated authorized officer is required for this student-status action.');
        }

        Gate::forUser($actor)->authorize('student-status.' . $ability, $student);

        return $actor;
    }

    /** Persist append-only audit evidence in the same transaction as each status decision. */
    private function writeStatusAudit(
        User $student,
        ?StudentStatusRecord $record,
        string $action,
        User $actor,
        array $changes = []
    ): StudentStatusAudit {
        $request = app()->bound('request') ? request() : null;
        $metadata = $changes;
        unset($metadata['old_status'], $metadata['new_status'], $metadata['old_decision'], $metadata['new_decision'], $metadata['reason'], $metadata['senate_reference']);

        return StudentStatusAudit::create([
            'student_id' => $student->id,
            'actor_id' => $actor->id,
            'student_status_record_id' => $record?->id,
            'action' => $action,
            'old_status' => $this->enumValue($changes['old_status'] ?? null),
            'new_status' => $this->enumValue($changes['new_status'] ?? $record?->status),
            'old_decision' => $changes['old_decision'] ?? null,
            'new_decision' => $changes['new_decision'] ?? $record?->senate_decision,
            'reason' => $changes['reason'] ?? $record?->reason,
            'senate_reference' => $changes['senate_reference'] ?? $record?->senate_reference,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => now(),
        ]);
    }

    private function enumValue(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }

    /**
     * Close any previous active status for a student.
     *
     * When a new status becomes active, the previous status should have its
     * end_date set to maintain a clean timeline.
     */
    private function closePreviousActiveStatus(
        string $userId,
        \DateTimeInterface|string $effectiveDate,
        User $actor,
        string $action
    ): void
    {
        $dateStr = $effectiveDate instanceof \DateTimeInterface
            ? $effectiveDate->format('Y-m-d')
            : Carbon::parse($effectiveDate)->toDateString();

        $records = StudentStatusRecord::where('user_id', $userId)
            ->whereIn('senate_decision', [self::WORKFLOW_SENATE_APPROVED, 'APPROVED'])
            ->whereNull('end_date')
            ->get();

        foreach ($records as $record) {
            $oldEndDate = $record->end_date?->toDateString();
            $record->update(['end_date' => $dateStr]);
            $this->writeStatusAudit($record->user, $record, $action, $actor, [
                'old_status' => $record->status,
                'new_status' => $record->status,
                'old_decision' => $record->senate_decision,
                'new_decision' => $record->senate_decision,
                'old_end_date' => $oldEndDate,
                'new_end_date' => $dateStr,
            ]);
        }
    }

    /**
     * Update academic progression record with withdrawal recommendation flag.
     */
    private function updateProgressionRecordWithRecommendation(
        User $user,
        string $academicSession,
        ?int $semester,
        array $progressionInfo
    ): void {
        // Find or create progression record for this session/semester
        $query = AcademicProgressionRecord::where('user_id', $user->id)
            ->where('academic_session', $academicSession);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        $progressionRecord = $query->first();

        if ($progressionRecord) {
            $progressionRecord->update([
                'withdrawal_recommended' => true,
            ]);
        } elseif ($user->academicDetail) {
            // Create new progression record if academic detail exists
            AcademicProgressionRecord::create([
                'user_id' => $user->id,
                'academic_detail_id' => $user->academicDetail->id,
                'academic_session' => $academicSession,
                'semester' => $semester ?? 1,
                'level' => (string) ($user->academicDetail->student_level_id ?? '100'),
                'cgpa' => $progressionInfo['cgpa'] ?? 0.00,
                'standing' => $progressionInfo['standing'] ?? AcademicProgressionService::STANDING_PROMOTED,
                'withdrawal_recommended' => true,
            ]);
        }
    }
}
