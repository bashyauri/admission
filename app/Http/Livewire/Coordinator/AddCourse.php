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
    public function addCourse(StudentCourse $course)
    {
        $this->departmentId = auth()->user()->coordinator->department_id;
        DepartmentCourse::create([
            'student_course_id' => $course->id,
            'department_id' => $this->departmentId,
            'units' => $course->units,

        ]);
    }
    public function render()
    {
        $coursesNotPicked = StudentCourse::whereNotIn('id', function ($query) {
            $query->select('student_course_id')
                ->from('department_courses')
                ->where('department_id', auth()->user()->coordinator->department_id);
        })
            ->where('title', 'like', '%' . $this->search . '%') // Filter by search input
            ->get();

        return view('livewire.coordinator.add-course', [
            'courses' => $coursesNotPicked,
        ]);
    }
}
