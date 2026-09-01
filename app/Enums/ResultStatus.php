<?php

namespace App\Enums;

enum ResultStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case EXAM_OFFICER_APPROVED = 'exam_officer_approved';
    case RELEASED = 'released';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUBMITTED => 'Submitted to Coordinator',
            self::EXAM_OFFICER_APPROVED => 'Coordinator Approved',
            self::RELEASED => 'Released',
        };
    }

    public function canBeEdited(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeSubmitted(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeApprovedByCoordinator(): bool
    {
        return $this === self::SUBMITTED;
    }

    public function canBeReleased(): bool
    {
        return $this === self::EXAM_OFFICER_APPROVED;
    }
}