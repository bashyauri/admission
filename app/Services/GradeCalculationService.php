<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicDetail;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\User;
use Illuminate\Support\Collection;

class GradeCalculationService
{
    /**
     * Calculate letter grade from total score based on NUC 5-point grading system.
     */
    public function calculateGrade(float|int $totalScore): string
    {
        return $this->getGradeAndPoint((float) $totalScore)['grade'];
    }

    /**
     * Calculate grade point from letter grade or total score.
     */
    public function calculateGradePoint(string|float|int $gradeOrScore): int
    {
        if (is_numeric($gradeOrScore)) {
            return $this->getGradeAndPoint((float) $gradeOrScore)['grade_point'];
        }

        return match (strtoupper(trim((string) $gradeOrScore))) {
            'A' => 5,
            'B' => 4,
            'C' => 3,
            'D' => 2,
            'E' => 1,
            default => 0,
        };
    }

    /**
     * Get letter grade and grade point from total score based on NUC 5-point grading system.
     *
     * @return array{grade: string, grade_point: int, description: string}
     */
    public function getGradeAndPoint(float $totalScore): array
    {
        return match (true) {
            $totalScore >= 70.0 => [
                'grade' => 'A',
                'grade_point' => 5,
                'description' => 'Excellent',
            ],
            $totalScore >= 60.0 => [
                'grade' => 'B',
                'grade_point' => 4,
                'description' => 'Very Good',
            ],
            $totalScore >= 50.0 => [
                'grade' => 'C',
                'grade_point' => 3,
                'description' => 'Good',
            ],
            $totalScore >= 45.0 => [
                'grade' => 'D',
                'grade_point' => 2,
                'description' => 'Fair',
            ],
            $totalScore >= 40.0 => [
                'grade' => 'E',
                'grade_point' => 1,
                'description' => 'Pass',
            ],
            default => [
                'grade' => 'F',
                'grade_point' => 0,
                'description' => 'Fail',
            ],
        };
    }

    /**
     * Compute quality points for a course (Grade Point * Credit Units).
     */
    public function calculateQualityPoints(int $gradePoint, int $creditUnits): int
    {
        return $gradePoint * $creditUnits;
    }

    /**
     * Determine Class of Degree from CGPA based on FUBK institutional standards.
     *
     * FUBK classification:
     *   4.50 – 5.00  →  First Class Honours
     *   3.50 – 4.49  →  Second Class Upper Division (2.1)
     *   2.50 – 3.49  →  Second Class Lower Division (2.2)
     *   1.00 – 2.49  →  Third Class Honours
     *   < 1.00       →  Below degree standard (probation / withdrawal)
     */
    public function getClassOfDegree(float $cgpa): string
    {
        return match (true) {
            $cgpa >= 4.50 => 'First Class Honours',
            $cgpa >= 3.50 => 'Second Class Upper Division',
            $cgpa >= 2.50 => 'Second Class Lower Division',
            $cgpa >= 1.00 => 'Third Class Honours',
            default => 'Below Degree Standard',
        };
    }

    /**
     * Calculate Semester GPA from a collection of results.
     *
     * @param Collection<int, Result> $results
     * @return array{semester_gpa: float, total_units: int, total_points: int}
     */
    public function calculateSemesterGpa(Collection $results): array
    {
        $totalUnits = 0;
        $totalPoints = 0;
        $courseSnapshots = app(ResultCourseSnapshotService::class);

        foreach ($results as $result) {
            $units = $courseSnapshots->units($result);
            $gradePoint = (int) ($result->grade_point ?? 0);

            $totalUnits += $units;
            $totalPoints += ($gradePoint * $units);
        }

        $gpa = $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0.00;

        return [
            'semester_gpa' => (float) $gpa,
            'total_units' => $totalUnits,
            'total_points' => $totalPoints,
        ];
    }

    /**
     * Calculate and record the student session GPA and cumulative GPA.
     *
     * Every academic calculation is session-based for governance reporting, so
     * the GPA is built from all released results in the given session rather than
     * from a single semester bucket.
     */
    public function processAndSaveGpaRecord(User $student, string $session, string $semester): ResultGpaRecord
    {
        $sessionResults = Result::with(['registeredCourse', 'departmentCourse.studentCourse'])
            ->where('user_id', $student->id)
            ->where('academic_session', $session)
            ->where('status', 'released')
            ->get();

        $sessionCalc = $this->calculateSemesterGpa($sessionResults);

        // Fetch all released historical results for the student up to and including this session.
        $allResults = Result::with(['registeredCourse', 'departmentCourse.studentCourse'])
            ->where('user_id', $student->id)
            ->where('status', 'released')
            ->get();

        $cumulativeCalc = $this->calculateSemesterGpa($allResults);
        $classOfDegree = $this->getClassOfDegree($cumulativeCalc['semester_gpa']);

        $academicDetail = $student->academicDetail;

        $record = ResultGpaRecord::updateOrCreate(
            [
                'user_id' => $student->id,
                'academic_session' => $session,
                'semester' => $semester,
            ],
            [
                'academic_detail_id' => $academicDetail?->id,
                'semester_gpa' => $sessionCalc['semester_gpa'],
                'total_credit_units' => $sessionCalc['total_units'],
                'total_grade_points' => $sessionCalc['total_points'],
                'cumulative_gpa' => $cumulativeCalc['semester_gpa'],
                'cumulative_credit_units' => $cumulativeCalc['total_units'],
                'cumulative_grade_points' => $cumulativeCalc['total_points'],
                'class_of_degree' => $classOfDegree,
            ]
        );

        if (config('academic_withdrawal.auto_apply', true)) {
            $semesterNumber = match (strtolower((string) $semester)) {
                'first', '1' => 1,
                'second', '2' => 2,
                default => 2,
            };
            app(AcademicProgressionService::class)->processAndApplyAcademicProgression($student, $session, $semesterNumber);
        }

        return $record;
    }

    /**
     * Calculate CGPA (Cumulative GPA) and academic summary for a student.
     *
     * @return array{cgpa: float, total_credit_units: int, total_grade_points: int, class_of_degree: string}
     */
    public function calculateCGPA(string|int $userId): array
    {
        $allResults = Result::with(['registeredCourse', 'departmentCourse.studentCourse'])
            ->where('user_id', $userId)
            ->where('status', 'released')
            ->get();

        $calc = $this->calculateSemesterGpa($allResults);
        $cgpa = $calc['semester_gpa'];
        $classOfDegree = $this->getClassOfDegree($cgpa);

        return [
            'cgpa' => $cgpa,
            'total_credit_units' => $calc['total_units'],
            'total_grade_points' => $calc['total_points'],
            'class_of_degree' => $classOfDegree,
        ];
    }
}
