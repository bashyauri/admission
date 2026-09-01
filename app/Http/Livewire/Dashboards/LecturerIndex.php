<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use App\Models\CourseAllocation;
use Illuminate\Support\Facades\Auth;

class LecturerIndex extends Component
{
    public function render()
    {
        // Get lecturer's assigned courses
        $assignedCourses = CourseAllocation::where('lecturer_id', Auth::id())
            ->with(['departmentCourse.studentCourse', 'departmentCourse.department'])
            ->get();
        
        return view('livewire.dashboards.lecturer-index', [
            'assignedCourses' => $assignedCourses
        ]);
    }
}
