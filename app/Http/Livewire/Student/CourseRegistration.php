<?php

namespace App\Http\Livewire\Student;

use Livewire\Component;
use App\Models\StudentCourse;
use Livewire\Attributes\Locked;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use App\Services\CourseRegistrationService;
use Livewire\Attributes\Computed;

class CourseRegistration extends Component
{
    #[Locked]
    public $student;
    #[Locked]
    public $departmentId;
    public $studentLevelId;
    public $editingCourseId;
    public  $isActive = false;
    public $registeredCourses;
    public $maxUnits;



    public function mount()
    {
        $this->departmentId = auth()->user()->academicDetail->department_id;
        $this->studentLevelId = auth()->user()->academicDetail->student_level_id;
        $this->student = auth()->user()->academicDetail;
    }
    public function deleteCourse(RegisteredCourse $registeredCourse): void
    {

        $registeredCourse->delete();
    }
    #[Computed]
    public function getTotalRegisteredUnitsProperty()
    {
        return $this->registeredCourses->sum('units');
    }

    public function addCourse(DepartmentCourse $course, CourseRegistrationService $courseRegistrationService): void
    {
        if ($this->hasUnits()) {
            $this->isActive = true;


            $studentCourse = StudentCourse::find($course->student_course_id);
            $semester = $courseRegistrationService->calculateSemester($studentCourse->code);


            auth()->user()->academicDetail->registeredCourses()->create([
                'department_course_id' => $course->id,
                'semester' => $semester,
                'units' => $course->units,
                'student_level_id' => $studentCourse->student_level_id,
                'academic_session' => config('remita.settings.academic_session')
            ]);
            $this->isActive = false;
        }
    }
    public function usePin()
    {
        $this->student->approval->markAsUsed();
    }
    private function hasUnits(): bool
    {
        return $this->totalRegisteredUnits < $this->maxUnits;
    }

    public function render()
    {
        $service = new CourseRegistrationService();

        $courses = $service->getAvailableCourses(
            departmentId: $this->departmentId,
            studentLevelId: $this->studentLevelId,
            studentId: $this->student->id,
            academicSession: config('remita.settings.academic_session')
        );

        $registeredCourses = $service->getRegisteredCourses(
            studentId: $this->student->id,
            academicSession: config('remita.settings.academic_session')
        );
        $this->registeredCourses = collect($registeredCourses);
        $this->maxUnits = $service->getMaxUnits($this->departmentId, $this->studentLevelId);


        return view('livewire.student.course-registration', [
            'courses' => $courses,
            'registeredCourses' => $registeredCourses
        ]);
    }
}
