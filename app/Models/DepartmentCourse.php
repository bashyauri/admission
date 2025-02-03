<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentCourse extends Model
{
    protected $fillable = ['student_course_id', 'department_id', 'units'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class);
    }
}
