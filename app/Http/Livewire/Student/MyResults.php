<?php

declare(strict_types=1);

namespace App\Http\Livewire\Student;

use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\User;
use App\Services\AcademicProgressionService;
use App\Services\GradeCalculationService;
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

        // Group results by academic_session, then by semester
        $groupedResults = [];
        $sessions = $rawResults->pluck('academic_session')->unique()->values();

        $gradeService = app(GradeCalculationService::class);
        $progressionService = app(AcademicProgressionService::class);

        // Fetch GPA records for fast lookup
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
                        $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $res->departmentCourse?->units ?? $res->departmentCourse?->studentCourse?->units ?? 0);
                        $gp = (int) ($res->grade_point ?? $gradeService->calculateGradePoint($res->grade ?? 'F'));
                        $tcr += $units;
                        $tqp += ($gp * $units);
                        if (strtoupper((string) $res->grade) !== 'F') {
                            $tcp += $units;
                        }
                    }

                    $gpa = $gpaRecord ? (float) $gpaRecord->semester_gpa : ($tcr > 0 ? round($tqp / $tcr, 2) : 0.0);
                    $cgpa = $gpaRecord ? (float) $gpaRecord->cumulative_gpa : null;

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

        // Overall cumulative calculation
        $allReleasedResults = Result::query()
            ->where('user_id', $user->id)
            ->where('status', 'released')
            ->get();

        $totalTcr = 0;
        $totalTcp = 0;
        $totalTqp = 0;

        foreach ($allReleasedResults as $res) {
            $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $res->departmentCourse?->units ?? 0);
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

        return view('livewire.student.my-results', [
            'academicDetail' => $academicDetail,
            'groupedResults' => $groupedResults,
            'totalTcr' => $totalTcr,
            'totalTcp' => $totalTcp,
            'totalTqp' => $totalTqp,
            'overallCgpa' => $overallCgpa,
            'classOfDegree' => $classOfDegree,
            'academicStanding' => $academicStanding,
            'isUndergraduate' => $user->isUndergraduate(),
        ])->layout('layouts.app');
    }
}
