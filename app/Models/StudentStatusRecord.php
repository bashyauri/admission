<?php

namespace App\Models;

use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StudentStatusRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => StudentStatus::class,
        'status_type' => StudentStatusType::class,
        'semester' => 'integer',
        'effective_date' => 'date',
        'end_date' => 'date',
        'senate_decision_date' => 'date',
        'reinstatement_eligible' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Historical status query scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [StudentStatus::ACTIVE, StudentStatus::REINSTATED]);
    }

    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->whereIn('status', [
            StudentStatus::VOLUNTARY_WITHDRAWAL,
            StudentStatus::ACADEMIC_WITHDRAWAL,
            StudentStatus::MEDICAL_WITHDRAWAL,
        ]);
    }

    public function scopeForSession(Builder $query, string $session): Builder
    {
        return $query->where('academic_session', $session);
    }

    public function scopeForSessionAndSemester(Builder $query, string $session, $semester): Builder
    {
        return $query->where('academic_session', $session)->where('semester', $semester);
    }

    public function scopeEffectiveAt(Builder $query, $date = null): Builder
    {
        $targetDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        return $query->where(function (Builder $q) use ($targetDate) {
            $q->whereNull('effective_date')
              ->orWhere('effective_date', '<=', $targetDate);
        })->where(function (Builder $q) use ($targetDate) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $targetDate);
        });
    }

    public function scopeSenateApproved(Builder $query): Builder
    {
        return $query->where('senate_decision', 'APPROVED');
    }

    public function scopeByStatusType(Builder $query, StudentStatusType|string $type): Builder
    {
        $value = $type instanceof StudentStatusType ? $type->value : $type;
        return $query->where('status_type', $value);
    }
}
