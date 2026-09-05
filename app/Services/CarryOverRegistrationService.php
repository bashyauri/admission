<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CarryOverCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Collection;

class CarryOverRegistrationService
{
    /**
     * NUC regulatory minimum credit units per semester.
     * This is a fixed regulatory floor — not configurable per department.
     */
    public const NUC_MIN_SEMESTER_UNITS = 15;

    /**
     * Fallback maximum when no department-level record exists in department_max_units.
     */
    public const DEFAULT_MAX_SEMESTER_UNITS = 24;

    /**
     * Record a failed result as an un-cleared carry-over course.
     */
    public function recordFailedCourse(Result $result): ?CarryOverCourse
    {
        if ($result->grade !== 'F' && (float) $result->total_score >= 40.0) {
            return null;
        }

        return CarryOverCourse::updateOrCreate(
            [
                'user_id' => $result->user_id,
                'department_course_id' => $result->department_course_id,
                'failed_session' => $result->academic_session,
            ],
            [
                'registered_course_id' => $result->registered_course_id,
                'failed_semester' => $result->semester,
                'failed_score' => $result->total_score ?? 0,
                'failed_grade' => $result->grade ?? 'F',
                'is_cleared' => false,
                'cleared_at' => null,
                'cleared_result_id' => null,
            ]
        );
    }

    /**
     * Process an approved result: if passed, clear any pending carry-over for this course.
     */
    public function processResultClearance(Result $result): bool
    {
        if ($result->grade === 'F' || (float) $result->total_score < 40.0) {
            $this->recordFailedCourse($result);
            return false;
        }

        // Student passed; clear pending carry-overs for this department course
        $carryOvers = CarryOverCourse::where('user_id', $result->user_id)
            ->where('department_course_id', $result->department_course_id)
            ->where('is_cleared', false)
            ->get();

        foreach ($carryOvers as $carryOver) {
            $carryOver->update([
                'is_cleared' => true,
                'cleared_at' => now(),
                'cleared_result_id' => $result->id,
                'retake_session' => $result->academic_session,
                'retake_semester' => $result->semester,
            ]);
        }

        return $carryOvers->isNotEmpty();
    }

    /**
     * Get all active (un-cleared) carry over courses for a student.
     *
     * @return Collection<int, CarryOverCourse>
     */
    public function getActiveCarryOvers(User $student, ?string $semester = null): Collection
    {
        $query = CarryOverCourse::with(['departmentCourse.studentCourse', 'registeredCourse'])
            ->where('user_id', $student->id)
            ->active();

        if ($semester !== null) {
            $query->where('failed_semester', $semester);
        }

        return $query->get();
    }

    /**
     * Validate credit load for semester registration including carry-over courses.
     *
     * Max units are looked up from the `department_max_units` table per department and level,
     * falling back to DEFAULT_MAX_SEMESTER_UNITS (24) if no record is configured.
     * Min units (NUC_MIN_SEMESTER_UNITS = 15) is a fixed NUC regulatory floor.
     *
     * @return array{is_valid: bool, total_units: int, max_units: int, min_units: int, errors: array<string>}
     */
    public function validateCreditUnits(int $regularUnits, int $carryOverUnits, int $departmentId, int $studentLevelId): array
    {
        $totalUnits = $regularUnits + $carryOverUnits;
        $errors = [];

        // Look up configured maximum for this department & level
        $maxUnits = (int) DepartmentMaxUnit::where('department_id', $departmentId)
            ->where('student_level_id', $studentLevelId)
            ->value('max_units');

        // Fallback to NUC default if department admin hasn't configured it yet
        if ($maxUnits <= 0) {
            $maxUnits = self::DEFAULT_MAX_SEMESTER_UNITS;
        }

        $minUnits = self::NUC_MIN_SEMESTER_UNITS;

        if ($totalUnits > $maxUnits) {
            $errors[] = "Total registered units ({$totalUnits}) exceeds the maximum allowed limit of {$maxUnits} units for this level.";
        }

        if ($totalUnits < $minUnits) {
            $errors[] = "Total registered units ({$totalUnits}) is below the minimum required limit of {$minUnits} units (NUC standard).";
        }

        return [
            'is_valid' => empty($errors),
            'total_units' => $totalUnits,
            'max_units' => $maxUnits,
            'min_units' => $minUnits,
            'errors' => $errors,
        ];
    }
}
