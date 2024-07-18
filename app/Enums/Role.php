<?php

namespace App\Enums;

enum Role: string
{
    case HOD = 'hod';
    case APPLICANT = 'applicant';
    case ADMIN = 'admin';
    case STUDENT = 'student';
}
