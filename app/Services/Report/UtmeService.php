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
}
