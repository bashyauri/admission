<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Models\User;
use App\Enums\ProgrammesEnum;
use App\Models\PostUtmeUpload;
use App\Models\ProposedCourse;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UtmeService.
 */
class UtmeService
{
    public function getAllImportedApplicants(): int
    {
        return PostUtmeUpload::count();
    }
    public function getImportedApplicants(): Collection
    {
        return PostUtmeUpload::query()->get(["jamb_no", "name", "course", "jamb_score", "created_at", "updated_at"]);
    }
    public function getUTMEApplicants()
    {
        return User::query()->where(["programme_id" => ProgrammesEnum::Undergraduate->value])
            ->count();
    }
    public function getUTMERecommendedApplicants(): int
    {


        return ProposedCourse::where(['status' => ApplicationStatus::RECOMMENDED, 'academic_session' => config('remita.settings.academic_session')])->count();
    }

    public function getUTMEShortlistedApplicants(): int
    {
        return ProposedCourse::with('user')
            ->where([
                'status' => ApplicationStatus::SHORTLISTED,
                'academic_session' => config('remita.settings.academic_session'),
                'jamb_no' => [
                    '!=',
                    null
                ]
            ])
            ->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::Undergraduate->value);
            })
            ->count();
    }
    public function getAllUTMEApplicants($status)
    {
        $query = ProposedCourse::select(
            'proposed_courses.*',
            'users.surname as surname',
            'users.firstname as firstname',
            'users.m_name as middlename',
            'users.picture as picture',
            'users.phone as phone',
            'courses.name AS course_name'

        )
            ->join('users', 'proposed_courses.user_id', '=', 'users.id')
            ->join('courses', 'proposed_courses.course_id', '=', 'courses.id')
            ->where('proposed_courses.status', $status)
            ->where('users.programme_id', ProgrammesEnum::Undergraduate->value);





        return $query->latest()->get();
    }
}
