<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\DisciplinaryAction;
use App\Models\Result;
use App\Models\User;
use App\Services\AcademicSessionService;
use App\Services\DisciplinaryActionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class ManageDisciplinaryActions extends Component
{
    use LivewireAlert;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $searchQuery = '';
    public string $statusFilter = 'all';
    public string $sanctionFilter = 'all';
    public string $selectedSession = 'all';
    public array $availableSessions = [];
    public array $studentSearchResults = [];
    public array $studentResults = [];

    public bool $showApplyModal = false;
    public bool $showDetailModal = false;
    public string|int|null $selectedStudentId = null;
    public string|int|null $selectedAcademicDetailId = null;
    public ?int $selectedActionId = null;
    public ?User $selectedStudent = null;
    public ?AcademicDetail $selectedAcademicDetail = null;
    public ?DisciplinaryAction $selectedAction = null;

    public string $sanctionType = 'repeat_session';
    public ?int $resultId = null;
    public string $academicSession = '';
    public string $effectiveSession = '';
    public string $semester = 'first';
    public string $senateRefNo = '';
    public string $verdictDate = '';
    public string $effectiveDate = '';
    public string $resumptionSession = '';
    public string $endDate = '';
    public string $remarks = '';

    public int $perPage = 15;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Disciplinary Actions Management.');
        }

        $defaultSession = (new AcademicSessionService())->getAcademicSession($user);
        $this->availableSessions = DB::table('registered_courses')
            ->whereNotNull('academic_session')
            ->where('academic_session', '!=', '')
            ->distinct()
            ->orderByDesc('academic_session')
            ->pluck('academic_session')
            ->map(fn ($session) => (string) $session)
            ->values()
            ->toArray();

        if (empty($this->availableSessions)) {
            $this->availableSessions = [$defaultSession];
        }

        $this->selectedSession = $this->availableSessions[0] ?? 'all';
        $this->academicSession = $this->selectedSession === 'all' ? $defaultSession : $this->selectedSession;
        $this->effectiveSession = $this->academicSession;
        $this->verdictDate = now()->toDateString();
        $this->effectiveDate = $this->verdictDate;
    }

    public function updatedSearchQuery(): void
    {
        $this->searchStudents();
    }

    public function searchStudents(): void
    {
        $term = trim($this->searchQuery);

        if ($term === '') {
            $this->studentSearchResults = [];
            return;
        }

        $normalizedTerm = preg_replace('/[^A-Za-z0-9]/u', '', $term) ?: $term;
        $normalizedLike = '%' . strtolower($normalizedTerm) . '%';

        $this->studentSearchResults = User::query()
            ->where('role', 'student')
            ->where('programme_id', ProgrammesEnum::Undergraduate->value)
            ->where(function ($query) use ($term, $normalizedLike) {
                $query->where('firstname', 'like', "%{$term}%")
                    ->orWhere('surname', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('academicDetail', function ($academic) use ($term, $normalizedLike) {
                        $academic->where('matric_no', 'like', "%{$term}%")
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(LOWER(matric_no), '/', ''), '-', ''), ' ', ''), '.', '') LIKE ?", [strtolower($normalizedLike)]);
                    });
            })
            ->with(['academicDetail' => fn ($query) => $query->with('department')])
            ->limit(8)
            ->get()
            ->map(function (User $student) {
                $academicDetail = $student->academicDetail;

                return [
                    'id' => $student->id,
                    'name' => trim(($student->firstname ?? '') . ' ' . ($student->surname ?? '')),
                    'matric_no' => $academicDetail?->matric_no ?? 'No matric number',
                    'department' => $academicDetail?->department?->name ?? 'Unassigned',
                    'session' => $academicDetail?->acad_session ?? 'N/A',
                ];
            })
            ->toArray();
    }

    public function openApplyModal(string $studentId): void
    {
        $this->selectedStudent = User::query()
            ->with(['academicDetail.department'])
            ->find($studentId);

        if (! $this->selectedStudent) {
            $this->alert('error', 'The selected student could not be found.');
            return;
        }

        $this->selectedStudentId = $this->selectedStudent->id;
        $this->selectedAcademicDetailId = $this->selectedStudent->academicDetail?->id;
        $this->selectedAcademicDetail = $this->selectedStudent->academicDetail;
        $this->showApplyModal = true;
        $this->sanctionType = 'repeat_session';
        $this->resultId = null;
        $this->senateRefNo = '';
        $this->remarks = '';
        $this->academicSession = $this->selectedSession === 'all' ? ($this->selectedAcademicDetail?->acad_session ?? '') : $this->selectedSession;
        $this->effectiveSession = $this->academicSession;
        $this->semester = 'first';
        $this->verdictDate = now()->toDateString();
        $this->effectiveDate = $this->verdictDate;
        $this->resumptionSession = '';
        $this->endDate = '';

        $this->loadStudentResults();
    }

    public function closeApplyModal(): void
    {
        $this->showApplyModal = false;
        $this->selectedStudent = null;
        $this->selectedAcademicDetail = null;
        $this->selectedStudentId = null;
        $this->selectedAcademicDetailId = null;
        $this->resultId = null;
        $this->senateRefNo = '';
        $this->remarks = '';
        $this->academicSession = $this->selectedSession === 'all' ? '' : $this->selectedSession;
        $this->effectiveSession = $this->academicSession;
        $this->semester = 'first';
        $this->resumptionSession = '';
        $this->endDate = '';
    }

    public function viewDetail(int $actionId): void
    {
        $action = DisciplinaryAction::query()
            ->with(['user.academicDetail.department', 'academicDetail.department', 'sanctionedBy', 'result'])
            ->find($actionId);

        if (! $action) {
            $this->alert('error', 'The selected disciplinary record could not be found.');
            return;
        }

        $this->selectedActionId = $actionId;
        $this->selectedAction = $action;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedActionId = null;
        $this->selectedAction = null;
    }

    public function updatedSanctionType(): void
    {
        if ($this->sanctionType !== 'course_cancellation') {
            $this->resultId = null;
        }
    }

    public function loadStudentResults(): void
    {
        if ($this->selectedStudentId === null) {
            $this->studentResults = [];
            return;
        }

        $this->studentResults = Result::query()
            ->with(['departmentCourse.studentCourse'])
            ->where('user_id', $this->selectedStudentId)
            ->whereIn('status', ['released', 'exam_officer_approved'])
            ->orderByDesc('academic_session')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function (Result $result) {
                return [
                    'id' => $result->id,
                    'course' => $result->course_title_snapshot ?? $result->departmentCourse?->studentCourse?->title ?? $result->course_code_snapshot ?? 'Course',
                    'session' => $result->academic_session,
                    'semester' => $result->semester,
                    'grade' => $result->grade,
                ];
            })
            ->toArray();
    }

    public function applySanction(DisciplinaryActionService $service): void
    {
        $student = User::query()->find($this->selectedStudentId);

        if (! $student || ! $student->isUndergraduate()) {
            $this->alert('error', 'Only undergraduate students can receive the current disciplinary sanctions.');
            return;
        }

        $payload = [
            'user_id' => $student->id,
            'academic_detail_id' => $this->selectedAcademicDetailId ?: $student->academicDetail?->id,
            'sanction_type' => $this->sanctionType,
            'academic_session' => $this->academicSession ?: ($this->selectedAcademicDetail?->acad_session ?? ''),
            'effective_session' => $this->effectiveSession ?: $this->academicSession,
            'semester' => $this->semester !== '' ? $this->semester : null,
            'senate_ref_no' => trim($this->senateRefNo),
            'verdict_date' => $this->verdictDate ?: now()->toDateString(),
            'effective_date' => $this->effectiveDate ?: $this->verdictDate ?: now()->toDateString(),
            'end_date' => $this->endDate !== '' ? $this->endDate : null,
            'resumption_session' => $this->resumptionSession !== '' ? $this->resumptionSession : null,
            'remarks' => $this->remarks,
            'result_id' => $this->sanctionType === 'course_cancellation' ? $this->resultId : null,
        ];

        try {
            $service->applySanction($payload);
            $this->closeApplyModal();
            $this->searchQuery = '';
            $this->searchStudents();
            $this->alert('success', 'Disciplinary sanction applied successfully.');
        } catch (\Throwable $exception) {
            $this->alert('error', $exception->getMessage());
        }
    }

    public function liftSanction(int $actionId, DisciplinaryActionService $service): void
    {
        try {
            $resolutionRef = $this->buildResolutionReference($actionId);
            $service->liftSanction($actionId, $resolutionRef);
            $this->alert('success', 'The disciplinary sanction was lifted and the audit trail was recorded.');
        } catch (\Throwable $exception) {
            $this->alert('error', $exception->getMessage());
        }
    }

    public function resolveAppeal(int $actionId, string $outcome, DisciplinaryActionService $service): void
    {
        try {
            $resolutionRef = $this->buildResolutionReference($actionId);
            $service->recordAppealOutcome($actionId, $outcome, $resolutionRef, 'Appeal reviewed via exam officer management panel.');
            $this->alert('success', 'The appeal decision has been recorded.');
        } catch (\Throwable $exception) {
            $this->alert('error', $exception->getMessage());
        }
    }

    private function buildResolutionReference(int $actionId): string
    {
        $action = DisciplinaryAction::query()->find($actionId);
        $reference = trim((string) ($action?->senate_ref_no ?? ''));

        if ($reference !== '' && app(\App\Services\StudentStatusService::class)->isValidSenateReference($reference)) {
            return $reference;
        }

        $year = now()->format('Y');
        $sequence = str_pad((string) $actionId, 3, '0', STR_PAD_LEFT);

        return "SEN-{$year}-{$sequence}";
    }

    public function render(): View
    {
        $query = DisciplinaryAction::query()
            ->with([
                'user.academicDetail.department',
                'academicDetail.department',
                'result.departmentCourse.studentCourse',
                'sanctionedBy',
            ])
            ->when($this->selectedSession !== 'all', fn ($q) => $q->where('academic_session', $this->selectedSession))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->when($this->sanctionFilter !== 'all', fn ($q) => $q->where('sanction_type', $this->sanctionFilter))
            ->when(trim($this->searchQuery) !== '', fn ($q) => $q->whereHas('user', function ($userQuery) {
                $term = trim($this->searchQuery);
                $userQuery->where('firstname', 'like', "%{$term}%")
                    ->orWhere('surname', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            }))
            ->orderByDesc('verdict_date');

        $actions = $query->paginate($this->perPage);

        return view('livewire.exam-officer.manage-disciplinary-actions', [
            'actions' => $actions,
            'searchResults' => $this->studentSearchResults,
        ]);
    }
}
