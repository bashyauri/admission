<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dashboards;

use App\Models\Department;
use App\Models\GraduationEligibility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ExamOfficerIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ── Academic Session & Semester Filters ──────────────────────────────────
    public string $selectedSession = '';
    public string $selectedSemester = 'first';
    public array $availableSessions = [];

    // ── Quick Report Generator ───────────────────────────────────────────────
    public array $allDepartments = [];
    public string $quickReportDeptId = '';

    // ── Department Summary Table State ───────────────────────────────────────
    public string $deptSearch = '';
    public int $perPage = 10;

    // ── Institutional Pipeline Metrics ───────────────────────────────────────
    public int $totalResultsInPipeline = 0;
    public int $coordinatorApproved = 0;     // Ready for Exam Officer release
    public int $releasedThisSemester = 0;    // Released to students
    public int $withCoordinator = 0;        // Lecturer submitted, waiting coordinator
    public int $withLecturer = 0;           // Draft / Pending
    public int $graduationEligibleCount = 0; // Final-year students pending clearance

    // ── Recent Activity ──────────────────────────────────────────────────────
    public array $recentlyReleased = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Exam Officer Command Center.');
        }

        /*
         * Academic sessions resolved from actual student registrations in registered_courses.
         */
        $this->availableSessions = DB::table('registered_courses')
            ->whereNotNull('academic_session')
            ->where('academic_session', '!=', '')
            ->distinct()
            ->orderByDesc('academic_session')
            ->pluck('academic_session')
            ->map(fn ($session) => (string) $session)
            ->values()
            ->toArray();

        $this->selectedSession = $this->availableSessions[0] ?? '';

        // Preload department list for quick Senate Broadsheet dropdown
        $this->allDepartments = Department::orderBy('name')->get(['id', 'name'])->toArray();
        if (! empty($this->allDepartments)) {
            $this->quickReportDeptId = (string) $this->allDepartments[0]['id'];
        }

        $this->loadMetrics();
    }

    public function updatedSelectedSession(): void
    {
        $this->loadMetrics();
        $this->resetPage();
    }

    public function updatedSelectedSemester(): void
    {
        $this->loadMetrics();
        $this->resetPage();
    }

    public function updatingDeptSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function loadMetrics(): void
    {
        $session  = $this->selectedSession;
        $semester = $this->selectedSemester;

        if ($session === '') {
            $this->totalResultsInPipeline  = 0;
            $this->coordinatorApproved     = 0;
            $this->releasedThisSemester    = 0;
            $this->withCoordinator         = 0;
            $this->withLecturer            = 0;
            $this->graduationEligibleCount = 0;
            $this->recentlyReleased        = [];
            return;
        }

        // ── 1. Pipeline Metrics (registered students in session) ──────────────
        $counts = DB::table('results as r')
            ->join('registered_courses as rc', 'rc.id', '=', 'r.registered_course_id')
            ->whereNull('r.deleted_at')
            ->where('r.academic_session', $session)
            ->where('rc.academic_session', $session)
            ->where('r.semester', $semester)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN r.status = 'exam_officer_approved' THEN 1 ELSE 0 END) AS coordinator_approved,
                SUM(CASE WHEN r.status = 'released'              THEN 1 ELSE 0 END) AS released,
                SUM(CASE WHEN r.status = 'submitted'             THEN 1 ELSE 0 END) AS with_coordinator,
                SUM(CASE WHEN r.status = 'pending'               THEN 1 ELSE 0 END) AS with_lecturer
            ")
            ->first();

        $this->coordinatorApproved    = (int) ($counts->coordinator_approved ?? 0);
        $this->releasedThisSemester   = (int) ($counts->released             ?? 0);
        $this->withCoordinator        = (int) ($counts->with_coordinator     ?? 0);
        $this->withLecturer           = (int) ($counts->with_lecturer        ?? 0);
        $this->totalResultsInPipeline = $this->coordinatorApproved + $this->releasedThisSemester
            + $this->withCoordinator + $this->withLecturer;

        // ── 2. Graduation Eligible Count (Phase 6) ───────────────────────────
        try {
            $this->graduationEligibleCount = GraduationEligibility::query()
                ->where('academic_session', $session)
                ->where('meets_requirements', true)
                ->where('is_cleared', false)
                ->count();
        } catch (\Throwable) {
            $this->graduationEligibleCount = 0;
        }

        // ── 3. Recent Releases by Exam Officer ────────────────────────────────
        $this->recentlyReleased = DB::table('result_approvals as ra')
            ->join('departments as d', 'd.id', '=', 'ra.department_id')
            ->join('users as u', 'u.id', '=', 'ra.approved_by')
            ->where('ra.academic_session', $session)
            ->where('ra.semester', $semester)
            ->where('ra.approval_level', 'exam_officer')
            ->where('ra.status', 'released')
            ->selectRaw("
                ra.id,
                ra.approved_at,
                ra.comments,
                d.name AS department_name,
                CONCAT(u.surname, ' ', u.firstname) AS released_by
            ")
            ->orderByDesc('ra.approved_at')
            ->limit(5)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toArray();
    }

    public function render(): View
    {
        $session  = $this->selectedSession;
        $semester = $this->selectedSemester;

        if ($session === '') {
            $departments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        } else {
            $query = DB::table('departments as d')
                ->join('department_courses as dc', 'dc.department_id', '=', 'd.id')
                ->join('registered_courses as rc', function ($join) use ($session) {
                    $join->on('rc.department_course_id', '=', 'dc.id')
                        ->where('rc.academic_session', '=', $session);
                })
                ->leftJoin('results as r', function ($join) use ($session, $semester) {
                    $join->on('r.registered_course_id', '=', 'rc.id')
                        ->whereNull('r.deleted_at')
                        ->where('r.academic_session', '=', $session)
                        ->where('r.semester', '=', $semester);
                });

            if (trim($this->deptSearch) !== '') {
                $query->where('d.name', 'like', '%' . trim($this->deptSearch) . '%');
            }

            $departments = $query
                ->selectRaw("
                    d.id AS department_id,
                    d.name AS department_name,
                    COUNT(DISTINCT dc.id) AS course_count,
                    SUM(CASE WHEN r.status = 'exam_officer_approved' THEN 1 ELSE 0 END) AS ready_count,
                    SUM(CASE WHEN r.status = 'released'              THEN 1 ELSE 0 END) AS released_count,
                    SUM(CASE WHEN r.status = 'submitted'             THEN 1 ELSE 0 END) AS with_coord_count,
                    SUM(CASE WHEN r.status = 'pending'               THEN 1 ELSE 0 END) AS with_lecturer_count,
                    COUNT(r.id) AS total_results
                ")
                ->groupBy('d.id', 'd.name')
                ->orderByDesc('ready_count')
                ->orderBy('d.name')
                ->paginate($this->perPage);
        }

        return view('livewire.dashboards.exam-officer-index', [
            'departments' => $departments,
        ]);
    }
}