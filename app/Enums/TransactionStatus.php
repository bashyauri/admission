<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case APPROVED = '00';
    case ACTIVATED = '01';
    const PENDING = null;
    public function toString(): string
    {
        return match ($this) {
            self::APPROVED => '00',
            self::ACTIVATED => '01',
            default => null,
        };
    }
}
