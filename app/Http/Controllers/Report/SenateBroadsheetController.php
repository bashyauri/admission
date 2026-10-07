<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\ResultReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SenateBroadsheetController extends Controller
{
    public function __construct(
        protected ResultReportingService $reportingService
    ) {}

    /**
     * Display the provisional staff broadsheet including all workflow stages.
     * Used during the approval workflow (Lecturer → Coordinator → Exam Officer).
     * Clearly marked PROVISIONAL in the view.
     */
    public function print(
        Request $request,
        Department|int $department,
        string $session,
        ?string $semester = null,
        ?int $level = null
    ): View {
        return $this->buildBroadsheet($request, $department, $session, $semester, $level, releasedOnly: false);
    }

    /**
     * Display the official final Senate broadsheet using RELEASED results only.
     * This is the authoritative document for Academic Board / Senate approval.
     */
    public function printFinal(
        Request $request,
        Department|int $department,
        string $session,
        ?string $semester = null,
        ?int $level = null
    ): View {
        return $this->buildBroadsheet($request, $department, $session, $semester, $level, releasedOnly: true);
    }

    private function buildBroadsheet(
        Request $request,
        Department|int $department,
        string $session,
        ?string $semester,
        ?int $level,
        bool $releasedOnly
    ): View {
        $departmentId = $department instanceof Department
            ? $department->id
            : (int) $department;

        $normalizedSession = str_replace('-', '/', $session);
        $normalizedSemester = $semester !== null ? trim((string) $semester) : null;

        $singleSemesterOnly = $normalizedSemester !== null
            && $normalizedSemester !== ''
            && (bool) $request->query('single_semester_only', false);

        $filters = [
            'department_id'    => $departmentId,
            'academic_session' => $normalizedSession,
            'semester'         => $singleSemesterOnly ? $normalizedSemester : null,
            'student_level_id' => $level,
            'course_id'        => $request->query('course_id') ? (int) $request->query('course_id') : null,
            'admission_session' => $request->query('admission_session')
                ? str_replace('-', '/', (string) $request->query('admission_session'))
                : null,
            'released_only'    => $releasedOnly,
            'final'            => $releasedOnly,
            'single_semester_only' => $singleSemesterOnly,
        ];

        $data = $this->reportingService->getDepartmentalBroadsheet($filters);

        return view('reports.senate-broadsheet', $data);
    }
}
