<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'department_id',
        'course_code',
        'course_title',
        'credit_units',
        'semester',
        'level',
        'is_compulsory',
        'is_prerequisite',
        'academic_session',
        'is_active',
        'effective_date',
        'expiry_date',
        'created_by',
        'change_reason',
    ];

    protected $casts = [
        'credit_units' => 'integer',
        'level' => 'integer',
        'is_compulsory' => 'boolean',
        'is_prerequisite' => 'boolean',
        'is_active' => 'boolean',
        'effective_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registeredCourses(): HasMany
    {
        return $this->hasMany(RegisteredCourse::class, 'course_version_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'course_version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSession($query, string $session)
    {
        return $query->where('academic_session', $session);
    }
}
