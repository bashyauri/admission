<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Department;

class Coordinator extends Model
{
    protected $fillable = ['user_id', 'course_id', 'department_id', 'student_level_id', 'academic_session'];
    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope to find coordinator for a specific course, level, and academic session (cohort)
     * This ensures students from the same course cohort maintain the same coordinator
     * Essential for departments with multiple courses/programs
     */
    public function scopeForCourseCohort($query, $courseId, $studentLevelId, $academicSession)
    {
        return $query->where('course_id', $courseId)
                    ->where('student_level_id', $studentLevelId)
                    ->where('academic_session', $academicSession);
    }
    
    /**
     * Scope to find coordinator for a specific department, level, and academic session (backward compatibility)
     * This supports existing department-based coordinators
     */
    public function scopeForDepartmentCohort($query, $departmentId, $studentLevelId, $academicSession)
    {
        return $query->where('department_id', $departmentId)
                    ->where('student_level_id', $studentLevelId)
                    ->where('academic_session', $academicSession);
    }
    
    /**
     * Scope to find coordinator for a specific course and level (session-agnostic)
     */
    public function scopeForCourseAndLevel($query, $courseId, $studentLevelId)
    {
        return $query->where('course_id', $courseId)
                    ->where('student_level_id', $studentLevelId);
    }
    
    /**
     * Scope to find coordinator for a specific department and level (session-agnostic)
     */
    public function scopeForDepartmentAndLevel($query, $departmentId, $studentLevelId)
    {
        return $query->where('department_id', $departmentId)
                    ->where('student_level_id', $studentLevelId);
    }
    
    /**
     * Check if this is a course-based coordinator
     */
    public function isCourseBased(): bool
    {
        return !is_null($this->course_id);
    }
    
    /**
     * Check if this is a department-based coordinator
     */
    public function isDepartmentBased(): bool
    {
        return !is_null($this->department_id);
    }
}
