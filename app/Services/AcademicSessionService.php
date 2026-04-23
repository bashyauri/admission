<?php

namespace App\Services;

use App\Models\User;

/**
 * Class AcademicSessionService.
 */
class AcademicSessionService
{
    /**
     * Get the academic session for a user, supporting admin, hod, student, UG/PG applicant, with fallback.
     * Priority: settings table > .env/config > default.
     */
    public function getAcademicSession(User $user): string
    {
        // Try to get from settings table
        $session = null;
        if (\Schema::hasTable('settings')) {
            $key = null;
            // Priority: applicant (PG/UG) > admin > hod > PG student > UG student
            if ($user->isApplicant()) {
                // Postgraduate applicant
                if ($user->isPostgraduate()) {
                    $key = 'PG_APPLICANT_SESSION';
                } else {
                    $key = 'UG_APPLICANT_SESSION';
                }
            } elseif ($user->isAdmin()) {
                $key = 'ADMIN_ACADEMIC_SESSION';
            } elseif ($user->isHod()) {
                $key = 'HOD_ACADEMIC_SESSION';
            } elseif ($user->isPostgraduate()) {
                // Postgraduate student (not applicant)
                $key = 'PG_ACADEMIC_SESSION';
            } else {
                $key = 'ACADEMIC_SESSION';
            }
            $session = \DB::table('settings')->where('key', $key)->value('value');
        }

        // Fallback to config if not set in DB
        if (!$session) {
            if ($user->isApplicant()) {
                if ($user->isPostgraduate()) {
                    $session = config('remita.settings.pg_applicant_session') ?? config('remita.settings.pg_academic_session');
                } else {
                    $session = config('remita.settings.ug_applicant_session') ?? config('remita.settings.academic_session');
                }
            } elseif ($user->isAdmin()) {
                $session = config('remita.settings.admin_academic_session') ?? config('remita.settings.academic_session');
            } elseif ($user->isHod()) {
                $session = config('remita.settings.hod_academic_session') ?? config('remita.settings.pg_academic_session');
            } elseif ($user->isPostgraduate()) {
                $session = config('remita.settings.pg_academic_session');
            } else {
                $session = config('remita.settings.academic_session');
            }
        }

        // Final fallback (configurable)
        return $session ?: config('remita.settings.default_academic_session', '2025/2026');
    }
}
