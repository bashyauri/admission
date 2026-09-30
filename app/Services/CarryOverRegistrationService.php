<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AcademicActivity;
use App\Models\CarryOverCourse;
use App\Models\AcademicDetail;
use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CarryOverRegistrationService
{
    /**
     * NUC regulatory minimum credit units per semester.
     * This is a fixed regulatory floor — not configurable per department.
     */
    public const NUC_MIN_SEMESTER_UNITS = 15;

    /**
     * Fallback maximum when no department-level record exists in department_max_units.
     */
    public const DEFAULT_MAX_SEMESTER_UNITS = 24;

    /**
     * Record a failed result as an un-cleared carry-over course.
     */
    public function recordFailedCourse(Result $result): ?CarryOverCourse
    {
        if ($result->grade !== 'F' && (float) $result->total_score >= 40.0) {
            return null;
        }

        return CarryOverCourse::updateOrCreate(
            [
                'user_id' => $result->user_id,
                'department_course_id' => $result->department_course_id,
                'failed_session' => $result->academic_session,
            ],
            [
                'registered_course_id' => $result->registered_course_id,
                'failed_semester' => $result->semester,
                'failed_score' => $result->total_score ?? 0,
                'failed_grade' => $result->grade ?? 'F',
                'is_cleared' => false,
                'cleared_at' => null,
                'cleared_result_id' => null,
            ]
        );
    }

    /**
     * Process an approved result: if passed, clear any pending carry-over for this course.
     */
    public function processResultClearance(Result $result): bool
    {
        $isUndergraduate = $result->user?->isUndergraduate() ?? false;
        if ($isUndergraduate && $result->status !== 'released') {
            return false;
        }

        if ($result->grade === 'F' || (float) $result->total_score < 40.0) {
            $this->recordFailedCourse($result);
            return false;
        }

        // Student passed; clear pending carry-overs for this department course
        $carryOverQuery = CarryOverCourse::query()
            ->where('user_id', $result->user_id)
            ->where('is_cleared', false);

        if ($isUndergraduate) {
            $carryOverQuery->where(function ($query) use ($result): void {
                $query->where('retake_registered_course_id', $result->registered_course_id)
                    ->orWhere(function ($legacyQuery) use ($result): void {
                        $legacyQuery->where('department_course_id', $result->department_course_id)
                            ->where('failed_session', '<', $result->academic_session);
                    });
            });
        } else {
            // Preserve the previous PG carry-over clearance behavior.
            $carryOverQuery->where('department_course_id', $result->department_course_id);
        }

        $carryOvers = $carryOverQuery->get();

        foreach ($carryOvers as $carryOver) {
            $clearance = [
                'is_cleared' => true,
                'cleared_at' => now(),
                'cleared_result_id' => $result->id,
                'retake_session' => $result->academic_session,
                'retake_semester' => $result->semester,
            ];
            if ($isUndergraduate) {
                $clearance['registration_status'] = 'cleared';
                $clearance['review_reason'] = null;
            }
            $carryOver->update($clearance);
        }

        return $carryOvers->isNotEmpty();
    }

    /**
     * Get all active (un-cleared) carry over courses for a student.
     *
     * @return Collection<int, CarryOverCourse>
     */
    public function getActiveCarryOvers(User $student, ?string $semester = null): Collection
    {
        $this->syncStudentClearances($student);

        $query = CarryOverCourse::with([
            'departmentCourse.studentCourse',
            'registeredCourse',
            'retakeRegisteredCourse.departmentCourse.studentCourse',
            'approvedDepartmentCourse.studentCourse',
            'reviewer',
        ])
            ->where('user_id', $student->id)
            ->active();

        if ($semester !== null) {
            $query->where('failed_semester', $semester);
        }

        return $query->get();
    }

    /**
     * Synchronize and clear any carry-over records where the student has subsequently
     * registered and passed the course with a released result.
     */
    public function syncStudentClearances(User $student): void
    {
        $activeCarryOvers = CarryOverCourse::query()
            ->where('user_id', $student->id)
            ->where('is_cleared', false)
            ->get();

        if ($activeCarryOvers->isEmpty()) {
            return;
        }

        foreach ($activeCarryOvers as $carryOver) {
            $passedResult = Result::query()
                ->where('user_id', $student->id)
                ->where('status', 'released')
                ->where('grade', '!=', 'F')
                ->where('total_score', '>=', 40.0)
                ->where(function ($q) use ($carryOver) {
                    if ($carryOver->retake_registered_course_id) {
                        $q->where('registered_course_id', $carryOver->retake_registered_course_id);
                    }
                    $courseId = $carryOver->approved_department_course_id ?? $carryOver->department_course_id;
                    $q->orWhere(function ($courseQuery) use ($courseId, $carryOver) {
                        $courseQuery->where('department_course_id', $courseId)
                            ->where('academic_session', '>', $carryOver->failed_session);
                    });
                })
                ->latest('id')
                ->first();

            if ($passedResult) {
                $this->processResultClearance($passedResult);
            }
        }
    }

    /**
     * Register eligible UG carry-overs when the student enters the active
     * course-registration period. Only the same stable DepartmentCourse ID is
     * considered a safe match; anything else requires departmental review.
     *
     * @return Collection<int, CarryOverCourse>
     */
    public function registerEligibleRetakes(
        User $student,
        AcademicDetail $academicDetail
    ): Collection {
        if (!$student->isUndergraduate() || $academicDetail->user_id !== $student->id) {
            return collect();
        }

        if (!$academicDetail->approval?->isPinUsed()) {
            return $this->getActiveCarryOvers($student);
        }

        if (!app(StudentStatusService::class)->canPerformAcademicActivity($student, AcademicActivity::COURSE_REGISTRATION)) {
            return $this->getActiveCarryOvers($student);
        }

        $academicSession = app(AcademicSessionService::class)->getAcademicSession($student);

        return DB::transaction(function () use ($student, $academicDetail, $academicSession): Collection {
            $carryOvers = CarryOverCourse::query()
                ->with('departmentCourse.studentCourse')
                ->where('user_id', $student->id)
                ->where('is_cleared', false)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $registeredUnits = (int) RegisteredCourse::query()
                ->where('academic_detail_id', $academicDetail->id)
                ->where('academic_session', $academicSession)
                ->sum(DB::raw('COALESCE(credit_units_snapshot, units)'));

            $maxUnits = (int) DepartmentMaxUnit::query()
                ->where('department_id', $academicDetail->department_id)
                ->where('student_level_id', $academicDetail->student_level_id)
                ->value('max_units');
            if ($maxUnits <= 0) {
                $maxUnits = self::DEFAULT_MAX_SEMESTER_UNITS;
            }

            foreach ($carryOvers as $carryOver) {
                if ($carryOver->retake_registered_course_id !== null) {
                    continue;
                }

                if (!$this->isNextEligiblePeriod($carryOver, $academicSession)) {
                    continue;
                }

                $offeringId = $carryOver->approved_department_course_id ?? $carryOver->department_course_id;
                $offering = DepartmentCourse::with('studentCourse')
                    ->whereKey($offeringId)
                    ->where('department_id', $academicDetail->department_id)
                    ->first();

                if (!$offering?->studentCourse) {
                    $this->requireDepartmentReview($carryOver, 'The original course offering is no longer available under its stable course identity.');
                    continue;
                }

                $existingRegistration = RegisteredCourse::query()
                    ->where('academic_detail_id', $academicDetail->id)
                    ->where('department_course_id', $offering->id)
                    ->where('academic_session', $academicSession)
                    ->first();

                if ($existingRegistration) {
                    $carryOver->forceFill([
                        'retake_registered_course_id' => $existingRegistration->id,
                        'retake_session' => $academicSession,
                        'retake_semester' => (string) $offering->studentCourse->semester,
                        'registration_status' => 'registered',
                        'review_reason' => null,
                    ])->save();
                    continue;
                }

                $units = (int) $offering->units;
                if ($registeredUnits + $units > $maxUnits) {
                    $carryOver->forceFill([
                        'registration_status' => 'limit_blocked',
                        'review_reason' => "The required retake ({$units} units) needs room within the {$maxUnits}-unit limit. Remove or adjust other eligible courses, then registration will retry.",
                    ])->save();
                    continue;
                }

                $studentCourse = $offering->studentCourse;
                $registration = RegisteredCourse::create([
                    'academic_detail_id' => $academicDetail->id,
                    'department_course_id' => $offering->id,
                    'student_level_id' => $studentCourse->student_level_id ?? $academicDetail->student_level_id,
                    'units' => (string) $units,
                    'academic_session' => $academicSession,
                    'course_code_snapshot' => $studentCourse->code,
                    'course_title_snapshot' => $studentCourse->title,
                    'credit_units_snapshot' => $units,
                    'semester_snapshot' => (string) $studentCourse->semester,
                    'level_snapshot' => $studentCourse->student_level_id ?? $academicDetail->student_level_id,
                ]);

                $carryOver->forceFill([
                    'retake_registered_course_id' => $registration->id,
                    'retake_session' => $academicSession,
                    'retake_semester' => (string) $studentCourse->semester,
                    'auto_registered' => true,
                    'auto_registered_at' => now(),
                    'registration_status' => 'registered',
                    'review_reason' => null,
                ])->save();

                $registeredUnits += $units;
            }

            return $this->getActiveCarryOvers($student);
        });
    }

    private function isNextEligiblePeriod(CarryOverCourse $carryOver, string $academicSession): bool
    {
        $failedYear = $this->sessionStartYear($carryOver->failed_session);
        $currentYear = $this->sessionStartYear($academicSession);

        if ($failedYear === null || $currentYear === null) {
            return false;
        }
        return $currentYear > $failedYear;
    }

    private function sessionStartYear(string $session): ?int
    {
        return preg_match('/^(\d{4})\s*[-\/]\s*\d{4}$/', trim($session), $matches)
            ? (int) $matches[1]
            : null;
    }

    private function requireDepartmentReview(CarryOverCourse $carryOver, string $reason): void
    {
        $carryOver->forceFill([
            'registration_status' => 'review_required',
            'review_reason' => $reason,
        ])->save();
    }

    public function approveDepartmentSuccessor(
        CarryOverCourse $carryOver,
        DepartmentCourse $successor,
        User $reviewer,
        string $reviewNote
    ): CarryOverCourse {
        if (!$reviewer->canActAsHod()) {
            throw new \InvalidArgumentException('Only an authorized department reviewer can approve a carry-over successor.');
        }
        if ($carryOver->is_cleared || $carryOver->registration_status !== 'review_required') {
            throw new \InvalidArgumentException('This carry-over is not awaiting department review.');
        }

        $carryOver->loadMissing('user.academicDetail');
        $academicDetail = $carryOver->user?->academicDetail;

        if (!$academicDetail || !$carryOver->user?->isUndergraduate()
            || (int) $successor->department_id !== (int) $academicDetail->department_id) {
            throw new \InvalidArgumentException('The approved successor must be offered by the student\'s department.');
        }
        if (!$reviewer->canActAsAdmin() && !$reviewer->canActAsCit()) {
            $authorizedDepartments = array_filter([
                $reviewer->hodDetails?->department_id,
                ...$reviewer->capabilityDepartments('hod'),
            ]);
            if (!in_array((int) $academicDetail->department_id, array_map('intval', $authorizedDepartments), true)) {
                throw new \InvalidArgumentException('You are not authorized to review carry-overs for this department.');
            }
        }
        if (!$successor->studentCourse()->exists()) {
            throw new \InvalidArgumentException('The selected successor is not available for registration.');
        }
        if (trim($reviewNote) === '') {
            throw new \InvalidArgumentException('A department review note is required to approve a successor offering.');
        }

        $carryOver->forceFill([
            'approved_department_course_id' => $successor->id,
            'registration_status' => 'pending',
            'review_reason' => null,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $reviewNote,
        ])->save();

        return $carryOver->fresh(['approvedDepartmentCourse.studentCourse', 'reviewer']);
    }

    /**
     * Validate credit load for semester registration including carry-over courses.
     *
     * Max units are looked up from the `department_max_units` table per department and level,
     * falling back to DEFAULT_MAX_SEMESTER_UNITS (24) if no record is configured.
     * Min units (NUC_MIN_SEMESTER_UNITS = 15) is a fixed NUC regulatory floor.
     *
     * @return array{is_valid: bool, total_units: int, max_units: int, min_units: int, errors: array<string>}
     */
    public function validateCreditUnits(int $regularUnits, int $carryOverUnits, int $departmentId, int $studentLevelId): array
    {
        $totalUnits = $regularUnits + $carryOverUnits;
        $errors = [];

        // Look up configured maximum for this department & level
        $maxUnits = (int) DepartmentMaxUnit::where('department_id', $departmentId)
            ->where('student_level_id', $studentLevelId)
            ->value('max_units');

        // Fallback to NUC default if department admin hasn't configured it yet
        if ($maxUnits <= 0) {
            $maxUnits = self::DEFAULT_MAX_SEMESTER_UNITS;
        }

        $minUnits = self::NUC_MIN_SEMESTER_UNITS;

        if ($totalUnits > $maxUnits) {
            $errors[] = "Total registered units ({$totalUnits}) exceeds the maximum allowed limit of {$maxUnits} units for this level.";
        }

        if ($totalUnits < $minUnits) {
            $errors[] = "Total registered units ({$totalUnits}) is below the minimum required limit of {$minUnits} units (NUC standard).";
        }

        return [
            'is_valid' => empty($errors),
            'total_units' => $totalUnits,
            'max_units' => $maxUnits,
            'min_units' => $minUnits,
            'errors' => $errors,
        ];
    }
}
