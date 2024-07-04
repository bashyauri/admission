<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposedCourse extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}