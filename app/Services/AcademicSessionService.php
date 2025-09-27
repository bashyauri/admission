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
        return $user->isApplicant() || $user->isHod() || $user->isAdmin() || $user->isPostgraduate()
            ? config('remita.settings.pg_academic_session')
            : config('remita.settings.academic_session');
    }
}
