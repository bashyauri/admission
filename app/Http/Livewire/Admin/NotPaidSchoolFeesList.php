<?php

namespace App\Http\Livewire\Admin;

use App\Services\Report\UtmeService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class NotPaidSchoolFeesList extends Component
{
    #[Locked]
    public $students;

    public $search = '';
    public $sortBy = 'surname';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $departmentFilter = '';
    public $levelFilter = '';
    public $statusFilter = '';

    public function mount(UtmeService $utmeService): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'You must be logged in as an Admin to view this page');
        $this->students = $utmeService->getUndergraduateStudentsNotPaidSchoolFees([
            'department' => $this->departmentFilter,
            'level' => $this->levelFilter,
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getFilteredStudentsProperty()
    {
        $query = collect($this->students);
        // Search filter (still in PHP)
        if ($this->search) {
            $query = $query->filter(function ($item) {
                $searchTerm = strtolower($this->search);
                $fullName = strtolower($item->surname . ' ' . $item->firstname . ' ' . $item->m_name);
                $rrr = strtolower($item->RRR ?? '');
                $departmentName = strtolower($item->department_name ?? '');
                $matricNo = strtolower($item->matric_no ?? '');
                $levelName = strtolower($item->level_name ?? '');
                return (
                    str_contains($fullName, $searchTerm) ||
                    str_contains($rrr, $searchTerm) ||
                    str_contains($departmentName, $searchTerm) ||
                    str_contains($matricNo, $searchTerm) ||
                    str_contains($levelName, $searchTerm)
                );
            });
        }
        // Status filter (still in PHP)
        if ($this->statusFilter) {
            $query = $query->filter(function ($item) {
                return strtolower($item->status) === strtolower($this->statusFilter);
            });
        }
        // Sorting (still in PHP)
        $query = $query->sortBy([
            [$this->sortBy, $this->sortDirection]
        ]);
        return $query;
    }

    public function getDepartmentsProperty()
    {
        return collect($this->students)
            ->pluck('department_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function getLevelsProperty()
    {
        return collect($this->students)
            ->pluck('level_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function getTotalCountProperty()
    {
        return collect($this->students)->count();
    }

    public function render()
    {
        return view('livewire.admin.not-paid-school-fees-list', [
            'students' => $this->students,
            'departments' => $this->departments,
            'levels' => $this->levels,
            'totalCount' => $this->totalCount,
        ]);
    }
}
