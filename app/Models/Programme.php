<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function user()
    {

        return $this->hasMany(User::class);
    }
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_programmes');
    }
}
