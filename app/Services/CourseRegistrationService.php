<?php

declare(strict_types=1);



namespace App\Services;

use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\RegisteredCourse;
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableCourses($departmentId, $studentLevelId, $studentId, $academicSession)
    {
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
                    ->orderBy('student_courses.semester');
            } elseif ($order === 'title') {
                $query->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
                    ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
                    ->orderBy('student_courses.title');
            } else {
                $query->orderBy($order); // Order by the provided column if valid.  Be cautious about allowing arbitrary column names to prevent SQL injection vulnerabilities.
            }
        }

        return $query->get();
    }




    public function getTotalUnitsOfRegisteredCourses($studentId, $academicSession): int
    {
        return (int)RegisteredCourse::where([
            'academic_session' => $academicSession,
            'academic_detail_id' => $studentId
        ])
            ->sum('units');
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
                'student_courses.semester',
                DB::raw('MAX(student_courses.student_level_id) as student_level_id') // aggregate function
            )
            ->groupBy(
                'registered_courses.academic_session',
                'student_courses.semester'
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
                'student_courses.semester' => $semester
            ])
            ->select(
                'registered_courses.*',
                'student_courses.code',
                'student_courses.title',
                'department_courses.units'
            )
            ->get();
    }
}
