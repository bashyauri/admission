<?php

namespace App\Http\Livewire\Coordinator;

use App\Models\StudentCourse;
use Livewire\Component;

class AddCourse extends Component
{
    public $search = '';
    public $departmentId;
    public $levelId;
    public function render()
    {
        // $coursesNotPicked = StudentCourse::whereNotIn('id', function ($query) {
        //     $query->select('course_id')
        //         ->from('selected_courses')
        //         ->where('department_id', $this->departmentId)
        //         ->where('level_id', $this->levelId);
        // })
        //     ->where('title', 'like', '%' . $this->search . '%') // Filter by search input
        //     ->get();
        $coursesNotPicked =
            StudentCourse::query()
            ->where('code', 'like', "%{$this->search}%")
            ->orWhere('title', 'like', "%{$this->search}%")
            ->get();



        return view('livewire.coordinator.add-course', [
            'courses' => $coursesNotPicked,
        ]);
    }
}
