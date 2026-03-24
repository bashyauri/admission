<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseDropAudit extends Model
{
    protected $fillable = [
        'admin_user_id',
        'academic_session',
        'course_codes',
        'filters',
        'matched_count',
        'dropped_count',
        'action_type',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'course_codes' => 'array',
            'filters' => 'array',
        ];
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
