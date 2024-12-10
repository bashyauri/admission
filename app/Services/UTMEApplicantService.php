<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Enums\ApplicationStatus;
use App\Models\ProposedCourse;

/**
 * Class UTMEApplicantService.
 */

class UTMEApplicantService
{
    public $user;


    public function dropApplicant($userId)
    {
        ProposedCourse::where('user_id', $userId)->update(
            [
                'remark' => ApplicationStatus::PENDING,
                'status' => ApplicationStatus::PENDING
            ]
        );
    }
    public function recommendApplicant($userId, array $attributes)
    {

        ProposedCourse::query()->where('user_id', $userId)->firstOrFail()->update(
            [
                'remark' => $attributes['remark'],
                'status' => $attributes['status'],
                'comment' => $attributes['comment'] ?? null
            ]
        );
    }
}
