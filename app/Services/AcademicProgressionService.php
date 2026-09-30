<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\CarryOverCourse;
use App\Models\DisciplinaryAction;
use App\Models\Programme;
use App\Models\ResultGpaRecord;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Collection;

class AcademicProgressionService
{
    public const STANDING_PROMOTED = 'PROMOTED';
    public const STANDING_PROBATION = 'PROBATION';
    public const STANDING_REPEAT = 'REPEAT';
    public const STANDING_SPILLOVER = 'SPILLOVER';
    public const STANDING_FRESH = 'FRESH';

    /**
     * Determine the academic standing of an undergraduate student based on approved session results.
     */
    public function determineAcademicStanding(User $user): array
    {
        if (!$user->isUndergraduate()) {
            return [
                'standing' => self::STANDING_PROMOTED,
                'cgpa' => 0.0,
                'has_uncleared_carryovers' => false,
                'reason' => 'Postgraduate or Non-UG programme',
            ];
        }

        $academicDetail = $user->academicDetail;
        if (!$academicDetail) {
            return [
                'standing' => self::STANDING_FRESH,
                'cgpa' => 0.0,
                'has_uncleared_carryovers' => false,
                'reason' => 'Fresh admitted student',
            ];
        }

        // Get latest GPA record
        $latestGpaRecord = ResultGpaRecord::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $hasUnclearedCarryOvers = CarryOverCourse::where('user_id', $user->id)
            ->active()
            ->exists();
        $currentSession = app(AcademicSessionService::class)->getAcademicSession($user);
        $hasSanctionedRepeat = DisciplinaryAction::query()
            ->where('user_id', $user->id)
            ->where('sanction_type', 'repeat_session')
            ->where('is_active', true)
            ->where('effective_session', $currentSession)
            ->exists();

        if (!$latestGpaRecord) {
            return [
                'standing' => $hasSanctionedRepeat ? self::STANDING_REPEAT : self::STANDING_PROMOTED,
                'cgpa' => 0.0,
                'has_uncleared_carryovers' => $hasUnclearedCarryOvers,
                'reason' => $hasSanctionedRepeat
                    ? "Senate disciplinary decision requires the student to repeat the effective session ({$currentSession})."
                    : 'No session GPA records found; default progression',
            ];
        }

        $cgpa = (float) $latestGpaRecord->cumulative_gpa;
        $currentLevel = (int) ($academicDetail->student_level_id ?? 1);
        $maxLevel = $this->getMaxProgramLevel($user);

        if ($hasSanctionedRepeat) {
            return [
                'standing' => self::STANDING_REPEAT,
                'cgpa' => $cgpa,
                'has_uncleared_carryovers' => $hasUnclearedCarryOvers,
                'reason' => "Senate disciplinary decision requires the student to repeat the effective session ({$currentSession}).",
            ];
        }

        // Check if student is in their final year with uncleared carry overs
        if ($currentLevel >= $maxLevel && $hasUnclearedCarryOvers) {
            return [
                'standing' => self::STANDING_SPILLOVER,
                'cgpa' => $cgpa,
                'has_uncleared_carryovers' => true,
                'reason' => "Final year student with uncleared carry-over courses (Spillover at Level {$maxLevel})",
            ];
        }

        // Standard NUC Progression Thresholds
        if ($cgpa >= 1.50) {
            return [
                'standing' => self::STANDING_PROMOTED,
                'cgpa' => $cgpa,
                'has_uncleared_carryovers' => $hasUnclearedCarryOvers,
                'reason' => 'Good Academic Standing (CGPA >= 1.50)',
            ];
        }

        if ($cgpa >= 1.00) {
            return [
                'standing' => self::STANDING_PROBATION,
                'cgpa' => $cgpa,
                'has_uncleared_carryovers' => $hasUnclearedCarryOvers,
                'reason' => 'Probation (1.00 <= CGPA < 1.50)',
            ];
        }

        return [
            'standing' => self::STANDING_REPEAT,
            'cgpa' => $cgpa,
            'has_uncleared_carryovers' => $hasUnclearedCarryOvers,
            'reason' => 'Repeat Level / Poor Academic Standing (CGPA < 1.00)',
        ];
    }

    /**
     * Get the next eligible student level for undergraduate school fees and registration.
     */
    public function getNextEligibleLevel(User $user): int
    {
        if (!$user->isUndergraduate()) {
            // For postgraduate / other roles, fallback to current or level 1
            return (int) ($user->academicDetail?->student_level_id ?? 1);
        }

        $academicDetail = $user->academicDetail;

        // Fresh student
        if (!$academicDetail) {
            // DE students start at level 2 (200 Level), UTME at level 1 (100 Level)
            return $user->isDe ? 2 : 1;
        }

        $currentLevel = (int) ($academicDetail->student_level_id ?? 1);
        $maxLevel = $this->getMaxProgramLevel($user);
        $standingInfo = $this->determineAcademicStanding($user);
        $standing = $standingInfo['standing'];

        switch ($standing) {
            case self::STANDING_REPEAT:
            case self::STANDING_SPILLOVER:
                // Retain current level (capped at max level)
                return min($currentLevel, $maxLevel);

            case self::STANDING_PROBATION:
            case self::STANDING_PROMOTED:
            default:
                // Advance to next level, but never exceed max program level
                $nextLevel = $currentLevel + 1;
                return min($nextLevel, $maxLevel);
        }
    }

    /**
     * Get the maximum level for the user's programme (e.g. 4 for 4-year, 5 for 5-year).
     */
    public function getMaxProgramLevel(User $user): int
    {
        $course = $user->academicDetail?->course ?? $user->proposedCourse?->course;
        if ($course && !empty($course->semesters)) {
            $semesters = (int) $course->semesters;
            $duration = $semesters > 0 ? (int) ceil($semesters / 2) : 4;
            return $duration > 0 ? $duration : 4;
        }

        return 4;
    }

    /**
     * Apply a Senate-mandated repeat outcome while retaining the previous progression
     * values for an auditable reversal if the sanction is quashed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function applySanctionedRepeat(
        User $student,
        AcademicDetail $academicDetail,
        string $session,
        ?int $semester,
        string $senateReference
    ): array
    {
        $semesters = $semester === null ? [1, 2] : [$semester];
        $latestGpa = ResultGpaRecord::where('user_id', $student->id)->latest('id')->value('cumulative_gpa');
        $level = $academicDetail->studentLevel?->level ?? (string) $academicDetail->student_level_id;
        $snapshots = [];

        foreach ($semesters as $semesterNumber) {
            $record = AcademicProgressionRecord::where('user_id', $student->id)
                ->where('academic_session', $session)
                ->where('semester', $semesterNumber)
                ->latest('id')
                ->first();

            if ($record) {
                $snapshots[] = ['created' => false, 'id' => $record->id, 'values' => $record->only([
                    'academic_session', 'semester', 'level', 'cgpa', 'standing', 'withdrawal_recommended',
                ])];
                $record->update(['standing' => self::STANDING_REPEAT, 'withdrawal_recommended' => false]);
                continue;
            }

            $record = AcademicProgressionRecord::create([
                'user_id' => $student->id,
                'academic_detail_id' => $academicDetail->id,
                'academic_session' => $session,
                'semester' => $semesterNumber,
                'level' => (string) $level,
                'cgpa' => $latestGpa ?? 0.00,
                'standing' => self::STANDING_REPEAT,
                'withdrawal_recommended' => false,
            ]);
            $snapshots[] = ['created' => true, 'id' => $record->id];
        }

        $resultQuery = Result::query()
            ->where('user_id', $student->id)
            ->where('academic_session', $session)
            ->whereIn('status', ['exam_officer_approved', 'released']);
        if ($semester !== null) {
            $resultQuery->where('semester', $semester === 1 ? 'first' : 'second');
        }

        $attemptSnapshots = [];
        foreach ($resultQuery->lockForUpdate()->get() as $result) {
            $note = 'REPEAT SESSION (SDC: ' . $senateReference . ')';
            $appliedRemarks = trim(implode(' | ', array_filter([$result->remarks, $note])));
            $attemptSnapshots[] = [
                'id' => $result->id,
                'remarks_before' => $result->remarks,
                'remarks_after' => $appliedRemarks,
            ];
            $result->update(['remarks' => $appliedRemarks]);
        }

        return ['progression_records' => $snapshots, 'result_attempts' => $attemptSnapshots];
    }

    /** Restore sanctioned repeat progression changes recorded by applySanctionedRepeat. */
    public function restoreSanctionedRepeat(array $snapshots): void
    {
        foreach ($snapshots['progression_records'] ?? [] as $snapshot) {
            $record = AcademicProgressionRecord::find($snapshot['id'] ?? null);
            if (!$record) {
                continue;
            }

            if ($snapshot['created'] ?? false) {
                $record->delete();
                continue;
            }

            $record->update($snapshot['values'] ?? []);
        }

        foreach ($snapshots['result_attempts'] ?? [] as $snapshot) {
            $result = Result::find($snapshot['id'] ?? null);
            if (!$result) {
                continue;
            }
            if ($result->remarks !== ($snapshot['remarks_after'] ?? null)) {
                throw new \LogicException('A repeat-sanctioned result was changed later; manual review is required before reversal.');
            }
            $result->update(['remarks' => $snapshot['remarks_before'] ?? null]);
        }
    }

    // =========================================================================
    // Task 6.7.2: Withdrawal Eligibility Engine
    // =========================================================================

    /**
     * Evaluate whether a student is eligible for an academic withdrawal recommendation.
     *
     * This method is RULE-DRIVEN and reads from config/academic_withdrawal.php.
     * It does NOT automatically withdraw any student — it returns a structured
     * assessment that an authorised officer must act upon.
     *
     * GOVERNANCE:
     *   - PG students always return ['eligible' => false].
     *   - All thresholds are configurable; no hard-coded CGPA is assumed.
     *   - The CGPA minimum rule is disabled by default until institutionally confirmed.
     *   - This method only produces a *recommendation*. Senate approval is
     *     required before any official status change.
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
    public function evaluateWithdrawalEligibility(User $user): array
    {
        // --- PG Guard: must never affect postgraduate workflows ---
        if (!$user->isUndergraduate()) {
            return [
                'eligible'        => false,
                'reason_code'     => null,
                'reason'          => 'Withdrawal eligibility evaluation is not applicable to Postgraduate students.',
                'standing'        => self::STANDING_PROMOTED,
                'cgpa'            => 0.0,
                'triggered_rules' => [],
            ];
        }

        $standingInfo = $this->determineAcademicStanding($user);
        $standing     = $standingInfo['standing'];
        $cgpa         = $standingInfo['cgpa'];

        $triggeredRules  = [];
        $firstReasonCode = null;
        $firstReason     = null;

        // -------------------------------------------------------------------
        // Rule 1: Consecutive Academic Probation
        // -------------------------------------------------------------------
        $rule = config('academic_withdrawal.consecutive_probation', []);
        if (!empty($rule['enabled'])) {
            $count = $this->countConsecutiveStandingSessions(
                $user,
                self::STANDING_PROBATION,
                $rule['unit'] ?? 'session'
            );
            if ($count >= (int) $rule['threshold']) {
                $triggeredRules[] = $rule['reason_code'];
                if ($firstReasonCode === null) {
                    $firstReasonCode = $rule['reason_code'];
                    $firstReason = str_replace('{threshold}', (string) $rule['threshold'], $rule['reason']);
                }
            }
        }

        // -------------------------------------------------------------------
        // Rule 2: Consecutive REPEAT Standing
        // -------------------------------------------------------------------
        $rule = config('academic_withdrawal.consecutive_repeat', []);
        if (!empty($rule['enabled'])) {
            $count = $this->countConsecutiveStandingSessions(
                $user,
                self::STANDING_REPEAT,
                $rule['unit'] ?? 'session'
            );
            if ($count >= (int) $rule['threshold']) {
                $triggeredRules[] = $rule['reason_code'];
                if ($firstReasonCode === null) {
                    $firstReasonCode = $rule['reason_code'];
                    $firstReason = str_replace('{threshold}', (string) $rule['threshold'], $rule['reason']);
                }
            }
        }

        // -------------------------------------------------------------------
        // Rule 3: Minimum CGPA Threshold (DISABLED by default)
        // -------------------------------------------------------------------
        $rule = config('academic_withdrawal.minimum_cgpa', []);
        if (!empty($rule['enabled'])) {
            $threshold = (float) $rule['threshold'];
            if ($cgpa < $threshold) {
                $triggeredRules[] = $rule['reason_code'];
                if ($firstReasonCode === null) {
                    $firstReasonCode = $rule['reason_code'];
                    $firstReason = str_replace(
                        ['{cgpa}', '{threshold}'],
                        [number_format($cgpa, 2), number_format($threshold, 2)],
                        $rule['reason']
                    );
                }
            }
        }

        // -------------------------------------------------------------------
        // Rule 4: Maximum Programme Residency Exceeded
        // -------------------------------------------------------------------
        $rule = config('academic_withdrawal.max_residency_exceeded', []);
        if (!empty($rule['enabled'])) {
            $maxLevel      = $this->getMaxProgramLevel($user);
            $multiplier    = (float) ($rule['multiplier'] ?? 1.5);
            $maxSessions   = (int) ceil($maxLevel * $multiplier);
            $sessionsStudied = $this->countDistinctSessionsWithResults($user);
            if ($sessionsStudied >= $maxSessions) {
                $triggeredRules[] = $rule['reason_code'];
                if ($firstReasonCode === null) {
                    $firstReasonCode = $rule['reason_code'];
                    $firstReason = str_replace('{max_sessions}', (string) $maxSessions, $rule['reason']);
                }
            }
        }

        // -------------------------------------------------------------------
        // Rule 5: Non-Registration Pattern
        // -------------------------------------------------------------------
        $rule = config('academic_withdrawal.non_registration', []);
        if (!empty($rule['enabled'])) {
            $consecutiveNonReg = $this->countConsecutiveNonRegistrationSessions($user);
            if ($consecutiveNonReg >= (int) $rule['threshold']) {
                $triggeredRules[] = $rule['reason_code'];
                if ($firstReasonCode === null) {
                    $firstReasonCode = $rule['reason_code'];
                    $firstReason = str_replace('{threshold}', (string) $rule['threshold'], $rule['reason']);
                }
            }
        }

        // -------------------------------------------------------------------
        // Return structured assessment — never an automatic action
        // -------------------------------------------------------------------
        return [
            'eligible'        => count($triggeredRules) > 0,
            'reason_code'     => $firstReasonCode,
            'reason'          => $firstReason ?? 'No withdrawal eligibility criteria met at this time.',
            'standing'        => $standing,
            'cgpa'            => $cgpa,
            'triggered_rules' => $triggeredRules,
        ];
    }

    // =========================================================================
    // Private Helper Methods (Task 6.7.2)
    // =========================================================================

    /**
     * Count consecutive academic sessions (most recent first) where the
     * student held the given standing.
     *
     * Prefers persisted AcademicProgressionRecord rows (Task 6.7.3+).
     * Falls back to on-the-fly derivation from ResultGpaRecord for
     * students whose progression has not yet been persisted explicitly.
     *
     * @param string $standing  One of the STANDING_* constants.
     * @param string $unit      'session' (default) groups all semesters per year;
     *                          'semester' counts each semester individually.
     */
    private function countConsecutiveStandingSessions(User $user, string $standing, string $unit = 'session'): int
    {
        $records = AcademicProgressionRecord::where('user_id', $user->id)
            ->orderByDesc('academic_session')
            ->orderByDesc('semester')
            ->get();

        if ($records->isEmpty()) {
            $records = $this->buildProgressionSnapshotsFromGpaRecords($user);
        }

        if ($unit === 'semester') {
            $groups = $records->map(fn ($r) => [
                'key'      => ($r->academic_session ?? '') . '-' . ($r->semester ?? ''),
                'standing' => $r->standing,
            ]);
        } else {
            // Group by session, pick worst standing per session
            $groups = $records->groupBy('academic_session')->map(function ($sessionRecords) {
                $standings = $sessionRecords->pluck('standing')->all();
                return ['key' => $sessionRecords->first()->academic_session, 'standing' => $this->worstStanding($standings)];
            })->values();
        }

        $consecutive = 0;
        foreach ($groups as $group) {
            if (($group['standing'] ?? '') === $standing) {
                $consecutive++;
            } else {
                break;
            }
        }

        return $consecutive;
    }

    /**
     * Count distinct academic sessions in which the student has at least one GPA record.
     */
    private function countDistinctSessionsWithResults(User $user): int
    {
        return ResultGpaRecord::where('user_id', $user->id)
            ->distinct('academic_session')
            ->count('academic_session');
    }

    /**
     * Count consecutive trailing sessions (most recent first) where the
     * student has no GPA results (proxy for non-registration / absence).
     */
    private function countConsecutiveNonRegistrationSessions(User $user): int
    {
        $academicDetail = $user->academicDetail;
        if (!$academicDetail) {
            return 0;
        }

        $admissionSession = $academicDetail->admission_session ?? $academicDetail->acad_session;
        if (!$admissionSession) {
            return 0;
        }

        $currentSession = $academicDetail->acad_session;
        if (!$currentSession && class_exists(AcademicSessionService::class)) {
            try {
                $currentSession = app(AcademicSessionService::class)->getAcademicSession($user);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (!$currentSession) {
            return 0;
        }

        $registeredSessions = ResultGpaRecord::where('user_id', $user->id)
            ->pluck('academic_session')
            ->unique();

        $allExpectedSessions = $this->generateSessionRange($admissionSession, $currentSession);

        $consecutive = 0;
        foreach (array_reverse($allExpectedSessions) as $session) {
            if ($registeredSessions->contains($session)) {
                break;
            }
            $consecutive++;
        }

        return $consecutive;
    }

    /**
     * Build lightweight standing snapshots from ResultGpaRecord entries when
     * AcademicProgressionRecord rows do not yet exist.
     */
    private function buildProgressionSnapshotsFromGpaRecords(User $user): Collection
    {
        return ResultGpaRecord::where('user_id', $user->id)
            ->orderByDesc('academic_session')
            ->orderByDesc('id')
            ->get()
            ->map(function ($gpa) {
                $cgpa = (float) $gpa->cumulative_gpa;

                $standing = match (true) {
                    $cgpa >= 1.50 => self::STANDING_PROMOTED,
                    $cgpa >= 1.00 => self::STANDING_PROBATION,
                    default       => self::STANDING_REPEAT,
                };

                return (object) [
                    'academic_session' => $gpa->academic_session,
                    'semester'         => $gpa->semester,
                    'standing'         => $standing,
                    'cgpa'             => $cgpa,
                ];
            });
    }

    /**
     * Return the worst academic standing from a collection of standing values.
     * Worst-first priority: REPEAT > PROBATION > SPILLOVER > PROMOTED > FRESH
     */
    private function worstStanding(array $standings): string
    {
        $priority = [
            self::STANDING_REPEAT    => 4,
            self::STANDING_PROBATION => 3,
            self::STANDING_SPILLOVER => 2,
            self::STANDING_PROMOTED  => 1,
            self::STANDING_FRESH     => 0,
        ];

        $worst = self::STANDING_PROMOTED;
        foreach ($standings as $s) {
            if (($priority[$s] ?? 0) > ($priority[$worst] ?? 0)) {
                $worst = $s;
            }
        }

        return $worst;
    }

    /**
     * Generate an ordered list of academic session strings (e.g. '2022/2023')
     * spanning from $from to $to inclusive.
     */
    private function generateSessionRange(string $from, string $to): array
    {
        $fromParts = explode('/', $from);
        $toParts   = explode('/', $to);

        $fromYear = (int) ($fromParts[0] ?? 0);
        $toYear   = (int) ($toParts[0] ?? 0);

        if ($fromYear <= 0 || $toYear <= 0 || $fromYear > $toYear) {
            return [$to];
        }

        $sessions = [];
        for ($y = $fromYear; $y <= $toYear; $y++) {
            $sessions[] = "{$y}/" . ($y + 1);
        }

        return $sessions;
    }
}
