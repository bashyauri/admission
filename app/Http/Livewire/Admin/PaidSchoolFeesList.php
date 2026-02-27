<?php

namespace App\Http\Livewire\Admin;

use App\Enums\TransactionStatus;
use App\Services\Report\UtmeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PaidSchoolFeesList extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'dept')]
    public string $department = '';

    #[Url(as: 'level')]
    public string $level = '';

    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'surname';

    #[Url(as: 'dir')]
    public string $sortDirection = 'asc';

    #[Url(as: 'per')]
    public int $perPage = 15;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
    }

    public function updatingLevel()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $academicSession = app(\App\Services\AcademicSessionService::class)
            ->getAcademicSession(Auth::user());

        $query = \App\Models\StudentTransaction::query()
            ->where('resource', config('remita.schoolfees.ug_schoolfees_description'))
            ->where('student_transactions.acad_session', $academicSession)
            ->where('status', TransactionStatus::APPROVED)
            ->join('users', 'student_transactions.user_id', '=', 'users.id')
            ->leftJoin('academic_details', 'academic_details.user_id', '=', 'users.id')
            ->leftJoin('departments', 'departments.id', '=', 'academic_details.department_id')
            ->leftJoin('student_levels', 'student_levels.id', '=', 'academic_details.student_level_id')
            ->select([
                'student_transactions.*',
                'users.surname',
                'users.firstname',
                'users.m_name',
                'users.phone',
                'academic_details.matric_no',
                'departments.name as department_name',
                'student_levels.level as level_name',
                'student_transactions.RRR',
            ])
            ->distinct('student_transactions.user_id');

        // Search
       if ($this->search !== '') {
    $searchTerm = '%' . trim($this->search) . '%';

    $query->where(function ($q) use ($searchTerm) {
        // Full name search (case-insensitive)
        $q->whereRaw(
            'LOWER(CONCAT_WS(" ", users.surname, users.firstname, users.m_name)) LIKE ?',
            [strtolower($searchTerm)]
        )

        // RRR (Remita Retrieval Reference)
        ->orWhere('student_transactions.RRR', 'LIKE', $searchTerm)

        // Matric number
        ->orWhere('academic_details.matric_no', 'LIKE', $searchTerm)

        // Department name
        ->orWhereRaw('LOWER(departments.name) LIKE ?', [strtolower($searchTerm)])

        // Level
        ->orWhereRaw('LOWER(student_levels.level) LIKE ?', [strtolower($searchTerm)]);
    });
}

        // Department filter
        if ($this->department !== '') {
            $query->whereRaw('LOWER(departments.name) = ?', [strtolower(trim($this->department))]);
        }

        // Level filter
        if ($this->level !== '') {
            $query->whereRaw('LOWER(student_levels.level) = ?', [strtolower(trim($this->level))]);
        }

        // Status filter (optional – currently all are APPROVED, so mostly placeholder)
        if ($this->status !== '') {
            $query->where('student_transactions.status', $this->status);
        }

        // Sorting
        $sortable = [
            'surname'         => 'users.surname',
            'matric_no'       => 'academic_details.matric_no',
            'department_name' => 'departments.name',
            'level_name'      => 'student_levels.level',
            'RRR'             => 'student_transactions.RRR',
        ];

        $sortField = $sortable[$this->sortBy] ?? 'users.surname';
        $query->orderBy($sortField, $this->sortDirection);

        $payments = $query->paginate($this->perPage);

        // For dropdowns (unique values)
        $departments = \App\Models\StudentTransaction::query()
            ->where('resource', config('remita.schoolfees.ug_schoolfees_description'))
            ->where('status', TransactionStatus::APPROVED)
          ->where('student_transactions.acad_session', $academicSession)
            ->join('users', 'student_transactions.user_id', 'users.id')
            ->join('academic_details', 'academic_details.user_id', 'users.id')
            ->join('departments', 'departments.id', 'academic_details.department_id')
            ->distinct()
            ->orderBy('departments.name')
            ->pluck('departments.name')
            ->filter()
            ->values();

        $levels = \App\Models\StudentTransaction::query()
            ->where('resource', config('remita.schoolfees.ug_schoolfees_description'))
            ->where('status', TransactionStatus::APPROVED)
           ->where('student_transactions.acad_session', $academicSession)
            ->join('users', 'student_transactions.user_id', 'users.id')
            ->join('academic_details', 'academic_details.user_id', 'users.id')
            ->join('student_levels', 'student_levels.id', 'academic_details.student_level_id')
            ->distinct()
            ->orderBy('student_levels.level')
            ->pluck('student_levels.level')
            ->filter()
            ->values();

        return view('livewire.admin.paid-school-fees-list', [
            'payments'      => $payments,
            'departments'   => $departments,
            'levels'        => $levels,
            'approvedStatus' => TransactionStatus::APPROVED,
        ]);
    }
}
