<?php

namespace App\Enums;

enum ProgrammesEnum: int
{
    case HND = 1;
    case ND = 2;
    case NDS = 3;
    case NCE = 4;
    case PD = 5;
    case PG = 6;
    case Undergraduate = 7;
    function getProgrammeValue(ProgrammesEnum $programme): int
    {
        return $programme->value;
    }
}