<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = ['academic_detail_id', 'coordinator_id', 'approval_status', 'approval_date', 'pin', 'is_used'];

    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'approval_status' => 'Approved',
        ]);
    }

    public function student()
    {
        return $this->belongsTo(AcademicDetail::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class, 'user_id');
    }
}
