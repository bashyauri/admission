<?php

namespace App\Enums;

enum Role: string
{
    case HOD = 'hod';
    case APPLICANT = 'applicant';
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case CIT = 'cit';
    public function toString(): string
    {
        return match ($this) {
            self::HOD => 'Head of Department',
            self::APPLICANT => 'Applicant',
            self::ADMIN => 'Admin',
            self::STUDENT => 'Student',
            self::CIT => 'Cit',
            default => null,
        };
    }
    public static function getRoles(): array
    {
        return array_map(function (Role $role) {
            return [
                'name' => $role->toString(),
                'value' => $role->value,
            ];
        }, self::cases());
    }
}
