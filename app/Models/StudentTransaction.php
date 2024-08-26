<?php

namespace App\Models;

use Monolog\Level;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentTransaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function student()
    {
        return $this->belongsTo(StudentTransaction::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
