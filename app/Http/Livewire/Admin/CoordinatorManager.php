<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Enums\Role;
use App\Models\AcademicDetail;
use App\Models\Coordinator;
use App\Models\Course;
use App\Models\Department;
use App\Models\RegisteredCourse;
use App\Models\Setting;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\AcademicSessionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class CoordinatorManager extends Component
{
    use LivewireAlert, WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Filters & Context
    |--------------------------------------------------------------------------
    */
    public string $selectedSession = '';
    public string $selectedDepartmentId = '';
    public string $selectedLevelId = '';
    public string $coordinatorType = 'all'; // 'all', 'course', 'department'
    public string $search = '';
    public string $activeTab = 'coordinators'; // 'coordinators', 'unassigned_cohorts'

    public array $availableSessions = [];

    /*
    |--------------------------------------------------------------------------
    | Modal & Form State
    |--------------------------------------------------------------------------
    */
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingCoordinatorId = null;

    public string $formAssignmentType = 'course'; // 'course' or 'department'
    public string $formUserId = '';
    public string $formCourseId = '';
    public string $formDepartmentId = '';
    public string $formLevelId = '';
    public string $formSession = '';

    public string $lecturerSearch = '';

    protected function rules(): array
    {
        $rules = [
            'formUserId' => ['required', 'uuid', 'exists:users,id'],
            'formLevelId' => ['required', 'integer', 'exists:student_levels,id'],
            'formSession' => ['required', 'string', 'max:20'],
        ];

        if ($this->formAssignmentType === 'course') {
            $rules['formCourseId'] = ['required', 'integer', 'exists:courses,id'];
        } else {
            $rules['formDepartmentId'] = ['required', 'integer', 'exists:departments,id'];
        }

        return $rules;
    }

    protected $messages = [
        'formUserId.required' => 'Please select a staff member to assign as coordinator.',
        'formUserId.uuid' => 'Invalid staff member selected.',
        'formCourseId.required' => 'Please select a course for this coordinator.',
        'formDepartmentId.required' => 'Please select a department.',
        'formLevelId.required' => 'Please select an admission student level.',
        'formSession.required' => 'Please specify the academic session for this cohort.',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $service = new AcademicSessionService();
        $defaultSession = $user ? $service->getAcademicSession($user) : (config('remita.settings.academic_session') ?: '2026/2027');

        $dbSessions = Setting::query()
            ->whereIn('key', [
                'ACADEMIC_SESSION',
                'HOD_ACADEMIC_SESSION',
                'PG_ACADEMIC_SESSION',
                'ADMIN_ACADEMIC_SESSION',
            ])
            ->pluck('value')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $registeredSessions = RegisteredCourse::query()
            ->pluck('academic_session')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $coordinatorSessions = Coordinator::query()
            ->pluck('academic_session')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $academicDetailSessions = AcademicDetail::query()
            ->pluck('admission_session')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->availableSessions = collect([
            ...$dbSessions,
            ...$registeredSessions,
            ...$coordinatorSessions,
            ...$academicDetailSessions,
            $defaultSession,
        ])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $this->selectedSession = $defaultSession;
        $this->formSession = $defaultSession;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSession(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedDepartmentId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedLevelId(): void
    {
        $this->resetPage();
    }

    public function updatingCoordinatorType(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedFormDepartmentId($value): void
    {
        if ($value && $this->formCourseId) {
            $course = Course::find($this->formCourseId);
            if ($course && (int) $course->department_id !== (int) $value) {
                $this->formCourseId = '';
            }
        }
    }

    public function updatedFormCourseId($value): void
    {
        if ($value) {
            $course = Course::find($value);
            if ($course && $course->department_id) {
                $this->formDepartmentId = (string) $course->department_id;
            }
        }
    }

    public function getFormCoursesProperty()
    {
        return Course::with('department')
            ->when($this->formDepartmentId, function ($query) {
                $query->where('department_id', $this->formDepartmentId);
            })
            ->orderBy('name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Staff Query for Selection
    |--------------------------------------------------------------------------
    */
    public function getStaffMembersProperty()
    {
        return User::query()
            ->where(function (Builder $query) {
                $query->whereIn('role', [
                    Role::COORDINATOR->value,
                    Role::LECTURER->value,
                    Role::HOD->value,
                    Role::ADMIN->value,
                ])
                ->orWhereHas('capabilities', function (Builder $q) {
                    $q->whereIn('capability', ['coordinator', 'lecturer'])
                      ->where('is_active', true);
                });
            })
            ->when($this->lecturerSearch, function (Builder $q) {
                $term = '%' . trim($this->lecturerSearch) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('surname', 'like', $term)
                        ->orWhere('firstname', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(20)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Modal Operations
    |--------------------------------------------------------------------------
    */
    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->editingCoordinatorId = null;
        $this->formAssignmentType = 'course';
        $this->formUserId = '';
        $this->formCourseId = '';
        $this->formDepartmentId = $this->selectedDepartmentId ?: '';
        $this->formLevelId = $this->selectedLevelId ?: '';
        $this->formSession = $this->selectedSession ?: ($this->availableSessions[0] ?? '2026/2027');
        $this->lecturerSearch = '';

        $this->showModal = true;
    }

    public function openAssignForCohort(int $courseId, ?int $levelId, string $session): void
    {
        $this->resetValidation();
        $course = Course::find($courseId);

        $this->isEditing = false;
        $this->editingCoordinatorId = null;
        $this->formAssignmentType = 'course';
        $this->formUserId = '';
        $this->formCourseId = (string) $courseId;
        $this->formDepartmentId = $course?->department_id ? (string) $course->department_id : '';
        $this->formLevelId = $levelId ? (string) $levelId : '';
        $this->formSession = $session;
        $this->lecturerSearch = '';

        $this->showModal = true;
    }

    public function openEditModal(int $coordinatorId): void
    {
        $this->resetValidation();
        $coordinator = Coordinator::with('course')->findOrFail($coordinatorId);

        $this->isEditing = true;
        $this->editingCoordinatorId = $coordinator->id;
        $this->formAssignmentType = $coordinator->course_id ? 'course' : 'department';
        $this->formUserId = (string) $coordinator->user_id;
        $this->formCourseId = $coordinator->course_id ? (string) $coordinator->course_id : '';
        $this->formDepartmentId = $coordinator->course?->department_id 
            ? (string) $coordinator->course->department_id 
            : ($coordinator->department_id ? (string) $coordinator->department_id : '');
        $this->formLevelId = $coordinator->student_level_id ? (string) $coordinator->student_level_id : '';
        $this->formSession = (string) $coordinator->academic_session;
        $this->lecturerSearch = '';

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Save / Reassign Coordinator
    |--------------------------------------------------------------------------
    */
    public function saveCoordinator(): void
    {
        $this->validate();

        $userId = $this->formUserId;
        $levelId = $this->formLevelId ? (int) $this->formLevelId : null;
        $session = trim($this->formSession);
        $courseId = $this->formAssignmentType === 'course' && $this->formCourseId ? (int) $this->formCourseId : null;
        $departmentId = $this->formDepartmentId ? (int) $this->formDepartmentId : null;

        if ($courseId && !$departmentId) {
            $course = Course::find($courseId);
            $departmentId = $course?->department_id ? (int) $course->department_id : null;
        }

        // Check if an existing coordinator is already assigned to this exact cohort
        $duplicateQuery = Coordinator::query()
            ->where('student_level_id', $levelId)
            ->where('academic_session', $session);

        if ($courseId) {
            $duplicateQuery->where('course_id', $courseId);
        } elseif ($departmentId) {
            $duplicateQuery->where('department_id', $departmentId);
        }

        if ($this->isEditing && $this->editingCoordinatorId) {
            $duplicateQuery->where('id', '!=', $this->editingCoordinatorId);
        }

        $existing = $duplicateQuery->first();

        if ($existing) {
            $existingUser = User::find($existing->user_id);
            $existingName = $existingUser ? trim($existingUser->surname . ' ' . $existingUser->firstname) : 'Another coordinator';

            // If we are creating/reassigning, we can update the existing one or prompt
            $this->alert('warning', "This cohort already has {$existingName} assigned. Updating the assignment...");
        }

        DB::transaction(function () use ($userId, $courseId, $departmentId, $levelId, $session) {
            if ($this->isEditing && $this->editingCoordinatorId) {
                $coordinator = Coordinator::findOrFail($this->editingCoordinatorId);
                $coordinator->update([
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'department_id' => $departmentId,
                    'student_level_id' => $levelId,
                    'academic_session' => $session,
                ]);
            } else {
                Coordinator::updateOrCreate(
                    [
                        'course_id' => $courseId,
                        'department_id' => $departmentId,
                        'student_level_id' => $levelId,
                        'academic_session' => $session,
                    ],
                    [
                        'user_id' => $userId,
                    ]
                );
            }

            // Ensure the user has COORDINATOR role or capability
            $assignedUser = User::find($userId);
            if ($assignedUser && in_array($assignedUser->role, [Role::LECTURER->value, Role::STUDENT->value, null])) {
                $assignedUser->update(['role' => Role::COORDINATOR->value]);
            }
        });

        $assignedUser = User::find($userId);
        $name = $assignedUser ? trim($assignedUser->surname . ' ' . $assignedUser->firstname) : 'User';

        $this->closeModal();
        $this->alert('success', "Coordinator {$name} successfully assigned to the cohort.");
    }

    /*
    |--------------------------------------------------------------------------
    | Delete / Remove Coordinator Assignment
    |--------------------------------------------------------------------------
    */
    public function deleteCoordinator(int $coordinatorId): void
    {
        $coordinator = Coordinator::find($coordinatorId);
        if (!$coordinator) {
            $this->alert('error', 'Coordinator record not found.');
            return;
        }

        $coordinator->delete();
        $this->alert('success', 'Coordinator assignment removed.');
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        // 1. Coordinators Query
        $coordinatorsQuery = Coordinator::with(['user', 'course.department', 'department', 'studentLevel'])
            ->when($this->selectedSession, function ($q) {
                $q->where('academic_session', $this->selectedSession);
            })
            ->when($this->selectedLevelId, function ($q) {
                $q->where('student_level_id', $this->selectedLevelId);
            })
            ->when($this->selectedDepartmentId, function ($q) {
                $deptId = $this->selectedDepartmentId;
                $q->where(function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId)
                        ->orWhereHas('course', fn($c) => $c->where('department_id', $deptId));
                });
            })
            ->when($this->coordinatorType === 'course', function ($q) {
                $q->whereNotNull('course_id');
            })
            ->when($this->coordinatorType === 'department', function ($q) {
                $q->whereNotNull('department_id');
            })
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('user', function ($u) use ($term) {
                        $u->where('surname', 'like', $term)
                          ->orWhere('firstname', 'like', $term)
                          ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('course', function ($c) use ($term) {
                        $c->where('name', 'like', $term);
                    })
                    ->orWhereHas('department', function ($d) use ($term) {
                        $d->where('name', 'like', $term)
                          ->orWhere('code', 'like', $term);
                    });
                });
            })
            ->orderByDesc('academic_session')
            ->orderBy('id');

        $coordinators = $coordinatorsQuery->paginate(15);

        // Calculate student counts for coordinators on the current page
        $coordinatorStudentCounts = [];
        foreach ($coordinators as $coord) {
            $studentCount = 0;
            if ($coord->course_id) {
                $studentCount = AcademicDetail::query()
                    ->where('course_id', $coord->course_id)
                    ->when($coord->student_level_id, fn($q) => $q->where('student_level_id', $coord->student_level_id))
                    ->when($coord->academic_session, fn($q) => $q->where('admission_session', $coord->academic_session))
                    ->count();
            } elseif ($coord->department_id) {
                $studentCount = AcademicDetail::query()
                    ->where('department_id', $coord->department_id)
                    ->when($coord->student_level_id, fn($q) => $q->where('student_level_id', $coord->student_level_id))
                    ->when($coord->academic_session, fn($q) => $q->where('admission_session', $coord->academic_session))
                    ->count();
            }
            $coordinatorStudentCounts[$coord->id] = $studentCount;
        }

        // 2. Unassigned Cohorts (Cohorts from academic_details with students but without matching course coordinator)
        $unassignedCohorts = [];
        if ($this->activeTab === 'unassigned_cohorts') {
            $cohorts = AcademicDetail::query()
                ->select('course_id', 'student_level_id', 'admission_session', DB::raw('count(*) as student_count'))
                ->whereNotNull('course_id')
                ->whereNotNull('admission_session')
                ->when($this->selectedSession, fn($q) => $q->where('admission_session', $this->selectedSession))
                ->when($this->selectedLevelId, fn($q) => $q->where('student_level_id', $this->selectedLevelId))
                ->groupBy('course_id', 'student_level_id', 'admission_session')
                ->having('student_count', '>', 0)
                ->get();

            foreach ($cohorts as $cohort) {
                $hasCoord = Coordinator::where('course_id', $cohort->course_id)
                    ->where('student_level_id', $cohort->student_level_id)
                    ->where('academic_session', $cohort->admission_session)
                    ->exists();

                if (!$hasCoord) {
                    $course = Course::with('department')->find($cohort->course_id);
                    $level = StudentLevel::find($cohort->student_level_id);
                    $unassignedCohorts[] = [
                        'course_id' => $cohort->course_id,
                        'course_name' => $course->name ?? 'N/A',
                        'department_name' => $course->department->name ?? 'N/A',
                        'student_level_id' => $cohort->student_level_id,
                        'level_name' => $level->level ?? ($cohort->student_level_id ? $cohort->student_level_id . 'L' : 'N/A'),
                        'academic_session' => $cohort->admission_session,
                        'student_count' => $cohort->student_count,
                    ];
                }
            }
        }

        // 3. Stats Overview
        $totalCoordinators = Coordinator::count();
        $courseBasedCoordinators = Coordinator::whereNotNull('course_id')->count();
        $deptBasedCoordinators = Coordinator::whereNotNull('department_id')->whereNull('course_id')->count();

        return view('livewire.admin.coordinator-manager', [
            'coordinators' => $coordinators,
            'coordinatorStudentCounts' => $coordinatorStudentCounts,
            'unassignedCohorts' => $unassignedCohorts,
            'departments' => Department::orderBy('name')->get(),
            'courses' => Course::with('department')->orderBy('name')->get(),
            'studentLevels' => StudentLevel::all(),
            'totalCoordinators' => $totalCoordinators,
            'courseBasedCoordinators' => $courseBasedCoordinators,
            'deptBasedCoordinators' => $deptBasedCoordinators,
            'staffMembers' => $this->staffMembers,
        ])->layout('layouts.app');
    }
}
