<?php

declare(strict_types=1);

namespace App\Services\Report;


use App\Enums\ApplicationStatus;
use App\Enums\ProgrammesEnum;
use App\Enums\TransactionStatus;
use App\Models\PostUtmeUpload;

use App\Models\ProposedCourse;
use App\Models\StudentTransaction;
use App\Models\User;
use App\Services\AcademicSessionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        return ProposedCourse::query()
            ->where('academic_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::Undergraduate->value);
            })
            ->count(); // Single-column query, directly optimized
    }
     public function getUTMEStudents(): int
    {
        return ProposedCourse::query()
            ->where('academic_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::Undergraduate->value);
            })
            ->count(); // Single-column query, directly optimized
    }
    /**
     * Get all unique undergraduate students (role=student) who have NOT paid school fees for the current session.
     * Returns a Collection of User models.
     */
    public function getUndergraduateStudentsNotPaidSchoolFees(): Collection
    {
        return User::where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->where('role', \App\Enums\Role::STUDENT->value)
            ->whereDoesntHave('studentTransactions', function ($q) {
                $q->where('resource', config('remita.schoolfees.ug_schoolfees_description'))
                  ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
                  ->where('status', TransactionStatus::APPROVED->value);
            })
            ->get();
    }

    public function getUTMERecommendedApplicants(): int
    {

        return ProposedCourse::where('status', ApplicationStatus::RECOMMENDED)
            ->where('academic_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::Undergraduate->value);
            })
            ->count();
    }

    public function getUTMEShortlistedApplicants(): int
    {
        return ProposedCourse::where('status', ApplicationStatus::SHORTLISTED)
            ->where('academic_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->whereNotNull('jamb_no') // Simplified `!= null` condition
            ->whereHas('user', function ($query) {
                $query->where('programme_id', ProgrammesEnum::Undergraduate->value);
            })
            ->count(); // Avoided unnecessary loading of related models
    }

    public function getAllUTMEApplicants(string $status = null): Collection
    {
        $query = ProposedCourse::query()->select(
            'proposed_courses.*',
            'users.surname as surname',
            'users.firstname as firstname',
            'users.m_name as middlename',
            'users.picture as picture',
            'users.phone as phone',
            'courses.name as course_name',
            'users.id as user',
        )
            ->join('users', 'proposed_courses.user_id', '=', 'users.id')
            ->join('courses', 'proposed_courses.course_id', '=', 'courses.id')

            ->where('users.programme_id', ProgrammesEnum::Undergraduate->value)
            ->where('proposed_courses.academic_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->orderBy('proposed_courses.created_at', 'desc'); // Replaced `latest()` with explicit orderBy for clarity

        $query->where('proposed_courses.status', $status);

        return $query->get();
    }
    public function recommendedUTMEApplicantsDetails()
    {
        $applicants = DB::table('users')
            ->join('proposed_courses', 'users.id', 'proposed_courses.user_id')

            ->join('courses', 'courses.id', 'proposed_courses.course_id')
            ->join('departments', 'departments.id', 'proposed_courses.department_id')
            ->join('states', 'states.id', 'users.state_id')
            ->join('lgas', 'lgas.id', 'users.lga_id')
            ->leftJoin('olevel_subject_grades', 'olevel_subject_grades.user_id', 'users.id')

            ->where('proposed_courses.status', '=', ApplicationStatus::RECOMMENDED)
            ->where('proposed_courses.academic_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->select(
                'users.id',
                'users.surname',
                'users.firstname',
                'users.m_name',
                'users.phone',
                'proposed_courses.status',
                'proposed_courses.remark',
                'proposed_courses.academic_session',


                'departments.name as department',
                'courses.name as course',
                'proposed_courses.jamb_score',
                'proposed_courses.comment',
                'proposed_courses.jamb_no',
                'states.name as state',
                'lgas.name as lga'
            )
            ->groupBy('users.id')
            ->groupBy('proposed_courses.status')
            ->groupBy('users.surname')
            ->groupBy('users.firstname')
            ->groupBy('users.m_name')
            ->groupBy('users.phone')
            ->groupBy('departments.name')
            ->groupBy('courses.name')
            ->groupBy('proposed_courses.jamb_score')
            ->groupBy('proposed_courses.jamb_no')
            ->groupBy('states.name')
            ->groupBy('proposed_courses.comment')
            ->groupBy('lga')
            ->groupBy('proposed_courses.remark')
            ->groupBy('proposed_courses.academic_session')
            ->groupBy('proposed_courses.course_id')
            // ->groupBy('application_form.comment')


            ->orderBy('proposed_courses.course_id', 'asc')
            ->get();

        $applicantIds = $applicants->pluck('id')->toArray();
        $examGrades = DB::table('olevel_subject_grades')
            ->whereIn('user_id', $applicantIds)
            ->get();

        $applicants = $applicants->map(function ($applicant) use ($examGrades) {
            $applicant->exam_grades = $examGrades->where('user_id', $applicant->id);
            return $applicant;
        });
        return $applicants;
    }
        /**
     * Get all undergraduate students who paid school fees for the current session.
     */
    public function getUndergraduateSchoolFeesPayments(): Collection
    {
        return StudentTransaction::query()
            ->select('student_transactions.*', 'users.surname', 'users.firstname', 'users.m_name', 'users.jamb_no', 'users.phone')
            ->join('users', 'student_transactions.user_id', '=', 'users.id')
            ->where('users.programme_id', ProgrammesEnum::Undergraduate->value)
            ->where('student_transactions.resource', config('remita.schoolfees.ug_schoolfees_description'))
            ->where('student_transactions.acad_session', app(AcademicSessionService::class)->getAcademicSession(Auth::user()))
            ->where('student_transactions.status', TransactionStatus::APPROVED->value)
            ->get();
    }

}
