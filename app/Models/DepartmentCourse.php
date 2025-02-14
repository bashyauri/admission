<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}