<?php

namespace App\Services\Report;

use App\Models\ProposedCourse;

/**
 * Class StudentReportService.
 */
class StudentReportService
{
    public function totalApplicants($departmentId = null, $year =)
    {
        return ProposedCourse::where(['departmentId' => $departmentId, 'academic_session' => $year])->count();
    }
}