<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordinator extends Model
{
    protected $fillable = ['user_id', 'department_id'];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
