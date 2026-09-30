<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarryOverCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registered_course_id',
        'retake_registered_course_id',
        'department_course_id',
        'failed_session',
        'failed_semester',
        'failed_score',
        'failed_grade',
        'retake_session',
        'retake_semester',
        'is_cleared',
        'cleared_at',
        'cleared_result_id',
        'auto_registered',
        'auto_registered_at',
        'registration_status',
        'review_reason',
        'approved_department_course_id',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'failed_score' => 'decimal:2',
        'is_cleared' => 'boolean',
        'cleared_at' => 'datetime',
        'auto_registered' => 'boolean',
        'auto_registered_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registeredCourse(): BelongsTo
    {
        return $this->belongsTo(RegisteredCourse::class);
    }

    public function retakeRegisteredCourse(): BelongsTo
    {
        return $this->belongsTo(RegisteredCourse::class, 'retake_registered_course_id');
    }

    public function approvedDepartmentCourse(): BelongsTo
    {
        return $this->belongsTo(DepartmentCourse::class, 'approved_department_course_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function departmentCourse(): BelongsTo
    {
        return $this->belongsTo(DepartmentCourse::class);
    }

    public function clearedResult(): BelongsTo
    {
        return $this->belongsTo(Result::class, 'cleared_result_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_cleared', false);
    }

    public function scopeCleared($query)
    {
        return $query->where('is_cleared', true);
    }
}
