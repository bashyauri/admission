<?php

namespace App\Http\Livewire\Coordinator;

use App\Livewire\Forms\EditUnitForm;
use App\Models\DepartmentCourse;
use App\Models\StudentCourse;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class AddCourse extends Component
{
    use LivewireAlert, WithPagination;

    public $search = '';
    public $departmentId;
    public $editingCourseId;
    public EditUnitForm $form;

    // Initialize the department ID when the component mounts
    public function mount(): void
    {
        $this->departmentId = auth()->user()->coordinator->department_id;
    }

    // Add a course to the department
    public function addCourse(StudentCourse $course): void
    {
        try {
            // Check if the course already exists in the department
            if ($this->isCourseAlreadyAdded($course->id)) {
                $this->alert('warning', 'This course has already been added to the department!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }

            // Add the course to the department
            DepartmentCourse::create([
                'student_course_id' => $course->id,
                'department_id' => $this->departmentId,
                'units' => $course->units,
            ]);

            $this->alert('success', 'Course added successfully!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Failed to add course.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }

    // Delete a course from the department
    public function deleteCourse(int $id): void
    {
        try {
            DepartmentCourse::findOrFail($id)->delete();
            $this->alert('success', 'Course deleted successfully!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Failed to delete course.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }

    // Start editing a course's units
    public function editCourse(int $id): void
    {
        $this->editingCourseId = $id;
        $departmentCourse = DepartmentCourse::findOrFail($id);
        $this->form->unit = $departmentCourse->units;
    }

    // Save the updated units for a course
    public function saveUnit(): void
    {
        try {
            $this->form->update($this->editingCourseId);
            $this->alert('success', 'Units updated successfully!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Failed to update units.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
        $this->cancelEdit();
    }

    // Cancel the editing process
    public function cancelEdit(): void
    {
        $this->editingCourseId = null;
    }

    // Check if a course is already added to the department
    private function isCourseAlreadyAdded(int $courseId): bool
    {
        return DepartmentCourse::where('student_course_id', $courseId)
            ->where('department_id', $this->departmentId)
            ->exists();
    }

    // Render the component view
    public function render()
    {
        $coursesNotPicked = $this->getCoursesNotPicked();
        $coursesPicked = $this->getCoursesPicked();

        return view('livewire.coordinator.add-course', [
            'courses' => $coursesNotPicked,
            'selectedCourses' => $coursesPicked,
        ]);
    }

    // Get courses not yet picked by the department
    private function getCoursesNotPicked()
    {
        return StudentCourse::query()
            ->leftJoin('department_courses', function ($join) {
                $join->on('student_courses.id', '=', 'department_courses.student_course_id')
                    ->where('department_courses.department_id', $this->departmentId);
            })
            ->whereNull('department_courses.id')
            ->where('student_courses.code', 'like', "%{$this->search}%")
            ->select('student_courses.*')
            ->paginate(10);
    }

    // Get courses already picked by the department
    private function getCoursesPicked()
    {
        return DepartmentCourse::query()
            ->where('department_id', $this->departmentId)
            ->with('studentCourse')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}