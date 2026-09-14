<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GraduationList extends Model
{
    use HasFactory;

    protected $table = 'graduation_lists';

    protected $fillable = [
        'title',
        'academic_session',
        'ceremony_date',
        'venue',
        'is_published',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Graduands included in this graduation list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GraduationListItem::class, 'graduation_list_id');
    }

    /**
     * Certificates issued under this graduation list.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(DegreeCertificate::class, 'graduation_list_id');
    }

    /**
     * Officer who published/approved the graduation list.
     */
    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Scope for published graduation lists.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope filtered by academic session.
     */
    public function scopeForSession(Builder $query, string $session): Builder
    {
        return $query->where('academic_session', $session);
    }
}
