<?php

declare(strict_types=1);



namespace App\Services;

use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\RegisteredCourse;
use Illuminate\Database\Eloquent\Collection;

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
        return DepartmentCourse::query()
            ->where('department_courses.department_id', $departmentId)
            ->whereHas('studentCourse', fn($query) => $query->where('student_level_id', $studentLevelId))
            ->leftJoin('registered_courses', function ($join) use ($studentId, $academicSession) {
                $join->on('registered_courses.department_course_id', '=', 'department_courses.id')
                    ->where('registered_courses.academic_detail_id', '=', $studentId)
                    ->where('registered_courses.academic_session', '=', $academicSession);
            })
            ->whereNull('registered_courses.id') // Only courses not yet registered
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
    public function getRegisteredCourses($studentId, $academicSession)
    {
        return RegisteredCourse::with([
            'departmentCourse' => function ($query) {
                $query->select('id', 'student_course_id', 'units');
            },
            'departmentCourse.studentCourse' => function ($query) {
                $query->select('id', 'code', 'semester', 'title', 'student_level_id');
            }
        ])
            ->where('academic_session', $academicSession)
            ->where('academic_detail_id', $studentId)
            ->orderByDesc('created_at')
            ->get();
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
}
