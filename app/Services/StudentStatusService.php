<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AcademicActivity;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        \DateTimeInterface|string|null $effectiveDate = null
    ): StudentStatusRecord {
        // Validate that user is undergraduate
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Withdrawal recommendations are only applicable to undergraduate students.');
        }

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
            $effDate
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
                'reinstatement_eligible' => true, // Default eligibility
                'processed_by' => $processedBy?->id,
                'notes' => $notes,
            ]);

            // Update academic progression record to flag withdrawal recommendation
            $this->updateProgressionRecordWithRecommendation($user, $academicSession, $semester, $progressionInfo);

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

        return DB::transaction(function () use ($recommendation, $submittedBy) {
            $recommendation->update([
                'senate_decision' => self::WORKFLOW_PENDING_SENATE,
                'processed_by' => $submittedBy?->id ?? $recommendation->processed_by,
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
            $this->closePreviousActiveStatus($recommendation->user_id, $effDate);

            // Approve the withdrawal
            $recommendation->update([
                'senate_decision' => self::WORKFLOW_SENATE_APPROVED,
                'senate_reference' => $senateReference,
                'senate_decision_date' => $decisionDate->toDateString(),
                'effective_date' => $effDate instanceof Carbon ? $effDate->toDateString() : (string) $effDate,
                'notes' => $senateDecisionDetails ?? $recommendation->notes,
                'processed_by' => $approvedBy?->id ?? $recommendation->processed_by,
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
        ?User $rejectedBy = null
    ): StudentStatusRecord {
        if ($recommendation->senate_decision !== self::WORKFLOW_PENDING_SENATE) {
            throw new \InvalidArgumentException('Only pending Senate decisions can be rejected.');
        }

        // Normalize date
        $decisionDate = $senateDecisionDate ? Carbon::parse($senateDecisionDate) : now();

        return DB::transaction(function () use ($recommendation, $rejectionReason, $decisionDate, $rejectedBy) {
            $recommendation->update([
                'senate_decision' => self::WORKFLOW_SENATE_REJECTED,
                'senate_decision_date' => $decisionDate->toDateString(),
                'notes' => $rejectionReason,
                'processed_by' => $rejectedBy?->id ?? $recommendation->processed_by,
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
        ?User $processedBy = null
    ): StudentStatusRecord {
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Voluntary withdrawal is only applicable to undergraduate students.');
        }

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
            $processedBy
        ) {
            $isApproved = $senateReference !== null;

            if ($isApproved) {
                // Close previous active status only if official Senate approval is attached
                $this->closePreviousActiveStatus($user->id, $effDate);
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
                'reinstatement_eligible' => true,
                'processed_by' => $processedBy?->id,
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
        ?User $processedBy = null
    ): StudentStatusRecord {
        if (!$user->isUndergraduate()) {
            throw new \InvalidArgumentException('Medical withdrawal is only applicable to undergraduate students.');
        }

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
            $processedBy
        ) {
            $isApproved = $senateReference !== null;

            if ($isApproved) {
                // Close previous active status only if official Senate approval is attached
                $this->closePreviousActiveStatus($user->id, $effDate);
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
                'reinstatement_eligible' => true,
                'processed_by' => $processedBy?->id,
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
        return (bool) preg_match('/^SEN[-\/]\d{4}(?:\/\d{4})?[-\/]\d+$/i', $reference);
    }

    // =========================================================================
    // Private Helper Methods
    // =========================================================================

    /**
     * Close any previous active status for a student.
     *
     * When a new status becomes active, the previous status should have its
     * end_date set to maintain a clean timeline.
     */
    private function closePreviousActiveStatus(string $userId, \DateTimeInterface|string $effectiveDate): void
    {
        $dateStr = $effectiveDate instanceof \DateTimeInterface
            ? $effectiveDate->format('Y-m-d')
            : Carbon::parse($effectiveDate)->toDateString();

        StudentStatusRecord::where('user_id', $userId)
            ->whereIn('senate_decision', [self::WORKFLOW_SENATE_APPROVED, 'APPROVED'])
            ->whereNull('end_date')
            ->update(['end_date' => $dateStr]);
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
