<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduationEligibility extends Model
{
    use HasFactory;

    protected $table = 'graduation_eligibilities';

    protected $fillable = [
        'user_id',
        'academic_detail_id',
        'academic_session',
        'final_cgpa',
        'class_of_degree',
        'total_units_earned',
        'total_units_required',
        'meets_requirements',
        'siwes_completed',
        'general_studies_completed',
        'entrepreneurship_completed',
        'is_cleared',
        'cleared_by',
        'cleared_at',
        'remarks',
    ];

    protected $casts = [
        'final_cgpa'                 => 'decimal:2',
        'total_units_earned'         => 'integer',
        'total_units_required'       => 'integer',
        'meets_requirements'         => 'boolean',
        'siwes_completed'            => 'boolean',
        'general_studies_completed'  => 'boolean',
        'entrepreneurship_completed' => 'boolean',
        'is_cleared'                 => 'boolean',
        'cleared_at'                 => 'datetime',
    ];

    /**
     * The student this graduation eligibility record belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for user relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Academic detail associated with the student.
     */
    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }

    /**
     * User/Officer who cleared the graduation eligibility.
     */
    public function clearedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    /**
     * Scope for cleared students.
     */
    public function scopeCleared(Builder $query): Builder
    {
        return $query->where('is_cleared', true);
    }

    /**
     * Scope for candidates who meet all academic requirements.
     */
    public function scopeMeetsRequirements(Builder $query): Builder
    {
        return $query->where('meets_requirements', true);
    }

    /**
     * Scope filtered by academic session.
     */
    public function scopeForSession(Builder $query, string $session): Builder
    {
        return $query->where('academic_session', $session);
    }
}
