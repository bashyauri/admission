<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case SHORTLISTED = 'shortlisted';
    case RECOMMENDED = 'recommended';
    const PENDING = null;
    public function toString(): string
    {
        return match ($this) {
            self::SHORTLISTED => 'shortlisted',
            self::RECOMMENDED => 'recommended',
            default => null,
        };
    }
}
