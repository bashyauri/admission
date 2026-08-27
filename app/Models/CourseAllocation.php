<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAllocation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function departmentCourse()
    {
        return $this->belongsTo(DepartmentCourse::class);
    }

   public function lecturer()
{
    return $this->belongsTo(User::class, 'lecturer_id', 'id');
}

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
