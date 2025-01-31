<?php

namespace App\Enums;

enum StudentLevel: int
{
    case YEAR_ONE = 1;
    case YEAR_TWO = 2;
    case YEAR_THREE = 3;
    case YEAR_FOUR = 4;
    case SPILL_OVER = 5;
    public function toString(): string
    {
        return match ($this) {
            self::YEAR_ONE => '100',
            self::YEAR_TWO => '200',
            self::YEAR_THREE => '300',
            self::YEAR_FOUR => '400',
            self::SPILL_OVER => 'SPILL OVER',


            default => null,
        };
    }
    public static function getLevels(): array
    {
        return array_map(function (StudentLevel $level) {
            return [
                'name' => $level->toString(),
                'value' => $level->value,
            ];
        }, self::cases());
    }
}