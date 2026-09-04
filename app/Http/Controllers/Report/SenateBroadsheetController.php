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
     * Display and print official Departmental / Senate Broadsheet matrix.
     *
     * @param Request $request
     * @param Department|int $department
     * @param string $session
     * @param string $semester
     * @param int|null $level
     * @return View
     */
    public function print(
        Request $request,
        Department|int $department,
        string $session,
        string $semester,
        ?int $level = null
    ): View {
        $departmentId = $department instanceof Department 
            ? $department->id 
            : (int) $department;

        $normalizedSession = str_replace('-', '/', $session);

        $filters = [
            'department_id' => $departmentId,
            'academic_session' => $normalizedSession,
            'semester' => $semester,
            'student_level_id' => $level,
            'course_id' => $request->query('course_id') ? (int) $request->query('course_id') : null,
            'admission_session' => $request->query('admission_session') 
                ? str_replace('-', '/', (string) $request->query('admission_session')) 
                : null,
        ];

        $data = $this->reportingService->getDepartmentalBroadsheet($filters);

        return view('reports.senate-broadsheet', $data);
    }
}
