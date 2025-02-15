<?php

namespace App\Http\Livewire\Student;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use App\Services\CourseRegistrationService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;

class CourseRegistration extends Component
{
    use LivewireAlert;
    #[Locked]
    public $student;
    #[Locked]
    public $departmentId;

    public $studentLevelId;
    public $editingCourseId;
    public bool $isActive = false;
    public Collection $registeredCourses;
    public int $maxUnits;
    protected $listeners = ['pinUsed' => '$refresh'];

    protected $courseService;

    public function mount()
    {

        $this->courseService = new CourseRegistrationService();

        $this->student = auth()->user()->academicDetail;
        $this->departmentId = $this->student->department_id;
        $this->studentLevelId = $this->student->student_level_id;


        $this->loadCourses(); // Load data into the properties
    }

    private function loadCourses(): void
    {
        $service = new CourseRegistrationService();
        $registeredCourses = $service->getRegisteredCourses(
            $this->student->id,
            config('remita.settings.academic_session')
        );

        $this->registeredCourses = collect($registeredCourses);
        $this->maxUnits = $service->getMaxUnits($this->departmentId, $this->studentLevelId);
    }

    #[Computed]
    public function getAvailableCourses(): Collection
    {
        $service = new CourseRegistrationService();
        return $service->getAvailableCourses(
            $this->departmentId,
            $this->studentLevelId,
            $this->student->id,
            config('remita.settings.academic_session')
        );
    }
    #[Computed]
    public function totalRegisteredUnits(): int
    {
        return $this->registeredCourses ? $this->registeredCourses->sum('units') : 0;
    }

    public function addCourse(DepartmentCourse $course): void
    {

        if (!$this->canAddCourse($course->units)) {
            $this->alert('error', 'Adding this course would exceed the maximum allowed units.');
            return;
        }

        $this->isActive = true;

        $studentCourse = $course->load('studentCourse')->studentCourse;


        $this->student->registeredCourses()->create([
            'department_course_id' => $course->id,
            'semester' => $studentCourse->semester,
            'units' => $course->units,
            'student_level_id' => $studentCourse->student_level_id,
            'academic_session' => config('remita.settings.academic_session')
        ]);

        $this->loadCourses();
        $this->isActive = false;
    }
    private function canAddCourse(int $courseUnits): bool
    {
        return ($this->totalRegisteredUnits + $courseUnits) <= $this->maxUnits;
    }

    public function deleteCourse(RegisteredCourse $registeredCourse): void
    {
        $this->isActive = true;
        $registeredCourse->delete();
        $this->isActive = false;
        $this->loadCourses();
    }

    public function usePin(): void
    {
        $this->student->approval->markAsUsed();
        $this->dispatch('pinUsed')->self();
    }

    public function render()
    {
        return view('livewire.student.course-registration', [
            'courses' => $this->getAvailableCourses,
            'registeredCourses' => $this->registeredCourses
        ]);
    }
}
