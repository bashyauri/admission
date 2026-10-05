<?php

namespace App\Http\Livewire\Student;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use App\Models\CarryOverCourse;
use App\Services\CourseRegistrationService;
use App\Services\CarryOverRegistrationService;
use App\Services\AcademicSessionService;
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
    }

    private function matchesSearch(string $query, ?string ...$fields): bool
    {
        $rawQuery = strtolower(trim($query));
        if ($rawQuery === '') {
            return true;
        }

        $cleanQuery = preg_replace('/[^a-z0-9]/', '', $rawQuery);

        foreach ($fields as $field) {
            if ($field === null || $field === '') {
                continue;
            }

            $rawField = strtolower($field);
            if (str_contains($rawField, $rawQuery)) {
                return true;
            }

            if ($cleanQuery !== '') {
                $cleanField = preg_replace('/[^a-z0-9]/', '', $rawField);
                if (str_contains($cleanField, $cleanQuery)) {
                    return true;
                }
            }
        }

        return false;
    }

    #[Computed]
    public function registeredCourses(): Collection
    {
        $service = new CourseRegistrationService();
        $courses = collect($service->getRegisteredCourses(
            $this->student->id,
            $this->currentAcademicSession()
        ));

        // Filter by search if provided
        if (trim((string) $this->searchRegistered) !== '') {
            $courses = $courses->filter(function ($course) {
                $code = $course->course_code_snapshot ?? $course->departmentCourse?->studentCourse?->code ?? '';
                $title = $course->course_title_snapshot ?? $course->departmentCourse?->studentCourse?->title ?? '';

                return $this->matchesSearch($this->searchRegistered, $code, $title);
            });
        }

        return $courses;
    }

    #[Computed]
    public function getAvailableCourses(): Collection
    {
        $service = new CourseRegistrationService();
        $courses = $service->getAvailableCourses(
            $this->departmentId,
            $this->studentLevelId,
            $this->student->id,
            $this->currentAcademicSession()
        );

        // Filter by semester if selected
        if ($this->semesterFilter !== 'all') {
            $courses = $courses->filter(fn ($course) => (string) $course->semester === (string) $this->semesterFilter);
        }

        // Filter by search if provided
        if (trim((string) $this->searchCourse) !== '') {
            $courses = $courses->filter(function ($course) {
                $code = $course->code ?? $course->studentCourse?->code ?? '';
                $title = $course->title ?? $course->studentCourse?->title ?? '';

                return $this->matchesSearch($this->searchCourse, $code, $title);
            });
        }

        return $courses;
    }

    #[Computed]
    public function isActivityAllowed(): bool
    {
        if (!$this->student?->user) {
            return false;
        }

        return app(\App\Services\StudentStatusService::class)->canPerformAcademicActivity(
            $this->student->user,
            \App\Enums\AcademicActivity::COURSE_REGISTRATION
        );
    }

    #[Computed]
    public function currentStudentStatus(): ?\App\Models\StudentStatusRecord
    {
        if (!$this->student?->user) {
            return null;
        }

        return app(\App\Services\StudentStatusService::class)->getCurrentStatus($this->student->user);
    }

    #[Computed]
    public function carryOverCourses(): Collection
    {
        $user = $this->student?->user;
        if (!$user?->isUndergraduate()) {
            return collect();
        }

        $carryOverService = app(CarryOverRegistrationService::class);
        return $this->student->approval?->isPinUsed()
            ? $carryOverService->registerEligibleRetakes($user, $this->student)
            : $carryOverService->getActiveCarryOvers($user);
    }

    #[Computed]
    public function isRegistrationApproved(): bool
    {
        return (bool) $this->student?->approval?->isApproved();
    }

    #[Computed]
    public function totalRegisteredUnits(): int
    {
        return (int) app(CourseRegistrationService::class)
            ->getTotalUnitsOfRegisteredCourses($this->student->id, $this->currentAcademicSession());
    }

    public function addCourse(DepartmentCourse $course): void
    {
        if ($this->isRegistrationApproved) {
            $this->alert('error', 'Course registration has been approved by your Level Coordinator and cannot be modified.', [
                'position' => 'top-end',
                'timer' => 4000,
                'toast' => true,
            ]);
            return;
        }

        if (!$this->isActivityAllowed) {
            $this->alert('error', 'Course registration is blocked due to your institutional student status.', [
                'position' => 'top-end',
                'timer' => 4000,
                'toast' => true,
            ]);
            return;
        }

        $isAlreadyRegistered = RegisteredCourse::query()
            ->where('academic_detail_id', $this->student->id)
            ->where('department_course_id', $course->id)
            ->where('academic_session', $this->currentAcademicSession())
            ->exists();

        if ($isAlreadyRegistered) {
            $this->alert('error', 'You have already registered for this course.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }

        if (!$this->canAddCourse((int) $course->units)) {
            $this->alert('error', 'Adding this course would exceed the maximum allowed units.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }

        $this->isActive = true;

        try {
            app(CourseRegistrationService::class)->registerCourse(
                $this->student,
                $course,
                $this->currentAcademicSession()
            );

            unset($this->registeredCourses);
            unset($this->getAvailableCourses);
            unset($this->totalRegisteredUnits);

            $this->alert('success', 'Course added successfully!', [
                'position' => 'top-end',
                'timer' => 2000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $isServiceError = str_contains($e->getMessage(), 'institutional status')
                || str_contains($e->getMessage(), 'exceed the maximum')
                || str_contains($e->getMessage(), 'not available for registration');
            $message = $isServiceError
                ? $e->getMessage()
                : 'Failed to add course. Please refresh and try again.';
            $this->alert('error', $message, [
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
        return ($this->totalRegisteredUnits + $courseUnits) <= $this->maxUnits;
    }

    private function currentAcademicSession(): string
    {
        return app(AcademicSessionService::class)->getAcademicSession($this->student->user);
    }

    public function deleteCourse(RegisteredCourse $registeredCourse): void
    {
        if ($this->isRegistrationApproved) {
            $this->alert('error', 'Course registration has been approved by your Level Coordinator and courses cannot be removed.', [
                'position' => 'top-end',
                'timer' => 4000,
                'toast' => true,
            ]);
            return;
        }

        if (!$this->isActivityAllowed) {
            $statusLabel = $this->currentStudentStatus?->status?->label() ?? 'inactive';
            $this->alert('error', "Course registration changes are blocked by your current institutional status ({$statusLabel}).", [
                'position' => 'top-end',
                'timer' => 4000,
                'toast' => true,
            ]);
            return;
        }

        $hasCarryOverHistory = auth()->user()?->isUndergraduate() && CarryOverCourse::query()
            ->where(function ($query) use ($registeredCourse): void {
                $query->where('registered_course_id', $registeredCourse->id)
                    ->orWhere('retake_registered_course_id', $registeredCourse->id);
            })
            ->exists();
        if ($hasCarryOverHistory) {
            $this->alert('error', 'This registration is part of carry-over history and cannot be removed.', [
                'position' => 'top-end',
                'timer' => 4000,
                'toast' => true,
            ]);
            return;
        }

        $this->isActive = true;

        try {
            $registeredCourse->delete();

            if (auth()->user()?->isUndergraduate() && $this->student->approval?->isPinUsed()) {
                app(CarryOverRegistrationService::class)
                    ->registerEligibleRetakes($this->student->user, $this->student);
                unset($this->carryOverCourses);
            }

            unset($this->registeredCourses);
            unset($this->getAvailableCourses);
            unset($this->totalRegisteredUnits);

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
            unset($this->getAvailableCourses);
        } elseif ($type === 'registered') {
            $this->searchRegistered = '';
            unset($this->registeredCourses);
        }
    }

    public function filterBySemester(string $semester): void
    {
        $this->semesterFilter = $semester;
        unset($this->getAvailableCourses);
    }

    public function render()
    {
        return view('livewire.student.course-registration', [
            'courses' => $this->getAvailableCourses,
            'registeredCourses' => $this->registeredCourses,
            'carryOverCourses' => $this->carryOverCourses,
        ]);
    }
}
