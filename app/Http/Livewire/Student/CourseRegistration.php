<?php

namespace App\Http\Livewire\Student;

use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Services\AcademicSessionService;
use App\Services\PaymentService;
use App\Services\CourseRegistrationService;
use Jantinnerezo\LivewireAlert\LivewireAlert;

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
    public string $searchCourse = ''; // Add search property
    public string $searchRegistered = ''; // Add search for registered courses
    protected $listeners = ['pinUsed' => '$refresh'];

    protected $courseService;

    public function mount()
    {
        $this->ensureSchoolFeesPaid();

        $this->courseService = new CourseRegistrationService();
        $this->student = Auth::user()->academicDetail;
        $this->departmentId = $this->student->department_id;
        $this->studentLevelId = $this->student->student_level_id;
        $this->maxUnits = $this->courseService->getMaxUnits($this->departmentId, $this->studentLevelId);
        $this->loadCourses();
    }

    private function loadCourses(): void
    {
        $service = new CourseRegistrationService();
        $this->registeredCourses = collect($service->getRegisteredCourses(
            $this->student->id,
            app(AcademicSessionService::class)->getAcademicSession(Auth::user())
        ));
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

        // Apply search filter if search term exists
        if (!empty($this->searchCourse)) {
            $searchTerm = strtolower($this->searchCourse);
            $courses = $courses->filter(function ($course) use ($searchTerm) {
                return str_contains(strtolower($course->code), $searchTerm) ||
                    str_contains(strtolower($course->title), $searchTerm);
            });
        }

        return $courses;
    }

    // Add computed property for filtered registered courses
    #[Computed]
    public function getFilteredRegisteredCourses(): Collection
    {
        $registeredCourses = $this->registeredCourses;

        // Apply search filter if search term exists
        if (!empty($this->searchRegistered)) {
            $searchTerm = strtolower($this->searchRegistered);
            $registeredCourses = $registeredCourses->filter(function ($course) use ($searchTerm) {
                return str_contains(strtolower($course->departmentCourse->studentCourse->code), $searchTerm) ||
                    str_contains(strtolower($course->departmentCourse->studentCourse->title), $searchTerm);
            });
        }

        return $registeredCourses;
    }

    public function addCourse(DepartmentCourse $course): void
    {
        $this->ensureSchoolFeesPaid();

        if ($this->registeredCourses->contains('department_course_id', $course->id)) {
            $this->alert('error', 'You have already registered for this course.');
            return;
        }

        if (!$this->canAddCourse($course->units)) {
            $this->alert('error', 'Adding this course would exceed the maximum allowed units.');
            return;
        }

        $this->isActive = true;
        $studentCourse = $course->studentCourse;

        $this->student->registeredCourses()->create([
            'department_course_id' => $course->id,
            'semester' => $studentCourse->semester,
            'units' => $course->units,
            'student_level_id' => $studentCourse->student_level_id,
            'academic_session' => config('remita.settings.academic_session')
        ]);

        $this->loadCourses();
        $this->isActive = false;

        // Clear search after adding course
        $this->searchCourse = '';
    }

    private function canAddCourse(int $courseUnits): bool
    {
        return ($this->registeredCourses->sum('units') + $courseUnits) <= $this->maxUnits;
    }

    public function deleteCourse($registeredCourseId): void
    {
        $this->ensureSchoolFeesPaid();

        $this->isActive = true;

        $registeredCourse = RegisteredCourse::find($registeredCourseId);

        if ($registeredCourse) {
            $registeredCourse->delete();
            $this->alert('success', 'Course removed successfully.');
        }

        $this->isActive = false;
        $this->loadCourses();
        $this->searchRegistered = '';
    }

    public function usePin(): void
    {
        $this->ensureSchoolFeesPaid();

        $this->student->approval->markAsUsed();
        $this->dispatch('pinUsed')->self();
    }

    private function ensureSchoolFeesPaid(): void
    {
        $hasPaidSchoolFees = app(PaymentService::class)
            ->hasStudentPaidSchoolFees((string) Auth::id());

        abort_unless($hasPaidSchoolFees, 403, 'You must pay school fees before using course registration.');
    }

    // Add method to clear search
    public function clearSearch($type): void
    {
        if ($type === 'available') {
            $this->searchCourse = '';
        } else {
            $this->searchRegistered = '';
        }
    }

    public function render()
    {
        return view('livewire.student.course-registration', [
            'courses' => $this->getAvailableCourses,
            'registeredCourses' => $this->getFilteredRegisteredCourses
        ]);
    }
}
