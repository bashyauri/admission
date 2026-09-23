<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Models\AcademicDetail;
use App\Models\Department;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Setting;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\AcademicSessionService;
use App\Services\GraduationService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class GraduationAudit extends Component
{
    use LivewireAlert;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Context Filters
    public string $selectedSession = '';
    public $selectedDepartmentId = 'all';
    public $selectedLevelId = 'all';
    public string $eligibilityFilter = 'all'; // all, eligible, deficient, cleared, uncleared, not_audited, staged
    public string $searchQuery = '';
    public int $perPage = 15;

    // Dropdown Choices
    public array $availableSessions = [];
    public array $availableDepartments = [];
    public array $availableLevels = [];

    // Batch Selection
    public array $selectedAcademicDetailIds = [];
    public bool $selectAll = false;

    // Deficiency Inspection Modal
    public bool $showDeficiencyModal = false;
    public ?array $inspectedAuditResult = null;
    public ?AcademicDetail $inspectedStudent = null;

    // Clearance Modal
    public bool $showClearModal = false;
    public ?int $clearingEligibilityId = null;
    public string $clearingStudentName = '';
    public string $clearingRemarks = '';

   public function mount(): void
{
    $user = Auth::user();
    if (!$user || !$user->canActAsExamOfficer()) {
        abort(403, 'Unauthorized access to Exam Officer Graduation Audit.');
    }

    $this->availableDepartments = Department::orderBy('name')->get()->toArray();

    // Load undergraduate graduating levels (typically 400, 500, 600)
    $levels = StudentLevel::whereIn('level', ['400', '500', '600'])->orderBy('level')->get()->toArray();
    if (empty($levels)) {
        $levels = StudentLevel::orderBy('level')->get()->toArray();
    }
    $this->availableLevels = $levels;

    // Resolve active session
    $service = new AcademicSessionService();
    $defaultSession = $service->getAcademicSession($user);

    // 1. Sessions stored in Settings
    $sessionKeys = ['ACADEMIC_SESSION', 'HOD_ACADEMIC_SESSION', 'PG_ACADEMIC_SESSION', 'ADMIN_ACADEMIC_SESSION'];
    $dbSessions = Setting::whereIn('key', $sessionKeys)
        ->pluck('value')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    // 2. Sessions that actually exist on student records
    $studentSessions = AcademicDetail::query()
        ->where(function ($q) {
            $q->where(function ($q2) {
                $q2->whereNotNull('admission_session')->where('admission_session', '!=', '');
            })->orWhere(function ($q2) {
                $q2->whereNotNull('acad_session')->where('acad_session', '!=', '');
            });
        })
        ->get(['admission_session', 'acad_session'])
        ->flatMap(fn ($row) => array_filter([
            $row->admission_session,
            $row->acad_session,
        ]))
        ->unique()
        ->values()
        ->toArray();

    // 3. Sessions that already have graduation eligibility records
    $eligibilitySessions = GraduationEligibility::query()
        ->whereNotNull('academic_session')
        ->where('academic_session', '!=', '')
        ->distinct()
        ->pluck('academic_session')
        ->toArray();

    $this->availableSessions = array_values(array_unique(array_merge(
        $dbSessions,
        $studentSessions,
        $eligibilitySessions,
        [$defaultSession]
    )));

    // Newest sessions first
    rsort($this->availableSessions);

    $this->selectedSession = $defaultSession;
}

    public function updatedSelectedSession(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedDepartmentId(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedLevelId(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedEligibilityFilter(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedAcademicDetailIds = $this->getCurrentPageAcademicDetailIds();
        } else {
            $this->selectedAcademicDetailIds = [];
        }
    }

    public function resetSelection(): void
    {
        $this->selectedAcademicDetailIds = [];
        $this->selectAll = false;
    }

    /**
     * Run batch graduation audit for the currently filtered cohort.
     */
    public function runCohortAudit(GraduationService $graduationService): void
    {
        try {
            $query = AcademicDetail::with(['user.academicDetail.programme', 'user.academicDetail.department', 'programme', 'department'])
                ->whereHas('user');

            if ($this->selectedDepartmentId !== 'all') {
                $query->where('department_id', (int) $this->selectedDepartmentId);
            }

            if ($this->selectedLevelId !== 'all') {
                $query->where('student_level_id', (int) $this->selectedLevelId);
            }

            $students = $query->get();

            if ($students->isEmpty()) {
                $this->alert('info', 'No student candidates found matching the selected Department and Level filters.');
                return;
            }

            $auditedCount = 0;
            $eligibleCount = 0;
            $deficientCount = 0;

            foreach ($students as $studentDetail) {
                if ($studentDetail->user) {
                    $result = $graduationService->checkEligibility($studentDetail->user, $this->selectedSession);
                    $auditedCount++;
                    if ($result['eligible']) {
                        $eligibleCount++;
                    } else {
                        $deficientCount++;
                    }
                }
            }

            $this->resetSelection();

            $this->alert('success', "Cohort audit completed for {$this->selectedSession}!", [
                'position' => 'center',
                'timer' => 5000,
                'toast' => false,
                'text' => "Evaluated {$auditedCount} candidates: {$eligibleCount} qualified, {$deficientCount} with deficiencies.",
            ]);
        } catch (Exception $e) {
            report($e);
            $this->alert('error', 'Cohort audit failed: ' . $e->getMessage());
        }
    }

    /**
     * Audit an individual candidate on-demand.
     */
    public function auditCandidate(int $academicDetailId, GraduationService $graduationService): void
    {
        try {
            $detail = AcademicDetail::with('user')->findOrFail($academicDetailId);
            if (!$detail->user) {
                $this->alert('error', 'Student user record could not be found.');
                return;
            }

            $result = $graduationService->checkEligibility($detail->user, $this->selectedSession);

            $statusText = $result['eligible'] ? 'Qualified for graduation.' : 'Deficiencies detected.';
            $this->alert($result['eligible'] ? 'success' : 'warning', "Audit completed: {$statusText}", [
                'position' => 'top-end',
                'timer' => 3500,
                'toast' => true,
            ]);
        } catch (Exception $e) {
            report($e);
            $this->alert('error', 'Audit error: ' . $e->getMessage());
        }
    }

    /**
     * Open inspection modal for deficiencies and requirements breakdown.
     */
    public function viewDeficiencies(int $academicDetailId, GraduationService $graduationService): void
    {
        $this->inspectedStudent = AcademicDetail::with(['user', 'department', 'programme', 'studentLevel'])
            ->findOrFail($academicDetailId);

        if (!$this->inspectedStudent->user) {
            $this->alert('error', 'Student user record not found.');
            return;
        }

        $this->inspectedAuditResult = $graduationService->checkEligibility(
            $this->inspectedStudent->user,
            $this->selectedSession
        );

        $this->showDeficiencyModal = true;
    }

    public function closeDeficiencyModal(): void
    {
        $this->showDeficiencyModal = false;
        $this->inspectedStudent = null;
        $this->inspectedAuditResult = null;
    }

    /**
     * Open clearance approval modal for an eligible candidate.
     */
    public function openClearModal(int $eligibilityId): void
    {
        $eligibility = GraduationEligibility::with(['user', 'academicDetail'])->findOrFail($eligibilityId);

        if (!$eligibility->meets_requirements) {
            $this->alert('error', 'Cannot clear candidate: Student does not meet all academic requirements.');
            return;
        }

        $this->clearingEligibilityId = $eligibility->id;
        $fullName = trim(($eligibility->user->surname ?? '') . ' ' . ($eligibility->user->firstname ?? ''));
        $matricNo = $eligibility->academicDetail->matric_no ?? '';
        $this->clearingStudentName = "{$fullName} ({$matricNo})";
        $this->clearingRemarks = 'Qualified for degree conferment and cleared by Exam Officer.';
        $this->showClearModal = true;
    }

    public function closeClearModal(): void
    {
        $this->showClearModal = false;
        $this->clearingEligibilityId = null;
        $this->clearingStudentName = '';
        $this->clearingRemarks = '';
    }

    /**
     * Confirm graduation clearance by Exam Officer.
     */
    public function confirmClearStudent(GraduationService $graduationService): void
    {
        if (!$this->clearingEligibilityId) {
            return;
        }

        try {
            $graduationService->clearStudent(
                $this->clearingEligibilityId,
                Auth::user(),
                $this->clearingRemarks
            );

            $this->closeClearModal();
            $this->alert('success', 'Candidate cleared for graduation successfully.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (Exception $e) {
            report($e);
            $this->alert('error', 'Clearance failed: ' . $e->getMessage());
        }
    }

    /**
     * Batch clear all eligible candidates currently selected or in filter.
     */
    public function batchClearEligible(GraduationService $graduationService): void
    {
        try {
            $officer = Auth::user();

            $eligibilitiesQuery = GraduationEligibility::where('academic_session', $this->selectedSession)
                ->where('meets_requirements', true)
                ->where('is_cleared', false);

            if (!empty($this->selectedAcademicDetailIds)) {
                $eligibilitiesQuery->whereIn('academic_detail_id', $this->selectedAcademicDetailIds);
            } elseif ($this->selectedDepartmentId !== 'all') {
                $eligibilitiesQuery->whereHas('academicDetail', fn($q) => $q->where('department_id', (int) $this->selectedDepartmentId));
            }

            $eligibilities = $eligibilitiesQuery->get();

            if ($eligibilities->isEmpty()) {
                $this->alert('info', 'No eligible uncleared candidates found to clear.');
                return;
            }

            $count = 0;
            foreach ($eligibilities as $eligibility) {
                $graduationService->clearStudent($eligibility, $officer, 'Approved via batch clearance.');
                $count++;
            }

            $this->resetSelection();
            $this->alert('success', "Successfully cleared {$count} eligible graduand(s).");
        } catch (Exception $e) {
            report($e);
            $this->alert('error', 'Batch clearance error: ' . $e->getMessage());
        }
    }

    /**
     * Stage an officially cleared graduand into the session GraduationList.
     */
    public function stageToGraduationList(int $eligibilityId, GraduationService $graduationService): void
    {
        try {
            $graduationService->addToGraduationList($eligibilityId, $this->selectedSession);

            $this->alert('success', 'Graduand successfully staged into the official Graduation List.', [
                'position' => 'top-end',
                'timer' => 3500,
                'toast' => true,
            ]);
        } catch (Exception $e) {
            report($e);
            $this->alert('error', 'Staging failed: ' . $e->getMessage());
        }
    }

    /**
     * Batch stage all cleared graduands into the session GraduationList.
     */
    public function batchStageToGraduationList(GraduationService $graduationService): void
    {
        try {
            $clearedQuery = GraduationEligibility::where('academic_session', $this->selectedSession)
                ->where('is_cleared', true);

            if (!empty($this->selectedAcademicDetailIds)) {
                $clearedQuery->whereIn('academic_detail_id', $this->selectedAcademicDetailIds);
            } elseif ($this->selectedDepartmentId !== 'all') {
                $clearedQuery->whereHas('academicDetail', fn($q) => $q->where('department_id', (int) $this->selectedDepartmentId));
            }

            $clearedRecords = $clearedQuery->get();

            if ($clearedRecords->isEmpty()) {
                $this->alert('info', 'No cleared candidates found ready for staging.');
                return;
            }

            $stagedCount = 0;
            foreach ($clearedRecords as $record) {
                $graduationService->addToGraduationList($record, $this->selectedSession);
                $stagedCount++;
            }

            $this->resetSelection();
            $this->alert('success', "Successfully staged {$stagedCount} cleared graduand(s) into the Graduation List.");
        } catch (Exception $e) {
            report($e);
            $this->alert('error', 'Batch staging failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper to get IDs on current page for select all.
     *
     * @return array<int>
     */
    protected function getCurrentPageAcademicDetailIds(): array
    {
        return $this->buildBaseQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Base query for academic details matching context filters.
     */
    protected function buildBaseQuery()
    {
        $query = AcademicDetail::query()
            ->with([
                'user',
                'department',
                'programme',
                'studentLevel',
                'graduationEligibilities' => function ($q) {
                    $q->where('academic_session', $this->selectedSession);
                },
                'graduationListItems' => function ($q) {
                    $q->whereHas('graduationList', function ($gl) {
                        $gl->where('academic_session', $this->selectedSession);
                    });
                },
            ])
            ->whereHas('user');

        if ($this->selectedDepartmentId !== 'all') {
            $query->where('department_id', (int) $this->selectedDepartmentId);
        }

        if ($this->selectedLevelId !== 'all') {
            $query->where('student_level_id', (int) $this->selectedLevelId);
        }

        if (!empty($this->searchQuery)) {
            $term = '%' . trim($this->searchQuery) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('matric_no', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('surname', 'like', $term)
                            ->orWhere('m_name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        // Apply Eligibility status filter
        switch ($this->eligibilityFilter) {
            case 'eligible':
                $query->whereHas('graduationEligibilities', function ($q) {
                    $q->where('academic_session', $this->selectedSession)
                        ->where('meets_requirements', true);
                });
                break;

            case 'deficient':
                $query->whereHas('graduationEligibilities', function ($q) {
                    $q->where('academic_session', $this->selectedSession)
                        ->where('meets_requirements', false);
                });
                break;

            case 'cleared':
                $query->whereHas('graduationEligibilities', function ($q) {
                    $q->where('academic_session', $this->selectedSession)
                        ->where('is_cleared', true);
                });
                break;

            case 'uncleared':
                $query->whereHas('graduationEligibilities', function ($q) {
                    $q->where('academic_session', $this->selectedSession)
                        ->where('meets_requirements', true)
                        ->where('is_cleared', false);
                });
                break;

            case 'not_audited':
                $query->whereDoesntHave('graduationEligibilities', function ($q) {
                    $q->where('academic_session', $this->selectedSession);
                });
                break;

            case 'staged':
                $query->whereHas('graduationListItems.graduationList', function ($q) {
                    $q->where('academic_session', $this->selectedSession);
                });
                break;
        }

        return $query;
    }

    public function render(): View
    {
        // 1. Calculate Metrics on the current cohort scope (filtered by dept & level)
        $metricsQuery = AcademicDetail::query()->whereHas('user');

        if ($this->selectedDepartmentId !== 'all') {
            $metricsQuery->where('department_id', (int) $this->selectedDepartmentId);
        }

        if ($this->selectedLevelId !== 'all') {
            $metricsQuery->where('student_level_id', (int) $this->selectedLevelId);
        }

        $totalCandidates = (clone $metricsQuery)->count();

        $auditedQuery = (clone $metricsQuery)->whereHas('graduationEligibilities', function ($q) {
            $q->where('academic_session', $this->selectedSession);
        });
        $auditedCount = (clone $auditedQuery)->count();

        $eligibleCount = (clone $metricsQuery)->whereHas('graduationEligibilities', function ($q) {
            $q->where('academic_session', $this->selectedSession)
                ->where('meets_requirements', true);
        })->count();

        $clearedCount = (clone $metricsQuery)->whereHas('graduationEligibilities', function ($q) {
            $q->where('academic_session', $this->selectedSession)
                ->where('is_cleared', true);
        })->count();

        $stagedCount = (clone $metricsQuery)->whereHas('graduationListItems.graduationList', function ($q) {
            $q->where('academic_session', $this->selectedSession);
        })->count();

        $deficientCount = (clone $metricsQuery)->whereHas('graduationEligibilities', function ($q) {
            $q->where('academic_session', $this->selectedSession)
                ->where('meets_requirements', false);
        })->count();

        $candidates = $this->buildBaseQuery()
            ->orderBy('matric_no')
            ->paginate($this->perPage);

        return view('livewire.exam-officer.graduation-audit', [
            'candidates' => $candidates,
            'totalCandidates' => $totalCandidates,
            'auditedCount' => $auditedCount,
            'eligibleCount' => $eligibleCount,
            'clearedCount' => $clearedCount,
            'stagedCount' => $stagedCount,
            'deficientCount' => $deficientCount,
        ])->layout('layouts.app');
    }
}
