<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function programs()
    {
        return $this->belongsToMany(Programme::class, 'department_programmes');
    }

    public function courses()
    {
        return $this->hasMany(Course::class)->withDefaults();
    }
    public function departmentCourses()
    {
        return $this->hasMany(DepartmentCourse::class);
    }
}
