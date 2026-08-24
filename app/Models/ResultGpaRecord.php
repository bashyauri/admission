<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultGpaRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'academic_detail_id',
        'semester',
        'academic_session',
        'semester_gpa',
        'total_credit_units',
        'total_grade_points',
        'cumulative_gpa',
        'cumulative_credit_units',
        'cumulative_grade_points',
        'class_of_degree',
    ];

    protected $casts = [
        'semester_gpa' => 'decimal:2',
        'cumulative_gpa' => 'decimal:2',
        'total_credit_units' => 'integer',
        'total_grade_points' => 'integer',
        'cumulative_credit_units' => 'integer',
        'cumulative_grade_points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function academicDetail(): BelongsTo
    {
        return $this->belongsTo(AcademicDetail::class);
    }
}
