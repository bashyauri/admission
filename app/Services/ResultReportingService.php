<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\DisciplinaryAction;
use App\Models\DepartmentMaxUnit;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResultReportingService
{
    protected ?StudentStatusService $studentStatusService;

    public function __construct(
        protected GradeCalculationService $gradeCalculator,
        protected AcademicProgressionService $progressionService,
        ?StudentStatusService $studentStatusService = null
    ) {
        $this->studentStatusService = $studentStatusService;
    }

    /**
     * Format academic level name into standard format (e.g., '100 Level', '200 Level').
     */
    public function formatLevelName(?string $levelVal, ?int $levelId = null): string
    {
        if (empty($levelVal) && empty($levelId)) {
            return 'All Levels';
        }

        $raw = trim((string) ($levelVal ?: $levelId));

        if (stripos($raw, 'Level') !== false) {
            return $raw;
        }

        if (is_numeric($raw)) {
            $num = (int) $raw;
            if ($num >= 1 && $num <= 9) {
                return ($num * 100) . ' Level';
            }
            if ($num >= 100 && $num <= 900) {
                return $num . ' Level';
            }
        }

        return $raw . ' Level';
    }

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
        $courseSnapshotService = app(ResultCourseSnapshotService::class);
        $departmentCourse = DepartmentCourse::with(['department', 'studentCourse'])
            ->findOrFail($departmentCourseId);

        // Fetch course allocation if exists
        $allocation = CourseAllocation::with('lecturer')
            ->where('department_course_id', $departmentCourseId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->first();

        // Build results query
        $resultsQuery = Result::with(['student.academicDetail.studentLevel', 'academicDetail.studentLevel', 'courseVersion', 'registeredCourse'])
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
            $units = $courseSnapshotService->units($result);
            $qualityPoints = $gradePoint * $units;

            if ($total !== null) {
                $satCount++;
                $totalScoresList[] = $total;
                if ($total >= 40.0 && $grade !== 'F') {
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

        $firstCourseSnapshot = $results->first()
            ? $courseSnapshotService->resolve($results->first())
            : [];
        $courseCode = $firstCourseSnapshot['code']
            ?? $departmentCourse->studentCourse?->code 
            ?? 'N/A';
        // StudentCourse uses 'title' column (not 'name')
        $courseTitle = $firstCourseSnapshot['title']
            ?? $departmentCourse->studentCourse?->title 
            ?? 'N/A';
        $creditUnits = (int) (($firstCourseSnapshot['units'] ?? null)
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
                // User model uses surname + firstname fields, not a single 'name' column
                'lecturer_name' => $allocation?->lecturer
                    ? trim(($allocation->lecturer->surname ?? '') . ' ' . ($allocation->lecturer->firstname ?? ''))
                    : 'Not Allocated',
                'session' => $session,
                'semester' => $semester,
                'level_id' => $levelId,
                'level_name' => $this->formatLevelName(null, $levelId),
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
        $courseSnapshotService = app(ResultCourseSnapshotService::class);
        $departmentId = (int) $filters['department_id'];
        $courseId = !empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $levelId = !empty($filters['student_level_id']) ? (int) $filters['student_level_id'] : null;
        $session = (string) $filters['academic_session'];
        $semester = (string) $filters['semester'];
        $admissionSession = !empty($filters['admission_session']) ? (string) $filters['admission_session'] : null;

        $department = Department::findOrFail($departmentId);
        $level = $levelId ? StudentLevel::find($levelId) : null;
        $programmeCourse = $courseId ? Course::find($courseId) : null;

        // 1. Fetch Students in this cohort/department/level (Undergraduate degree only)
        $studentsQuery = User::with(['academicDetail.course', 'academicDetail.studentLevel', 'proposedCourse'])
            ->where('programme_id', ProgrammesEnum::Undergraduate->value)
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
        $statusService = $this->studentStatusService ??= app(StudentStatusService::class);
        $statusesForSession = $statusService->getStatusesForSession($studentIds, $session);
        $disciplinaryActions = DisciplinaryAction::query()
            ->whereIn('user_id', $studentIds)
            ->where('is_active', true)
            ->where(function ($query) use ($session) {
                $query->where('academic_session', $session)
                    ->orWhere('effective_session', $session)
                    ->orWhereNull('effective_session');
            })
            ->orderByDesc('verdict_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('user_id');

        $releasedOnly = !empty($filters['released_only']) || !empty($filters['final']);

        // 2. Fetch all Results for these students in this session (across both semesters for full session broadsheet)
        $resultsQuery = Result::with(['departmentCourse.studentCourse', 'registeredCourse'])
            ->whereIn('user_id', $studentIds)
            ->where('academic_session', $session);

        if ($releasedOnly) {
            $resultsQuery->where('status', 'released');
        }

        $singleSemesterOnly = !empty($filters['single_semester_only'])
            && !empty($semester)
            && $semester !== 'all'
            && $semester !== 'session';

        if ($singleSemesterOnly) {
            $resultsQuery->where('semester', $semester);
        }

        if (!$singleSemesterOnly) {
            $resultsQuery->whereIn('semester', ['first', 'second']);
        }

        $results = $resultsQuery->get();

        $stageSummary = [
            'pending' => 0,
            'submitted' => 0,
            'exam_officer_approved' => 0,
            'released' => 0,
        ];
        foreach ($results as $res) {
            $st = (string) $res->status;
            $stageSummary[$st] = ($stageSummary[$st] ?? 0) + 1;
        }

        $unreleasedCount = $stageSummary['pending'] + $stageSummary['submitted'] + $stageSummary['exam_officer_approved'];
        $isProvisional = !$releasedOnly && ($unreleasedCount > 0 || $results->isEmpty());

        $includedStages = [];
        if ($stageSummary['pending'] > 0) { $includedStages[] = 'Lecturer Draft'; }
        if ($stageSummary['submitted'] > 0) { $includedStages[] = 'Coordinator Review'; }
        if ($stageSummary['exam_officer_approved'] > 0) { $includedStages[] = 'Exam Officer Review'; }
        if ($stageSummary['released'] > 0) { $includedStages[] = 'Released'; }

        $workflowStagesIncluded = $isProvisional
            ? ($includedStages !== [] ? 'Provisional Review (' . implode(', ', $includedStages) . ')' : 'Provisional Staff Review')
            : 'Official Senate Final (Released Results Only)';

        // 3. Fetch cumulative ResultGpaRecords for these students for this session
        $gpaRecords = ResultGpaRecord::whereIn('user_id', $studentIds)
            ->where('academic_session', $session)
            ->orderByDesc('id')
            ->get()
            ->groupBy('user_id')
            ->map(fn($recs) => $recs->first());

        // Derive previous academic session for CGPA LS (e.g. 2024/2025 -> 2023/2024)
        $parts = explode('/', $session);
        $prevSession = (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1]))
            ? ((int) $parts[0] - 1) . '/' . ((int) $parts[1] - 1)
            : null;

        $prevGpaRecords = $prevSession
            ? ResultGpaRecord::whereIn('user_id', $studentIds)
                ->where('academic_session', $prevSession)
                ->orderByDesc('id')
                ->get()
                ->groupBy('user_id')
                ->map(fn($recs) => $recs->first())
            : collect();

        // 4. Fetch all active carry-overs for remark calculation
        $carryOvers = CarryOverCourse::whereIn('user_id', $studentIds)
            ->active()
            ->with(['departmentCourse.studentCourse', 'registeredCourse'])
            ->get()
            ->groupBy('user_id');

        // 5. Extract unique courses across all results to form columns
        $courseColumns = [];
        foreach ($results as $res) {
            $courseSnapshot = $courseSnapshotService->resolve($res);
            $code = $courseSnapshot['code']
                ?? $res->departmentCourse?->studentCourse?->code 
                ?? "CRS-{$res->department_course_id}";
            $title = $courseSnapshot['title']
                ?? $res->departmentCourse?->studentCourse?->title
                ?? 'Course';
            $units = $courseSnapshot['units'];

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
            $resultsByCode = $userResults->keyBy(function (Result $r) use ($courseSnapshotService) {
                return $courseSnapshotService->resolve($r)['code']
                    ?? $r->departmentCourse?->studentCourse?->code 
                    ?? "CRS-{$r->department_course_id}";
            });

            // Session Units and Points calculation (UTS & GPTS)
            $uts = 0; // Units Taken Session
            $gpts = 0; // Grade Points Session
            $courseScoresMap = [];
            $failedCoursesThisSession = [];

            foreach ($headers as $hdr) {
                $code = $hdr['code'];
                /** @var Result|null $res */
                $res = $resultsByCode->get($code);

                if ($res) {
                    $units = $courseSnapshotService->units($res) ?: (int) $hdr['units'];
                    $total = $res->total_score !== null ? (float) $res->total_score : null;
                    $grade = $res->grade ?? ($total !== null ? $this->gradeCalculator->calculateGrade($total) : 'F');
                    $gradePoint = $res->grade_point ?? ($total !== null ? $this->gradeCalculator->calculateGradePoint($total) : 0);
                    $qualityPoints = $gradePoint * $units;

                    $uts += $units;
                    $gpts += $qualityPoints;

                    if ($grade === 'F' || ($total !== null && $total < 40.0)) {
                        $failedCoursesThisSession[] = $code;
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

            // Cumulative Calculation (UTD & GPTD & CGPA)
            /** @var ResultGpaRecord|null $gpaRecord */
            $gpaRecord = $gpaRecords->get($student->id);

            if ($gpaRecord && $gpaRecord->cumulative_credit_units > 0) {
                $utd = (int) $gpaRecord->cumulative_credit_units;
                $gptd = (int) $gpaRecord->cumulative_grade_points;
                $cgpa = (float) $gpaRecord->cumulative_gpa;
                $classOfDegree = $gpaRecord->class_of_degree ?? $this->gradeCalculator->getClassOfDegree($cgpa);
            } else {
                // Fallback: Compute on the fly from all historical released results
                $allUserResults = Result::with(['departmentCourse.studentCourse', 'registeredCourse'])
                    ->where('user_id', $student->id)
                    ->where('status', 'released')
                    ->get();

                $cumCalc = $this->gradeCalculator->calculateSemesterGpa($allUserResults);
                $utd = $cumCalc['total_units'] > 0 ? $cumCalc['total_units'] : $uts;
                $gptd = $cumCalc['total_points'] > 0 ? $cumCalc['total_points'] : $gpts;
                $cgpa = $utd > 0 ? round($gptd / $utd, 2) : $gpa;
                $classOfDegree = $this->gradeCalculator->getClassOfDegree($cgpa);
            }

            // Last Session CGPA (CGPA LS)
            /** @var ResultGpaRecord|null $prevGpa */
            $prevGpa = $prevGpaRecords->get($student->id);
            $cgpaLs = ($prevGpa && $prevGpa->cumulative_gpa > 0)
                ? number_format((float) $prevGpa->cumulative_gpa, 2)
                : '-';

            // Determine Academic Standing & Official Senate Remarks
            $standingInfo = $this->progressionService->determineAcademicStanding($student);
            $standing = $standingInfo['standing'] ?? AcademicProgressionService::STANDING_PROMOTED;

            // Build course breakdown items for Nigerian Senate report
            $courseBreakdownItems = [];
            foreach ($resultsByCode as $code => $res) {
                $units = $courseSnapshotService->units($res);
                $grade = $res->grade ?? ($res->total_score !== null ? $this->gradeCalculator->calculateGrade((float) $res->total_score) : 'F');
                $courseBreakdownItems[] = "{$code} - {$units} - {$grade}";
            }

            // Determine Academic Standing & Status matching Affiliation Broadsheet standards
            $studentCarryOvers = $carryOvers->get($student->id, collect());
            $activeCarryCodes = $studentCarryOvers->map(function (CarryOverCourse $co) {
                return $co->registeredCourse?->course_code_snapshot
                    ?? $co->departmentCourse?->studentCourse?->code
                    ?? 'N/A';
            })->filter()->all();

            $allUnclearedCourses = array_values(array_unique(array_merge($failedCoursesThisSession, $activeCarryCodes)));

            $hasResults = ($uts > 0 || $utd > 0 || !empty($courseBreakdownItems));
            $statusRecord = $statusesForSession->get($student->id);
            $isOfficiallyWithdrawn = $statusRecord?->status?->isWithdrawn() === true;
            $statusText = $isOfficiallyWithdrawn
                ? 'WITHDRAWN: ' . strtoupper($statusRecord->status->label())
                : null;
            $disciplinaryRemark = $disciplinaryActions->get($student->id, collect())
                ->map(fn (DisciplinaryAction $action) => $this->formatDisciplinaryRemark($action))
                ->filter()
                ->values()
                ->implode(' | ');

            if (!$hasResults) {
                // Student has no exam records for this cohort/level (e.g. Direct Entry starting at 200L, unexamined, or deferred)
                $statusText ??= $student->isDe ? 'DIRECT ENTRY (200L ENTRY)' : 'NO REGISTRATION / NO RESULT';
                $remark = $isOfficiallyWithdrawn
                    ? $statusText
                    : ($student->isDe ? 'D.E. CANDIDATE (STARTS AT 200L)' : 'NO RESULT');
                $isPass = false;
            } else {
                $isPass = !$isOfficiallyWithdrawn
                    && empty($allUnclearedCourses)
                    && $cgpa >= 1.00;  // Updated to match FUBK policy (CGPA >= 1.00 is good standing)
                if (!$isOfficiallyWithdrawn) {
                    $statusText = match ($standing) {
                        AcademicProgressionService::STANDING_PROBATION => 'ON PROBATION',
                        AcademicProgressionService::STANDING_REPEAT => 'REPEAT LEVEL',
                        default => null,
                    };
                }

                if ($isOfficiallyWithdrawn) {
                    $remark = $statusText;
                } elseif ($standing === AcademicProgressionService::STANDING_REPEAT) {
                    $repeatList = !empty($allUnclearedCourses) ? ' | REPEAT: ' . implode(', ', $allUnclearedCourses) : '';
                    $remark = 'REPEAT LEVEL' . $repeatList;
                } elseif (!empty($allUnclearedCourses)) {
                    $remark = 'REPEAT: ' . implode(', ', $allUnclearedCourses);
                } else {
                    $remark = $isPass ? 'PASS' : ($statusText ?? 'PASS');
                }
            }

            $broadsheetRows[] = [
                'user_id' => $student->id,
                'matric_no' => $matricNo,
                'student_name' => $studentName,
                'level' => $this->formatLevelName($acadDetail?->studentLevel?->level, (int) ($acadDetail?->student_level_id ?? $levelId)),
                'course_scores' => $courseScoresMap,
                'course_breakdown_items' => $courseBreakdownItems,
                'uts' => $uts,
                'gpts' => $gpts,
                'gpa' => $gpa,
                'utd' => $utd,
                'gptd' => $gptd,
                'cgpa_ls' => $cgpaLs,
                'cgpa' => $cgpa,
                'class_of_degree' => $classOfDegree,
                'standing' => $standing,
                'is_pass' => $isPass,
                'repeat_courses' => $allUnclearedCourses,
                'status_text' => $statusText,
                'status_is_withdrawn' => $isOfficiallyWithdrawn,
                'status_display' => $isOfficiallyWithdrawn ? 'WITHDRAWN: ' . strtoupper($statusRecord->status->label()) : $statusText,
                'status_session' => $isOfficiallyWithdrawn ? $statusRecord->academic_session : null,
                'status_effective_date' => $isOfficiallyWithdrawn ? $statusRecord->effective_date?->toDateString() : null,
                'status_senate_reference' => $isOfficiallyWithdrawn ? $statusRecord->senate_reference : null,
                'remark' => trim(implode(' | ', array_filter([$remark, $disciplinaryRemark]))),
                'disciplinary_remarks' => $disciplinaryRemark ?: null,
            ];

        }

        $summaryStats = $this->getSenateSummaryStats(collect($broadsheetRows));

        $deptMaxUnit = $levelId 
            ? DepartmentMaxUnit::where('department_id', $departmentId)->where('student_level_id', $levelId)->value('max_units')
            : DepartmentMaxUnit::where('department_id', $departmentId)->value('max_units');

        $maxUnitsTs = $deptMaxUnit ? (int) ($deptMaxUnit * 2) : 48;
        $minUnitsTs = $deptMaxUnit ? (int) floor($deptMaxUnit * 1.5) : 30;

        return [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'faculty' => $department->faculty ?? 'AFFILIATION',
            ],
            'programme' => $programmeCourse?->name ?? ($department->name ? 'B.SC. ' . $department->name : 'All Department Programmes'),
            'session' => $session,
            'semester' => $semester,
            'level' => $this->formatLevelName($level?->level, $levelId),
            'min_units_ts' => $minUnitsTs,
            'max_units_ts' => $maxUnitsTs,
            'min_units_td' => 0,
            'max_units_td' => 0,
            'headers' => $headers,
            'students' => $broadsheetRows,
            'summary' => $summaryStats,
            'is_provisional' => $isProvisional,
            'workflow_stages_included' => $workflowStagesIncluded,
            'stage_summary' => $stageSummary,
        ];
    }

    protected function formatDisciplinaryRemark(DisciplinaryAction $action): string
    {
        $reference = trim((string) ($action->senate_ref_no ?: 'SENATE REF PENDING'));

        return match ($action->sanction_type) {
            'course_cancellation' => 'DISCIPLINARY: EXAM MALPRACTICE (SDC: ' . $reference . ')',
            'repeat_session' => 'DISCIPLINARY: REPEAT SESSION (SDC: ' . $reference . ')',
            'suspension' => 'DISCIPLINARY: SUSPENSION (SDC: ' . $reference . ')',
            'expulsion' => 'DISCIPLINARY: EXPULSION (SDC: ' . $reference . ')',
            default => 'DISCIPLINARY: ACTION (SDC: ' . $reference . ')',
        };
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
        $specialCasesCount = 0;

        $classDistribution = [
            'First Class Honours' => 0,
            'Second Class Upper Division' => 0,
            'Second Class Lower Division' => 0,
            'Third Class Honours' => 0,
            'Pass' => 0,
            'Below Degree Standard' => 0,
            'Fail' => 0,
        ];

        foreach ($broadsheetStudents as $student) {
            $standing = $student['standing'] ?? '';
            $remark = (string) ($student['remark'] ?? '');
            $statusText = (string) ($student['status_text'] ?? '');
            $repeatCourses = $student['repeat_courses'] ?? [];
            $class = (string) ($student['class_of_degree'] ?? '');
            $uts = (int) ($student['uts'] ?? 0);
            $utd = (int) ($student['utd'] ?? 0);

            $isPass = (bool) ($student['is_pass'] ?? false);
            if (!$isPass && ($remark === 'PASS' || str_starts_with($remark, 'PASS'))) {
                $isPass = true;
            }

            if (!empty($repeatCourses) || ($standing === AcademicProgressionService::STANDING_REPEAT) || str_starts_with($remark, 'REPEAT')) {
                $repeatCount++;
            }

            if (str_contains($statusText, 'WITHDRAWN')) {
                $withdrawnCount++;
            } elseif ($isPass) {
                $passCount++;
            } elseif ($statusText === 'ON PROBATION' || $remark === 'PROBATION' || $standing === AcademicProgressionService::STANDING_PROBATION) {
                $probationCount++;
            } elseif (str_contains($statusText, 'SPILLOVER') || $remark === 'SPILLOVER' || $standing === AcademicProgressionService::STANDING_SPILLOVER) {
                $spilloverCount++;
            } elseif ($uts === 0 && $utd === 0) {
                // Students with 0 examination units (Direct Entry, unexamined, special cases)
                $specialCasesCount++;
            } else {
                $specialCasesCount++;
            }

            if (isset($classDistribution[$class]) && ($uts > 0 || $utd > 0)) {
                $classDistribution[$class]++;
            }
        }

        $othersCount = max(0, $total - ($passCount + $probationCount + $withdrawnCount + $specialCasesCount));

        return [
            'total_students' => $total,
            'pass_count' => $passCount,
            'probation_count' => $probationCount,
            'withdrawn_count' => $withdrawnCount,
            'repeat_count' => $repeatCount,
            'spillover_count' => $spilloverCount,
            'special_cases_count' => $specialCasesCount,
            'others_count' => $othersCount,
            'pass_percentage' => $total > 0 ? round(($passCount / $total) * 100, 1) : 0.0,
            'probation_percentage' => $total > 0 ? round(($probationCount / $total) * 100, 1) : 0.0,
            'withdrawn_percentage' => $total > 0 ? round(($withdrawnCount / $total) * 100, 1) : 0.0,
            'repeat_percentage' => $total > 0 ? round(($repeatCount / $total) * 100, 1) : 0.0,
            'spillover_percentage' => $total > 0 ? round(($spilloverCount / $total) * 100, 1) : 0.0,
            'special_cases_percentage' => $total > 0 ? round(($specialCasesCount / $total) * 100, 1) : 0.0,
            'others_percentage' => $total > 0 ? round(($othersCount / $total) * 100, 1) : 0.0,
            'class_distribution' => $classDistribution,
        ];
    }

    /**
     * Generate the consolidated Senate Graduation Broadsheet matrix.
     *
     * @param array{
     *     academic_session: string,
     *     department_id?: int|null,
     *     course_id?: int|null,
     *     cleared_only?: bool
     * } $filters
     * @return array<string, mixed>
     */
    public function getSenateGraduationBroadsheet(array $filters): array
    {
        $session = (string) $filters['academic_session'];
        $departmentId = !empty($filters['department_id']) ? (int) $filters['department_id'] : null;
        $courseId = !empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $clearedOnly = !empty($filters['cleared_only']);

        $department = $departmentId ? Department::find($departmentId) : null;
        $programmeCourse = $courseId ? Course::find($courseId) : null;

        // Query GraduationEligibility for Undergraduate candidates
        $query = GraduationEligibility::with([
            'user.academicDetail.department',
            'user.academicDetail.programme',
            'user.academicDetail.course',
            'user.academicDetail.studentLevel',
            'user.proposedCourse',
            'academicDetail.department',
            'academicDetail.programme',
            'academicDetail.course',
            'clearedBy',
        ])
        ->where('academic_session', $session)
        ->whereHas('user', function ($q) use ($departmentId, $courseId) {
            $q->where('programme_id', ProgrammesEnum::Undergraduate->value);
            if ($departmentId !== null || $courseId !== null) {
                $q->whereHas('academicDetail', function ($ad) use ($departmentId, $courseId) {
                    if ($departmentId !== null) {
                        $ad->where('department_id', $departmentId);
                    }
                    if ($courseId !== null) {
                        $ad->where('course_id', $courseId);
                    }
                });
            }
        });

        if ($clearedOnly) {
            $query->where('is_cleared', true);
        }

        $eligibilityRecords = $query->get();

        // If no eligibility records exist yet for this session/department, fallback to scanning final-year candidates in AcademicDetails
        if ($eligibilityRecords->isEmpty()) {
            $studentsQuery = User::with([
                'academicDetail.department',
                'academicDetail.programme',
                'academicDetail.course',
                'academicDetail.studentLevel',
                'proposedCourse',
            ])
            ->where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->whereHas('academicDetail', function ($ad) use ($departmentId, $courseId) {
                if ($departmentId !== null) {
                    $ad->where('department_id', $departmentId);
                }
                if ($courseId !== null) {
                    $ad->where('course_id', $courseId);
                }
            });

            $students = $studentsQuery->get()->sortBy(function (User $u) {
                return $u->academicDetail?->matric_no ?? $u->name;
            })->values();

            $graduationService = app(GraduationService::class);
            $eligibilityRecords = collect();

            foreach ($students as $student) {
                $audit = $graduationService->checkEligibility($student, $session);
                /** @var GraduationEligibility $eligibility */
                $eligibility = $audit['eligibility'];
                $eligibility->setRelation('user', $student);
                $eligibility->setRelation('academicDetail', $student->academicDetail);
                $eligibilityRecords->push($eligibility);
            }
        }

        $studentIds = $eligibilityRecords->pluck('user_id')->unique()->filter()->all();

        // Fetch cumulative GPA records for CQP and UTD
        $gpaRecords = ResultGpaRecord::whereIn('user_id', $studentIds)
            ->where('academic_session', $session)
            ->latest('id')
            ->get()
            ->keyBy('user_id');

        $sortedRecords = $eligibilityRecords->sortBy(function (GraduationEligibility $rec) {
            return $rec->academicDetail?->matric_no ?? $rec->user?->academicDetail?->matric_no ?? $rec->user?->name ?? '';
        })->values();

        $graduandRows = [];

        foreach ($sortedRecords as $rec) {
            $user = $rec->user;
            $acad = $rec->academicDetail ?? $user?->academicDetail;
            $matricNo = $acad?->matric_no ?? 'N/A';
            $studentName = trim(($user?->surname ?? '') . ' ' . ($user?->firstname ?? '') . ' ' . ($user?->m_name ?? ''));
            if (empty($studentName)) {
                $studentName = $user?->name ?? 'Unknown Graduand';
            }

            $entrySession = $acad?->admission_session ?? $acad?->acad_session ?? 'N/A';
            $isDe = $user?->isDe ?? false;
            $modeOfEntry = $isDe ? 'Direct Entry (200L)' : 'UTME (100L)';
            $deptName = $acad?->department?->name ?? $department?->name ?? 'N/A';
            $progName = $acad?->course?->name ?? $acad?->programme?->name ?? $programmeCourse?->name ?? 'B.Sc Degree';

            $totalUnitsEarned = (int) $rec->total_units_earned;
            $totalUnitsRequired = (int) $rec->total_units_required;
            $finalCgpa = (float) $rec->final_cgpa;
            $classOfDegree = $rec->class_of_degree ?? $this->gradeCalculator->getClassOfDegree($finalCgpa);

            /** @var ResultGpaRecord|null $gpaRec */
            $gpaRec = $gpaRecords->get($rec->user_id);
            $cqp = $gpaRec?->cumulative_grade_points ? (int) $gpaRec->cumulative_grade_points : (int) round($finalCgpa * $totalUnitsEarned);

            $statusText = $rec->is_cleared
                ? 'CLEARED FOR GRADUATION'
                : ($rec->meets_requirements ? 'QUALIFIED (PENDING SENATE CLEARANCE)' : 'DEFICIENT');

            $graduandRows[] = [
                'eligibility_id' => $rec->id,
                'user_id' => $rec->user_id,
                'matric_no' => $matricNo,
                'student_name' => $studentName,
                'entry_session' => $entrySession,
                'mode_of_entry' => $modeOfEntry,
                'department_name' => $deptName,
                'programme_name' => $progName,
                'total_units_required' => $totalUnitsRequired,
                'total_units_earned' => $totalUnitsEarned,
                'cqp' => $cqp,
                'final_cgpa' => $finalCgpa,
                'class_of_degree' => $classOfDegree,
                'meets_requirements' => (bool) $rec->meets_requirements,
                'is_cleared' => (bool) $rec->is_cleared,
                'cleared_by_name' => $rec->clearedBy ? trim(($rec->clearedBy->surname ?? '') . ' ' . ($rec->clearedBy->firstname ?? '')) : null,
                'cleared_at' => $rec->cleared_at?->format('d/m/Y H:i'),
                'status_text' => $statusText,
                'remarks' => $rec->remarks ?? ($rec->meets_requirements ? 'Degree Conferment Recommended' : 'Requirements Not Met'),
            ];
        }

        $summaryStats = $this->getSenateGraduationSummaryStats(collect($graduandRows));

        return [
            'department' => [
                'id' => $department?->id,
                'name' => $department?->name ?? 'All Academic Departments',
                'faculty' => $department?->faculty ?? 'Faculty of Science / Affiliation Directorate',
            ],
            'programme' => $programmeCourse?->name ?? 'All Undergraduate Degree Programmes',
            'session' => $session,
            'graduands' => $graduandRows,
            'summary' => $summaryStats,
        ];
    }

    /**
     * Compute institutional summary statistics for Senate Graduation Broadsheet.
     *
     * @param Collection<int, array<string, mixed>> $graduands
     * @return array<string, mixed>
     */
    public function getSenateGraduationSummaryStats(Collection $graduands): array
    {
        $total = $graduands->count();
        $clearedCount = 0;
        $qualifiedCount = 0;
        $deficientCount = 0;

        $firstClass = 0;
        $secondUpper = 0;
        $secondLower = 0;
        $thirdClass = 0;
        $passCount = 0;

        foreach ($graduands as $g) {
            if (!empty($g['is_cleared'])) {
                $clearedCount++;
            }
            if (!empty($g['meets_requirements'])) {
                $qualifiedCount++;
            } else {
                $deficientCount++;
            }

            $class = (string) ($g['class_of_degree'] ?? '');
            if (str_contains($class, 'First Class')) {
                $firstClass++;
            } elseif (str_contains($class, 'Upper') || str_contains($class, '2.1') || str_contains($class, 'Second Class (Upper')) {
                $secondUpper++;
            } elseif (str_contains($class, 'Lower') || str_contains($class, '2.2') || str_contains($class, 'Second Class (Lower')) {
                $secondLower++;
            } elseif (str_contains($class, 'Third Class')) {
                $thirdClass++;
            } elseif (str_contains($class, 'Pass')) {
                $passCount++;
            }
        }

        return [
            'total_graduands' => $total,
            'cleared_count' => $clearedCount,
            'qualified_count' => $qualifiedCount,
            'deficient_count' => $deficientCount,
            'first_class_count' => $firstClass,
            'first_class_percentage' => $total > 0 ? round(($firstClass / $total) * 100, 1) : 0.0,
            'second_upper_count' => $secondUpper,
            'second_upper_percentage' => $total > 0 ? round(($secondUpper / $total) * 100, 1) : 0.0,
            'second_lower_count' => $secondLower,
            'second_lower_percentage' => $total > 0 ? round(($secondLower / $total) * 100, 1) : 0.0,
            'third_class_count' => $thirdClass,
            'third_class_percentage' => $total > 0 ? round(($thirdClass / $total) * 100, 1) : 0.0,
            'pass_count' => $passCount,
            'pass_percentage' => $total > 0 ? round(($passCount / $total) * 100, 1) : 0.0,
            'deficient_percentage' => $total > 0 ? round(($deficientCount / $total) * 100, 1) : 0.0,
        ];
    }

   /**
 * Generate the consolidated Cohort Progression Master Broadsheet across all academic sessions,
 * including full chronological journey, session GPAs, cumulative CGPA, and a detailed
 * Carry-Over Incurred vs Cleared Resolution Ledger.
 *
 * @param array{
 *     department_id?: int|null,
 *     admission_session: string,
 *     course_id?: int|null,
 *     student_level_id?: int|null,
 *     status?: string|null
 * } $filters
 * @return array<string, mixed>
 */
public function getCohortProgressionBroadsheet(array $filters): array
{
    $courseSnapshotService = app(ResultCourseSnapshotService::class);
    $departmentId     = !empty($filters['department_id']) ? (int) $filters['department_id'] : null;
    $admissionSession = !empty($filters['admission_session']) ? (string) $filters['admission_session'] : 'all';
    $levelId          = !empty($filters['student_level_id']) ? (int) $filters['student_level_id'] : null;
    $courseId         = !empty($filters['course_id']) ? (int) $filters['course_id'] : null;

    $department      = $departmentId ? Department::findOrFail($departmentId) : null;
    $programmeCourse = $courseId ? Course::find($courseId) : null;
    $level           = $levelId ? StudentLevel::find($levelId) : null;

    // 1. Fetch Students belonging to this department & admission cohort (Undergraduate only)
    $studentsQuery = User::with([
        'academicDetail.course',
        'academicDetail.studentLevel',
        'academicDetail.programme',
        'proposedCourse',
    ])
    ->where('programme_id', ProgrammesEnum::Undergraduate->value)
    ->whereHas('academicDetail', function ($q) use ($departmentId, $courseId, $levelId, $admissionSession) {
        if ($departmentId !== null) {
            $q->where('department_id', $departmentId);
        }

        if ($courseId !== null) {
            $q->where('course_id', $courseId);
        }

        if ($levelId !== null) {
            $q->where('student_level_id', $levelId);
        }

        if (!empty($admissionSession) && strtolower($admissionSession) !== 'all') {
            $q->where(function ($sq) use ($admissionSession) {
                $sq->where('admission_session', $admissionSession)
                   ->orWhere('acad_session', $admissionSession);
            });
        }
    });

    $students = $studentsQuery->get()->sortBy(function (User $u) {
        return $u->academicDetail?->matric_no ?? $u->name;
    })->values();

    $studentIds = $students->pluck('id')->all();

    $releasedOnly = !empty($filters['released_only']) || !empty($filters['final']);

    // 2. Fetch all historical results for these students ordered chronologically
    $rawResultsQuery = Result::with(['departmentCourse.studentCourse', 'registeredCourse', 'courseVersion'])
        ->whereIn('user_id', $studentIds);

    if ($releasedOnly) {
        $rawResultsQuery->where('status', 'released');
    } else {
        $rawResultsQuery->whereIn('status', ['pending', 'submitted', 'hod_approved', 'exam_officer_approved', 'released']);
    }

    $rawResults = $rawResultsQuery->orderBy('academic_session', 'asc')
        ->orderByRaw("CASE WHEN LOWER(semester) = 'first' THEN 1 ELSE 2 END")
        ->orderBy('created_at', 'asc')
        ->get();

    $stageSummary = [
        'pending' => 0,
        'submitted' => 0,
        'exam_officer_approved' => 0,
        'released' => 0,
    ];
    foreach ($rawResults as $res) {
        $st = (string) $res->status;
        $stageSummary[$st] = ($stageSummary[$st] ?? 0) + 1;
    }

    $unreleasedCount = $stageSummary['pending'] + $stageSummary['submitted'] + $stageSummary['exam_officer_approved'];
    $isProvisional = !$releasedOnly && ($unreleasedCount > 0 || $rawResults->isEmpty());

    $includedStages = [];
    if ($stageSummary['pending'] > 0) { $includedStages[] = 'Lecturer Draft'; }
    if ($stageSummary['submitted'] > 0) { $includedStages[] = 'Coordinator Review'; }
    if ($stageSummary['exam_officer_approved'] > 0) { $includedStages[] = 'Exam Officer Review'; }
    if ($stageSummary['released'] > 0) { $includedStages[] = 'Released'; }

    $workflowStagesIncluded = $isProvisional
        ? ($includedStages !== [] ? 'Provisional Review (' . implode(', ', $includedStages) . ')' : 'Provisional Staff Review')
        : 'Official Senate Final (Released Results Only)';

    $resultsGroupedByStudent = $rawResults->groupBy('user_id');

    // 3. Fetch all carry-over records (both active and cleared)
    $carryOversGrouped = CarryOverCourse::with([
        'departmentCourse.studentCourse',
        'registeredCourse',
        'clearedResult.departmentCourse.studentCourse',
    ])
    ->whereIn('user_id', $studentIds)
    ->orderBy('failed_session', 'asc')
    ->orderBy('failed_semester', 'asc')
    ->get()
    ->groupBy('user_id');

    // 4. Fetch ResultGpaRecords
    $gpaRecordsGrouped = ResultGpaRecord::whereIn('user_id', $studentIds)
        ->orderBy('academic_session', 'asc')
        ->orderByRaw("CASE WHEN LOWER(semester) = 'first' THEN 1 ELSE 2 END")
        ->get()
        ->groupBy('user_id');

    $statusService = $this->studentStatusService ??= app(StudentStatusService::class);
    $cohortStatuses = $statusService->getCurrentStatuses($studentIds);
    $disciplinaryActions = DisciplinaryAction::query()
        ->whereIn('user_id', $studentIds)
        ->where('is_active', true)
        ->orderBy('effective_session')
        ->get()
        ->groupBy('user_id');

    // Identify all unique academic sessions present across the entire cohort
    $cohortSessions = $rawResults->pluck('academic_session')
        ->filter()
        ->unique()
        ->values()
        ->all();

    sort($cohortSessions);

    $studentRows               = [];
    $totalOutstandingCarryOvers = 0;
    $totalResolvedCarryOvers   = 0;
    $studentsWithCleanRecord   = 0;
    $studentsWithResolvedOnly  = 0;
    $studentsWithDeficiencies  = 0;
    $studentsWithdrawnCount    = 0;

    foreach ($students as $student) {
        $acadDetail  = $student->academicDetail;
        $matricNo    = $acadDetail?->matric_no ?? 'N/A';
        $studentName = trim("{$student->surname} {$student->firstname} {$student->m_name}");
        if (empty(trim($studentName))) {
            $studentName = $student->name ?? 'Unknown Student';
        }

        $statusRec = $cohortStatuses->get($student->id);
        $isCohortWithdrawn = $statusRec?->status?->isWithdrawn() === true;
        $officialStatusRemark = $isCohortWithdrawn
            ? 'WITHDRAWN: ' . strtoupper((string) $statusRec->status->label())
            : null;
        $disciplinaryRemark = $disciplinaryActions->get($student->id, collect())
            ->map(fn (DisciplinaryAction $action) => $this->formatDisciplinaryRemark($action))
            ->filter()
            ->values()
            ->implode(' | ');

        /** @var Collection<int, Result> $userResults */
        $userResults = $resultsGroupedByStudent->get($student->id, collect());

        // Build chronological sessions progression
        $sessionsProgression = [];
        $runningCcr = 0; // Cumulative Credit Registered
        $runningCcp = 0; // Cumulative Credit Passed
        $runningCqp = 0; // Cumulative Quality Points

        $userSessions = $userResults->pluck('academic_session')->filter()->unique()->values();

        // Sessional course collections for carryover detection
        $failedAttempts = [];
        $passedAttempts = [];

        foreach ($userResults as $r) {
            $courseSnapshot = $courseSnapshotService->resolve($r);
            $code  = $courseSnapshot['code']
                ?? $r->departmentCourse?->studentCourse?->code
                ?? "CRS-{$r->department_course_id}";
            $units = $courseSnapshot['units'];
            $total = $r->total_score !== null ? (float) $r->total_score : null;
            $grade = strtoupper(trim((string) ($r->grade ?? ($total !== null ? $this->gradeCalculator->calculateGrade($total) : 'F'))));

            $isFail = ($grade === 'F' || ($total !== null && $total < 40.0));
            if ($isFail) {
                $failedAttempts[] = [
                    'result_id'            => $r->id,
                    'course_code'          => $code,
                    'course_title'         => $courseSnapshot['title']
                        ?? $r->departmentCourse?->studentCourse?->title
                        ?? 'Course',
                    'units'                => $units,
                    'session'              => $r->academic_session,
                    'semester'             => ucfirst((string) $r->semester),
                    'score'                => $total,
                    'grade'                => $grade,
                    'department_course_id' => $r->department_course_id,
                ];
            } else {
                $passedAttempts[] = [
                    'result_id'            => $r->id,
                    'course_code'          => $code,
                    'units'                => $units,
                    'session'              => $r->academic_session,
                    'semester'             => ucfirst((string) $r->semester),
                    'score'                => $total,
                    'grade'                => $grade,
                    'department_course_id' => $r->department_course_id,
                ];
            }
        }

        foreach ($userSessions as $uSession) {
            $sessionResults = $userResults->where('academic_session', $uSession);

            $semestersMap = [];
            $sessionTcr   = 0;
            $sessionTcp   = 0;
            $sessionTqp   = 0;

            foreach (['first', 'second'] as $semKey) {
                $semResults = $sessionResults->filter(fn ($r) => strtolower((string) $r->semester) === $semKey);
                if ($semResults->isEmpty()) {
                    continue;
                }

                $coursesList = [];
                $semTcr      = 0;
                $semTcp      = 0;
                $semTqp      = 0;

                foreach ($semResults as $res) {
                    $courseSnapshot = $courseSnapshotService->resolve($res);
                    $code       = $courseSnapshot['code']
                        ?? $res->departmentCourse?->studentCourse?->code
                        ?? "CRS-{$res->department_course_id}";
                    $title      = $courseSnapshot['title']
                        ?? $res->departmentCourse?->studentCourse?->title
                        ?? 'Course';
                    $units      = $courseSnapshot['units'];
                    $total      = $res->total_score !== null ? (float) $res->total_score : null;
                    $grade      = $res->grade ?? ($total !== null ? $this->gradeCalculator->calculateGrade($total) : 'F');
                    $gradePoint = (int) ($res->grade_point ?? ($total !== null ? $this->gradeCalculator->calculateGradePoint($total) : 0));
                    $qp         = $units * $gradePoint;

                    $semTcr += $units;
                    $semTqp += $qp;
                    if ($grade !== 'F' && ($total === null || $total >= 40.0)) {
                        $semTcp += $units;
                    }

                    $coursesList[] = [
                        'code'           => $code,
                        'title'          => $title,
                        'units'          => $units,
                        'score'          => $total,
                        'grade'          => $grade,
                        'grade_point'    => $gradePoint,
                        'quality_points' => $qp,
                        'is_repeated'    => (bool) $res->is_repeated,
                    ];
                }

                $semGpa = $semTcr > 0 ? round($semTqp / $semTcr, 2) : 0.00;

                $semestersMap[$semKey] = [
                    'semester' => ucfirst($semKey),
                    'tcr'      => $semTcr,
                    'tcp'      => $semTcp,
                    'tqp'      => $semTqp,
                    'gpa'      => $semGpa,
                    'courses'  => $coursesList,
                ];

                $sessionTcr += $semTcr;
                $sessionTcp += $semTcp;
                $sessionTqp += $semTqp;
            }

            $sessionGpa = $sessionTcr > 0 ? round($sessionTqp / $sessionTcr, 2) : 0.00;

            $runningCcr  += $sessionTcr;
            $runningCcp  += $sessionTcp;
            $runningCqp  += $sessionTqp;
            $runningCgpa  = $runningCcr > 0 ? round($runningCqp / $runningCcr, 2) : 0.00;

            $sessionsProgression[$uSession] = [
                'session'       => $uSession,
                'tcr'           => $sessionTcr,
                'tcp'           => $sessionTcp,
                'tqp'           => $sessionTqp,
                'session_gpa'   => $sessionGpa,
                'running_ccr'   => $runningCcr,
                'running_ccp'   => $runningCcp,
                'running_cqp'   => $runningCqp,
                'running_cgpa'  => $runningCgpa,
                'semesters'     => $semestersMap,
            ];
        }

        // Overall Cumulative calculations
        $finalCgpa     = $runningCcr > 0 ? round($runningCqp / $runningCcr, 2) : 0.00;
        $classOfDegree = $this->gradeCalculator->getClassOfDegree($finalCgpa);

        // Build Carry-Over Incurred vs Cleared Ledger
        /** @var Collection<int, CarryOverCourse> $studentCarryOvers */
        $studentCarryOvers = $carryOversGrouped->get($student->id, collect());
        $carryOverLedger   = [];
        $handledCodes      = [];

        // A. Incorporate records from carry_over_courses table
        foreach ($studentCarryOvers as $co) {
            $code  = $co->registeredCourse?->course_code_snapshot
                ?? $co->departmentCourse?->studentCourse?->code
                ?? "CRS-{$co->department_course_id}";
            $title = $co->registeredCourse?->course_title_snapshot
                ?? $co->departmentCourse?->studentCourse?->title
                ?? $co->departmentCourse?->studentCourse?->title
                ?? 'Course';
            $units = (int) ($co->registeredCourse?->credit_units_snapshot
                ?? $co->registeredCourse?->units
                ?? $co->departmentCourse?->units
                ?? 0);

            $isCleared    = (bool) $co->is_cleared;
            $clearedScore = $co->clearedResult?->total_score !== null ? (float) $co->clearedResult->total_score : null;
            $clearedGrade = $co->clearedResult?->grade;

            // Check if student passed it later
            if (!$isCleared) {
                $passing = collect($passedAttempts)->first(function ($pa) use ($co, $code) {
                    return ($pa['department_course_id'] == $co->department_course_id
                            || strtoupper($pa['course_code']) === strtoupper($code))
                        && $pa['session'] >= $co->failed_session;
                });
                if ($passing) {
                    $isCleared             = true;
                    $clearedScore          = $passing['score'];
                    $clearedGrade          = $passing['grade'];
                    $co->retake_session    = $passing['session'];
                    $co->retake_semester   = $passing['semester'];
                }
            }

            $carryOverLedger[] = [
                'course_code'     => $code,
                'course_title'    => $title,
                'units'           => $units,
                'failed_session'  => $co->failed_session,
                'failed_semester' => ucfirst((string) $co->failed_semester),
                'failed_score'    => $co->failed_score !== null ? (float) $co->failed_score : null,
                'failed_grade'    => $co->failed_grade ?? 'F',
                'is_cleared'      => $isCleared,
                'cleared_at'      => $co->cleared_at ? $co->cleared_at->format('d/m/Y') : null,
                'retake_session'  => $co->retake_session,
                'retake_semester' => $co->retake_semester ? ucfirst((string) $co->retake_semester) : null,
                'cleared_score'   => $clearedScore,
                'cleared_grade'   => $clearedGrade,
                'status'          => $isCleared ? 'CLEARED' : 'OUTSTANDING',
            ];

            $handledCodes[] = strtoupper($code) . '_' . $co->failed_session;
        }

        // B. Auto-detect any failed results that were NOT in carry_over_courses table
        foreach ($failedAttempts as $fa) {
            $key = strtoupper($fa['course_code']) . '_' . $fa['session'];
            if (in_array($key, $handledCodes, true)) {
                continue;
            }

            $clearingAttempt = collect($passedAttempts)->first(function ($pa) use ($fa) {
                return ($pa['department_course_id'] == $fa['department_course_id']
                        || strtoupper($pa['course_code']) === strtoupper($fa['course_code']))
                    && ($pa['session'] > $fa['session']
                        || ($pa['session'] === $fa['session'] && $pa['semester'] !== $fa['semester']));
            });

            $isCleared = $clearingAttempt !== null;
            $carryOverLedger[] = [
                'course_code'     => $fa['course_code'],
                'course_title'    => $fa['course_title'],
                'units'           => $fa['units'],
                'failed_session'  => $fa['session'],
                'failed_semester' => $fa['semester'],
                'failed_score'    => $fa['score'],
                'failed_grade'    => $fa['grade'],
                'is_cleared'      => $isCleared,
                'cleared_at'      => null,
                'retake_session'  => $clearingAttempt['session'] ?? null,
                'retake_semester' => $clearingAttempt['semester'] ?? null,
                'cleared_score'   => $clearingAttempt['score'] ?? null,
                'cleared_grade'   => $clearingAttempt['grade'] ?? null,
                'status'          => $isCleared ? 'CLEARED' : 'OUTSTANDING',
            ];
            $handledCodes[] = $key;
        }

        $hasOutstanding = collect($carryOverLedger)->contains('status', 'OUTSTANDING');
        $hasResolved    = collect($carryOverLedger)->contains('status', 'CLEARED');

        if ($hasResolved) {
            $totalResolvedCarryOvers += collect($carryOverLedger)->where('status', 'CLEARED')->count();
        }
        if ($hasOutstanding) {
            $totalOutstandingCarryOvers += collect($carryOverLedger)->where('status', 'OUTSTANDING')->count();
        }

        // Categorize student compliance
        if ($isCohortWithdrawn) {
            $studentsWithdrawnCount++;
            $standingRemark = 'WITHDRAWN: ' . strtoupper($statusRec->status->label());
        } elseif (empty($carryOverLedger)) {
            $studentsWithCleanRecord++;
            $standingRemark = 'PASS (CLEAN RECORD)';
        } elseif (!$hasOutstanding) {
            $studentsWithResolvedOnly++;
            $standingRemark = 'PASS (ALL CARRY-OVERS CLEARED)';
        } else {
            $studentsWithDeficiencies++;
            $outstandingCodes = collect($carryOverLedger)
                ->where('status', 'OUTSTANDING')
                ->pluck('course_code')
                ->all();
            $standingRemark = 'DEFICIENT: ' . implode(', ', $outstandingCodes);
        }

        $displayRemark = trim(implode(' | ', array_filter([
            $disciplinaryRemark ?: null,
            $officialStatusRemark ?: null,
            $standingRemark,
        ])));

        $entryMode = !empty($student->isDe) || str_contains(strtolower((string) $acadDetail?->student_level_id), '200')
            ? 'DIRECT ENTRY (200L)'
            : 'UTME (100L)';

        $studentRows[] = [
            'user_id'                    => $student->id,
            'matric_no'                  => $matricNo,
            'student_name'               => $studentName,
            'entry_session'              => $acadDetail?->admission_session ?? $admissionSession,
            'entry_mode'                 => $entryMode,
            'current_level'              => $this->formatLevelName($acadDetail?->studentLevel?->level, (int) $acadDetail?->student_level_id),
            'programme'                  => $acadDetail?->course?->name ?? $programmeCourse?->name ?? 'Degree',
            'sessions'                   => $sessionsProgression,
            'total_ccr'                  => $runningCcr,
            'total_ccp'                  => $runningCcp,
            'total_cqp'                  => $runningCqp,
            'final_cgpa'                 => $finalCgpa,
            'class_of_degree'            => $classOfDegree,
            'carry_overs'                => $carryOverLedger,
            'carry_overs_count'          => count($carryOverLedger),
            'has_outstanding_carryovers' => $hasOutstanding,
            'standing_remark'            => $standingRemark,
            'display_remark'             => $displayRemark,
            'disciplinary_remarks'       => $disciplinaryRemark ?: null,
            'official_status_remark'     => $officialStatusRemark,
            'is_withdrawn'               => $isCohortWithdrawn,
            'withdrawal_status'          => $isCohortWithdrawn ? $statusRec->status->label() : null,
            'withdrawal_session'         => $isCohortWithdrawn ? $statusRec->academic_session : null,
            'withdrawal_reference'       => $isCohortWithdrawn ? $statusRec->senate_reference : null,
        ];
    }

    $totalStudents = count($studentRows);

    return [
        'department' => [
            'id'      => $department?->id,
            'name'    => $department?->name ?? 'All Departments',
            'faculty' => $department?->faculty ?? 'AFFILIATION',
        ],
        'admission_session' => $admissionSession,
        'level'             => $level ? $this->formatLevelName($level->level, $level->id) : 'All Levels',
        'student_level_id'  => $levelId,
        'programme'         => $programmeCourse?->name
            ?? ($department?->name ? 'B.SC. ' . $department->name : 'All Programmes'),
        'cohort_sessions'   => $cohortSessions,
        'students'          => $studentRows,
        'statistics'        => [
            'total_students'                => $totalStudents,
            'clean_record_count'            => $studentsWithCleanRecord,
            'clean_record_percentage'       => $totalStudents > 0 ? round(($studentsWithCleanRecord / $totalStudents) * 100, 1) : 0.0,
            'resolved_carryover_count'      => $studentsWithResolvedOnly,
            'resolved_carryover_percentage' => $totalStudents > 0 ? round(($studentsWithResolvedOnly / $totalStudents) * 100, 1) : 0.0,
            'deficient_count'               => $studentsWithDeficiencies,
            'deficient_percentage'          => $totalStudents > 0 ? round(($studentsWithDeficiencies / $totalStudents) * 100, 1) : 0.0,
            'withdrawn_count'               => $studentsWithdrawnCount,
            'withdrawn_percentage'          => $totalStudents > 0 ? round(($studentsWithdrawnCount / $totalStudents) * 100, 1) : 0.0,
            'total_carryovers_recorded'     => $totalOutstandingCarryOvers + $totalResolvedCarryOvers,
            'total_carryovers_cleared'      => $totalResolvedCarryOvers,
            'total_carryovers_outstanding'  => $totalOutstandingCarryOvers,
        ],
        'is_provisional'           => $isProvisional,
        'workflow_stages_included' => $workflowStagesIncluded,
        'stage_summary'            => $stageSummary,
    ];
}

    /**
     * Generate the Official Senate Pass List for publication, grouped by Class of Degree.
     *
     * @param array{
     *     academic_session: string,
     *     department_id?: int|null,
     *     course_id?: int|null,
     *     cleared_only?: bool
     * } $filters
     * @return array<string, mixed>
     */
    public function getSenatePassList(array $filters): array
    {
        $session = (string) $filters['academic_session'];
        $departmentId = !empty($filters['department_id']) ? (int) $filters['department_id'] : null;
        $courseId = !empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $clearedOnly = !empty($filters['cleared_only']);

        $department = $departmentId ? Department::find($departmentId) : null;
        $programmeCourse = $courseId ? Course::find($courseId) : null;

        // Query GraduationListItems for students officially staged into the graduation list
        $query = GraduationListItem::with([
            'user.academicDetail.department',
            'user.academicDetail.programme',
            'user.academicDetail.course',
            'user.academicDetail.studentLevel',
            'user.proposedCourse',
            'academicDetail.department',
            'academicDetail.programme',
            'academicDetail.course',
            'academicDetail.studentLevel',
            'graduationList',
            'academicDetail.graduationEligibility',
        ])
        ->whereHas('graduationList', function ($gl) use ($session) {
            $gl->where('academic_session', $session);
        })
        ->whereHas('user', function ($q) use ($departmentId, $courseId) {
            $q->where('programme_id', ProgrammesEnum::Undergraduate->value);
            if ($departmentId !== null || $courseId !== null) {
                $q->whereHas('academicDetail', function ($ad) use ($departmentId, $courseId) {
                    if ($departmentId !== null) {
                        $ad->where('department_id', $departmentId);
                    }
                    if ($courseId !== null) {
                        $ad->where('course_id', $courseId);
                    }
                });
            }
        });

        if ($clearedOnly) {
            $query->whereHas('academicDetail.graduationEligibility', function ($ge) {
                $ge->where('is_cleared', true);
            });
        }

        $graduationListItems = $query->get();

        // If no graduation list items exist, fallback to graduation eligibility records
        if ($graduationListItems->isEmpty()) {
            $eligibilityQuery = GraduationEligibility::with([
                'academicDetail.department',
                'academicDetail.programme',
                'academicDetail.course',
                'academicDetail.studentLevel',
                'user.academicDetail.department',
                'user.academicDetail.programme',
                'user.academicDetail.course',
                'user.academicDetail.studentLevel',
                'user.proposedCourse',
                'clearedBy',
            ])
            ->where('academic_session', $session)
            ->where('meets_requirements', true)
            ->whereHas('user', function ($q) use ($departmentId, $courseId) {
                $q->where('programme_id', ProgrammesEnum::Undergraduate->value);
                if ($departmentId !== null || $courseId !== null) {
                    $q->whereHas('academicDetail', function ($ad) use ($departmentId, $courseId) {
                        if ($departmentId !== null) {
                            $ad->where('department_id', $departmentId);
                        }
                        if ($courseId !== null) {
                            $ad->where('course_id', $courseId);
                        }
                    });
                }
            });

            if ($clearedOnly) {
                $eligibilityQuery->where('is_cleared', true);
            }

            $eligibilityRecords = $eligibilityQuery->get();

            $passListRows = [];
            foreach ($eligibilityRecords as $rec) {
                $user = $rec->user;
                $acad = $rec->academicDetail ?? $user?->academicDetail;
                $matricNo = $acad?->matric_no ?? 'N/A';
                $studentName = trim(($user?->surname ?? '') . ' ' . ($user?->firstname ?? '') . ' ' . ($user?->m_name ?? ''));
                if (empty($studentName)) {
                    $studentName = $user?->name ?? 'Unknown Graduand';
                }

                $entrySession = $acad?->admission_session ?? $acad?->acad_session ?? 'N/A';
                $isDe = $user?->isDe ?? false;
                $modeOfEntry = $isDe ? 'Direct Entry (200L)' : 'UTME (100L)';
                $deptName = $acad?->department?->name ?? $department?->name ?? 'N/A';
                $progName = $acad?->course?->name ?? $acad?->programme?->name ?? $programmeCourse?->name ?? 'B.Sc Degree';

                $totalUnitsEarned = (int) $rec->total_units_earned;
                $totalUnitsRequired = (int) $rec->total_units_required;
                $finalCgpa = (float) $rec->final_cgpa;
                $classOfDegree = $rec->class_of_degree ?? $this->gradeCalculator->getClassOfDegree($finalCgpa);

                $passListRows[] = [
                    'matric_no' => $matricNo,
                    'student_name' => $studentName,
                    'entry_session' => $entrySession,
                    'mode_of_entry' => $modeOfEntry,
                    'department_name' => $deptName,
                    'programme_name' => $progName,
                    'total_units_required' => $totalUnitsRequired,
                    'total_units_earned' => $totalUnitsEarned,
                    'final_cgpa' => $finalCgpa,
                    'class_of_degree' => $classOfDegree,
                    'is_cleared' => (bool) $rec->is_cleared,
                    'remarks' => $rec->remarks ?? 'Recommended for Degree Conferment',
                ];
            }
        } else {
            $passListRows = [];
            foreach ($graduationListItems as $item) {
                $user = $item->user;
                $acad = $item->academicDetail ?? $user?->academicDetail;
                $matricNo = $item->matric_no ?? $acad?->matric_no ?? 'N/A';
                $studentName = $item->full_name ?? trim(($user?->surname ?? '') . ' ' . ($user?->firstname ?? '') . ' ' . ($user?->m_name ?? ''));
                if (empty($studentName)) {
                    $studentName = $user?->name ?? 'Unknown Graduand';
                }

                $entrySession = $acad?->admission_session ?? $acad?->acad_session ?? 'N/A';
                $isDe = $user?->isDe ?? false;
                $modeOfEntry = $isDe ? 'Direct Entry (200L)' : 'UTME (100L)';
                $deptName = $acad?->department?->name ?? $department?->name ?? 'N/A';
                $progName = $acad?->course?->name ?? $acad?->programme?->name ?? $programmeCourse?->name ?? 'B.Sc Degree';

                $finalCgpa = (float) ($item->final_cgpa ?? 0.00);
                $classOfDegree = $item->class_of_degree ?? $this->gradeCalculator->getClassOfDegree($finalCgpa);

                $passListRows[] = [
                    'matric_no' => $matricNo,
                    'student_name' => $studentName,
                    'entry_session' => $entrySession,
                    'mode_of_entry' => $modeOfEntry,
                    'department_name' => $deptName,
                    'programme_name' => $progName,
                    'total_units_required' => (int) ($item->total_units_required ?? 0),
                    'total_units_earned' => (int) ($item->total_units_earned ?? 0),
                    'final_cgpa' => $finalCgpa,
                    'class_of_degree' => $classOfDegree,
                    'is_cleared' => true,
                    'remarks' => 'Officially Staged for Degree Conferment',
                ];
            }
        }

        // Group by Class of Degree for publication format
        $groupedByClass = [];
        $classOrder = [
            'First Class Honours',
            'Second Class Upper Division',
            'Second Class Lower Division',
            'Third Class Honours',
        ];

        foreach ($classOrder as $class) {
            $groupedByClass[$class] = collect($passListRows)
                ->filter(fn($row) => str_contains((string) $row['class_of_degree'], $class))
                ->sortBy(fn($row) => $row['matric_no'])
                ->values()
                ->all();
        }

        // Add any students with unmatched class names
        $unmatchedClasses = collect($passListRows)
            ->filter(fn($row) => !collect($classOrder)->contains(fn($class) => str_contains((string) $row['class_of_degree'], $class)))
            ->sortBy(fn($row) => $row['matric_no'])
            ->values()
            ->all();

        if (!empty($unmatchedClasses)) {
            $groupedByClass['Other'] = $unmatchedClasses;
        }

        $summaryStats = $this->getSenatePassListSummaryStats(collect($passListRows));

        return [
            'department' => [
                'id' => $department?->id,
                'name' => $department?->name ?? 'All Academic Departments',
                'faculty' => $department?->faculty ?? 'Faculty of Science / Affiliation Directorate',
            ],
            'programme' => $programmeCourse?->name ?? 'All Undergraduate Degree Programmes',
            'session' => $session,
            'graduands' => $passListRows,
            'grouped_by_class' => $groupedByClass,
            'summary' => $summaryStats,
        ];
    }

    /**
     * Compute summary statistics for Senate Pass List.
     *
     * @param Collection<int, array<string, mixed>> $graduands
     * @return array<string, mixed>
     */
    public function getSenatePassListSummaryStats(Collection $graduands): array
    {
        $total = $graduands->count();

        $firstClass = 0;
        $secondUpper = 0;
        $secondLower = 0;
        $thirdClass = 0;
        $passCount = 0;

        foreach ($graduands as $g) {
            $class = (string) ($g['class_of_degree'] ?? '');
            if (str_contains($class, 'First Class')) {
                $firstClass++;
            } elseif (str_contains($class, 'Upper') || str_contains($class, '2.1') || str_contains($class, 'Second Class (Upper')) {
                $secondUpper++;
            } elseif (str_contains($class, 'Lower') || str_contains($class, '2.2') || str_contains($class, 'Second Class (Lower')) {
                $secondLower++;
            } elseif (str_contains($class, 'Third Class')) {
                $thirdClass++;
            } elseif (str_contains($class, 'Pass')) {
                $passCount++;
            }
        }

        return [
            'total_graduands' => $total,
            'first_class_count' => $firstClass,
            'first_class_percentage' => $total > 0 ? round(($firstClass / $total) * 100, 1) : 0.0,
            'second_upper_count' => $secondUpper,
            'second_upper_percentage' => $total > 0 ? round(($secondUpper / $total) * 100, 1) : 0.0,
            'second_lower_count' => $secondLower,
            'second_lower_percentage' => $total > 0 ? round(($secondLower / $total) * 100, 1) : 0.0,
            'third_class_count' => $thirdClass,
            'third_class_percentage' => $total > 0 ? round(($thirdClass / $total) * 100, 1) : 0.0,
            'pass_count' => $passCount,
            'pass_percentage' => $total > 0 ? round(($passCount / $total) * 100, 1) : 0.0,
        ];
    }

    /** Return the filtered UG withdrawal ledger and its report summaries. */
    public function getWithdrawalLedger(array $filters = []): array
    {
        $query = $this->withdrawalQuery($filters);
        $records = $query->orderByDesc('effective_date')->orderByDesc('id')->get();
        $rows = $this->formatWithdrawalRows($records);

        return [
            'report_title' => 'Student Withdrawal Ledger',
            'filters' => $filters,
            'rows' => $rows,
            'summary' => $this->getWithdrawalSummary($rows),
            'by_session' => $rows->groupBy('academic_session')->map->count()->sortKeysDesc(),
            'by_type' => $rows->groupBy('withdrawal_type')->map->count()->sortKeys(),
            'by_department' => $rows->groupBy('department')->map->count()->sortKeys(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'programmes' => Programme::query()->whereKey(ProgrammesEnum::Undergraduate->value)->orderBy('name')->get(['id', 'name']),
            'sessions' => StudentStatusRecord::query()
                ->whereIn('status', $this->withdrawalStatusValues())
                ->whereHas('user', fn ($q) => $q->where('programme_id', ProgrammesEnum::Undergraduate->value))
                ->whereNotNull('academic_session')->distinct()->orderByDesc('academic_session')->pluck('academic_session'),
            'withdrawal_types' => collect(StudentStatus::cases())->filter(fn (StudentStatus $status) => $status->isWithdrawn()),
            'senate_decisions' => $this->senateDecisionOptions(),
        ];
    }

    /** Senate view of withdrawal decisions, references, and reinstatement outcomes. */
    public function getSenateWithdrawalReport(array $filters = []): array
    {
        $data = $this->getWithdrawalLedger($filters);
        $data['report_title'] = 'Senate Withdrawal Compliance Report';
        $data['by_senate_decision'] = $data['rows']->groupBy('senate_decision')->map->count()->sortKeys();
        $data['missing_senate_reference_count'] = $data['rows']
            ->filter(fn (array $row) => in_array($row['senate_decision'], ['SENATE_APPROVED', 'APPROVED'], true) && !$row['senate_reference'])
            ->count();

        return $data;
    }

    /** Department-level withdrawal counts and status breakdowns. */
    public function getDepartmentalWithdrawalReport(array $filters = []): array
    {
        $data = $this->getWithdrawalLedger($filters);
        $data['report_title'] = 'Departmental Withdrawal Summary';
        $data['department_summary'] = $data['rows']->groupBy('department')->map(function (Collection $rows, string $department) {
            return [
                'department' => $department,
                'total' => $rows->count(),
                'academic' => $rows->where('withdrawal_type', StudentStatus::ACADEMIC_WITHDRAWAL->label())->count(),
                'voluntary' => $rows->where('withdrawal_type', StudentStatus::VOLUNTARY_WITHDRAWAL->label())->count(),
                'medical' => $rows->where('withdrawal_type', StudentStatus::MEDICAL_WITHDRAWAL->label())->count(),
                'senate_pending' => $rows->where('senate_decision', 'PENDING_SENATE')->count(),
                'reinstated' => $rows->where('reinstatement_status', 'Reinstated')->count(),
            ];
        })->values();

        return $data;
    }

    /** Reinstatement requests and decisions associated with withdrawal history. */
    public function getReinstatementReport(array $filters = []): array
    {
        $data = $this->getWithdrawalLedger($filters);
        $data['report_title'] = 'Reinstatement Tracking Report';
        $data['rows'] = $data['rows']->filter(fn (array $row) => $row['reinstatement_request_state'] !== null)->values();
        $data['by_session'] = $data['rows']->groupBy('academic_session')->map->count()->sortKeysDesc();
        $data['by_type'] = $data['rows']->groupBy('withdrawal_type')->map->count()->sortKeys();
        $data['by_department'] = $data['rows']->groupBy('department')->map->count()->sortKeys();
        $data['summary'] = [
            'total_requests' => $data['rows']->count(),
            'requested' => $data['rows']->count(),
            'pending' => $data['rows']->filter(fn (array $row) => in_array($row['reinstatement_request_state'], [
                'REINSTATEMENT_REQUESTED', 'REINSTATEMENT_DEPARTMENT_APPROVED', 'REINSTATEMENT_FACULTY_APPROVED',
            ], true))->count(),
            'reinstated' => $data['rows']->where('reinstatement_status', 'Reinstated')->count(),
            'rejected' => $data['rows']->filter(fn (array $row) => str_contains((string) $row['reinstatement_request_state'], 'REJECTED'))->count(),
        ];

        return $data;
    }

    private function withdrawalQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = StudentStatusRecord::query()
            ->with(['user.academicDetail.department', 'user.academicDetail.programme', 'academicDetail.department', 'academicDetail.programme'])
            ->whereIn('status', $this->withdrawalStatusValues())
            ->whereHas('user', fn ($q) => $q->where('programme_id', ProgrammesEnum::Undergraduate->value));

        if (!empty($filters['academic_session'])) {
            $query->where('academic_session', $filters['academic_session']);
        }
        if (!empty($filters['department_id'])) {
            $query->whereHas('academicDetail', fn ($q) => $q->where('department_id', $filters['department_id']));
        }
        if (!empty($filters['programme_id'])) {
            $query->whereHas('academicDetail', fn ($q) => $q->where('programme_id', $filters['programme_id']));
        }
        if (!empty($filters['withdrawal_type'])) {
            $type = $filters['withdrawal_type'];
            if (in_array($type, $this->withdrawalStatusValues(), true)) {
                $query->where('status', $type);
            } else {
                $query->where('status_type', $type);
            }
        }
        if (!empty($filters['status'])) {
            if (in_array($filters['status'], $this->withdrawalStatusValues(), true)) {
                $query->where('status', $filters['status']);
            } else {
                $query->where('senate_decision', $filters['status']);
            }
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('effective_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('effective_date', '<=', $filters['date_to']);
        }
        if (array_key_exists('reinstatement_eligible', $filters) && $filters['reinstatement_eligible'] !== '' && $filters['reinstatement_eligible'] !== null) {
            $query->where('reinstatement_eligible', (bool) $filters['reinstatement_eligible']);
        }
        if (!empty($filters['senate_reference'])) {
            $query->where('senate_reference', 'like', '%' . $filters['senate_reference'] . '%');
        }

        return $query;
    }

    private function withdrawalStatusValues(): array
    {
        return array_values(array_map(
            fn (StudentStatus $status) => $status->value,
            array_filter(StudentStatus::cases(), fn (StudentStatus $status) => $status->isWithdrawn())
        ));
    }

    private function senateDecisionOptions(): array
    {
        return [
            'WITHDRAWAL_RECOMMENDED', 'PENDING_SENATE', 'SENATE_APPROVED', 'SENATE_REJECTED',
        ];
    }

    private function formatWithdrawalRows(Collection $records): Collection
    {
        $userIds = $records->pluck('user_id')->unique()->all();
        $events = StudentStatusRecord::query()
            ->whereIn('user_id', $userIds)
            ->where(function ($query) {
                $query->where('reason_code', 'REINSTATEMENT_REQUEST')
                    ->orWhere('reason_code', 'SENATE_REINSTATEMENT')
                    ->orWhere('reason_code', 'REINSTATEMENT_REJECTED');
            })
            ->orderBy('effective_date')->orderBy('id')->get()->groupBy('user_id');
        $usedEvents = [];

        return $records->map(function (StudentStatusRecord $record) use ($events, &$usedEvents) {
            $user = $record->user;
            $detail = $record->academicDetail ?? $user?->academicDetail;
            $request = $events->get($record->user_id, collect())
                ->first(fn (StudentStatusRecord $event) => $event->reason_code === 'REINSTATEMENT_REQUEST'
                    && !isset($usedEvents[$event->id])
                    && (string) $event->effective_date >= (string) $record->effective_date);
            $reinstatement = $events->get($record->user_id, collect())
                ->first(fn (StudentStatusRecord $event) => in_array($event->reason_code, ['SENATE_REINSTATEMENT', 'REINSTATEMENT_REJECTED'], true)
                    && !isset($usedEvents[$event->id])
                    && (string) $event->effective_date >= (string) ($request?->effective_date ?? $record->effective_date));

            if ($request) {
                $usedEvents[$request->id] = true;
            }
            if ($reinstatement) {
                $usedEvents[$reinstatement->id] = true;
            }

            $requestState = $request?->senate_decision;
            $reinstatementStatus = match (true) {
                $reinstatement?->reason_code === 'SENATE_REINSTATEMENT' => 'Reinstated',
                $reinstatement?->reason_code === 'REINSTATEMENT_REJECTED' => 'Rejected',
                $requestState === 'REINSTATEMENT_FACULTY_APPROVED' => 'Pending Senate',
                $requestState === 'REINSTATEMENT_DEPARTMENT_APPROVED' => 'Faculty Review',
                $requestState === 'REINSTATEMENT_REQUESTED' => 'Department Review',
                str_contains((string) $requestState, 'REJECTED') => 'Rejected',
                default => $record->reinstatement_eligible ? 'Eligible' : 'Not eligible',
            };

            return [
                'id' => $record->id,
                'matric_no' => $detail?->matric_no ?? '—',
                'student_name' => trim(($user?->surname ?? '') . ' ' . ($user?->firstname ?? '') . ' ' . ($user?->m_name ?? '')),
                'programme' => $detail?->programme?->name ?? $user?->programme?->name ?? '—',
                'department' => $detail?->department?->name ?? 'Unassigned',
                'withdrawal_type' => $record->status?->label() ?? (string) $record->status,
                'status' => $record->status?->value ?? (string) $record->status,
                'academic_session' => $record->academic_session,
                'semester' => $record->semester,
                'effective_date' => $record->effective_date?->toDateString(),
                'end_date' => $record->end_date?->toDateString(),
                'senate_reference' => $record->senate_reference,
                'senate_decision' => (string) $record->senate_decision,
                'senate_decision_date' => $record->senate_decision_date?->toDateString(),
                'reinstatement_eligible' => (bool) $record->reinstatement_eligible,
                'reinstatement_status' => $reinstatementStatus,
                'reinstatement_request_state' => $requestState,
                'reinstatement_session' => $reinstatement?->academic_session,
                'reinstatement_reference' => $reinstatement?->senate_reference ?? $request?->senate_reference,
                'reinstatement_date' => $reinstatement?->effective_date?->toDateString(),
            ];
        });
    }

    private function getWithdrawalSummary(Collection $rows): array
    {
        return [
            'total_withdrawals' => $rows->count(),
            'senate_approved' => $rows->filter(fn (array $row) => in_array($row['senate_decision'], ['SENATE_APPROVED', 'APPROVED'], true))->count(),
            'pending_senate' => $rows->where('senate_decision', 'PENDING_SENATE')->count(),
            'rejected' => $rows->where('senate_decision', 'SENATE_REJECTED')->count(),
            'reinstatement_eligible' => $rows->where('reinstatement_eligible', true)->count(),
            'reinstated' => $rows->where('reinstatement_status', 'Reinstated')->count(),
        ];
    }

}
