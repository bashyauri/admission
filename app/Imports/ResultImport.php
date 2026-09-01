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
    public array $previewRows = [];

    public function __construct(
        CourseAllocation $allocation,
        string $session,
        string $semester,
        private readonly bool $shouldPersist = true
    )
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
            $rowNumber = $index + 2;
            $matricNo = trim($row['matric_no'] ?? '');
            $caScore = trim($row['ca_score_max_40'] ?? '');
            $examScore = trim($row['exam_score_max_60'] ?? '');
            $absentValue = strtolower(trim($row['absent_yesno'] ?? 'no'));
            $isAbsent = in_array($absentValue, ['yes', 'y', 'true', '1'], true);
            
            // Skip empty rows
            if (empty($matricNo) && empty($caScore) && empty($examScore)) continue;

            if (empty($matricNo)) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'Matric No is missing.');
                continue;
            }

            // Find the student
            $academicDetail = AcademicDetail::where('matric_no', $matricNo)->first();
            if (!$academicDetail) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'Student is not registered in the system.');
                continue;
            }

            // Check if student registered for this course
            $regCourse = RegisteredCourse::where('academic_detail_id', $academicDetail->id)
                ->where('department_course_id', $this->allocation->department_course_id)
                ->where('academic_session', $session)
                ->first();

            if (!$regCourse) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'Student is not registered for this course.');
                continue;
            }

            // Validate scores
            $ca = $caScore === '' ? null : floatval($caScore);
            $exam = $examScore === '' ? null : floatval($examScore);

            if ($ca !== null && ($ca < 0 || $ca > 40)) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'CA score must be between 0 and 40.');
                continue;
            }

            if ($exam !== null && ($exam < 0 || $exam > 60)) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'Exam score must be between 0 and 60.');
                continue;
            }

            if (!$isAbsent && ($ca === null || $exam === null)) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'Both CA and exam scores are required.');
                continue;
            }

            // Calculate grade
            $total = $isAbsent ? 0.0 : floatval($ca) + floatval($exam);
            $grade = $isAbsent ? 'F' : $gradeService->calculateGrade($total);
            $gradePoint = $gradeService->calculateGradePoint($grade);
            $creditUnits = $regCourse->units;

            // Check existing result status
            $existing = Result::where('user_id', $academicDetail->user_id)
                ->where('department_course_id', $this->allocation->department_course_id)
                ->where('academic_session', $session)
                ->where('semester', $semester)
                ->first();

            if ($existing && $existing->status !== 'pending') {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, 'Result is already submitted and cannot be updated.');
                continue;
            }

            $this->previewRows[] = [
                'row' => $rowNumber,
                'matric_no' => $matricNo,
                'ca_score' => $ca,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade,
                'is_valid' => true,
                'message' => $isAbsent ? 'Absent - will be recorded as F' : 'Ready to import',
            ];

            if (!$this->shouldPersist) {
                $this->successCount++;

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
                    'ca_score' => $isAbsent ? 0 : $ca,
                    'exam_score' => $isAbsent ? 0 : $exam,
                    'total_score' => $total,
                    'grade' => $grade,
                    'grade_point' => $gradePoint,
                    'credit_units' => $creditUnits,
                    'grade_point_total' => $gradePoint * $creditUnits,
                    'status' => 'pending',
                    'lecturer_id' => Auth::id(),
                    'remarks' => $isAbsent ? 'Absent' : null,
                ]
            );

            $this->successCount++;
        }
    }

    private function addPreviewError(
        int $rowNumber,
        string $matricNo,
        string $caScore,
        string $examScore,
        string $message
    ): void {
        $this->errors[] = "Row {$rowNumber}: {$message}";
        $this->previewRows[] = [
            'row' => $rowNumber,
            'matric_no' => $matricNo ?: 'Missing',
            'ca_score' => $caScore,
            'exam_score' => $examScore,
            'total_score' => null,
            'grade' => '-',
            'is_valid' => false,
            'message' => $message,
        ];
    }
}
