<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DepartmentMaxUnit;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\Transcript;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use DomainException;
use Illuminate\Support\Collection;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class TranscriptService
{
    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Generate an official or student-copy PDF transcript for an undergraduate student.
     *
     * @param User|string $user Student instance or UUID
     * @param bool $official Whether to mark as official transcript
     * @param array $options Additional options (destination, purpose, remarks)
     * @return DomPdfWrapper
     */
    public function generateTranscript(User|string $user, bool $official = true, array $options = []): DomPdfWrapper
    {
        $student = $user instanceof User ? $user : User::findOrFail($user);

        if (!$student->isUndergraduate()) {
            throw new DomainException('Academic transcripts in this module are strictly for undergraduate students.');
        }

        $data = $this->buildTranscriptData($student, $official, $options);

        /** @var DomPdfWrapper $pdf */
        $pdf = Pdf::loadView('transcripts.official', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        return $pdf;
    }

    /**
     * Download the PDF transcript directly.
     */
    public function downloadTranscript(User|string $user, bool $official = true, array $options = []): Response
    {
        $student = $user instanceof User ? $user : User::findOrFail($user);
        $pdf = $this->generateTranscript($student, $official, $options);
        $filename = 'transcript_' . ($student->academicDetail?->matric_no ? str_replace('/', '_', $student->academicDetail->matric_no) : $student->id) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream the PDF transcript in browser.
     */
    public function streamTranscript(User|string $user, bool $official = true, array $options = []): Response
    {
        $student = $user instanceof User ? $user : User::findOrFail($user);
        $pdf = $this->generateTranscript($student, $official, $options);
        $filename = 'transcript_' . ($student->academicDetail?->matric_no ? str_replace('/', '_', $student->academicDetail->matric_no) : $student->id) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Build all view data for the transcript.
     */
    public function buildTranscriptData(User $student, bool $official = true, array $options = []): array
    {
        $academicDetail = $student->academicDetail
            ? $student->academicDetail->loadMissing(['department', 'programme', 'studentLevel', 'course'])
            : null;

        $resultsBreakdown = $this->getStudentResultsBreakdown($student->id);
        $gpaRecords       = $this->getGPARecords($student->id);
        $summary          = $this->calculateSummary($student->id);

        // Fetch DepartmentMaxUnit for allowed credit registered reference
        $departmentMaxUnit = null;
        if ($academicDetail?->department_id && $academicDetail?->student_level_id) {
            $departmentMaxUnit = DepartmentMaxUnit::where([
                'department_id'    => $academicDetail->department_id,
                'student_level_id' => $academicDetail->student_level_id,
            ])->value('max_units');
        }

        // Create or update database Transcript record for verification audit
        $transcript = $this->getOrCreateTranscriptRecord($student, $options, $official);

        // Generate QR code pointing to online database verification route
        $verificationUrl = route('transcripts.verify', ['code' => $transcript->verification_code]);
        $qrCodeSvg = base64_encode(
            (string) QrCode::format('svg')
                ->size(95)
                ->margin(1)
                ->errorCorrection('M')
                ->generate($verificationUrl)
        );

        // Base64 encode crest logo for 100% reliable DomPDF embedding
        $logoPath = public_path('assets/img/fubk-icon.jpg');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        return [
            'student'           => $student,
            'academicDetail'    => $academicDetail,
            'resultsBreakdown'  => $resultsBreakdown,
            'gpaRecords'        => $gpaRecords,
            'summary'           => $summary,
            'departmentMaxUnit' => $departmentMaxUnit,
            'transcript'        => $transcript,
            'official'          => $official,
            'verificationUrl'   => $verificationUrl,
            'qrCodeSvg'         => $qrCodeSvg,
            'logoBase64'        => $logoBase64,
            'generated_at'      => now(),
        ];
    }

    /**
     * Get student results chronologically organized by session and semester,
     * including explicit detection of all repeated course attempts.
     */
    public function getStudentResultsBreakdown(string $userId): array
    {
        $rawResults = Result::query()
            ->with([
                'departmentCourse.studentCourse',
                'registeredCourse.departmentCourse.studentCourse',
            ])
            ->where('user_id', $userId)
            ->where('status', 'released')
            ->orderBy('academic_session', 'asc')
            ->orderByRaw("CASE WHEN LOWER(semester) = 'first' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->get();

        // Track course attempts chronologically across all semesters
        $courseAttemptCounts = [];
        $sessions = $rawResults->pluck('academic_session')->unique()->values();

        $semestersData = [];
        $runningCcr = 0; // Cumulative Credit Registered
        $runningCcp = 0; // Cumulative Credit Passed
        $runningCqp = 0; // Cumulative Quality Points

        foreach ($sessions as $session) {
            $sessionResults = $rawResults->where('academic_session', $session);

            foreach (['first', 'second'] as $semKey) {
                $coursesInSemester = $sessionResults->filter(function ($res) use ($semKey) {
                    return strtolower($res->semester ?? '') === $semKey;
                })->values();

                if ($coursesInSemester->isEmpty()) {
                    continue;
                }

                $semesterCourses = [];
                $semTcr = 0; // Total Credit Registered
                $semTcp = 0; // Total Credit Passed
                $semTqp = 0; // Total Quality Points

                foreach ($coursesInSemester as $res) {
                    $code = $res->course_code_snapshot
                        ?? $res->registeredCourse?->course_code_snapshot
                        ?? $res->departmentCourse?->studentCourse?->code
                        ?? 'N/A';

                    $title = $res->course_title_snapshot
                        ?? $res->registeredCourse?->course_title_snapshot
                        ?? $res->departmentCourse?->studentCourse?->title
                        ?? 'N/A';

                    $units = (int) ($res->credit_units_snapshot
                        ?? $res->credit_units
                        ?? $res->registeredCourse?->credit_units_snapshot
                        ?? $res->departmentCourse?->units
                        ?? $res->departmentCourse?->studentCourse?->units
                        ?? 0);

                    $grade = strtoupper(trim((string) ($res->grade ?? 'F')));
                    $gradePoint = (int) ($res->grade_point ?? $this->gradeCalculationService->calculateGradePoint($grade));
                    $qp = $units * $gradePoint;

                    // Track repeat attempts
                    $codeKey = strtoupper(trim($code));
                    $courseAttemptCounts[$codeKey] = ($courseAttemptCounts[$codeKey] ?? 0) + 1;
                    $attemptNumber = $courseAttemptCounts[$codeKey];
                    $isRepeated = $attemptNumber > 1 || (bool) $res->is_repeated;

                    $semTcr += $units;
                    $semTqp += $qp;
                    if ($grade !== 'F') {
                        $semTcp += $units;
                    }

                    $semesterCourses[] = [
                        'code'           => $code,
                        'title'          => $title,
                        'units'          => $units,
                        'score'          => $res->total_score,
                        'grade'          => $grade,
                        'grade_point'    => $gradePoint,
                        'quality_points' => $qp,
                        'is_repeated'    => $isRepeated,
                        'attempt_number' => $attemptNumber,
                    ];
                }

                $semGpa = $semTcr > 0 ? round($semTqp / $semTcr, 2) : 0.00;

                // Update running cumulative totals
                $runningCcr += $semTcr;
                $runningCcp += $semTcp;
                $runningCqp += $semTqp;
                $runningCgpa = $runningCcr > 0 ? round($runningCqp / $runningCcr, 2) : 0.00;

                $semesterTitle = $semKey === 'first'
                    ? 'First Semester (Harmattan)'
                    : 'Second Semester (Rain)';

                $semestersData[] = [
                    'academic_session' => $session,
                    'semester'         => $semKey,
                    'semester_title'   => $semesterTitle,
                    'courses'          => $semesterCourses,
                    // Semester stats
                    'tcr'              => $semTcr,
                    'tcp'              => $semTcp,
                    'tqp'              => $semTqp,
                    'gpa'              => $semGpa,
                    // Cumulative stats up to this semester
                    'ccr'              => $runningCcr,
                    'ccp'              => $runningCcp,
                    'cqp'              => $runningCqp,
                    'cgpa'             => $runningCgpa,
                ];
            }
        }

        return $semestersData;
    }

    /**
     * Get student results organized by session and semester (Array format matching plan).
     */
    public function getStudentResults(string $userId): array
    {
        return Result::where('user_id', $userId)
            ->where('status', 'released')
            ->orderBy('academic_session')
            ->orderBy('semester')
            ->get()
            ->groupBy(['academic_session', 'semester'])
            ->toArray();
    }

    /**
     * Get stored GPA records for student.
     */
    public function getGPARecords(string $userId): Collection
    {
        return ResultGpaRecord::where('user_id', $userId)
            ->orderBy('academic_session')
            ->orderBy('semester')
            ->get();
    }

    /**
     * Calculate summary statistics for student transcript.
     */
    public function calculateSummary(string $userId): array
    {
        $cgpaData = $this->gradeCalculationService->calculateCGPA($userId);

        $passedUnits = Result::where('user_id', $userId)
            ->where('status', 'released')
            ->where('grade', '!=', 'F')
            ->get()
            ->sum(function ($r) {
                return (int) ($r->credit_units_snapshot ?? $r->credit_units ?? 0);
            });

        return [
            'total_credit_units'        => $cgpaData['total_credit_units'],
            'total_credit_units_earned' => (int) $passedUnits,
            'total_grade_points'        => $cgpaData['total_grade_points'],
            'final_cgpa'                => $cgpaData['cgpa'],
            'class_of_degree'           => $cgpaData['class_of_degree'],
        ];
    }

    /**
     * Fetch or create a persistent Transcript audit record with a unique verification code.
     */
    protected function getOrCreateTranscriptRecord(User $student, array $options, bool $official): Transcript
    {
        $existing = Transcript::where('user_id', $student->id)->latest()->first();

        if ($existing && !empty($existing->verification_code)) {
            return $existing;
        }

        return Transcript::create([
            'user_id'           => $student->id,
            'request_number'    => Transcript::generateRequestNumber(),
            'verification_code' => Transcript::generateVerificationCode(),
            'destination'       => $options['destination'] ?? 'Student Portal / Official Copy',
            'purpose'           => $options['purpose'] ?? ($official ? 'Official Verification' : 'Student Academic Record Copy'),
            'status'            => 'ready',
            'fee'               => 0.00,
            'fee_paid'          => true,
            'remarks'           => $options['remarks'] ?? 'Generated via TranscriptService',
        ]);
    }
}
