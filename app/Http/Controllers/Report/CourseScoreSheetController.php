<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\DepartmentCourse;
use App\Services\ResultReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseScoreSheetController extends Controller
{
    public function __construct(
        protected ResultReportingService $reportingService
    ) {}

    /**
     * Display and print official course score sheet.
     *
     * @param Request $request
     * @param int|DepartmentCourse $departmentCourse
     * @param string $session
     * @param string $semester
     * @param int|null $level
     * @return View
     */
    public function print(
        Request $request,
        DepartmentCourse|int $departmentCourse,
        string $session,
        string $semester,
        ?int $level = null
    ): View {
        $departmentCourseId = $departmentCourse instanceof DepartmentCourse 
            ? $departmentCourse->id 
            : (int) $departmentCourse;

        $normalizedSession = str_replace('-', '/', $session);

        $data = $this->reportingService->getCourseScoreSheet(
            $departmentCourseId,
            $normalizedSession,
            $semester,
            $level
        );

        return view('reports.course-score-sheet', $data);
    }
}
