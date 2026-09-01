<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Course;

class AcademicDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['admission_session']; // Allow mass assignment for admission session

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class);
    }
    public function studentTransactions()
    {
        return $this->hasMany(StudentTransaction::class);
    }
    public function ugStudentLevel()
    {
        return $this->belongsTo(UgStudentLevel::class);
    }
    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class, 'academic_detail_id');
    }
    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
    
    /**
     * Get the appropriate coordinator for this student based on their cohort
     * Supports both course-based (new) and department-based (legacy) coordinators for backward compatibility
     */
    public function getCourseCohortCoordinatorAttribute(): ?Coordinator
    {
        $admissionSession = $this->admission_session ?? $this->acad_session ?? null;
        
        // 1. Try exact course-based cohort coordinator (course + level + admission session)
        if ($this->course_id && $admissionSession && $this->student_level_id) {
            $courseCoordinator = Coordinator::forCourseCohort(
                $this->course_id,
                $this->student_level_id,
                $admissionSession
            )->first();
            
            if ($courseCoordinator) {
                return $courseCoordinator;
            }
        }
        
        // 2. Try department-based cohort coordinator (dept + level + admission session)
        if ($this->department_id && $admissionSession && $this->student_level_id) {
            $deptCoord = Coordinator::forDepartmentCohort(
                $this->department_id,
                $this->student_level_id,
                $admissionSession
            )->first();

            if ($deptCoord) {
                return $deptCoord;
            }
        }

        // 3. Try course-based coordinator (course + level, session agnostic)
        if ($this->course_id && $this->student_level_id) {
            $courseLevelCoord = Coordinator::forCourseAndLevel(
                $this->course_id,
                $this->student_level_id
            )->first();

            if ($courseLevelCoord) {
                return $courseLevelCoord;
            }
        }

        // 4. Try department-based coordinator (dept + level, session agnostic)
        if ($this->department_id && $this->student_level_id) {
            $deptLevelCoord = Coordinator::forDepartmentAndLevel(
                $this->department_id,
                $this->student_level_id
            )->first();

            if ($deptLevelCoord) {
                return $deptLevelCoord;
            }
        }

        // 5. Fallback to direct legacy coordinator_id relationship
        if ($this->coordinator_id) {
            return Coordinator::find($this->coordinator_id);
        }
        
        return null;
    }
    public function registeredCourses()
    {
        return $this->hasMany(RegisteredCourse::class, 'academic_detail_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}