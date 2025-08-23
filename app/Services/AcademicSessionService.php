<?php

namespace App\Services;

use App\Models\User;

/**
 * Class AcademicSessionService.
 */
class AcademicSessionService
{
    public function getAcademicSession(User $user): string
    {
        if ($user->isApplicant() || $user->isHod()) {
            return config('remita.settings.pg_academic_session');
        }
        return config('remita.settings.academic_session');
    }
}