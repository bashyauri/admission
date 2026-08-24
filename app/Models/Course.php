<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CourseVersion::class);
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(CourseVersion::class)->where('is_active', true)->latestOfMany();
    }

    public function changeHistory(): HasMany
    {
        return $this->hasMany(CourseChangeHistory::class);
    }

    public function mappingsFrom(): HasMany
    {
        return $this->hasMany(CourseMapping::class, 'old_course_id');
    }

    public function mappingsTo(): HasMany
    {
        return $this->hasMany(CourseMapping::class, 'new_course_id');
    }
}
