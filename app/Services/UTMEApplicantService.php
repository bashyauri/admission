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
        // Handle session formats such as "2026/2027", "2026", or "26"
        if (str_contains($year, '/')) {
            $parts = explode('/', trim($year));
            $startYear = (int) $parts[0];
            $endYear = (int) ($parts[1] ?? ($startYear + 1));
            $currentSession = "{$startYear}/{$endYear}";
            $yearCode = substr((string) $startYear, 2, 2);
        } else {
            $fullYear = strlen($year) === 2 ? "20{$year}" : $year;
            $startYear = (int) $fullYear;
            $nextSessionYear = $startYear + 1;
            $currentSession = "{$startYear}/{$nextSessionYear}";
            $yearCode = substr((string) $startYear, 2, 2);
        }

        // Get all existing undergraduate matric numbers for this department and session cohort.
        // Postgraduate (PG) records and returning students from older sessions are strictly excluded.
        $existingSerials = DB::table('academic_details')
            ->where('department_id', $departmentId)
            ->where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->where(function ($query) use ($currentSession, $yearCode): void {
                $query->where('admission_session', $currentSession)
                    ->orWhere(function ($q) use ($currentSession, $yearCode): void {
                        $q->whereNull('admission_session')
                            ->where('acad_session', $currentSession)
                            ->where('matric_no', 'like', "{$yearCode}%");
                    });
            })
            ->where('matric_no', 'like', "{$yearCode}%")
            ->lockForUpdate()
            ->pluck('matric_no');

        // Extract maximum 3-digit serial from existing cohort matric numbers
        $maxSerial = 0;
        foreach ($existingSerials as $matric) {
            if (preg_match('/(\d{3})$/', (string) $matric, $matches) === 1) {
                $serial = (int) $matches[1];
                if ($serial > $maxSerial) {
                    $maxSerial = $serial;
                }
            }
        }

        // Determine next sequence based on cohort count and highest assigned serial
        $nextSequence = max($existingSerials->count() + 1, $maxSerial + 1);
        do {
            $nextNumber = str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
            $candidateMatric = "{$yearCode}{$modeOfEntry}{$facultyCode}{$departmentCode}{$nextNumber}";
            $exists = DB::table('academic_details')->where('matric_no', $candidateMatric)->exists();
            if ($exists) {
                $nextSequence++;
            }
        } while ($exists);

        return $candidateMatric;
    }


    public function changeToStudent(User $user): void
    {
        $user->update([
            'role' => Role::STUDENT
        ]);
    }
}
