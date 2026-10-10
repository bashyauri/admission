<?php

declare(strict_types=1);



namespace App\Services;

use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\RegisteredCourse;
use App\Models\CarryOverCourse;
use App\Models\AcademicDetail;
use App\Models\User;
use App\Enums\AcademicActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CourseRegistrationService
{
    /**
     * Get available courses for the student.
     *
     * @param int $departmentId
     * @param int $studentLevelId
     * @param int $studentId
     * @param string $academicSession
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getAvailableCourses($departmentId, $studentLevelId, $studentId, $academicSession)
    {
        $academicDetail = \App\Models\AcademicDetail::with('user')->find($studentId);
        $user = $academicDetail?->user ?? \App\Models\User::find($studentId);

        if ($user && !app(StudentStatusService::class)->canPerformAcademicActivity($user, \App\Enums\AcademicActivity::COURSE_REGISTRATION)) {
            return collect();
        }

        $query = DepartmentCourse::with('studentCourse') // Eager load the relationship
            ->where('department_courses.department_id', $departmentId);

        // UG offerings are defined by department and level. Keep PG selection
        // rules unchanged until their offering model is verified separately.
        if ($user?->isUndergraduate()) {
            $query->whereHas('studentCourse', fn ($courseQuery) => $courseQuery->where('student_level_id', $studentLevelId));
        }

        $courses = $query
            ->whereDoesntHave('registeredCourses', function ($query) use ($studentId, $academicSession) {
                $query->where('academic_detail_id', $studentId)
                    ->where('academic_session', $academicSession);
            })
            ->join('student_courses', 'student_courses.id', '=', 'department_courses.student_course_id')
            ->select([
                'department_courses.id',
                'department_courses.units',
                'student_courses.code',
                'student_courses.semester',
                'student_courses.title',
                'student_courses.student_level_id'
            ])
            ->get();

        return $courses->sort(function (DepartmentCourse $left, DepartmentCourse $right): int {
            $leftCourse = $left->studentCourse;
            $rightCourse = $right->studentCourse;

            return $this->semesterSortRank($leftCourse?->semester) <=> $this->semesterSortRank($rightCourse?->semester)
                ?: $this->courseCodeSortParts($leftCourse?->code)[0] <=> $this->courseCodeSortParts($rightCourse?->code)[0]
                ?: strnatcasecmp($this->courseCodeSortParts($leftCourse?->code)[1], $this->courseCodeSortParts($rightCourse?->code)[1])
                ?: strnatcasecmp((string) $leftCourse?->code, (string) $rightCourse?->code)
                ?: ((int) $left->id <=> (int) $right->id);
        })->values();
    }

    /**
     * Check if a student can register for courses based on status gate.
     */
    public function canStudentRegisterCourses(\App\Models\User|\App\Models\AcademicDetail|string|int $student): bool
    {
        $user = $student instanceof \App\Models\User
            ? $student
            : ($student instanceof \App\Models\AcademicDetail
                ? $student->user
                : (\App\Models\User::find($student) ?? \App\Models\AcademicDetail::find($student)?->user));

        if (!$user) {
            return false;
        }

        return app(StudentStatusService::class)->canPerformAcademicActivity($user, \App\Enums\AcademicActivity::COURSE_REGISTRATION);
    }

    /** Persist a registration only after rechecking authoritative student status and unit limit. */
    public function registerCourse(AcademicDetail $student, DepartmentCourse $course, string $academicSession): RegisteredCourse
    {
        $course->loadMissing('studentCourse');
        $user = $student->user;
        if (!$user || !app(StudentStatusService::class)->canPerformAcademicActivity($user, AcademicActivity::COURSE_REGISTRATION)) {
            $status = $user ? app(StudentStatusService::class)->getCurrentStatus($user) : null;
            $label = $status?->status?->label() ?? 'inactive';
            throw new \InvalidArgumentException("Course registration is blocked by the student's current institutional status ({$label}).");
        }

        $studentCourse = $course->studentCourse;
        if (!$studentCourse) {
            throw new \InvalidArgumentException('This course is not available for registration. Refresh the page and try again.');
        }

        $wrongDepartment = (int) $course->department_id !== (int) $student->department_id;
        $wrongUndergraduateLevel = $user->isUndergraduate()
            && (int) $studentCourse->student_level_id !== (int) $student->student_level_id;

        if ($wrongDepartment || $wrongUndergraduateLevel) {
            throw new \InvalidArgumentException('This course is not available for your department and level.');
        }

        if ($user->isUndergraduate()) {
            $carryOvers = app(CarryOverRegistrationService::class)
                ->registerEligibleRetakes($user, $student);

            if ($carryOvers->contains(fn ($carryOver) => $carryOver->registration_status === 'limit_blocked')) {
                throw new \InvalidArgumentException(
                    'A required carry-over retake is waiting for room under your unit limit. Remove an eligible course before adding other courses.'
                );
            }
        }

        // Enforce the configured unit limit as a hard cap (Task 5.6).
        // This is the authoritative check — the Livewire canAddCourse() is UI-only.
        if ($user->isUndergraduate()) {
            $maxUnits = $this->getMaxUnits((int) $student->department_id, (int) $student->student_level_id);
            if ($maxUnits > 0) {
                $currentTotal = $this->getTotalUnitsOfRegisteredCourses($student->id, $academicSession);
                $courseUnits = (int) $course->units;
                if (($currentTotal + $courseUnits) > $maxUnits) {
                    throw new \InvalidArgumentException(
                        "Adding this course ({$courseUnits} units) would exceed the maximum allowed load of {$maxUnits} units for your level. "
                        . "You currently have {$currentTotal} units registered."
                    );
                }
            }
        }

        $registrationAttributes = [
            'department_course_id' => $course->id,
            'semester' => $studentCourse->semester,
            'units' => $course->units,
            'student_level_id' => $studentCourse->student_level_id,
            'academic_session' => $academicSession,
        ];

        if ($user->isUndergraduate()) {
            $registrationAttributes += [
                'course_code_snapshot' => $studentCourse->code,
                'course_title_snapshot' => $studentCourse->title,
                'credit_units_snapshot' => $course->units,
                'semester_snapshot' => (string) $studentCourse->semester,
                'level_snapshot' => $studentCourse->student_level_id,
            ];
        }

        return $student->registeredCourses()->create($registrationAttributes);
    }


    /**
     * Get registered courses for the student.
     *
     * @param int $studentId
     * @param string $academicSession
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRegisteredCourses($studentId, $academicSession, $order = null): Collection
    {
        $query = RegisteredCourse::with([
            'departmentCourse' => function ($query) {
                $query->select('id', 'student_course_id', 'units');
            },
            'departmentCourse.studentCourse' => function ($query) {
                $query->select('id', 'code', 'semester', 'title', 'student_level_id');
            }
        ])
            ->where(['academic_session' => $academicSession, 'academic_detail_id' => $studentId]);

        $query->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
            ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
            ->orderByRaw('CASE WHEN EXISTS (
                SELECT 1 FROM carry_over_courses
                WHERE carry_over_courses.retake_registered_course_id = registered_courses.id
            ) THEN 0 ELSE 1 END');

        if ($order === 'title') {
            $query->orderByRaw('COALESCE(registered_courses.course_title_snapshot, student_courses.title)')
                ->orderByRaw('COALESCE(registered_courses.course_code_snapshot, student_courses.code)')
                ->orderBy('registered_courses.id');
        } elseif ($order === null || $order === 'semester') {
            $query->orderByRaw('COALESCE(registered_courses.semester_snapshot, student_courses.semester)')
                ->orderByRaw('COALESCE(registered_courses.course_code_snapshot, student_courses.code)')
                ->orderBy('registered_courses.id');
        } else {
            $query->orderBy($order)->orderBy('registered_courses.id');
        }

        $courses = $query->select('registered_courses.*')->get();
        $carryOverRegistrationIds = CarryOverCourse::query()
            ->whereIn('retake_registered_course_id', $courses->pluck('id'))
            ->pluck('retake_registered_course_id')
            ->mapWithKeys(fn ($id) => [(int) $id => true]);

        return $courses->sort(function (RegisteredCourse $left, RegisteredCourse $right) use ($carryOverRegistrationIds): int {
            $leftCarryOver = $carryOverRegistrationIds->has((int) $left->id) ? 0 : 1;
            $rightCarryOver = $carryOverRegistrationIds->has((int) $right->id) ? 0 : 1;
            if ($leftCarryOver !== $rightCarryOver) {
                return $leftCarryOver <=> $rightCarryOver;
            }

            $leftSemester = $left->semester_snapshot ?? $left->departmentCourse?->studentCourse?->semester;
            $rightSemester = $right->semester_snapshot ?? $right->departmentCourse?->studentCourse?->semester;
            $semesterOrder = $this->semesterSortRank($leftSemester) <=> $this->semesterSortRank($rightSemester);
            if ($semesterOrder !== 0) {
                return $semesterOrder;
            }

            $leftCode = $left->course_code_snapshot ?? $left->departmentCourse?->studentCourse?->code;
            $rightCode = $right->course_code_snapshot ?? $right->departmentCourse?->studentCourse?->code;
            $leftCodeParts = $this->courseCodeSortParts($leftCode);
            $rightCodeParts = $this->courseCodeSortParts($rightCode);

            return $leftCodeParts[0] <=> $rightCodeParts[0]
                ?: strnatcasecmp($leftCodeParts[1], $rightCodeParts[1])
                ?: strnatcasecmp((string) $leftCode, (string) $rightCode)
                ?: ((int) $left->id <=> (int) $right->id);
        })->values();
    }

    private function semesterSortRank(mixed $semester): int
    {
        return match (strtolower(trim((string) $semester))) {
            '1', 'first', 'harmattan' => 1,
            '2', 'second', 'rain' => 2,
            default => 3,
        };
    }

    /** @return array{int, string} */
    private function courseCodeSortParts(?string $courseCode): array
    {
        $courseCode = trim((string) $courseCode);
        if (preg_match('/(\d+)/', $courseCode, $matches, PREG_OFFSET_CAPTURE) !== 1) {
            return [PHP_INT_MAX, $courseCode];
        }

        $number = (int) $matches[1][0];
        $prefix = substr($courseCode, 0, $matches[1][1]);

        return [$number, $prefix];
    }




    public function getTotalUnitsOfRegisteredCourses($studentId, $academicSession): int
    {
        return (int)RegisteredCourse::where([
            'academic_session' => $academicSession,
            'academic_detail_id' => $studentId
        ])
            ->sum(DB::raw('COALESCE(credit_units_snapshot, units)'));
    }



    public function getMaxUnits($departmentId, $level): int
    {
        return (int) DepartmentMaxUnit::where([
            'department_id' => $departmentId,
            'student_level_id' => $level
        ])->value('max_units');
    }

    public function calculateSemester(string $courseCode): int
    {
        $lastDigit = substr($courseCode, -1);
        return $lastDigit % 2 === 0 ? 2 : 1;
    }
    public function getExamCard($studentId)
    {
        return DB::table('registered_courses')
            ->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
            ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
            ->where('registered_courses.academic_detail_id', $studentId)
            ->select(
                'registered_courses.academic_session',
                DB::raw('COALESCE(registered_courses.semester_snapshot, student_courses.semester) as semester'),
                DB::raw('MAX(COALESCE(registered_courses.level_snapshot, registered_courses.student_level_id, student_courses.student_level_id)) as student_level_id')
            )
            ->groupBy(
                'registered_courses.academic_session',
                DB::raw('COALESCE(registered_courses.semester_snapshot, student_courses.semester)')
            )
            ->orderBy('registered_courses.academic_session')
            ->get();
    }
    public function getRegisteredCoursesBySemester($studentId, $academicSession, $semester)
    {
        return DB::table('registered_courses')
            ->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
            ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
            ->where([
                'registered_courses.academic_detail_id' => $studentId,
                'registered_courses.academic_session' => $academicSession,
            ])
            ->whereRaw('COALESCE(registered_courses.semester_snapshot, student_courses.semester) = ?', [(string) $semester])
            ->select(
                'registered_courses.*',
                DB::raw('COALESCE(registered_courses.course_code_snapshot, student_courses.code) as code'),
                DB::raw('COALESCE(registered_courses.course_title_snapshot, student_courses.title) as title'),
                DB::raw('COALESCE(registered_courses.credit_units_snapshot, registered_courses.units, department_courses.units) as units')
            )
            ->get();
    }
}
