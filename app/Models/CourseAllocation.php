<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegisteredCourse;

class CourseAllocation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function departmentCourse()
    {
        return $this->belongsTo(DepartmentCourse::class);
    }

   public function lecturer()
{
    return $this->belongsTo(User::class, 'lecturer_id', 'id');
}

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function registeredCourses()
    {
        return $this->hasMany(RegisteredCourse::class, 'department_course_id', 'department_course_id');
    }
    
    public function getStudentsCountAttribute()
    {
        return $this->registeredCourses()
            ->where('academic_session', $this->academic_session)
            ->count();
    }
}
