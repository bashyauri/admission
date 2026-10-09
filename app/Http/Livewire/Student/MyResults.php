<?php

declare(strict_types=1);

namespace App\Http\Livewire\Student;

use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\User;
use App\Enums\StudentStatus;
use App\Services\AcademicProgressionService;
use App\Services\GradeCalculationService;
use App\Services\StudentStatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyResults extends Component
{
    public string $selectedSession = 'all';

    public array $availableSessions = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        $this->loadAvailableSessions();
    }

    public function loadAvailableSessions(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $sessions = Result::query()
            ->where('user_id', $user->id)
            ->where('status', 'released')
            ->orderBy('academic_session', 'desc')
            ->pluck('academic_session')
            ->unique()
            ->values()
            ->toArray();

        $this->availableSessions = $sessions;
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $academicDetail = $user->academicDetail ? $user->academicDetail->loadMissing(['department', 'programme', 'studentLevel', 'course']) : null;

        // Query released results
        $resultsQuery = Result::query()
            ->with([
                'departmentCourse.studentCourse',
                'registeredCourse.departmentCourse.studentCourse',
            ])
            ->where('user_id', $user->id)
            ->where('status', 'released');

        if ($this->selectedSession !== 'all' && !empty($this->selectedSession)) {
            $resultsQuery->where('academic_session', $this->selectedSession);
        }

        $rawResults = $resultsQuery
            ->orderBy('academic_session', 'desc')
            ->orderBy('semester', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate a historical running CGPA for each semester from released attempts.
        // Stored GPA records may contain a cumulative value calculated after later terms.
        $allReleasedResults = Result::query()
            ->with(['registeredCourse', 'departmentCourse.studentCourse'])
            ->where('user_id', $user->id)
            ->where('status', 'released')
            ->orderBy('academic_session')
            ->orderByRaw("CASE WHEN LOWER(semester) IN ('first', '1') THEN 1 WHEN LOWER(semester) IN ('second', '2') THEN 2 ELSE 3 END")
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $groupedResults = [];
        $sessions = $rawResults->pluck('academic_session')->unique()->values();

        $gradeService = app(GradeCalculationService::class);
        $courseSnapshotService = app(\App\Services\ResultCourseSnapshotService::class);
        $progressionService = app(AcademicProgressionService::class);
        $isUndergraduate = $user->isUndergraduate();
        if ($isUndergraduate) {
            foreach ($rawResults as $result) {
                $result->setAttribute('resolved_course_snapshot', $courseSnapshotService->resolve($result));
            }
        }
        $unitsForResult = static fn (Result $result): int => $isUndergraduate
            ? $courseSnapshotService->units($result)
            : (int) ($result->credit_units_snapshot
                ?? $result->credit_units
                ?? $result->departmentCourse?->units
                ?? 0);

        $cumulativeCgpaBySemester = [];
        $runningCreditUnits = 0;
        $runningQualityPoints = 0;

        foreach ($allReleasedResults as $releasedResult) {
            $units = $unitsForResult($releasedResult);
            $gradePoint = (int) ($releasedResult->grade_point ?? $gradeService->calculateGradePoint($releasedResult->grade ?? 'F'));
            $runningCreditUnits += $units;
            $runningQualityPoints += $gradePoint * $units;

            if ($runningCreditUnits > 0) {
                $semesterKey = $releasedResult->academic_session . '_' . strtolower((string) $releasedResult->semester);
                $cumulativeCgpaBySemester[$semesterKey] = round($runningQualityPoints / $runningCreditUnits, 2);
            }
        }

        // Fetch GPA records for semester GPA values and related academic metadata.
        $gpaRecords = ResultGpaRecord::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy(function ($record) {
                return $record->academic_session . '_' . strtolower($record->semester);
            });

        foreach ($sessions as $session) {
            $sessionResults = $rawResults->where('academic_session', $session);
            $semesters = ['first', 'second'];

            foreach ($semesters as $semester) {
                $semesterCourses = $sessionResults->filter(function ($res) use ($semester) {
                    return strtolower($res->semester ?? '') === $semester;
                })->values();

                if ($semesterCourses->isNotEmpty()) {
                    $key = $session . '_' . $semester;
                    $gpaRecord = $gpaRecords->get($key);

                    // Compute or extract metrics
                    $tcr = 0; // Total Credit Registered
                    $tcp = 0; // Total Credit Passed
                    $tqp = 0; // Total Quality Points

                    foreach ($semesterCourses as $res) {
                        $units = $unitsForResult($res);
                        $gp = (int) ($res->grade_point ?? $gradeService->calculateGradePoint($res->grade ?? 'F'));
                        $tcr += $units;
                        $tqp += ($gp * $units);
                        if (strtoupper((string) $res->grade) !== 'F') {
                            $tcp += $units;
                        }
                    }

                    $gpa = $gpaRecord ? (float) $gpaRecord->semester_gpa : ($tcr > 0 ? round($tqp / $tcr, 2) : 0.0);
                    $cgpa = $cumulativeCgpaBySemester[$key] ?? null;

                    $groupedResults[$session][$semester] = [
                        'courses' => $semesterCourses,
                        'tcr' => $tcr,
                        'tcp' => $tcp,
                        'tqp' => $tqp,
                        'gpa' => $gpa,
                        'cgpa' => $cgpa,
                        'gpa_record' => $gpaRecord,
                    ];
                }
            }
        }

        // Overall cumulative calculation from the same released attempts.

        $totalTcr = 0;
        $totalTcp = 0;
        $totalTqp = 0;

        foreach ($allReleasedResults as $res) {
            $units = $unitsForResult($res);
            $gp = (int) ($res->grade_point ?? $gradeService->calculateGradePoint($res->grade ?? 'F'));
            $totalTcr += $units;
            $totalTqp += ($gp * $units);
            if (strtoupper((string) $res->grade) !== 'F') {
                $totalTcp += $units;
            }
        }

        $overallCgpa = $totalTcr > 0 ? round($totalTqp / $totalTcr, 2) : 0.0;
        $classOfDegree = $totalTcr > 0 ? $gradeService->getClassOfDegree($overallCgpa) : 'N/A';
        $academicStanding = $progressionService->determineAcademicStanding($user);
        $officialStatus = $isUndergraduate
            ? app(StudentStatusService::class)->getCurrentStatus($user)
            : null;
        $hasSenateConfirmedAcademicWithdrawal = in_array($officialStatus?->status, [
            StudentStatus::ACADEMIC_WITHDRAWAL_PROGRAM,
            StudentStatus::ACADEMIC_WITHDRAWAL_UNIVERSITY,
        ], true);

        if ($hasSenateConfirmedAcademicWithdrawal) {
            $academicStanding['standing'] = $officialStatus->status === StudentStatus::ACADEMIC_WITHDRAWAL_PROGRAM
                ? AcademicProgressionService::STANDING_WITHDRAWN_PROGRAM
                : AcademicProgressionService::STANDING_WITHDRAWN_UNIVERSITY;
        } else {
            if (in_array($academicStanding['standing'] ?? null, [
                AcademicProgressionService::STANDING_WITHDRAWN_PROGRAM,
                AcademicProgressionService::STANDING_WITHDRAWN_UNIVERSITY,
            ], true)) {
                $academicStanding['standing'] = 'ACADEMIC REVIEW';
            }

            if ($classOfDegree === 'Below Degree Standard') {
                $classOfDegree = 'N/A';
            }
        }

        $graduationEligibility = $user->graduationEligibility;
        $degreeCertificate = $user->degreeCertificate;

        return view('livewire.student.my-results', [
            'academicDetail' => $academicDetail,
            'groupedResults' => $groupedResults,
            'totalTcr' => $totalTcr,
            'totalTcp' => $totalTcp,
            'totalTqp' => $totalTqp,
            'overallCgpa' => $overallCgpa,
            'classOfDegree' => $classOfDegree,
            'academicStanding' => $academicStanding,
            'isUndergraduate' => $isUndergraduate,
            'graduationEligibility' => $graduationEligibility,
            'degreeCertificate' => $degreeCertificate,
        ])->layout('layouts.app');
    }
}
