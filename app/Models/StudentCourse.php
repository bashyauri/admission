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
        'semester',
        'max_ca',
        'max_exam',
    ];

    protected $casts = [
        'max_ca' => 'integer',
        'max_exam' => 'integer',
    ];

    public function getMaxCa(): int
    {
        return (int) ($this->max_ca ?? 40);
    }

    public function getMaxExam(): int
    {
        return (int) ($this->max_exam ?? 60);
    }

    public function departmentCourses(): HasMany
    {
        return $this->hasMany(DepartmentCourse::class);
    }
}
