<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PostUtmeUpload extends Model
{
    use HasUuids;
    protected $fillable = ['jamb_no', 'name'];
}
