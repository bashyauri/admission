<?php

namespace App\Services\Report;

use App\Models\ProposedCourse;

/**
 * Class StudentReportService.
 */
class StudentReportService
{


    public function totalApplicants($departmentId = '')
    {
        return ProposedCourse::where(['department_id' => $departmentId, 'academic_session' => config('remita.settings.academic_session')])->count();
    }
}