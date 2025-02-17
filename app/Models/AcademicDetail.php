<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class);
    }
    public function studentTransactions()
    {
        return $this->hasMany(StudentTransaction::class);
    }
    public function ugStudentLevel()
    {
        return $this->belongsTo(UgStudentLevel::class);
    }
    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class, 'academic_detail_id');
    }
    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
    public function registeredCourses()
    {
        return $this->hasMany(RegisteredCourse::class, 'academic_detail_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}