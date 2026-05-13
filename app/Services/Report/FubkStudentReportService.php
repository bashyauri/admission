<?php

declare(strict_types=1);

namespace App\Services\Report;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FubkStudentReportService
{
    public function getFreshStudents(
        string $academicSession,
        string $search = '',
        int $perPage = 15,
        ?int $departmentId = null,
        ?int $programmeId = null
    ): LengthAwarePaginator
    {
        return $this->paginateReport('fresh', $academicSession, $search, $perPage, $departmentId, $programmeId);
    }

    public function getReturningStudents(
        string $academicSession,
        string $search = '',
        int $perPage = 15,
        ?int $departmentId = null,
        ?int $programmeId = null
    ): LengthAwarePaginator
    {
        return $this->paginateReport('returning', $academicSession, $search, $perPage, $departmentId, $programmeId);
    }

    public function getAllStudents(
        string $academicSession,
        string $search = '',
        int $perPage = 15,
        ?int $departmentId = null,
        ?int $programmeId = null
    ): LengthAwarePaginator
    {
        return $this->paginateReport('all', $academicSession, $search, $perPage, $departmentId, $programmeId);
    }

    public function exportFreshStudents(string $academicSession, string $search = '', ?int $departmentId = null, ?int $programmeId = null): Collection
    {
        return $this->mapForDisplay($this->baseQuery('fresh', $academicSession, $search, $departmentId, $programmeId)->get());
    }

    public function exportReturningStudents(string $academicSession, string $search = '', ?int $departmentId = null, ?int $programmeId = null): Collection
    {
        return $this->mapForDisplay($this->baseQuery('returning', $academicSession, $search, $departmentId, $programmeId)->get());
    }

    public function exportAllStudents(string $academicSession, string $search = '', ?int $departmentId = null, ?int $programmeId = null): Collection
    {
        return $this->mapForDisplay($this->baseQuery('all', $academicSession, $search, $departmentId, $programmeId)->get());
    }

    public function exportCourseRegistrations(
        string $type,
        string $academicSession,
        string $search = '',
        ?int $departmentId = null,
        ?int $programmeId = null
    ): Collection
    {
        return $this->mapCourseRegistrationsForDisplay(
            $this->courseRegistrationBaseQuery($type, $academicSession, $search, $departmentId, $programmeId)->get()
        );
    }

    public function getDepartments(): Collection
    {
        return DB::table('departments')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getProgrammes(): Collection
    {
        return DB::table('programmes')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function paginateReport(
        string $type,
        string $academicSession,
        string $search,
        int $perPage,
        ?int $departmentId,
        ?int $programmeId
    ): LengthAwarePaginator
    {
        $paginator = $this->baseQuery($type, $academicSession, $search, $departmentId, $programmeId)
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection($this->mapForDisplay($paginator->getCollection()));

        return $paginator;
    }

    private function baseQuery(
        string $type,
        string $academicSession,
        string $search,
        ?int $departmentId = null,
        ?int $programmeId = null
    ): Builder
    {
        $matricYearToken = $this->getMatricYearTokenFromSession($academicSession);

        $latestAcademicDetail = DB::table('academic_details as ad')
            ->select('ad.*')
            ->whereRaw('ad.id = (SELECT MAX(inner_ad.id) FROM academic_details inner_ad WHERE inner_ad.user_id = ad.user_id)');

        return DB::table('users')
            ->joinSub($latestAcademicDetail, 'academic_details', function ($join): void {
                $join->on('academic_details.user_id', '=', 'users.id');
            })
            ->leftJoin('states', 'states.id', '=', 'users.state_id')
            ->leftJoin('lgas', 'lgas.id', '=', 'users.lga_id')
            ->leftJoin('departments as admitted_departments', 'admitted_departments.id', '=', 'academic_details.department_id')
            ->leftJoin('proposed_courses', 'proposed_courses.user_id', '=', 'users.id')
            ->leftJoin('departments as proposed_departments', 'proposed_departments.id', '=', 'proposed_courses.department_id')
            ->leftJoin('programmes', 'programmes.id', '=', 'users.programme_id')
            ->leftJoin('registered_courses', function ($join) use ($academicSession): void {
                $join->on('registered_courses.academic_detail_id', '=', 'academic_details.id')
                    ->where('registered_courses.academic_session', '=', $academicSession);
            })
            ->leftJoin('department_courses', 'department_courses.id', '=', 'registered_courses.department_course_id')
            ->leftJoin('student_courses', 'student_courses.id', '=', 'department_courses.student_course_id')
            ->leftJoin('student_levels', 'student_levels.id', '=', 'registered_courses.student_level_id')
            ->where('users.role', 'student')
            ->whereNotNull('registered_courses.id')
            ->when($departmentId !== null, function (Builder $query) use ($departmentId): void {
                $query->whereRaw('COALESCE(admitted_departments.id, proposed_departments.id) = ?', [$departmentId]);
            })
            ->when($programmeId !== null, function (Builder $query) use ($programmeId): void {
                $query->where('users.programme_id', '=', $programmeId);
            })
            ->when($type === 'fresh', function (Builder $query) use ($matricYearToken): void {
                $query->whereNotNull('academic_details.matric_no')
                    ->whereRaw('LEFT(academic_details.matric_no, 2) = ?', [$matricYearToken]);
            })
            ->when($type === 'returning', function (Builder $query) use ($matricYearToken): void {
                $query->where(function (Builder $nested) use ($matricYearToken): void {
                    $nested->whereNull('academic_details.matric_no')
                        ->orWhere(function (Builder $byMatric) use ($matricYearToken): void {
                            $byMatric->whereRaw('LEFT(academic_details.matric_no, 2) != ?', [$matricYearToken]);
                        });
                });
            })
            ->when(trim($search) !== '', function (Builder $query) use ($search): void {
                $term = '%' . strtolower(trim($search)) . '%';

                $query->where(function (Builder $nested) use ($term): void {
                    $nested->whereRaw('LOWER(users.surname) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.firstname) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.m_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.email) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.phone) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.jamb_no) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(academic_details.matric_no) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(student_courses.code) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(student_courses.title) LIKE ?', [$term]);
                });
            })
            ->groupBy([
                'users.id',
                'users.surname',
                'users.firstname',
                'users.m_name',
                'users.email',
                'users.phone',
                'users.gender',
                'users.nationality',
                'users.jamb_no',
                'states.name',
                'lgas.name',
                'academic_details.matric_no',
                'academic_details.acad_session',
                'admitted_departments.name',
                'proposed_departments.name',
                'programmes.name',
            ])
            ->orderByRaw('COALESCE(admitted_departments.name, proposed_departments.name) ASC')
            ->orderBy('programmes.name')
            ->orderByRaw('MIN(CAST(student_levels.level AS UNSIGNED)) ASC')
            ->orderByRaw('MIN(student_courses.code) ASC')
            ->orderBy('users.surname')
            ->orderBy('users.firstname')
            ->select([
                'users.id',
                'users.surname',
                'users.firstname',
                'users.m_name',
                'users.email',
                'users.phone',
                'users.gender',
                'users.nationality',
                'users.jamb_no',
                'states.name as state_name',
                'lgas.name as lga_name',
                'academic_details.matric_no',
                'academic_details.acad_session as admission_session',
                DB::raw('COALESCE(admitted_departments.name, proposed_departments.name) as department_name'),
                'programmes.name as programme_name',
                DB::raw('MIN(CAST(student_levels.level AS UNSIGNED)) as level_sort'),
                DB::raw('MIN(student_courses.code) as first_course_code'),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(student_courses.code, ' - ', student_courses.title) ORDER BY student_courses.code SEPARATOR ' | ') as registered_courses"),
            ]);
    }

    private function getMatricYearTokenFromSession(string $academicSession): string
    {
        if (preg_match('/(20\d{2})/', $academicSession, $matches) === 1) {
            return substr($matches[1], -2);
        }

        return substr(date('Y'), -2);
    }

    private function courseRegistrationBaseQuery(
        string $type,
        string $academicSession,
        string $search,
        ?int $departmentId = null,
        ?int $programmeId = null
    ): Builder {
        $matricYearToken = $this->getMatricYearTokenFromSession($academicSession);

        $latestAcademicDetail = DB::table('academic_details as ad')
            ->select('ad.*')
            ->whereRaw('ad.id = (SELECT MAX(inner_ad.id) FROM academic_details inner_ad WHERE inner_ad.user_id = ad.user_id)');

        return DB::table('users')
            ->joinSub($latestAcademicDetail, 'academic_details', function ($join): void {
                $join->on('academic_details.user_id', '=', 'users.id');
            })
            ->leftJoin('departments as admitted_departments', 'admitted_departments.id', '=', 'academic_details.department_id')
            ->leftJoin('proposed_courses', 'proposed_courses.user_id', '=', 'users.id')
            ->leftJoin('departments as proposed_departments', 'proposed_departments.id', '=', 'proposed_courses.department_id')
            ->leftJoin('programmes', 'programmes.id', '=', 'users.programme_id')
            ->join('registered_courses', function ($join) use ($academicSession): void {
                $join->on('registered_courses.academic_detail_id', '=', 'academic_details.id')
                    ->where('registered_courses.academic_session', '=', $academicSession);
            })
            ->join('department_courses', 'department_courses.id', '=', 'registered_courses.department_course_id')
            ->join('student_courses', 'student_courses.id', '=', 'department_courses.student_course_id')
            ->leftJoin('student_levels', 'student_levels.id', '=', 'registered_courses.student_level_id')
            ->where('users.role', 'student')
            ->when($departmentId !== null, function (Builder $query) use ($departmentId): void {
                $query->whereRaw('COALESCE(admitted_departments.id, proposed_departments.id) = ?', [$departmentId]);
            })
            ->when($programmeId !== null, function (Builder $query) use ($programmeId): void {
                $query->where('users.programme_id', '=', $programmeId);
            })
            ->when($type === 'fresh', function (Builder $query) use ($matricYearToken): void {
                $query->whereNotNull('academic_details.matric_no')
                    ->whereRaw('LEFT(academic_details.matric_no, 2) = ?', [$matricYearToken]);
            })
            ->when($type === 'returning', function (Builder $query) use ($matricYearToken): void {
                $query->where(function (Builder $nested) use ($matricYearToken): void {
                    $nested->whereNull('academic_details.matric_no')
                        ->orWhereRaw('LEFT(academic_details.matric_no, 2) != ?', [$matricYearToken]);
                });
            })
            ->when(trim($search) !== '', function (Builder $query) use ($search): void {
                $term = '%' . strtolower(trim($search)) . '%';

                $query->where(function (Builder $nested) use ($term): void {
                    $nested->whereRaw('LOWER(users.surname) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.firstname) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(users.m_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(academic_details.matric_no) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(student_courses.code) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(student_courses.title) LIKE ?', [$term]);
                });
            })
            ->orderByRaw('COALESCE(admitted_departments.name, proposed_departments.name) ASC')
            ->orderBy('programmes.name')
            ->orderBy('users.surname')
            ->orderBy('users.firstname')
            ->orderBy('users.m_name')
            ->orderBy('academic_details.matric_no')
            ->orderByRaw('CAST(student_levels.level AS UNSIGNED) ASC')
            ->orderBy('student_courses.semester')
            ->orderBy('student_courses.code')
            ->select([
                'users.id',
                'users.surname',
                'users.firstname',
                'users.m_name',
                'academic_details.matric_no',
                'registered_courses.academic_session',
                DB::raw('COALESCE(admitted_departments.name, proposed_departments.name) as department_name'),
                'programmes.name as programme_name',
                'student_levels.level as level_name',
                'student_courses.semester',
                'student_courses.code as course_code',
                'student_courses.title as course_title',
                'registered_courses.units as credit_units',
            ]);
    }

    private function mapCourseRegistrationsForDisplay(Collection $rows): Collection
    {
        return $rows->map(static function (object $row): array {
            return [
                'student_name' => trim(implode(' ', array_filter([$row->surname, $row->firstname, $row->m_name]))),
                'matric_no' => $row->matric_no,
                'department_name' => $row->department_name,
                'programme_name' => $row->programme_name,
                'academic_session' => $row->academic_session,
                'level_name' => $row->level_name,
                'semester' => $row->semester,
                'course_code' => $row->course_code,
                'course_title' => $row->course_title,
                'credit_units' => $row->credit_units,
            ];
        });
    }


    private function mapForDisplay(Collection $rows): Collection
    {
        return $rows->map(static function (object $row): array {
            return [
                'id' => $row->id,
                'full_name' => trim(implode(' ', array_filter([$row->surname, $row->firstname, $row->m_name]))),
                'surname' => $row->surname,
                'firstname' => $row->firstname,
                'middle_name' => $row->m_name,
                'email' => $row->email,
                'phone' => $row->phone,
                'gender' => $row->gender,
                'nationality' => $row->nationality,
                'state' => $row->state_name,
                'lga' => $row->lga_name,
                'jamb_no' => $row->jamb_no,
                'matric_no' => $row->matric_no,
                'department_name' => $row->department_name,
                'programme_name' => $row->programme_name,
                'admission_session' => $row->admission_session,
                'registered_courses' => $row->registered_courses ? explode(' | ', $row->registered_courses) : [],
                'registered_courses_text' => $row->registered_courses ?: 'No registered courses',
            ];
        });
    }
}
