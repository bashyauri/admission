<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UgStudentLevel extends Model
{
    public function academicDetails()
    {
        return $this->hasMany(AcademicDetail::class);
    }
}
