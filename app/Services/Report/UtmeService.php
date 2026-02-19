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
     * Get all undergraduate students with their school fees payment status for the current session.
     * Returns a Collection of User models with payment status.
     */
    /**
     * Get filtered undergraduate students with payment status for the current session.
     * Supports database-level filtering for better performance.
     */
    public function getUndergraduateStudentsWithPaymentStatusFiltered(array $filters = []): Collection
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }
        
        $academicSession = app(AcademicSessionService::class)->getAcademicSession($user);
        
        $query = User::where('users.programme_id', ProgrammesEnum::Undergraduate->value)
            ->where('users.role', \App\Enums\Role::STUDENT->value)
            ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
            ->leftJoin('student_transactions', function ($join) use ($academicSession) {
                $join->on('student_transactions.user_id', '=', 'users.id')
                     ->where('student_transactions.resource', config('remita.schoolfees.ug_schoolfees_description'))
                     ->where('student_transactions.acad_session', $academicSession);
            })
            ->leftJoin('departments', 'departments.id', '=', 'academic_details.department_id')
            ->leftJoin('student_levels', 'student_levels.id', '=', 'academic_details.student_level_id')
            ->select('users.*', 'academic_details.matric_no', 'academic_details.department_id', 'academic_details.student_level_id', 
                   'student_transactions.status as payment_status', 'student_transactions.RRR as RRR',
                   'departments.name as department_name', 'student_levels.level as level_name')
            ->where(function($query) {
                $query->whereNull('student_transactions.status')
                      ->orWhere('student_transactions.status', '!=', TransactionStatus::APPROVED->value);
            });

        // Apply filters
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(CONCAT(users.surname, " ", users.firstname, " ", users.m_name)) LIKE ?', [strtolower($searchTerm)])
                  ->orWhere('users.email', 'LIKE', $searchTerm)
                  ->orWhere('users.phone', 'LIKE', $searchTerm)
                  ->orWhere('academic_details.matric_no', 'LIKE', $searchTerm);
            });
        }

        if (!empty($filters['department'])) {
            // Change to INNER JOIN for department filtering to be strict
            $query = User::where('users.programme_id', ProgrammesEnum::Undergraduate->value)
                ->where('users.role', \App\Enums\Role::STUDENT->value)
                ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
                ->leftJoin('student_transactions', function ($join) use ($academicSession) {
                    $join->on('student_transactions.user_id', '=', 'users.id')
                         ->where('student_transactions.resource', config('remita.schoolfees.ug_schoolfees_description'))
                         ->where('student_transactions.acad_session', $academicSession);
                })
                ->join('departments', 'departments.id', '=', 'academic_details.department_id') // INNER JOIN
                ->leftJoin('student_levels', 'student_levels.id', '=', 'academic_details.student_level_id')
                ->select('users.*', 'academic_details.matric_no', 'academic_details.department_id', 'academic_details.student_level_id',
                       'student_transactions.status as payment_status', 'student_transactions.RRR as RRR',
                       'departments.name as department_name', 'student_levels.level as level_name')
                ->where('departments.name', $filters['department'])
                ->where(function($query) {
                    $query->whereNull('student_transactions.status')
                          ->orWhere('student_transactions.status', '!=', TransactionStatus::APPROVED->value);
                });
        }

        if (!empty($filters['level'])) {
            if (!empty($filters['department'])) {
                // If department filter is already applied, add level filter to existing query
                $query->where('student_levels.level', $filters['level'])
                      ->whereNotNull('student_levels.level');
            } else {
                // If only level filter, use INNER JOIN for student_levels
                $query = User::where('users.programme_id', ProgrammesEnum::Undergraduate->value)
                    ->where('users.role', \App\Enums\Role::STUDENT->value)
                    ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
                    ->leftJoin('student_transactions', function ($join) use ($academicSession) {
                        $join->on('student_transactions.user_id', '=', 'users.id')
                             ->where('student_transactions.resource', config('remita.schoolfees.ug_schoolfees_description'))
                             ->where('student_transactions.acad_session', $academicSession);
                    })
                    ->leftJoin('departments', 'departments.id', '=', 'academic_details.department_id')
                    ->join('student_levels', 'student_levels.id', '=', 'academic_details.student_level_id') // INNER JOIN
                    ->select('users.*', 'academic_details.matric_no', 'academic_details.department_id', 'academic_details.student_level_id',
                           'student_transactions.status as payment_status', 'student_transactions.RRR as RRR',
                           'departments.name as department_name', 'student_levels.level as level_name')
                    ->where('student_levels.level', $filters['level'])
                    ->where(function($query) {
                        $query->whereNull('student_transactions.status')
                              ->orWhere('student_transactions.status', '!=', TransactionStatus::APPROVED->value);
                    });
            }
        }

        // Apply sorting
        $sortBy = $filters['sortBy'] ?? 'surname';
        $sortDirection = $filters['sortDirection'] ?? 'asc';

        
        if (in_array($sortBy, ['surname', 'firstname', 'email', 'phone', 'matric_no', 'department_name', 'level_name'])) {
            $query->orderBy($sortBy === 'surname' ? 'users.surname' : 
                           ($sortBy === 'firstname' ? 'users.firstname' : 
                           ($sortBy === 'email' ? 'users.email' : 
                           ($sortBy === 'phone' ? 'users.phone' : 
                           ($sortBy === 'matric_no' ? 'academic_details.matric_no' : 
                           ($sortBy === 'department_name' ? 'departments.name' : 'student_levels.level'))))), $sortDirection);
        }

        return $query->get();
    }

    public function getUndergraduateStudentsWithPaymentStatus(): Collection
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }
        
        $academicSession = app(AcademicSessionService::class)->getAcademicSession($user);
        
        return User::where('users.programme_id', ProgrammesEnum::Undergraduate->value)
            ->where('users.role', \App\Enums\Role::STUDENT->value)
            ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
            ->leftJoin('student_transactions', function ($join) use ($academicSession) {
                $join->on('student_transactions.user_id', '=', 'users.id')
                     ->where('student_transactions.resource', config('remita.schoolfees.ug_schoolfees_description'))
                     ->where('student_transactions.acad_session', $academicSession);
            })
            ->leftJoin('departments', 'departments.id', '=', 'academic_details.department_id')
            ->leftJoin('student_levels', 'student_levels.id', '=', 'academic_details.student_level_id')
            ->select('users.*', 'academic_details.matric_no', 'academic_details.department_id', 'academic_details.student_level_id', 
                   'student_transactions.status as payment_status', 'student_transactions.RRR as RRR',
                   'departments.name as department_name', 'student_levels.level as level_name')
            ->where(function($query) {
                $query->whereNull('student_transactions.status')
                      ->orWhere('student_transactions.status', '!=', TransactionStatus::APPROVED->value);
            })
            ->orderBy('users.surname')
            ->orderBy('users.firstname')
            ->get();
    }

    public function getUndergraduateStudentsNotPaidSchoolFees(): Collection
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }
        
        $academicSession = app(AcademicSessionService::class)->getAcademicSession($user);
        
        return User::where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->where('role', \App\Enums\Role::STUDENT->value)
            ->whereDoesntHave('studentTransactions', function ($q) use ($academicSession) {
                $q->where('resource', config('remita.schoolfees.ug_schoolfees_description'))
                  ->where('acad_session', $academicSession)
                  ->where('status', TransactionStatus::APPROVED->value);
            })
            ->get(['id', 'surname', 'firstname', 'm_name', 'email', 'phone', 'programme_id', 'role']);
    }

    
    public function getUtmeFirstSchoolFeesPaymentAll(): Collection
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }
        
        $academicSession = app(AcademicSessionService::class)->getAcademicSession($user);
        
        return StudentTransaction::select(
            'student_transactions.*',
            'users.surname',
            'users.firstname',
            'users.m_name',
            'users.jamb_no',
            'users.phone',
            'academic_details.matric_no',
            'departments.name as department_name',
            'student_levels.level as level_name'
        )
            ->join('users', 'student_transactions.user_id', '=', 'users.id')
            ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
            ->leftJoin('departments', 'departments.id', '=', 'academic_details.department_id')
            ->leftJoin('student_levels', 'student_levels.id', '=', 'academic_details.student_level_id')
            ->where([
                'student_transactions.resource' => config('remita.schoolfees.ug_schoolfees_description'),
                'student_transactions.status' => \App\Enums\TransactionStatus::APPROVED,
                'student_transactions.acad_session' => $academicSession,
            ])
            ->orderBy('users.surname')
            ->orderBy('users.firstname')
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
            ->get();
    }

}
