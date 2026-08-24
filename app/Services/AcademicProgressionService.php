<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Programme;
use App\Models\ResultGpaRecord;
use App\Models\User;

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

        if (!$latestGpaRecord) {
            return [
                'standing' => self::STANDING_PROMOTED,
                'cgpa' => 0.0,
                'has_uncleared_carryovers' => $hasUnclearedCarryOvers,
                'reason' => 'No session GPA records found; default progression',
            ];
        }

        $cgpa = (float) $latestGpaRecord->cumulative_gpa;
        $currentLevel = (int) ($academicDetail->student_level_id ?? 1);
        $maxLevel = $this->getMaxProgramLevel($user);

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
}
