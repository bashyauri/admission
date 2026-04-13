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
        // Use PG academic session if user is admin or hod
        if ($user->isAdmin() || $user->isHod()) {
            return config('remita.settings.pg_academic_session');
        }
        return $user->isApplicant() || $user->isPostgraduate()
            ? config('remita.settings.pg_academic_session')
            : config('remita.settings.academic_session');
    }
}
