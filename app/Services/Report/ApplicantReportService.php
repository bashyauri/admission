<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\ApplicationStatus;
use App\Enums\ProgrammesEnum;
use App\Enums\TransactionStatus;
use App\Models\ProposedCourse;
use Illuminate\Support\Facades\DB;

/**
 * Class ApplicantReportService.
 */
class ApplicantReportService
{


    public function totalApplicants($departmentId = null): int
    {
        if ($departmentId) {
            $query = ProposedCourse::where(['department_id' => $departmentId, 'academic_session' => config('remita.settings.academic_session')])
                ->whereHas('user', function ($query) {
                    $query->where('programme_id', ProgrammesEnum::PG->value);
                })->count();
        } else {

            $query = ProposedCourse::where('academic_session', config('remita.settings.academic_session'))->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::PG->value);
            })->count();
        }

        return $query;
    }
    public function applicantsNotRecommended($departmentId = null): int
    {

        if ($departmentId) {
            $query = ProposedCourse::where(['department_id' => $departmentId, 'status' => ApplicationStatus::PENDING, 'academic_session' => config('remita.settings.academic_session')])
                ->whereHas('user', function ($query) {
                    $query->where('programme_id', ProgrammesEnum::PG->value);
                })->count();
        } else {
            $query = ProposedCourse::where(['status' => ApplicationStatus::PENDING, 'academic_session' => config('remita.settings.academic_session')])->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::PG->value);
            })->count();
        }

        return $query;
    }
    public function applicantsShortlisted($departmentId = null): int
    {
        if ($departmentId) {
            $query = ProposedCourse::where(['department_id' => $departmentId, 'status' => ApplicationStatus::SHORTLISTED, 'academic_session' => config('remita.settings.academic_session')])->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::PG->value);
            })->count();
        } else {
            $query = ProposedCourse::where(['status' => ApplicationStatus::SHORTLISTED, 'academic_session' => config('remita.settings.academic_session')])->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::PG->value);
            })->count();
        }
        return $query;
    }
    public function getApplicants($status)
    {

        $query = ProposedCourse::select(
            'proposed_courses.*',
            'users.surname as surname',
            'users.programme_id as programme_id',
            'users.firstname as firstname',
            'users.m_name as middlename',
            'users.picture as picture',
            'users.phone as phone',
            'courses.name AS course_name'
        )
            ->join('users', 'proposed_courses.user_id', '=', 'users.id')
            ->join('courses', 'proposed_courses.course_id', '=', 'courses.id')
            ->where('users.programme_id', ProgrammesEnum::PG->value)
            ->where('proposed_courses.status', $status);


        // Add department filter if the user is a HOD
        if ($departmentId = auth()->user()->hodDetails?->department_id) {
            $query->where('proposed_courses.department_id', $departmentId);
        }



        return $query->latest()->get();
    }





    public function getApplicantsNotRecommended()
    {
        return $this->getApplicants(ApplicationStatus::PENDING);
    }
    public function getAllApplicants()
    {
        $query = ProposedCourse::select(
            'proposed_courses.*',
            'users.surname as surname',
            'users.firstname as firstname',
            'users.programme_id as programme_id',
            'users.m_name as middlename',
            'users.picture as picture',
            'users.phone as phone',
            'courses.name AS course_name'
        )
            ->join('users', 'proposed_courses.user_id', '=', 'users.id')
            ->join('courses', 'proposed_courses.course_id', '=', 'courses.id');

        // Add department filter if the user is a HOD
        if ($departmentId = auth()->user()->hodDetails?->department_id) {
            $query->where('proposed_courses.department_id', $departmentId)->where('users.programme_id', ProgrammesEnum::PG->value);
        }



        return $query->latest()->get();
    }


    public function getApplicantsShortlisted()
    {
        return $this->getApplicants(ApplicationStatus::SHORTLISTED);
    }
    public function getPaidAdmissionFees($departmentId = null): int
    {
        if ($departmentId) {
            return DB::table('transactions')
                ->join('proposed_courses', 'proposed_courses.user_id', '=', 'transactions.user_id')
                ->where([
                    'proposed_courses.department_id' => $departmentId,
                    'transactions.resource' => config('remita.admission.description'),
                    'transactions.status' => TransactionStatus::APPROVED,
                    'transactions.acad_session' => config('remita.settings.academic_session')
                ])->count();
        }
        return
            DB::table('transactions')
            ->join('proposed_courses', 'proposed_courses.user_id', '=', 'transactions.user_id')
            ->where([
                'transactions.resource' => config('remita.admission.description'),
                'transactions.status' => TransactionStatus::APPROVED,
                'transactions.acad_session' => config('remita.settings.academic_session')
            ])->count();
    }
    public function getPaidAcceptanceFees($departmentId = null): int
    {
        if ($departmentId) {
            return DB::table('transactions')
                ->join('proposed_courses', 'proposed_courses.user_id', '=', 'transactions.user_id')
                ->where([
                    'proposed_courses.department_id' => $departmentId,
                    'transactions.resource' => config('remita.acceptance.description'),
                    'transactions.status' => TransactionStatus::APPROVED,
                    'transactions.acad_session' => config('remita.settings.academic_session')
                ])->count();
        }
        return
            DB::table('transactions')
            ->join('proposed_courses', 'proposed_courses.user_id', '=', 'transactions.user_id')
            ->where([
                'transactions.resource' => config('remita.acceptance.description'),
                'transactions.status' => TransactionStatus::APPROVED,
                'transactions.acad_session' => config('remita.settings.academic_session')
            ])->count();;
    }
}
