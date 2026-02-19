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

    public function mount(UtmeService $utmeService): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'You must be logged in as an Admin to view this page');
        $this->students = $utmeService->getUndergraduateStudentsWithPaymentStatusFiltered($this->getFilters());
    }
    
    public function updatedSearch()
    {
        $this->refreshData();
    }
    
    public function updatedDepartmentFilter()
    {
        $this->refreshData();
    }
    
    public function updatedLevelFilter()
    {
        $this->refreshData();
    }
    
    public function updatedSortBy()
    {
        $this->refreshData();
    }
    
    public function updatedSortDirection()
    {
        $this->refreshData();
    }
    
    private function refreshData(): void
    {
        $utmeService = app(UtmeService::class);
        $this->students = $utmeService->getUndergraduateStudentsWithPaymentStatusFiltered($this->getFilters());
    }
    
    private function getFilters(): array
    {
        return [
            'search' => $this->search,
            'department' => $this->departmentFilter,
            'level' => $this->levelFilter,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->refreshData();
    }

    public function getFilteredStudentsProperty()
    {
        return collect($this->students);
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
