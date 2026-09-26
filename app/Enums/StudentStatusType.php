<?php

namespace App\Enums;

enum StudentStatusType: string
{
    case ACADEMIC = 'academic';
    case VOLUNTARY = 'voluntary';
    case MEDICAL = 'medical';
    case DISCIPLINARY = 'disciplinary';

    public function label(): string
    {
        return match ($this) {
            self::ACADEMIC => 'Academic',
            self::VOLUNTARY => 'Voluntary',
            self::MEDICAL => 'Medical',
            self::DISCIPLINARY => 'Disciplinary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
