<?php


namespace App\Http\Livewire\Staff;

use App\Enums\ProgrammesEnum;
use App\Enums\TransactionStatus;
use App\Models\IdCardProcessing as IdCardProcessingModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IdCardProcessing extends Component
{
    use WithPagination;

    public string $session;
    public string $search = '';
    public string $filter = 'all'; // all|processed|printed|not_printed
    public int $perPage = 12;
    public string $sortBy = 'surname'; // surname|level|department
    public string $sortDir = 'asc';    // asc|desc
    public bool $showStats = true;
    public bool $showDetail = false;
    public array $detail = [];


    protected $queryString = [
        'search'  => ['except' => ''],
        'filter'  => ['except' => 'all'],
        'sortBy'  => ['except' => 'surname'],
        'sortDir' => ['except' => 'asc'],
        'perPage' => ['except' => 12],
    ];

    public function mount(): void
    {
        $this->session = app(\App\Services\AcademicSessionService::class)
            ->getAcademicSession(auth()->user());
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }
    public function resetFilters(): void
    {
        $this->search   = '';
        $this->filter   = 'all';
        $this->sortBy   = 'surname';
        $this->sortDir  = 'asc';
        $this->perPage  = 12;
        $this->resetPage();
    }

    public function toggleSort(string $column): void
    {
        if (!in_array($column, ['surname', 'level', 'department'], true)) {
            return;
        }
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    public function markProcessed(string $userId): void
    {
        try {
            IdCardProcessingModel::updateOrCreate(
                ['user_id' => $userId, 'academic_session' => $this->session],
                ['status' => 'processed']
            );
            session()->flash('success', 'Student marked processed.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to mark processed: ' . $e->getMessage());
        }
    }
    public function markPrinted(string $userId, bool $printed = true): void
    {
        try {
            IdCardProcessingModel::updateOrCreate(
                ['user_id' => $userId, 'academic_session' => $this->session],
                ['print_status' => $printed ? 'printed' : 'not_printed']
            );
            session()->flash('success', $printed ? 'Marked printed.' : 'Marked not printed.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to update print status.');
        }
    }
    public function openDetail(string $userId): void
    {
        $record = User::query()
            ->select('id', 'surname', 'firstname', 'm_name')
            ->with(['academicDetail.department'])
            ->where('id', $userId)
            ->first();

        if (!$record) {
            session()->flash('error', 'Student not found.');
            return;
        }
        $this->detail = [
            'id'         => $record->id,
            'surname'    => $record->surname,
            'firstname'  => $record->firstname,
            'm_name'     => $record->m_name,
            'full_name'  => trim($record->surname . ', ' . $record->firstname . ' ' . ($record->m_name ?? '')),
            'matric_no'  => optional($record->academicDetail)->matric_no ?? '—',
            'department' => optional($record->academicDetail->department)->name ?? '—',
            'level'      => ($record->academicDetail->student_level_id ?? 0) * 100,
        ];
        $this->showDetail = true;
    }
    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->detail = [];
    }

    private function baseQuery()
    {
        $session  = $this->session;
        $approved = TransactionStatus::APPROVED->value;
        $programmeId = ProgrammesEnum::Undergraduate->value;
        $resource = config('remita.schoolfees.ug_schoolfees_description');

        $q = User::query()
            ->whereHas('academicDetail', function ($q) use ($programmeId) {
                $q->where('programme_id', $programmeId);
            })
            ->whereHas('studentTransactions', function ($q) use ($session, $resource, $approved) {
                $q->where('acad_session', $session)
                    ->where('resource', $resource)
                    ->where('status', $approved);
            })
            ->with(['academicDetail.department']);

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('surname', 'like', $term)
                    ->orWhere('firstname', 'like', $term)
                    ->orWhere('m_name', 'like', $term)
                    ->orWhereHas('academicDetail', fn($a) => $a->where('matric_no', 'like', $term))
                    ->orWhereHas('academicDetail.department', fn($d) => $d->where('name', 'like', $term))
                    ->orWhereHas('academicDetail', fn($a) => $a->where('student_level_id', 'like', $term));
            });
        }

        // Apply sorting
        if ($this->sortBy === 'surname') {
            $q->orderBy('surname', $this->sortDir);
        } elseif ($this->sortBy === 'level') {
            $levelSub = DB::table('academic_details')
                ->select('student_level_id')
                ->whereColumn('academic_details.user_id', 'users.id')
                ->limit(1);

            $q->addSelect(['lvl' => $levelSub])->orderBy('lvl', $this->sortDir)->orderBy('surname');
        } elseif ($this->sortBy === 'department') {
            $deptSub = DB::table('academic_details')
                ->join('departments', 'academic_details.department_id', '=', 'departments.id')
                ->select('departments.name')
                ->whereColumn('academic_details.user_id', 'users.id')
                ->limit(1);

            $q->addSelect(['dept_name' => $deptSub])->orderBy('dept_name', $this->sortDir)->orderBy('surname');
        }

        return $q;
    }

    private function applyFilter($collection)
    {
        return $collection->filter(function ($row) {
            return match ($this->filter) {
                'processed'   => $row['status'] === 'processed',
                'printed'     => $row['print'] === 'printed',
                'not_printed' => $row['print'] !== 'printed',
                default       => true,
            };
        })->values();
    }

    public function getStats(): array
    {
        $allIds = $this->baseQuery()->pluck('id');
        $records = IdCardProcessingModel::whereIn('user_id', $allIds)
            ->where('academic_session', $this->session)
            ->get();

        return [
            'total'     => $allIds->count(),
            'processed' => $records->where('status', 'processed')->count(),
            'printed'   => $records->where('print_status', 'printed')->count(),
            'remaining' => $allIds->count() - $records->where('print_status', 'printed')->count(),
        ];
    }

    public function render()
    {
        $paginator = $this->baseQuery()->paginate($this->perPage);
        $ids = collect($paginator->items())->pluck('id');

        $statusMap = IdCardProcessingModel::whereIn('user_id', $ids)
            ->where('academic_session', $this->session)
            ->get()
            ->keyBy('user_id');

        $rows = collect($paginator->items())->map(function ($u) use ($statusMap) {
            $proc = $statusMap->get($u->id);
            return [
                'id'         => $u->id,
                'surname'    => $u->surname,
                'firstname'  => $u->firstname,
                'm_name'     => $u->m_name,
                'matric_no'  => optional($u->academicDetail)->matric_no ?? '—',
                'department' => optional($u->academicDetail->department)->name ?? '—',
                'level'      => ($u->academicDetail->student_level_id ?? 0) * 100,
                'photo'      => $u->picture ? $u->profilePicture()  : asset('images/avatar.png'),
                'status'     => $proc->status ?? 'unprocessed',
                'print'      => $proc->print_status ?? 'not_printed',
            ];
        });

        $rows = $this->applyFilter($rows);
        $stats = $this->showStats ? $this->getStats() : null;

        return view('livewire.staff.id-card-processing', [
            'rows'    => $rows,
            'pageObj' => $paginator,
            'stats'   => $stats,
            'session' => $this->session,
            'sortBy'  => $this->sortBy,
            'sortDir' => $this->sortDir,
            'perPage' => $this->perPage,
            'filter'  => $this->filter,
            'search'  => $this->search,
        ]);
    }
}
