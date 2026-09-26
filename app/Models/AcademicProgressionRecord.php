<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicProgressionRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'cgpa' => 'decimal:2',
        'semester' => 'integer',
        'withdrawal_recommended' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class);
    }

    public function scopeForSession(Builder $query, string $session): Builder
    {
        return $query->where('academic_session', $session);
    }

    public function scopeForSemester(Builder $query, int $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    public function scopeWithdrawalRecommended(Builder $query): Builder
    {
        return $query->where('withdrawal_recommended', true);
    }
}
