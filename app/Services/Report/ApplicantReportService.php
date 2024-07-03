<?php

namespace App\Services\Report;

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
    public function getApplicants()
    {
        return
            ProposedCourse::select(
                'proposed_courses.*',
                'users.surname as surname',
                'users.firstname as firstname',
                'users.m_name as middlename',
                'users.picture as picture',
                'users.phone as phone',

                'courses.name AS course_name'
            )
            ->join('users', function ($join) {
                $join->on('proposed_courses.user_id', '=', 'users.id');
            })
            ->join('courses', function ($join) {
                $join->on('proposed_courses.course_id', '=', 'courses.id');
            })
            ->where('proposed_courses.department_id', 1)
            ->latest()
            ->get();
    }
}
