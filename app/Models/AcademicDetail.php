<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class);
    }
    public function studentTransactions()
    {
        return $this->hasMany(StudentTransaction::class);
    }
}