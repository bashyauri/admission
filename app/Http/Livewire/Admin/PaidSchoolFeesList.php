<?php

namespace App\Http\Livewire\Admin;

use App\Enums\TransactionStatus;
use App\Services\Report\UtmeService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class PaidSchoolFeesList extends Component
{
    use WithPagination;
    
    #[Locked]
    public $schoolFees;
    
    public $search = '';
    public $sortBy = 'surname';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $departmentFilter = '';
    public $statusFilter = '';
    public $levelFilter = '';

    public function mount(UtmeService $utmeService): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'You must be logged in as an Admin to view this page');
        $this->schoolFees = $utmeService->getUtmeFirstSchoolFeesPaymentAll();
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

    public function getFilteredSchoolFeesProperty()
    {
        $query = collect($this->schoolFees);

        // Search filter
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

        // Department filter
        if ($this->departmentFilter) {
            $query = $query->filter(function ($item) {
                $departmentName = strtolower($item->department_name ?? '');
                return $departmentName === strtolower($this->departmentFilter);
            });
        }
        
        // Level filter
        if ($this->levelFilter) {
            $query = $query->filter(function ($item) {
                $levelName = strtolower($item->level_name ?? '');
                return $levelName === strtolower($this->levelFilter);
            });
        }

        // Status filter
        if ($this->statusFilter) {
            $query = $query->filter(function ($item) {
                return strtolower($item->status) === strtolower($this->statusFilter);
            });
        }

        // Sorting
        $query = $query->sortBy([
            [$this->sortBy, $this->sortDirection]
        ]);

        return $query;
    }

    public function getDepartmentsProperty()
    {
        return collect($this->schoolFees)
            ->pluck('department_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function getLevelsProperty()
    {
        return collect($this->schoolFees)
            ->pluck('level_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function getTotalCountProperty()
    {
        return collect($this->schoolFees)->count();
    }

    public function getStatusesProperty()
    {
        return collect($this->schoolFees)
            ->pluck('status')
            ->unique()
            ->sort()
            ->values();
    }

    public function render()
    {
        return view('livewire.admin.paid-school-fees-list', [
            'schoolFees' => $this->schoolFees,
            'departments' => $this->departments,
            'levels' => $this->levels,
            'statuses' => $this->statuses,
            'totalCount' => $this->totalCount,
            'approvedStatus' => TransactionStatus::APPROVED,
            'pendingStatus' => TransactionStatus::PENDING,
        ]);
    }
}
