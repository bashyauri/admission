<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'course_change_history';

    protected $fillable = [
        'course_id',
        'previous_version_id',
        'new_version_id',
        'change_type',
        'old_values',
        'new_values',
        'academic_session',
        'changed_by',
        'reason',
        'change_date',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'change_date' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class, 'previous_version_id');
    }

    public function newVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class, 'new_version_id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
