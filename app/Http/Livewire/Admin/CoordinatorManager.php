<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Enums\Role;
use App\Models\AcademicDetail;
use App\Models\Coordinator;
use App\Models\Course;
use App\Models\Department;
use App\Models\RegisteredCourse;
use App\Models\Result;
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
    public $departments;
    public $courses;
    public $studentLevels;

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
            ->distinct()
            ->pluck('academic_session')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $coordinatorSessions = Coordinator::query()
            ->distinct()
            ->pluck('academic_session')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $academicDetailSessions = AcademicDetail::query()
            ->distinct()
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

        $this->departments = Department::orderBy('name')->get();
        $this->courses = Course::with('department')->orderBy('name')->get();
        $this->studentLevels = StudentLevel::orderBy('id')->get();
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

            if ($this->isEditing) {
                $this->alert('error', "Cannot update assignment: {$existingName} is already assigned as coordinator for this exact cohort (Course, Level, and Session). Please edit that coordinator record directly instead.");
                return;
            }
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
    | Re-link Unassigned Submitted Results
    |--------------------------------------------------------------------------
    */
    public function relinkUnassignedResults(): void
    {
        $unassignedResults = Result::whereNull('coordinator_id')
            ->whereIn('status', ['submitted', 'pending'])
            ->with(['academicDetail.coordinator', 'registeredCourse'])
            ->get();

        if ($unassignedResults->isEmpty()) {
            $this->alert('info', 'No unassigned submitted or pending results found to re-link.');
            return;
        }

        $coordinators = Coordinator::all();
        $relinkedCount = 0;
        $unmatchedCount = 0;

        DB::transaction(function () use ($unassignedResults, $coordinators, &$relinkedCount, &$unmatchedCount) {
            foreach ($unassignedResults as $result) {
                $ad = $result->academicDetail;
                if (!$ad) {
                    $unmatchedCount++;
                    continue;
                }

                $candidate = null;

                // 1. Direct coordinator from academic details
                if ($ad->coordinator_id) {
                    $candidate = $coordinators->firstWhere('id', $ad->coordinator_id);
                }

                // 2. Resolve level directly
                $levelId = $result->level_snapshot 
                    ?? $result->registeredCourse?->level_snapshot 
                    ?? $result->registeredCourse?->student_level_id 
                    ?? $ad->student_level_id 
                    ?? 1;

                // 3. Exact course cohort match
                if (!$candidate && $ad->course_id && $ad->admission_session) {
                    $candidate = $coordinators->first(function ($c) use ($ad, $levelId) {
                        return $c->course_id == $ad->course_id 
                            && $c->student_level_id == $levelId 
                            && $c->academic_session == $ad->admission_session;
                    });
                }

                // 4. Course + academic session match
                if (!$candidate && $ad->course_id && $result->academic_session) {
                    $candidate = $coordinators->first(function ($c) use ($ad, $levelId, $result) {
                        return $c->course_id == $ad->course_id 
                            && $c->student_level_id == $levelId 
                            && $c->academic_session == $result->academic_session;
                    });
                }

                // 5. Dept cohort match
                if (!$candidate && $ad->department_id && $ad->admission_session) {
                    $candidate = $coordinators->first(function ($c) use ($ad, $levelId) {
                        return $c->department_id == $ad->department_id 
                            && $c->student_level_id == $levelId 
                            && $c->academic_session == $ad->admission_session;
                    });
                }

                // 6. Course + level (session-agnostic)
                if (!$candidate && $ad->course_id) {
                    $candidate = $coordinators->first(function ($c) use ($ad, $levelId) {
                        return $c->course_id == $ad->course_id 
                            && $c->student_level_id == $levelId;
                    });
                }

                // 7. Dept + level (session-agnostic)
                if (!$candidate && $ad->department_id) {
                    $candidate = $coordinators->first(function ($c) use ($ad, $levelId) {
                        return $c->department_id == $ad->department_id 
                            && $c->student_level_id == $levelId;
                    });
                }

                if ($candidate) {
                    $result->update(['coordinator_id' => $candidate->id]);
                    $relinkedCount++;
                } else {
                    $unmatchedCount++;
                }
            }
        });

        if ($relinkedCount > 0) {
            $msg = "Successfully re-linked {$relinkedCount} result(s) to their respective coordinators.";
            if ($unmatchedCount > 0) {
                $msg .= " ({$unmatchedCount} result(s) could not be matched; please assign a coordinator for their cohort).";
            }
            $this->alert('success', $msg);
        } else {
            $this->alert('warning', "Could not find matching coordinators for {$unmatchedCount} result(s). Please configure coordinators for those cohorts first.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $unassignedSubmittedResultCount = Result::whereNull('coordinator_id')
            ->whereIn('status', ['submitted', 'pending'])
            ->count();
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

        // Calculate student counts for coordinators on the current page using one grouped aggregate query.
        $coordinatorStudentCounts = [];

        if ($coordinators->isNotEmpty()) {
            $coordinatorIds = $coordinators->pluck('id')->all();

            $courseStudentCounts = DB::table('coordinators as c')
                ->join('academic_details as ad', function ($join) {
                    $join->on('ad.course_id', '=', 'c.course_id')
                        ->on('ad.student_level_id', '=', 'c.student_level_id')
                        ->on('ad.admission_session', '=', 'c.academic_session');
                })
                ->whereIn('c.id', $coordinatorIds)
                ->whereNotNull('c.course_id')
                ->select('c.id', DB::raw('COUNT(ad.id) as student_count'))
                ->groupBy('c.id')
                ->pluck('student_count', 'id')
                ->mapWithKeys(fn ($count, $id) => [(int) $id => (int) $count])
                ->all();

            $departmentStudentCounts = DB::table('coordinators as c')
                ->join('academic_details as ad', function ($join) {
                    $join->on('ad.department_id', '=', 'c.department_id')
                        ->on('ad.student_level_id', '=', 'c.student_level_id')
                        ->on('ad.admission_session', '=', 'c.academic_session');
                })
                ->whereIn('c.id', $coordinatorIds)
                ->whereNotNull('c.department_id')
                ->whereNull('c.course_id')
                ->select('c.id', DB::raw('COUNT(ad.id) as student_count'))
                ->groupBy('c.id')
                ->pluck('student_count', 'id')
                ->mapWithKeys(fn ($count, $id) => [(int) $id => (int) $count])
                ->all();

            foreach ($coordinators as $coord) {
                if ($coord->course_id) {
                    $coordinatorStudentCounts[$coord->id] = (int) ($courseStudentCounts[$coord->id] ?? 0);
                } elseif ($coord->department_id) {
                    $coordinatorStudentCounts[$coord->id] = (int) ($departmentStudentCounts[$coord->id] ?? 0);
                } else {
                    $coordinatorStudentCounts[$coord->id] = 0;
                }
            }
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
            'departments' => $this->departments,
            'courses' => $this->courses,
            'studentLevels' => $this->studentLevels,
            'totalCoordinators' => $totalCoordinators,
            'courseBasedCoordinators' => $courseBasedCoordinators,
            'deptBasedCoordinators' => $deptBasedCoordinators,
            'unassignedSubmittedResultCount' => $unassignedSubmittedResultCount,
            'staffMembers' => $this->staffMembers,
        ])->layout('layouts.app');
    }
}
