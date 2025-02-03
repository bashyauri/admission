<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourse extends Model
{
    protected $fillable = [
        'code',
        'title',
        'units',
        'student_level_id',
        'semester'
    ];
    public function departmentCourses(): HasMany
    {
        return $this->hasMany(DepartmentCourse::class);
    }
}
