<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use App\Models\ProposedCourse;
use App\Enums\ApplicationStatus;
use App\Enums\ProgrammesEnum;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\AcademicSessionService;
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
    public function recommendApplicant($userId,  $attributes)
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
        return StudentTransaction::select(
            'student_transactions.*',
            'users.surname',
            'users.firstname',
            'users.m_name',
            'users.jamb_no',
            'users.phone'
        )
            ->join('users', 'student_transactions.user_id', '=', 'users.id')
            ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
            ->whereNull('academic_details.id') // only freshers
            ->where([
                'student_transactions.resource' => config('remita.schoolfees.ug_schoolfees_description'),
                'student_transactions.acad_session' => app(AcademicSessionService::class)
                    ->getAcademicSession(Auth::user()),
            ])
            ->get();
    }

    public function generateUgRegistrationNumber(
        string $year,
        string $modeOfEntry,
        string $facultyCode = "09",
        string $departmentCode,
        int $departmentId
    ): string {
        // Ensure full year format
        $fullYear = strlen($year) === 2 ? "20{$year}" : $year;
        $yearCode = substr($fullYear, 2, 2); // e.g., "25" for 2025

        // Get the current academic session string (e.g., "2024/2025")
        $currentSession = app(AcademicSessionService::class)
            ->getAcademicSession(auth()->user());

        // Get total students in this department for this session
        $count = DB::table('academic_details')
            ->where('department_id', $departmentId)
            ->where('programme_id', ProgrammesEnum::Undergraduate)
            ->where('acad_session', $currentSession) // 👈 count only current session
            ->lockForUpdate()
            ->count();

        // Increment count for next student
        $nextNumber = str_pad((string)($count + 1), 3, '0', STR_PAD_LEFT);

        // Construct registration number
        return "{$yearCode}{$modeOfEntry}{$facultyCode}{$departmentCode}{$nextNumber}";
    }


    public function changeToStudent(User $user): void
    {
        $user->update([
            'role' => Role::STUDENT
        ]);
    }
}
