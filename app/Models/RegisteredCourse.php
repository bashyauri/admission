<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisteredCourse extends Model
{

    protected $fillable = ['department_course_id', 'academic_detail_id', 'units', 'academic_session', 'student_level_id'];
    public function departmentCourse(): BelongsTo
    {
        return $this->belongsTo(DepartmentCourse::class, 'department_course_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class);
    }
}
