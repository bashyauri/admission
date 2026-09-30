<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Result;
use App\Models\RegisteredCourse;

/**
 * Resolve the historical course details attached to a UG result attempt.
 *
 * Result snapshots are preferred, then registration snapshots. Live course
 * details are only a legacy fallback when neither historical snapshot exists.
 */
class ResultCourseSnapshotService
{
    /** @return array{code: ?string, title: ?string, units: int, semester: ?string, level: ?int} */
    public function resolve(Result $result): array
    {
        $registeredCourse = $result->registeredCourse;
        $departmentCourse = $result->departmentCourse ?? $registeredCourse?->departmentCourse;
        $studentCourse = $departmentCourse?->studentCourse;

        return [
            'code' => $this->firstText(
                $result->course_code_snapshot,
                $registeredCourse?->course_code_snapshot,
                $studentCourse?->code,
            ),
            'title' => $this->firstText(
                $result->course_title_snapshot,
                $registeredCourse?->course_title_snapshot,
                $studentCourse?->title,
            ),
            'units' => $this->units($result),
            'semester' => $this->firstText(
                $result->semester_snapshot,
                $registeredCourse?->semester_snapshot,
                $result->semester,
                $studentCourse?->semester,
            ),
            'level' => $result->level_snapshot
                ?? $registeredCourse?->level_snapshot
                ?? $registeredCourse?->student_level_id
                ?? $studentCourse?->student_level_id,
        ];
    }

    public function units(Result $result): int
    {
        if ($result->credit_units_snapshot !== null) {
            return (int) $result->credit_units_snapshot;
        }

        $registeredCourse = $result->registeredCourse;
        if ($registeredCourse?->credit_units_snapshot !== null) {
            return (int) $registeredCourse->credit_units_snapshot;
        }

        if ($result->credit_units !== null) {
            return (int) $result->credit_units;
        }

        $departmentCourse = $result->departmentCourse ?? $registeredCourse?->departmentCourse;
        $studentCourse = $departmentCourse?->studentCourse;

        return (int) ($departmentCourse?->units ?? $studentCourse?->units ?? 0);
    }

    /** @return array{code: ?string, title: ?string, units: int, semester: ?string, level: ?int} */
    public function resolveRegistration(RegisteredCourse $registeredCourse): array
    {
        $studentCourse = $registeredCourse->departmentCourse?->studentCourse;

        return [
            'code' => $this->firstText($registeredCourse->course_code_snapshot, $studentCourse?->code),
            'title' => $this->firstText($registeredCourse->course_title_snapshot, $studentCourse?->title),
            'units' => (int) ($registeredCourse->credit_units_snapshot ?? $registeredCourse->units),
            'semester' => $this->firstText($registeredCourse->semester_snapshot, $studentCourse?->semester),
            'level' => $registeredCourse->level_snapshot
                ?? $registeredCourse->student_level_id
                ?? $studentCourse?->student_level_id,
        ];
    }

    private function firstText(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
