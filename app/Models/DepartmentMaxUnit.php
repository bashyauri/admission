<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentMaxUnit extends Model
{
    protected $fillable = ['department_id', 'max_units', 'student_level_id'];
}
