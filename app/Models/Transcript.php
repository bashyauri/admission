<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transcript extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_number',
        'verification_code',
        'destination',
        'purpose',
        'status',
        'fee',
        'fee_paid',
        'processed_by',
        'processed_at',
        'file_path',
        'sent_at',
        'collected_at',
        'collected_by',
        'remarks',
    ];

    protected $casts = [
        'fee'          => 'decimal:2',
        'fee_paid'     => 'boolean',
        'processed_at' => 'datetime',
        'sent_at'      => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Generate unique request number format: TRQ-YYYYMMDD-XXXX
     */
    public static function generateRequestNumber(): string
    {
        do {
            $number = 'TRQ-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Generate unique verification code format: TRV-XXXX-XXXX-XXXX
     */
    public static function generateVerificationCode(): string
    {
        do {
            $code = 'TRV-' . strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (static::where('verification_code', $code)->exists());

        return $code;
    }
}
