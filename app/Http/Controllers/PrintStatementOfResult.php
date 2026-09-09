<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProgrammesEnum;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Generates a printable Semester Statement of Result for an undergraduate student.
 *
 * Route: GET /student/print-statement/{session}/{semester}
 * Only released results are shown. Strictly Undergraduate scope.
 */
class PrintStatementOfResult extends Controller
{
    public function __invoke(Request $request, string $session, string $semester)
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        abort_unless($student !== null, 401);

        // Strictly Undergraduate only
        if (!$student->isUndergraduate()) {
            abort(403, 'This statement of result is only available for undergraduate students.');
        }

        // Normalise URL-safe dashes back to slashes: 2024-2025 → 2024/2025
        $academicSession = str_replace('-', '/', $session);
        $semesterNorm    = strtolower($semester); // 'first' or 'second'

        abort_unless(in_array($semesterNorm, ['first', 'second'], true), 404);

        $academicDetail = $student->academicDetail
            ? $student->academicDetail->loadMissing(['department', 'programme', 'studentLevel', 'course'])
            : null;

        // Fetch released results for this specific session + semester
        $results = Result::query()
            ->with([
                'departmentCourse.studentCourse',
                'registeredCourse.departmentCourse.studentCourse',
            ])
            ->where('user_id', $student->id)
            ->where('status', 'released')
            ->where('academic_session', $academicSession)
            ->whereRaw('LOWER(semester) = ?', [$semesterNorm])
            ->orderBy('created_at', 'asc')
            ->get();

        abort_if($results->isEmpty(), 404, 'No released results found for this semester.');

        $gradeService = app(GradeCalculationService::class);

        // Compute semester totals
        $tcr = 0; // Total Credit Registered
        $tcp = 0; // Total Credit Passed
        $tqp = 0; // Total Quality Points

        foreach ($results as $res) {
            $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $res->departmentCourse?->units ?? 0);
            $gp    = (int) ($res->grade_point ?? $gradeService->calculateGradePoint($res->grade ?? 'F'));
            $tcr  += $units;
            $tqp  += ($gp * $units);
            if (strtoupper((string) $res->grade) !== 'F') {
                $tcp += $units;
            }
        }

        // Look up persisted GPA record for this semester
        $gpaKey    = $academicSession . '_' . $semesterNorm;
        $gpaRecord = ResultGpaRecord::where('user_id', $student->id)
            ->where('academic_session', $academicSession)
            ->whereRaw('LOWER(semester) = ?', [$semesterNorm])
            ->first();

        $semesterGpa = $gpaRecord ? (float) $gpaRecord->semester_gpa : ($tcr > 0 ? round($tqp / $tcr, 2) : 0.0);
        $cgpa        = $gpaRecord ? (float) $gpaRecord->cumulative_gpa : null;
        $classOfDegree = $cgpa !== null ? $gradeService->getClassOfDegree($cgpa) : 'N/A';

        // Issue reference number: SOR-{MATRIC}-{SESSION}-{SEM}
        $matricNo  = $academicDetail?->matric_no ?? 'N/A';
        $refNumber = 'SOR-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $matricNo))
            . '-' . str_replace('/', '', $academicSession)
            . '-' . strtoupper(substr($semesterNorm, 0, 3));

        return view('student.print-statement-of-result', [
            'student'        => $student,
            'academicDetail' => $academicDetail,
            'results'        => $results,
            'academicSession'=> $academicSession,
            'semester'       => $semesterNorm,
            'tcr'            => $tcr,
            'tcp'            => $tcp,
            'tqp'            => $tqp,
            'semesterGpa'    => $semesterGpa,
            'cgpa'           => $cgpa,
            'classOfDegree'  => $classOfDegree,
            'gpaRecord'      => $gpaRecord,
            'refNumber'      => $refNumber,
            'gradeService'   => $gradeService,
        ]);
    }
}
