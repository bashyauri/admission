<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduationListItem extends Model
{
    use HasFactory;

    protected $table = 'graduation_list_items';

    protected $fillable = [
        'graduation_list_id',
        'user_id',
        'academic_detail_id',
        'matric_no',
        'full_name',
        'programme',
        'department',
        'final_cgpa',
        'class_of_degree',
        'rank',
        'is_present',
    ];

    protected $casts = [
        'final_cgpa' => 'decimal:2',
        'rank'       => 'integer',
        'is_present' => 'boolean',
    ];

    /**
     * The parent graduation list.
     */
    public function graduationList(): BelongsTo
    {
        return $this->belongsTo(GraduationList::class, 'graduation_list_id');
    }

    /**
     * The student graduand.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for student user.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The student's academic detail.
     */
    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }
}
