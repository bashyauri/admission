<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostUtmeUpload extends Model
{
    use HasUuids;
    protected $fillable = ['jamb_no', 'name', 'jamb_score', 'course', 'acad_session'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jamb_no', 'jamb_no');
    }
}
