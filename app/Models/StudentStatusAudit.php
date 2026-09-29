<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class StudentStatusAudit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Student status audit entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Student status audit entries are immutable.'));
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function statusRecord(): BelongsTo
    {
        return $this->belongsTo(StudentStatusRecord::class, 'student_status_record_id');
    }
}
