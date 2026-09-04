<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResultReportingService
{
    public function __construct(
        protected GradeCalculationService $gradeCalculator,
        protected AcademicProgressionService $progressionService
    ) {}

    /**
     * Generate an official course-level score sheet with student breakdown and summary statistics.
     *
     * @param int $departmentCourseId
     * @param string $session
     * @param string $semester
     * @param int|null $levelId
     * @return array
     */
    public function getCourseScoreSheet(
        int $departmentCourseId,
        string $session,
        string $semester,
        ?int $levelId = null
    ): array {
        $departmentCourse = DepartmentCourse::with(['department', 'studentCourse'])
            ->findOrFail($departmentCourseId);

        // Fetch course allocation if exists
        $allocation = CourseAllocation::with('lecturer')
            ->where('department_course_id', $departmentCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->first();

        // Build results query
        $resultsQuery = Result::with(['student.academicDetail.studentLevel', 'academicDetail.studentLevel', 'courseVersion'])
            ->where('department_course_id', $departmentCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester);

        if ($levelId !== null) {
            $resultsQuery->where(function ($query) use ($levelId) {
                $query->where('level_snapshot', $levelId)
                    ->orWhereHas('academicDetail', function ($q) use ($levelId) {
                        $q->where('student_level_id', $levelId);
                    });
            });
        }

        $results = $resultsQuery->get();

        // Sort students by Matric No
        $sortedResults = $results->sortBy(function (Result $res) {
            return $res->student?->academicDetail?->matric_no 
                ?? $res->academicDetail?->matric_no 
                ?? $res->student?->name 
                ?? '';
        })->values();

        $studentsList = [];
        $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        $totalScoresList = [];
        $satCount = 0;
        $absentCount = 0;
        $passedCount = 0;
        $failedCount = 0;

        foreach ($sortedResults as $result) {
            $user = $result->student;
            $acadDetail = $result->academicDetail ?? $user?->academicDetail;
            $matricNo = $acadDetail?->matric_no ?? 'N/A';
            $studentName = $user ? trim("{$user->surname} {$user->firstname} {$user->m_name}") : 'Unknown Student';
            if (empty(trim($studentName))) {
                $studentName = $user?->name ?? 'Unknown Student';
            }

            $ca = $result->ca_score !== null ? (float) $result->ca_score : null;
            $exam = $result->exam_score !== null ? (float) $result->exam_score : null;
            $total = $result->total_score !== null ? (float) $result->total_score : null;

            $grade = $result->grade ?? ($total !== null ? $this->gradeCalculator->calculateGrade($total) : 'F');
            $gradePoint = $result->grade_point ?? ($total !== null ? $this->gradeCalculator->calculateGradePoint($total) : 0);
            $units = (int) ($result->credit_units_snapshot ?? $result->credit_units ?? $departmentCourse->units ?? 0);
            $qualityPoints = $gradePoint * $units;

            if ($total !== null) {
                $satCount++;
                $totalScoresList[] = $total;
                if ($total >= 45.0 && $grade !== 'F') {
                    $passedCount++;
                } else {
                    $failedCount++;
                }

                if (isset($gradeCounts[$grade])) {
                    $gradeCounts[$grade]++;
                }
            } else {
                $absentCount++;
            }

            $studentsList[] = [
                'result_id' => $result->id,
                'user_id' => $result->user_id,
                'matric_no' => $matricNo,
                'student_name' => strtoupper($studentName),
                'ca_score' => $ca,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade,
                'grade_point' => $gradePoint,
                'credit_units' => $units,
                'quality_points' => $qualityPoints,
                'status' => $result->status,
                'remarks' => $result->remarks,
            ];
        }

        $totalCount = count($studentsList);
        $highestScore = !empty($totalScoresList) ? max($totalScoresList) : 0.0;
        $lowestScore = !empty($totalScoresList) ? min($totalScoresList) : 0.0;
        $averageScore = !empty($totalScoresList) ? round(array_sum($totalScoresList) / count($totalScoresList), 2) : 0.0;

        $courseCode = $results->first()?->course_code_snapshot 
            ?? $departmentCourse->studentCourse?->code 
            ?? 'N/A';
        $courseTitle = $results->first()?->course_title_snapshot 
            ?? $departmentCourse->studentCourse?->name 
            ?? 'N/A';
        $creditUnits = (int) ($results->first()?->credit_units_snapshot 
            ?? $departmentCourse->units 
            ?? 0);

        return [
            'course' => [
                'department_course_id' => $departmentCourse->id,
                'code' => $courseCode,
                'title' => $courseTitle,
                'credit_units' => $creditUnits,
                'department_id' => $departmentCourse->department_id,
                'department_name' => $departmentCourse->department?->name ?? 'N/A',
                'faculty_name' => $departmentCourse->department?->faculty ?? 'N/A',
                'lecturer_name' => $allocation?->lecturer?->name ?? 'Not Allocated',
                'session' => $session,
                'semester' => $semester,
                'level_id' => $levelId,
            ],
            'students' => $studentsList,
            'statistics' => [
                'total_students' => $totalCount,
                'sat_exam' => $satCount,
                'absent' => $absentCount,
                'passed' => $passedCount,
                'failed' => $failedCount,
                'pass_percentage' => $satCount > 0 ? round(($passedCount / $satCount) * 100, 1) : 0.0,
                'fail_percentage' => $satCount > 0 ? round(($failedCount / $satCount) * 100, 1) : 0.0,
                'highest_score' => $highestScore,
                'lowest_score' => $lowestScore,
                'average_score' => $averageScore,
                'grade_distribution' => $gradeCounts,
            ],
        ];
    }

    /**
     * Generate the consolidated Departmental / Senate Broadsheet matrix.
     *
     * @param array{
     *     department_id: int,
     *     course_id?: int|null,
     *     student_level_id?: int|null,
     *     academic_session: string,
     *     semester: string,
     *     admission_session?: string|null
     * } $filters
     * @return array
     */
    public function getDepartmentalBroadsheet(array $filters): array
    {
        $departmentId = (int) $filters['department_id'];
        $courseId = !empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $levelId = !empty($filters['student_level_id']) ? (int) $filters['student_level_id'] : null;
        $session = (string) $filters['academic_session'];
        $semester = (string) $filters['semester'];
        $admissionSession = !empty($filters['admission_session']) ? (string) $filters['admission_session'] : null;

        $department = Department::findOrFail($departmentId);
        $level = $levelId ? StudentLevel::find($levelId) : null;
        $programmeCourse = $courseId ? Course::find($courseId) : null;

        // 1. Fetch Students in this cohort/department/level
        $studentsQuery = User::with(['academicDetail.course', 'academicDetail.studentLevel'])
            ->whereHas('academicDetail', function ($query) use ($departmentId, $courseId, $levelId, $admissionSession) {
                $query->where('department_id', $departmentId);

                if ($courseId !== null) {
                    $query->where('course_id', $courseId);
                }

                if ($levelId !== null) {
                    $query->where('student_level_id', $levelId);
                }

                if ($admissionSession !== null) {
                    $query->where('admission_session', $admissionSession);
                }
            });

        $students = $studentsQuery->get()->sortBy(function (User $u) {
            return $u->academicDetail?->matric_no ?? $u->name;
        })->values();

        $studentIds = $students->pluck('id')->all();

        // 2. Fetch all Results for these students in this session & semester
        $results = Result::with(['departmentCourse.studentCourse'])
            ->whereIn('user_id', $studentIds)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->get();

        // 3. Fetch cumulative ResultGpaRecords for these students up to this session & semester
        $gpaRecords = ResultGpaRecord::whereIn('user_id', $studentIds)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->get()
            ->keyBy('user_id');

        // 4. Fetch all active carry-overs for remark calculation
        $carryOvers = CarryOverCourse::whereIn('user_id', $studentIds)
            ->active()
            ->with(['departmentCourse.studentCourse'])
            ->get()
            ->groupBy('user_id');

        // 5. Extract unique courses across all results to form columns
        $courseColumns = [];
        foreach ($results as $res) {
            $code = $res->course_code_snapshot 
                ?? $res->departmentCourse?->studentCourse?->code 
                ?? "CRS-{$res->department_course_id}";
            $title = $res->course_title_snapshot 
                ?? $res->departmentCourse?->studentCourse?->name 
                ?? 'Course';
            $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $res->departmentCourse?->units ?? 0);

            if (!isset($courseColumns[$code])) {
                $courseColumns[$code] = [
                    'code' => $code,
                    'title' => $title,
                    'units' => $units,
                    'department_course_id' => $res->department_course_id,
                ];
            }
        }

        // Sort course columns alphabetically by course code
        ksort($courseColumns);
        $headers = array_values($courseColumns);

        // 6. Build Student Matrix Rows
        $broadsheetRows = [];
        $studentResultsGrouped = $results->groupBy('user_id');

        foreach ($students as $student) {
            $acadDetail = $student->academicDetail;
            $matricNo = $acadDetail?->matric_no ?? 'N/A';
            $studentName = trim("{$student->surname} {$student->firstname} {$student->m_name}");
            if (empty(trim($studentName))) {
                $studentName = $student->name ?? 'Unknown Student';
            }

            /** @var Collection<int, Result> $userResults */
            $userResults = $studentResultsGrouped->get($student->id, collect());
            $resultsByCode = $userResults->keyBy(function (Result $r) {
                return $r->course_code_snapshot 
                    ?? $r->departmentCourse?->studentCourse?->code 
                    ?? "CRS-{$r->department_course_id}";
            });

            // Semester Units and Points calculation
            $uts = 0; // Units Taken Semester
            $gpts = 0; // Grade Points Semester
            $courseScoresMap = [];
            $failedCoursesThisSemester = [];

            foreach ($headers as $hdr) {
                $code = $hdr['code'];
                /** @var Result|null $res */
                $res = $resultsByCode->get($code);

                if ($res) {
                    $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $hdr['units']);
                    $total = $res->total_score !== null ? (float) $res->total_score : null;
                    $grade = $res->grade ?? ($total !== null ? $this->gradeCalculator->calculateGrade($total) : 'F');
                    $gradePoint = $res->grade_point ?? ($total !== null ? $this->gradeCalculator->calculateGradePoint($total) : 0);
                    $qualityPoints = $gradePoint * $units;

                    $uts += $units;
                    $gpts += $qualityPoints;

                    if ($grade === 'F' || ($total !== null && $total < 45.0)) {
                        $failedCoursesThisSemester[] = $code;
                    }

                    $courseScoresMap[$code] = [
                        'ca' => $res->ca_score !== null ? (float) $res->ca_score : null,
                        'exam' => $res->exam_score !== null ? (float) $res->exam_score : null,
                        'total' => $total,
                        'grade' => $grade,
                        'grade_point' => $gradePoint,
                        'units' => $units,
                        'quality_points' => $qualityPoints,
                        'status' => $res->status,
                    ];
                } else {
                    $courseScoresMap[$code] = null;
                }
            }

            $gpa = $uts > 0 ? round($gpts / $uts, 2) : 0.00;

            // Cumulative Calculation
            /** @var ResultGpaRecord|null $gpaRecord */
            $gpaRecord = $gpaRecords->get($student->id);

            if ($gpaRecord && $gpaRecord->cumulative_credit_units > 0) {
                $utd = (int) $gpaRecord->cumulative_credit_units;
                $gptd = (int) $gpaRecord->cumulative_grade_points;
                $cgpa = (float) $gpaRecord->cumulative_gpa;
                $classOfDegree = $gpaRecord->class_of_degree ?? $this->gradeCalculator->getClassOfDegree($cgpa);
            } else {
                // Fallback: Compute on the fly from all historical approved results
                $allUserResults = Result::where('user_id', $student->id)
                    ->whereIn('status', ['hod_approved', 'exam_officer_approved', 'released'])
                    ->get();

                $cumCalc = $this->gradeCalculator->calculateSemesterGpa($allUserResults);
                $utd = $cumCalc['total_units'] > 0 ? $cumCalc['total_units'] : $uts;
                $gptd = $cumCalc['total_points'] > 0 ? $cumCalc['total_points'] : $gpts;
                $cgpa = $utd > 0 ? round($gptd / $utd, 2) : $gpa;
                $classOfDegree = $this->gradeCalculator->getClassOfDegree($cgpa);
            }

            // Determine Academic Standing & Official Senate Remarks
            $standingInfo = $this->progressionService->determineAcademicStanding($student);
            $standing = $standingInfo['standing'] ?? AcademicProgressionService::STANDING_PROMOTED;

            // Build course breakdown items for Nigerian Senate report
            $courseBreakdownItems = [];
            foreach ($resultsByCode as $code => $res) {
                $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? 0);
                $grade = $res->grade ?? ($res->total_score !== null ? $this->gradeCalculator->calculateGrade((float) $res->total_score) : 'F');
                $courseBreakdownItems[] = "{$code} - {$units} - {$grade}";
            }

            // Determine Academic Standing & Status matching Affiliation Broadsheet standards
            $studentCarryOvers = $carryOvers->get($student->id, collect());
            $activeCarryCodes = $studentCarryOvers->map(function (CarryOverCourse $co) {
                return $co->departmentCourse?->studentCourse?->code ?? 'N/A';
            })->filter()->all();

            $allUnclearedCourses = array_values(array_unique(array_merge($failedCoursesThisSemester, $activeCarryCodes)));

            $statusText = null;
            if ($cgpa < 0.50) {
                $statusText = 'WITHDRAWN FROM THE UNIVERSITY';
            } elseif ($cgpa < 0.75) {
                $statusText = 'WITHDRAWN FROM PROGRAM';
            } elseif ($cgpa < 1.00 || $cgpa < 1.50) {
                $statusText = 'ON PROBATION';
            }

            $isPass = empty($allUnclearedCourses) && $cgpa >= 1.50;

            $broadsheetRows[] = [
                'user_id' => $student->id,
                'matric_no' => $matricNo,
                'student_name' => $studentName,
                'level' => $acadDetail?->studentLevel?->level ?? $level?->level ?? '100',
                'course_scores' => $courseScoresMap,
                'course_breakdown_items' => $courseBreakdownItems,
                'uts' => $uts,
                'gpts' => $gpts,
                'gpa' => $gpa,
                'utd' => $utd,
                'gptd' => $gptd,
                'cgpa_ls' => '-',
                'cgpa' => $cgpa,
                'class_of_degree' => $classOfDegree,
                'standing' => $standing,
                'is_pass' => $isPass,
                'repeat_courses' => $allUnclearedCourses,
                'status_text' => $statusText,
                'remark' => !empty($allUnclearedCourses) ? 'REPEAT: ' . implode(', ', $allUnclearedCourses) : ($isPass ? 'PASS' : ($statusText ?? 'PASS')),
            ];

        }

        $summaryStats = $this->getSenateSummaryStats(collect($broadsheetRows));

        return [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'faculty' => $department->faculty ?? 'N/A',
            ],
            'programme' => $programmeCourse?->name ?? 'All Department Programmes',
            'session' => $session,
            'semester' => $semester,
            'level' => $level?->level ?? ($levelId ? "{$levelId}00" : 'All Levels'),
            'headers' => $headers,
            'students' => $broadsheetRows,
            'summary' => $summaryStats,
        ];
    }

    /**
     * Compute Senate aggregate distribution statistics across a cohort.
     *
     * @param Collection<int, array> $broadsheetStudents
     * @return array
     */
    public function getSenateSummaryStats(Collection $broadsheetStudents): array
    {
        $total = $broadsheetStudents->count();
        $passCount = 0;
        $probationCount = 0;
        $withdrawnCount = 0;
        $repeatCount = 0;
        $spilloverCount = 0;

        $classDistribution = [
            'First Class Honours' => 0,
            'Second Class Upper Division' => 0,
            'Second Class Lower Division' => 0,
            'Third Class Honours' => 0,
            'Pass' => 0,
            'Fail' => 0,
        ];

        foreach ($broadsheetStudents as $student) {
            $standing = $student['standing'] ?? '';
            $remark = (string) ($student['remark'] ?? '');
            $statusText = (string) ($student['status_text'] ?? '');
            $repeatCourses = $student['repeat_courses'] ?? [];
            $class = (string) ($student['class_of_degree'] ?? '');

            $isPass = (bool) ($student['is_pass'] ?? false);
            if (!$isPass && ($remark === 'PASS' || str_starts_with($remark, 'PASS'))) {
                $isPass = true;
            }

            if ($isPass) {
                $passCount++;
            } else {
                if (!empty($repeatCourses) || str_starts_with($remark, 'REPEAT')) {
                    $repeatCount++;
                }
                if ($statusText === 'ON PROBATION' || $remark === 'PROBATION' || $standing === AcademicProgressionService::STANDING_PROBATION) {
                    $probationCount++;
                } elseif (str_contains($statusText, 'WITHDRAWN') || str_contains($remark, 'WITHDRAWN')) {
                    $withdrawnCount++;
                } elseif (str_contains($statusText, 'SPILLOVER') || $remark === 'SPILLOVER' || $standing === AcademicProgressionService::STANDING_SPILLOVER) {
                    $spilloverCount++;
                }
            }

            if (isset($classDistribution[$class])) {
                $classDistribution[$class]++;
            }
        }

        $othersCount = max(0, $total - $passCount);

        return [
            'total_students' => $total,
            'pass_count' => $passCount,
            'probation_count' => $probationCount,
            'withdrawn_count' => $withdrawnCount,
            'repeat_count' => $repeatCount,
            'spillover_count' => $spilloverCount,
            'special_cases_count' => 0,
            'others_count' => $othersCount,
            'pass_percentage' => $total > 0 ? round(($passCount / $total) * 100, 1) : 0.0,
            'probation_percentage' => $total > 0 ? round(($probationCount / $total) * 100, 1) : 0.0,
            'withdrawn_percentage' => $total > 0 ? round(($withdrawnCount / $total) * 100, 1) : 0.0,
            'repeat_percentage' => $total > 0 ? round(($repeatCount / $total) * 100, 1) : 0.0,
            'spillover_percentage' => $total > 0 ? round(($spilloverCount / $total) * 100, 1) : 0.0,
            'special_cases_percentage' => 0.0,
            'others_percentage' => $total > 0 ? round(($othersCount / $total) * 100, 1) : 0.0,
            'class_distribution' => $classDistribution,
        ];
    }

}
