<?php

namespace App\Http\Livewire\Coordinator;

use App\Livewire\Forms\EditUnitForm;
use App\Models\DepartmentCourse;
use App\Models\StudentCourse;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AddCourse extends Component
{
    use LivewireAlert;
    public $search = '';
    public $departmentId;
    public $levelId;
    public $editingCourseId;
    public EditUnitForm $form;



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
    public function editCourse($id)
    {
        $this->editingCourseId = $id;
        $departmentCourse = DepartmentCourse::findOrFail($id);
        $this->form->unit = $departmentCourse->units;
    }
    public function saveUnit()
    {
        try {

            $this->form->update($this->editingCourseId);
            $this->alert('success', 'Updated Successfully', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Update failed.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
        $this->cancelEdit();
    }
    public function cancelEdit()
    {
        $this->editingCourseId = null;
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
            ->paginate(10);

        $coursesPicked = DepartmentCourse::query()
            ->where('department_id', $this->departmentId)
            ->with('studentCourse')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.coordinator.add-course', [
            'courses' => $coursesNotPicked,
            'selectedCourses' => $coursesPicked,
        ]);
    }
}
