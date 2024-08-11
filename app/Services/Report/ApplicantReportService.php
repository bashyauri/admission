<?php

namespace App\Services\Report;

use App\Enums\ApplicationStatus;
use App\Models\ProposedCourse;
use Illuminate\Support\Facades\DB;

/**
 * Class ApplicantReportService.
 */
class ApplicantReportService
{


    public function totalApplicants($departmentId = null)
    {
        return ProposedCourse::where(['department_id' => $departmentId, 'academic_session' => config('remita.settings.academic_session')])->count();
    }
    public function applicantsNotRecommended($departmentId = null)
    {
        return ProposedCourse::where(['department_id' => $departmentId, 'status' => ApplicationStatus::PENDING, 'academic_session' => config('remita.settings.academic_session')])->count();
    }
    public function applicantsShortlisted($departmentId = null)
    {
        return ProposedCourse::where(['department_id' => $departmentId, 'status' => ApplicationStatus::SHORTLISTED, 'academic_session' => config('remita.settings.academic_session')])->count();
    }
    public function getApplicants($status = null)
    {
        $query = ProposedCourse::select(
            'proposed_courses.*',
            'users.surname as surname',
            'users.firstname as firstname',
            'users.m_name as middlename',
            'users.picture as picture',
            'users.phone as phone',
            'courses.name AS course_name'
        )
            ->join('users', 'proposed_courses.user_id', '=', 'users.id')
            ->join('courses', 'proposed_courses.course_id', '=', 'courses.id')
            ->where('proposed_courses.department_id', auth()->user()->hodDetails->department_id);

        if ($status !== null) {
            $query->where('proposed_courses.status', $status);
        }

        return $query->latest()->get();
    }

    public function getApplicantsNotRecommended()
    {
        return $this->getApplicants(ApplicationStatus::PENDING);
    }

    public function getApplicantsShortlisted()
    {
        return $this->getApplicants(ApplicationStatus::SHORTLISTED);
    }
}
