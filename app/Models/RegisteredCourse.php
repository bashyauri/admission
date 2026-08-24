<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RegisteredCourse extends Model
{
    protected $fillable = [
        'department_course_id',
        'academic_detail_id',
        'student_level_id',
        'units',
        'academic_session',
        'course_code_snapshot',
        'course_title_snapshot',
        'credit_units_snapshot',
        'semester_snapshot',
        'level_snapshot',
        'course_version_id',
    ];

    public function departmentCourse(): BelongsTo
    {
        return $this->belongsTo(DepartmentCourse::class, 'department_course_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }

    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }

    public function courseVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class, 'course_version_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class, 'registered_course_id');
    }
}
