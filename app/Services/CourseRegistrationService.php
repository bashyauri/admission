<?php

declare(strict_types=1);



namespace App\Services;

use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\RegisteredCourse;
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

        return DepartmentCourse::with('studentCourse') // Eager load the relationship
            ->where('department_courses.department_id', $departmentId)
            // ->whereHas('studentCourse', fn($query) => $query->where('student_level_id', $studentLevelId))
            ->whereDoesntHave('registeredCourses', function ($query) use ($studentId, $academicSession) {
                $query->where('academic_detail_id', $studentId)
                    ->where('academic_session', $academicSession);
            })
            ->join('student_courses', 'student_courses.id', '=', 'department_courses.student_course_id')
            ->orderBy('student_courses.semester')
            ->select([
                'department_courses.id',
                'department_courses.units',
                'student_courses.code',
                'student_courses.semester',
                'student_courses.title',
                'student_courses.student_level_id'
            ])
            ->get();
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

    /** Persist a registration only after rechecking authoritative student status. */
    public function registerCourse(AcademicDetail $student, DepartmentCourse $course, string $academicSession): RegisteredCourse
    {
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

        if ($order === null) {
            $query->orderBy('created_at', 'desc'); // Default order
        } else {

            if ($order === 'semester') {
                $query->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
                    ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
                    ->orderByRaw('COALESCE(registered_courses.semester_snapshot, student_courses.semester)');
            } elseif ($order === 'title') {
                $query->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
                    ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
                    ->orderByRaw('COALESCE(registered_courses.course_title_snapshot, student_courses.title)');
            } else {
                $query->orderBy($order); // Order by the provided column if valid.  Be cautious about allowing arbitrary column names to prevent SQL injection vulnerabilities.
            }
        }

        return $query->select('registered_courses.*')->get();
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
