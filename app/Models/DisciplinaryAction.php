<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryAction extends Model
{
    /**
     * `is_active` tracks whether this sanction remains in force in the disciplinary ledger.
     * For suspension and expulsion, StudentStatusRecord remains the authoritative status;
     * this record links to it and must not independently determine student access.
     */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'verdict_date' => 'date',
            'is_active' => 'boolean',
            'is_appealed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class);
    }

    public function sanctionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sanctioned_by');
    }

    public function studentStatusRecord(): BelongsTo
    {
        return $this->belongsTo(StudentStatusRecord::class);
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }

    public function auditEntries(): HasMany
    {
        return $this->hasMany(DisciplinaryActionAudit::class);
    }
}
