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
        return StudentTransaction::select('student_transactions.*', 'users.surname', 'users.firstname', 'users.m_name', 'users.jamb_no', 'users.phone')
            ->join('users', 'student_transactions.user_id', '=', 'users.id')
            ->where([
                'student_transactions.resource' => config('remita.schoolfees.ug_schoolfees_description'),
                'student_transactions.acad_session' => app(AcademicSessionService::class)->getAcademicSession(Auth::user())
            ])->get();
    }
    public function generateUgRegistrationNumber($year, $modeOfEntry, $facultyCode = "09", $departmentCode, $departmentId): string
    {
        // Ensure $year is in the correct format
        // Default to config value
        $fullYear = strlen($year) === 2 ? "20{$year}" : $year; // Convert 2-digit year to 4-digit year if needed
        $yearCode = substr($fullYear, 2, 2); // Extract last two digits (e.g., "24")

        // Get the last registration number
        $lastRegNumber = DB::table('academic_details')
            ->where('department_id', $departmentId)
            ->where('programme_id', ProgrammesEnum::Undergraduate)->count();



        // Extract the last increment or default to 0
        // $lastIncrement = $lastRegNumber ? intval(substr($lastRegNumber, -3)) : 0;
        //     dd($lastIncrement);


        // Increment and pad to 3 digits
        $newIncrement = str_pad((string)($lastRegNumber + 1), 3, '0', STR_PAD_LEFT);



        // Construct the registration number
        return "{$yearCode}{$modeOfEntry}{$facultyCode}{$departmentCode}{$newIncrement}";
    }
    public function changeToStudent(User $user): void
    {
        $user->update([
            'role' => Role::STUDENT
        ]);
    }
}
