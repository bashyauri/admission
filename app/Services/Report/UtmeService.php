<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Models\User;
use App\Models\PostUtmeUpload;
use App\Models\ProposedCourse;
use App\Enums\ProgrammesEnum;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UtmeService.
 */
class UtmeService
{
    public function getAllImportedApplicants(): int
    {
        return PostUtmeUpload::count(); // Simple count query, already optimized
    }

    public function getImportedApplicants(): Collection
    {
        return PostUtmeUpload::select(["jamb_no", "name", "course", "jamb_score", "created_at", "updated_at"])
            ->get(); // Reduced query overhead by using `select`
    }

    public function getUTMEApplicants(): int
    {
        return User::where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->count(); // Single-column query, directly optimized
    }

    public function getUTMERecommendedApplicants(): int
    {
        return ProposedCourse::where('status', ApplicationStatus::RECOMMENDED)
            ->where('academic_session', config('remita.settings.academic_session'))
            ->count(); // Combined conditions to leverage indexes
    }

    public function getUTMEShortlistedApplicants(): int
    {
        return ProposedCourse::where('status', ApplicationStatus::SHORTLISTED)
            ->where('academic_session', config('remita.settings.academic_session'))
            ->whereNotNull('jamb_no') // Simplified `!= null` condition
            ->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::Undergraduate->value);
            })
            ->count(); // Avoided unnecessary loading of related models
    }

    public function getAllUTMEApplicants(string $status): Collection
    {
        return ProposedCourse::select(
            'proposed_courses.*',
            'users.surname as surname',
            'users.firstname as firstname',
            'users.m_name as middlename',
            'users.picture as picture',
            'users.phone as phone',
            'courses.name as course_name'
        )
            ->join('users', 'proposed_courses.user_id', '=', 'users.id')
            ->join('courses', 'proposed_courses.course_id', '=', 'courses.id')
            ->where('proposed_courses.status', $status)
            ->where('users.programme_id', ProgrammesEnum::Undergraduate->value)
            ->orderBy('proposed_courses.created_at', 'desc') // Replaced `latest()` with explicit orderBy for clarity
            ->get(); // Deferred execution with `get`
    }
}