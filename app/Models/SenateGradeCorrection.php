<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SenateGradeCorrection extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'senate_decision_date' => 'date',
            'verified_at' => 'datetime',
            'implemented_at' => 'datetime',
            'adjustment_amount' => 'decimal:2',
            'original_ca' => 'decimal:2',
            'original_exam' => 'decimal:2',
            'original_total' => 'decimal:2',
            'corrected_ca' => 'decimal:2',
            'corrected_exam' => 'decimal:2',
            'corrected_total' => 'decimal:2',
            'carry_over_before' => 'array',
            'carry_over_after' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Senate grade correction audit entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Senate grade correction audit entries are immutable.'));
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }

    public function implementer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'implemented_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
