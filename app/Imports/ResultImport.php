<?php

namespace App\Imports;

use App\Models\AcademicDetail;
use App\Models\CourseAllocation;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Services\GradeCalculationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ResultImport implements ToCollection, WithHeadingRow
{
    protected $allocation;
    protected $session;
    protected $semester;
    public $errors = [];
    public $successCount = 0;
    public array $previewRows = [];
    public int $maxCa;
    public int $maxExam;

    public function __construct(
        CourseAllocation $allocation,
        string $session,
        string $semester,
        private readonly bool $shouldPersist = true,
        ?int $maxCa = null,
        ?int $maxExam = null
    ) {
        $this->allocation = $allocation;
        $this->session = $session;
        $this->semester = $semester;

        $studentCourse = $allocation->relationLoaded('departmentCourse') && $allocation->departmentCourse
            ? $allocation->departmentCourse->studentCourse
            : ($allocation->departmentCourse()->with('studentCourse')->first()?->studentCourse ?? null);

        $this->maxCa = $maxCa ?? $studentCourse?->getMaxCa() ?? 40;
        $this->maxExam = $maxExam ?? $studentCourse?->getMaxExam() ?? 60;
    }

    public function collection(Collection $rows)
    {
        $session = $this->session;
        $semester = $this->semester;
        $gradeService = new GradeCalculationService();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $rowArray = $row->toArray();

            $matricNo = trim((string) ($rowArray['matric_no'] ?? ''));
            $caScore = $this->extractScore($rowArray, 'ca', $this->maxCa);
            $examScore = $this->extractScore($rowArray, 'exam', $this->maxExam);

            $absentValue = strtolower(trim((string) ($rowArray['absent_yesno'] ?? $rowArray['absent'] ?? 'no')));
            $isAbsent = in_array($absentValue, ['yes', 'y', 'true', '1'], true);

            // Skip empty rows
            if (empty($matricNo) && empty($caScore) && empty($examScore)) {
                continue;
            }

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

            // Validate scores against dynamic max weights
            $ca = $caScore === '' ? null : floatval($caScore);
            $exam = $examScore === '' ? null : floatval($examScore);

            if ($ca !== null && ($ca < 0 || $ca > $this->maxCa)) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, "CA score must be between 0 and {$this->maxCa}.");
                continue;
            }

            if ($exam !== null && ($exam < 0 || $exam > $this->maxExam)) {
                $this->addPreviewError($rowNumber, $matricNo, $caScore, $examScore, "Exam score must be between 0 and {$this->maxExam}.");
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

    /**
     * Flexible extraction of score from CSV row supporting various column naming patterns.
     */
    protected function extractScore(array $row, string $type, int $max): string
    {
        // 1. Direct slugified candidate keys
        $candidates = [
            "{$type}_score_max_{$max}",
            "{$type}_score",
            "{$type}_max_{$max}",
            "{$type}",
            "{$type}_score_max_40",
            "{$type}_score_max_60",
        ];

        foreach ($candidates as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }

        // 2. Fuzzy matching by key prefix
        foreach ($row as $k => $v) {
            $normalizedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $k));
            if (str_starts_with($normalizedKey, $type) && $v !== null && trim((string) $v) !== '') {
                return trim((string) $v);
            }
        }

        return '';
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
