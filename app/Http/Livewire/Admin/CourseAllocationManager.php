<?php

namespace App\Http\Livewire\Admin;

use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Setting;
use App\Models\User;
use App\Services\AcademicSessionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CourseAllocationManager extends Component
{
    use LivewireAlert;

    /*
    |--------------------------------------------------------------------------
    | Academic Context
    |--------------------------------------------------------------------------
    */

    public string $selectedSession = '';

    public string $selectedSemester = 'first';

    public $selectedDepartmentId = '';


    /*
    |--------------------------------------------------------------------------
    | Course Browser
    |--------------------------------------------------------------------------
    */

    public string $courseSearch = '';

    public string $courseStatus = 'all';


    /*
    |--------------------------------------------------------------------------
    | Allocation Modal
    |--------------------------------------------------------------------------
    */

    public bool $showAllocationModal = false;

    public $selectedCourseId = null;

    public string $lecturerSearch = '';


    /*
    |--------------------------------------------------------------------------
    | Sessions
    |--------------------------------------------------------------------------
    */

    public array $availableSessions = [];


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $service = new AcademicSessionService();

        $defaultSession = $service->getAcademicSession($user);

        $sessionKeys = [
            'ACADEMIC_SESSION',
            'HOD_ACADEMIC_SESSION',
            'PG_ACADEMIC_SESSION',
            'ADMIN_ACADEMIC_SESSION',
        ];

        $dbSessions = Setting::query()
            ->whereIn('key', $sessionKeys)
            ->pluck('value')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->availableSessions = collect([
            ...$dbSessions,
            $defaultSession,
        ])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $this->selectedSession = $defaultSession;

        $firstDepartment = Department::query()
            ->orderBy('name')
            ->first();

        if ($firstDepartment) {
            $this->selectedDepartmentId = $firstDepartment->id;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Context Changes
    |--------------------------------------------------------------------------
    */

    public function updatedSelectedDepartmentId(): void
    {
        $this->resetCourseBrowser();

        $this->closeAllocationModal();
    }

    public function updatedSelectedSession(): void
    {
        $this->resetCourseBrowser();

        $this->closeAllocationModal();
    }

    public function updatedSelectedSemester(): void
    {
        $this->resetCourseBrowser();

        $this->closeAllocationModal();
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Browser
    |--------------------------------------------------------------------------
    */

    protected function resetCourseBrowser(): void
    {
        $this->courseSearch = '';

        $this->courseStatus = 'all';
    }


    /*
    |--------------------------------------------------------------------------
    | Course Semester
    |--------------------------------------------------------------------------
    */

    protected function getCourseSemester(): int
    {
        return $this->selectedSemester === 'first'
            ? 1
            : 2;
    }


    /*
    |--------------------------------------------------------------------------
    | Open Allocation Modal
    |--------------------------------------------------------------------------
    */

    public function openAllocationModal($courseId): void
    {
        if (!$this->selectedDepartmentId) {
            $this->alert(
                'warning',
                'Please select a department first.'
            );

            return;
        }

        $courseSemester = $this->getCourseSemester();

        $course = DepartmentCourse::query()
            ->with('studentCourse')
            ->where('id', $courseId)
            ->where('department_id', $this->selectedDepartmentId)
            ->whereHas('studentCourse', function (Builder $query) use ($courseSemester) {
                $query
                    ->where('semester', $courseSemester)
                    ->where('active', 1);
            })
            ->first();

        if (!$course) {
            $this->alert(
                'error',
                'The selected course is not available.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent opening modal for allocated course
        |--------------------------------------------------------------------------
        */

        $alreadyAllocated = CourseAllocation::query()
            ->where('department_course_id', $course->id)
            ->where('academic_session', $this->selectedSession)
            ->where('semester', $this->selectedSemester)
            ->exists();

        if ($alreadyAllocated) {
            $this->alert(
                'info',
                'This course has already been allocated for this session and semester.'
            );

            return;
        }

        $this->selectedCourseId = $course->id;

        $this->lecturerSearch = '';

        $this->showAllocationModal = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Close Modal
    |--------------------------------------------------------------------------
    */

    public function closeAllocationModal(): void
    {
        $this->showAllocationModal = false;

        $this->selectedCourseId = null;

        $this->lecturerSearch = '';

        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | Lecturer Query
    |--------------------------------------------------------------------------
    */

    protected function lecturerQuery(): Builder
    {
        return User::query()
            ->where(function (Builder $query) {
                $query
                    ->where('role', 'lecturer')
                    ->orWhereHas('capabilities', function (Builder $query) {
                        $query
                            ->where('capability', 'lecturer')
                            ->where('is_active', true);
                    });
            });
    }


    /*
    |--------------------------------------------------------------------------
    | Assign Course
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | The lecturer UUID is passed directly from the Blade button.
    |
    */

    public function assignCourse($lecturerId): void
    {
        if (!$this->selectedCourseId) {
            $this->alert(
                'error',
                'No course has been selected.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find Lecturer
        |--------------------------------------------------------------------------
        */

        $lecturer = $this->lecturerQuery()
            ->where('users.id', $lecturerId)
            ->first();

        if (!$lecturer) {
            $this->alert(
                'error',
                'The selected lecturer could not be found.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find Course
        |--------------------------------------------------------------------------
        */

        $courseSemester = $this->getCourseSemester();

        $departmentCourse = DepartmentCourse::query()
            ->with('studentCourse')
            ->where('id', $this->selectedCourseId)
            ->where('department_id', $this->selectedDepartmentId)
            ->whereHas('studentCourse', function (Builder $query) use ($courseSemester) {
                $query
                    ->where('semester', $courseSemester)
                    ->where('active', 1);
            })
            ->first();

        if (!$departmentCourse) {
            $this->alert(
                'error',
                'The selected course could not be found.'
            );

            $this->closeAllocationModal();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Allocation
        |--------------------------------------------------------------------------
        */

        $alreadyAllocated = CourseAllocation::query()
            ->where('department_course_id', $departmentCourse->id)
            ->where('academic_session', $this->selectedSession)
            ->where('semester', $this->selectedSemester)
            ->exists();

        if ($alreadyAllocated) {
            $this->alert(
                'warning',
                'This course has already been allocated.'
            );

            $this->closeAllocationModal();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Allocation
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($departmentCourse, $lecturer) {
            CourseAllocation::create([
                'department_course_id' => $departmentCourse->id,
                'department_id' => $this->selectedDepartmentId,
                'lecturer_id' => $lecturer->id,
                'academic_session' => $this->selectedSession,
                'semester' => $this->selectedSemester,
                'assigned_units' => $departmentCourse->studentCourse->units ?? null,
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $courseCode = $departmentCourse->studentCourse->code ?? 'Course';

        $lecturerName = trim(
            ($lecturer->firstname ?? '') . ' ' .
            ($lecturer->surname ?? '')
        );

        $this->closeAllocationModal();

        $this->alert(
            'success',
            "{$courseCode} has been allocated to {$lecturerName}."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Allocation
    |--------------------------------------------------------------------------
    */

    public function removeAllocation($allocationId): void
    {
        $allocation = CourseAllocation::query()
            ->where('id', $allocationId)
            ->first();

        if (!$allocation) {
            $this->alert(
                'error',
                'Allocation not found.'
            );

            return;
        }

        $allocation->delete();

        $this->alert(
            'success',
            'Course allocation removed successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | Departments
        |--------------------------------------------------------------------------
        */

        $departments = Department::query()
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Semester
        |--------------------------------------------------------------------------
        */

        $courseSemester = $this->getCourseSemester();


        /*
        |--------------------------------------------------------------------------
        | Current Allocations
        |--------------------------------------------------------------------------
        */

        $allocations = CourseAllocation::query()
            ->with([
                'departmentCourse.studentCourse',
                'lecturer',
                'department',
            ])
            ->where('academic_session', $this->selectedSession)
            ->where('semester', $this->selectedSemester)
            ->when(
                $this->selectedDepartmentId,
                fn (Builder $query) =>
                    $query->where(
                        'department_id',
                        $this->selectedDepartmentId
                    )
            )
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Allocated Course IDs
        |--------------------------------------------------------------------------
        */

        $allocatedCourseIds = $allocations
            ->pluck('department_course_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        */

        $departmentCourses = collect();

        if ($this->selectedDepartmentId) {
            $departmentCourses = DepartmentCourse::query()
                ->with('studentCourse')
                ->where(
                    'department_id',
                    $this->selectedDepartmentId
                )
                ->whereHas('studentCourse', function (Builder $query) use ($courseSemester) {
                    $query
                        ->where('semester', $courseSemester)
                        ->where('active', 1);
                })
                ->when(
                    trim($this->courseSearch) !== '',
                    function (Builder $query) {
                        $search = '%' . trim($this->courseSearch) . '%';

                        $query->whereHas(
                            'studentCourse',
                            function (Builder $q) use ($search) {
                                $q->where('code', 'like', $search)
                                    ->orWhere('title', 'like', $search);
                            }
                        );
                    }
                )
                ->when(
                    $this->courseStatus === 'allocated',
                    function (Builder $query) use ($allocatedCourseIds) {
                        if (empty($allocatedCourseIds)) {
                            $query->whereRaw('1 = 0');

                            return;
                        }

                        $query->whereIn(
                            'id',
                            $allocatedCourseIds
                        );
                    }
                )
                ->when(
                    $this->courseStatus === 'unallocated',
                    function (Builder $query) use ($allocatedCourseIds) {
                        if (!empty($allocatedCourseIds)) {
                            $query->whereNotIn(
                                'id',
                                $allocatedCourseIds
                            );
                        }
                    }
                )
                ->orderBy('id')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Course
        |--------------------------------------------------------------------------
        */

        $selectedCourse = null;

        if ($this->selectedCourseId) {
            $selectedCourse = DepartmentCourse::query()
                ->with('studentCourse')
                ->where('id', $this->selectedCourseId)
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Lecturers
        |--------------------------------------------------------------------------
        */

        $lecturers = $this->lecturerQuery()
            ->when(
                trim($this->lecturerSearch) !== '',
                function (Builder $query) {
                    $search = '%' . trim($this->lecturerSearch) . '%';

                    $query->where(function (Builder $q) use ($search) {
                        $q->where('firstname', 'like', $search)
                            ->orWhere('surname', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
                }
            )
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(50)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalCourses = 0;

        $allocatedCourses = 0;

        $unallocatedCourses = 0;

        if ($this->selectedDepartmentId) {
            $totalCourses = DepartmentCourse::query()
                ->where(
                    'department_id',
                    $this->selectedDepartmentId
                )
                ->whereHas('studentCourse', function (Builder $query) use ($courseSemester) {
                    $query
                        ->where('semester', $courseSemester)
                        ->where('active', 1);
                })
                ->count();

            $allocatedCourses = count($allocatedCourseIds);

            $unallocatedCourses = max(
                0,
                $totalCourses - $allocatedCourses
            );
        }


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'livewire.admin.course-allocation-manager',
            [
                'departments' => $departments,
                'departmentCourses' => $departmentCourses,
                'allocations' => $allocations,
                'lecturers' => $lecturers,
                'selectedCourse' => $selectedCourse,
                'totalCourses' => $totalCourses,
                'allocatedCourses' => $allocatedCourses,
                'unallocatedCourses' => $unallocatedCourses,
                'allocatedCourseIds' => $allocatedCourseIds,
            ]
        )->layout('layouts.app');
    }
}