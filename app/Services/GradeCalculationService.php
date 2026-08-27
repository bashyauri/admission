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
     * Determine Class of Degree from CGPA based on NUC standards.
     */
    public function getClassOfDegree(float $cgpa): string
    {
        return match (true) {
            $cgpa >= 4.50 => 'First Class Honours',
            $cgpa >= 3.50 => 'Second Class Upper Division',
            $cgpa >= 2.40 => 'Second Class Lower Division',
            $cgpa >= 1.50 => 'Third Class Honours',
            $cgpa >= 1.00 => 'Pass',
            default => 'Fail',
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

        foreach ($results as $result) {
            $units = (int) ($result->credit_units_snapshot ?? $result->credit_units ?? 0);
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
     * Calculate and record Semester and Cumulative GPA for a student in a specific session & semester.
     */
    public function processAndSaveGpaRecord(User $student, string $session, string $semester): ResultGpaRecord
    {
        $semesterResults = Result::where('user_id', $student->id)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->whereIn('status', ['hod_approved', 'exam_officer_approved', 'released'])
            ->get();

        $semesterCalc = $this->calculateSemesterGpa($semesterResults);

        // Fetch all approved historical results for the student up to and including this session/semester
        $allResults = Result::where('user_id', $student->id)
            ->whereIn('status', ['hod_approved', 'exam_officer_approved', 'released'])
            ->get();

        $cumulativeCalc = $this->calculateSemesterGpa($allResults);
        $classOfDegree = $this->getClassOfDegree($cumulativeCalc['semester_gpa']);

        $academicDetail = $student->academicDetail;

        return ResultGpaRecord::updateOrCreate(
            [
                'user_id' => $student->id,
                'academic_session' => $session,
                'semester' => $semester,
            ],
            [
                'academic_detail_id' => $academicDetail?->id,
                'semester_gpa' => $semesterCalc['semester_gpa'],
                'total_credit_units' => $semesterCalc['total_units'],
                'total_grade_points' => $semesterCalc['total_points'],
                'cumulative_gpa' => $cumulativeCalc['semester_gpa'],
                'cumulative_credit_units' => $cumulativeCalc['total_units'],
                'cumulative_grade_points' => $cumulativeCalc['total_points'],
                'class_of_degree' => $classOfDegree,
            ]
        );
    }
}
