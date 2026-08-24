<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_course_id',
        'new_course_id',
        'mapping_type',
        'effective_session',
        'created_by',
        'remarks',
    ];

    public function oldCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'old_course_id');
    }

    public function newCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'new_course_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
