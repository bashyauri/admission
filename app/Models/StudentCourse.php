<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCourse extends Model
{
    protected $fillable = [
        'code',
        'title',
        'units',
        'student_level_id',
        'semester'
    ];
}
