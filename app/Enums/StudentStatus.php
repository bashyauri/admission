<?php

namespace App\Enums;

enum StudentStatus: string
{
    case ACTIVE = 'active';
    case VOLUNTARY_WITHDRAWAL = 'voluntary_withdrawal';
    case ACADEMIC_WITHDRAWAL = 'academic_withdrawal';
    case MEDICAL_WITHDRAWAL = 'medical_withdrawal';
    case SUSPENDED = 'suspended';
    case EXPELLED = 'expelled';
    case REINSTATED = 'reinstated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::VOLUNTARY_WITHDRAWAL => 'Voluntary Withdrawal',
            self::ACADEMIC_WITHDRAWAL => 'Academic Withdrawal',
            self::MEDICAL_WITHDRAWAL => 'Medical Withdrawal',
            self::SUSPENDED => 'Suspended',
            self::EXPELLED => 'Expelled',
            self::REINSTATED => 'Reinstated',
        };
    }

    public function isWithdrawn(): bool
    {
        return in_array($this, [
            self::VOLUNTARY_WITHDRAWAL,
            self::ACADEMIC_WITHDRAWAL,
            self::MEDICAL_WITHDRAWAL,
        ], true);
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::ACTIVE,
            self::REINSTATED,
        ], true);
    }

    public function isSuspendedOrExpelled(): bool
    {
        return in_array($this, [
            self::SUSPENDED,
            self::EXPELLED,
        ], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
