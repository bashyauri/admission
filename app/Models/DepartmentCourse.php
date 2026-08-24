<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentCourse extends Model
{
    protected $fillable = ['student_course_id', 'department_id', 'units'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function studentCourse(): BelongsTo
    {
        return $this->belongsTo(StudentCourse::class);
    }

    public function registeredCourses(): HasMany
    {
        return $this->hasMany(RegisteredCourse::class, 'department_course_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'department_course_id');
    }

    public function carryOverRecords(): HasMany
    {
        return $this->hasMany(CarryOverCourse::class, 'department_course_id');
    }
}