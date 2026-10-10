<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = ['academic_detail_id', 'coordinator_id', 'approval_status', 'approval_date', 'pin', 'is_used', 'registration_submitted_at'];

    protected $casts = ['registration_submitted_at' => 'datetime'];

    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'approval_status' => $this->approval_status === 'Approved' ? 'Approved' : 'Pending',
        ]);
    }

    public function isPinUsed(): bool
    {
        return (bool) $this->is_used;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'Approved';
    }

    public function isSubmitted(): bool
    {
        return $this->registration_submitted_at !== null && !$this->isApproved();
    }

    public function approve(?int $coordinatorId = null): void
    {
        $this->update([
            'approval_status' => 'Approved',
            'approval_date' => now(),
            'coordinator_id' => $coordinatorId ?? $this->coordinator_id,
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'approval_status' => 'Pending',
            'approval_date' => null,
            'registration_submitted_at' => null,
        ]);
    }

    public function student()
    {
        return $this->belongsTo(AcademicDetail::class, 'academic_detail_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class, 'coordinator_id');
    }
}
