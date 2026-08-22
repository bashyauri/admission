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
    public $searchCourse = '';
    public $searchRegistered = '';
    public $semesterFilter = 'all'; // all, 1, 2
    protected $listeners = ['pinUsed' => '$refresh'];

    protected $courseService;

    public function mount()
    {

        $this->courseService = new CourseRegistrationService();

        $this->student = auth()->user()->academicDetail;
        $this->departmentId = $this->student->department_id;
        $this->studentLevelId = $this->student->student_level_id;
        $this->maxUnits = $this->courseService->getMaxUnits($this->departmentId, $this->studentLevelId);


        $this->loadCourses(); // Load data into the properties
    }

    private function loadCourses(): void
    {
        $service = new CourseRegistrationService();
        $courses = collect($service->getRegisteredCourses(
            $this->student->id,
            config('remita.settings.academic_session')
        ));
        
        // Filter by search if provided
        if ($this->searchRegistered) {
            $courses = $courses->filter(function ($course) {
                return str_contains(strtolower($course->departmentCourse->studentCourse->code), strtolower($this->searchRegistered)) ||
                       str_contains(strtolower($course->departmentCourse->studentCourse->title), strtolower($this->searchRegistered));
            });
        }
        
        $this->registeredCourses = $courses;
    }

    #[Computed]
    public function getAvailableCourses(): Collection
    {
        $service = new CourseRegistrationService();
        $courses = $service->getAvailableCourses(
            $this->departmentId,
            $this->studentLevelId,
            $this->student->id,
            config('remita.settings.academic_session')
        );
        
        // Filter by semester if selected
        if ($this->semesterFilter !== 'all') {
            $courses = $courses->where('semester', $this->semesterFilter);
        }
        
        // Filter by search if provided
        if ($this->searchCourse) {
            $courses = $courses->filter(function ($course) {
                return str_contains(strtolower($course->code), strtolower($this->searchCourse)) ||
                       str_contains(strtolower($course->title), strtolower($this->searchCourse));
            });
        }
        
        return $courses;
    }

    public function addCourse(DepartmentCourse $course): void
    {
        if ($this->registeredCourses->contains('department_course_id', $course->id)) {
            $this->alert('error', 'You have already registered for this course.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }

        if (!$this->canAddCourse($course->units)) {
            $this->alert('error', 'Adding this course would exceed the maximum allowed units.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }

        $this->isActive = true;

        $studentCourse = $course->studentCourse;

        try {
            $this->student->registeredCourses()->create([
                'department_course_id' => $course->id,
                'semester' => $studentCourse->semester,
                'units' => $course->units,
                'student_level_id' => $studentCourse->student_level_id,
                'academic_session' => config('remita.settings.academic_session')
            ]);

            $this->loadCourses();
            $this->alert('success', 'Course added successfully!', [
                'position' => 'top-end',
                'timer' => 2000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to add course. Please try again.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } finally {
            $this->isActive = false;
        }
    }
    private function canAddCourse(int $courseUnits): bool
    {
        return ($this->registeredCourses->sum('units') + $courseUnits) <= $this->maxUnits;
    }

    public function deleteCourse(RegisteredCourse $registeredCourse): void
    {
        $this->isActive = true;
        
        try {
            $registeredCourse->delete();
            $this->loadCourses();
            $this->alert('success', 'Course removed successfully!', [
                'position' => 'top-end',
                'timer' => 2000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to remove course. Please try again.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } finally {
            $this->isActive = false;
        }
    }

    public function usePin(): void
    {
        $this->student->approval->markAsUsed();
        $this->dispatch('pinUsed')->self();
    }

    public function clearSearch(string $type): void
    {
        if ($type === 'available') {
            $this->searchCourse = '';
        } elseif ($type === 'registered') {
            $this->searchRegistered = '';
        }
    }
    
    public function filterBySemester(string $semester): void
    {
        $this->semesterFilter = $semester;
    }

    public function render()
    {
        return view('livewire.student.course-registration', [
            'courses' => $this->getAvailableCourses(),
            'registeredCourses' => $this->registeredCourses
        ]);
    }
}
