<?php

namespace App\Http\Livewire\Coordinator;

use App\Models\DepartmentCourse;
use App\Models\StudentCourse;
use Livewire\Component;

class AddCourse extends Component
{
    public $search = '';
    public $departmentId;
    public $levelId;

    public function mount()
    {
        $this->departmentId = auth()->user()->coordinator->department_id;
    }

    public function addCourse(StudentCourse $course)
    {
        DepartmentCourse::create([
            'student_course_id' => $course->id,
            'department_id' => $this->departmentId,
            'units' => $course->units,
        ]);
    }
    public function deleteCourse($id)
    {
        DepartmentCourse::findOrFail($id)->delete();
    }

    public function render()
    {
        $coursesNotPicked = StudentCourse::query()
            ->leftJoin('department_courses', function ($join) {
                $join->on('student_courses.id', '=', 'department_courses.student_course_id')
                    ->where('department_courses.department_id', $this->departmentId);
            })
            ->whereNull('department_courses.id')
            ->where('student_courses.code', 'like', "%{$this->search}%")
            ->select('student_courses.*')
            ->get();

        $coursesPicked = DepartmentCourse::query()
            ->where('department_id', $this->departmentId)
            ->with('studentCourse') // Add relationship if available
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.coordinator.add-course', [
            'courses' => $coursesNotPicked,
            'selectedCourses' => $coursesPicked,
        ]);
    }
}
