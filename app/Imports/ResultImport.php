<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\AcademicDetail;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\CourseAllocation;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Auth;

class ResultImport implements ToCollection, WithHeadingRow
{
    protected $allocation;
    protected string $session;
    protected string $semester;
    public $errors = [];
    public $successCount = 0;

    public function __construct(CourseAllocation $allocation, string $session, string $semester)
    {
        $this->allocation = $allocation;
        $this->session = $session;
        $this->semester = $semester;
    }

    public function collection(Collection $rows)
    {
        $session = $this->session;
        $semester = $this->semester;
        $gradeService = new GradeCalculationService();

        foreach ($rows as $index => $row) {
            $matricNo = trim($row['matric_no'] ?? '');
            $caScore = trim($row['ca_score_max_40'] ?? '');
            $examScore = trim($row['exam_score_max_60'] ?? '');
            
            // Skip empty rows
            if (empty($matricNo) && empty($caScore) && empty($examScore)) continue;

            if (empty($matricNo)) {
                $this->errors[] = "Row " . ($index + 2) . ": Matric No is missing.";
                continue;
            }

            // Find the student
            $academicDetail = AcademicDetail::where('matric_no', $matricNo)->first();
            if (!$academicDetail) {
                $this->errors[] = "Row " . ($index + 2) . ": Student with Matric No {$matricNo} not found.";
                continue;
            }

            // Check if student registered for this course
            $regCourse = RegisteredCourse::where('academic_detail_id', $academicDetail->id)
                ->where('department_course_id', $this->allocation->department_course_id)
                ->where('academic_session', $session)
                ->first();

            if (!$regCourse) {
                $this->errors[] = "Row " . ($index + 2) . ": Student {$matricNo} is not registered for this course.";
                continue;
            }

            // Validate scores
            $ca = $caScore === '' ? null : floatval($caScore);
            $exam = $examScore === '' ? null : floatval($examScore);

            if ($ca !== null && ($ca < 0 || $ca > 40)) {
                $this->errors[] = "Row " . ($index + 2) . ": Invalid CA score for {$matricNo}. Must be 0-40.";
                continue;
            }

            if ($exam !== null && ($exam < 0 || $exam > 60)) {
                $this->errors[] = "Row " . ($index + 2) . ": Invalid Exam score for {$matricNo}. Must be 0-60.";
                continue;
            }

            // Calculate grade
            $total = floatval($ca ?? 0) + floatval($exam ?? 0);
            $grade = $gradeService->calculateGrade($total);
            $gradePoint = $gradeService->calculateGradePoint($grade);
            $creditUnits = $regCourse->units;

            // Check existing result status
            $existing = Result::where('user_id', $academicDetail->user_id)
                ->where('department_course_id', $this->allocation->department_course_id)
                ->where('academic_session', $session)
                ->where('semester', $semester)
                ->first();

            if ($existing && $existing->status !== 'pending') {
                $this->errors[] = "Row " . ($index + 2) . ": Result for {$matricNo} is already submitted and cannot be updated.";
                continue;
            }

            // Save result
            Result::updateOrCreate(
                [
                    'user_id' => $academicDetail->user_id,
                    'registered_course_id' => $regCourse->id,
                    'academic_session' => $session,
                    'semester' => $semester,
                ],
                [
                    'department_course_id' => $this->allocation->department_course_id,
                    'academic_detail_id' => $regCourse->academic_detail_id,
                    'ca_score' => $ca,
                    'exam_score' => $exam,
                    'total_score' => $total,
                    'grade' => $grade,
                    'grade_point' => $gradePoint,
                    'credit_units' => $creditUnits,
                    'grade_point_total' => $gradePoint * $creditUnits,
                    'status' => 'pending',
                    'lecturer_id' => Auth::id(),
                ]
            );

            $this->successCount++;
        }
    }
}
