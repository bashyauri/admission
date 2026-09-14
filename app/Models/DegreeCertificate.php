<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DegreeCertificate extends Model
{
    use HasFactory;

    protected $table = 'degree_certificates';

    protected $fillable = [
        'user_id',
        'academic_detail_id',
        'graduation_list_id',
        'certificate_number',
        'certificate_type',
        'class_of_degree',
        'issue_date',
        'file_path',
        'is_printed',
        'is_collected',
        'collected_at',
        'collected_by',
        'recipient_name',
        'remarks',
    ];

    protected $casts = [
        'issue_date'   => 'date',
        'is_printed'   => 'boolean',
        'is_collected' => 'boolean',
        'collected_at' => 'datetime',
    ];

    /**
     * The student recipient of the certificate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for student relationship.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Associated academic details.
     */
    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }

    /**
     * Associated graduation list.
     */
    public function graduationList(): BelongsTo
    {
        return $this->belongsTo(GraduationList::class, 'graduation_list_id');
    }

    /**
     * Officer who logged or processed collection.
     */
    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Scope for collected certificates.
     */
    public function scopeCollected(Builder $query): Builder
    {
        return $query->where('is_collected', true);
    }

    /**
     * Scope for uncollected certificates.
     */
    public function scopePendingCollection(Builder $query): Builder
    {
        return $query->where('is_collected', false);
    }

    /**
     * Generate unique certificate number: CERT-{YEAR}-{RANDOM}
     */
    public static function generateCertificateNumber(?string $session = null): string
    {
        $prefix = 'CERT-';
        if ($session) {
            $cleaned = preg_replace('/[^0-9]/', '', $session);
            $prefix .= $cleaned . '-';
        } else {
            $prefix .= date('Y') . '-';
        }

        do {
            $number = $prefix . strtoupper(Str::random(6));
        } while (static::where('certificate_number', $number)->exists());

        return $number;
    }
}
