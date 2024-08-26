<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLevel extends Model
{
    use HasFactory;
    public function academicDetails()
    {
        return $this->hasMany(AcademicDetail::class);
    }
}