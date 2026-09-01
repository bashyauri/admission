<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'registered_course_id',
        'department_course_id',
        'academic_detail_id',
        'course_version_id',
        'course_code_snapshot',
        'course_title_snapshot',
        'credit_units_snapshot',
        'semester_snapshot',
        'level_snapshot',
        'semester',
        'academic_session',
        'ca_score',
        'exam_score',
        'total_score',
        'grade',
        'grade_point',
        'credit_units',
        'grade_point_total',
        'status',
        'lecturer_id',
        'coordinator_id',
        'coordinator_approved_by',
        'coordinator_approved_at',
        'exam_officer_approved_by',
        'exam_officer_approved_at',
        'remarks',
        'is_repeated',
        'original_result_id',
    ];

    protected $casts = [
        'ca_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'grade_point' => 'integer',
        'credit_units' => 'integer',
        'grade_point_total' => 'integer',
        'is_repeated' => 'boolean',
        'coordinator_approved_at' => 'datetime',
        'exam_officer_approved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registeredCourse(): BelongsTo
    {
        return $this->belongsTo(RegisteredCourse::class, 'registered_course_id');
    }

    public function departmentCourse(): BelongsTo
    {
        return $this->belongsTo(DepartmentCourse::class, 'department_course_id');
    }

    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }

    public function courseVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class, 'course_version_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Coordinator::class, 'coordinator_id');
    }

    public function coordinatorApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_approved_by');
    }

    public function examOfficerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exam_officer_approved_by');
    }

    public function originalResult(): BelongsTo
    {
        return $this->belongsTo(Result::class, 'original_result_id');
    }

    public function repeatedResults(): HasMany
    {
        return $this->hasMany(Result::class, 'original_result_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeCoordinatorApproved($query)
    {
        return $query->where('status', 'exam_officer_approved');
    }
    
    /**
     * Scope to get results submitted to a specific coordinator
     */
    public function scopeForCoordinator($query, $coordinatorId)
    {
        return $query->where('coordinator_id', $coordinatorId)->where('status', 'submitted');
    }

    public function scopeExamOfficerApproved($query)
    {
        return $query->where('status', 'exam_officer_approved');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }

    public function scopePassed($query)
    {
        return $query->where('grade', '!=', 'F');
    }

    public function scopeFailed($query)
    {
        return $query->where('grade', 'F');
    }
}
