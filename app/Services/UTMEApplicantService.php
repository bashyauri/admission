<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Enums\ApplicationStatus;
use App\Models\ProposedCourse;
use App\Models\StudentTransaction;
use Illuminate\Database\Eloquent\Collection;

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
    public function shortlist($id)
    {

        ProposedCourse::query()->where('id', $id)->firstOrFail()->update(
            [
                'remark' => ApplicationStatus::SHORTLISTED->toString(),

            ]
        );
    }
    public function drop($id): void
    {
        ProposedCourse::query()->where('id', $id)->firstOrFail()->update(
            attributes: [
                'remark' => null,
                'status' => ApplicationStatus::PENDING,
                'comment' => null,

            ]
        );
    }
    public function getUtmeFirstSchoolFeesPayment(): Collection
    {
        return StudentTransaction::select('student_transactions.*', 'users.surname', 'users.firstname', 'users.m_name', 'users.jamb_no', 'users.phone')
            ->join('users', 'student_transactions.user_id', '=', 'users.id')
            ->where([
                'student_transactions.resource' => config('remita.schoolfees.ug_schoolfees_description'),
            ])->get();
    }
}
