<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\AcademicDetail;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionSessionSynchronizer extends Component
{
    use WithPagination;

    public array $selectedAcademicDetailIds = [];

    public bool $showConfirmationModal = false;

    public string $filterDepartmentId = '';

    public string $filterAdmissionSession = '';

    public string $search = '';

    public int $perPage = 25;

    public function updatingSelectedAcademicDetailIds(): void
    {
        $this->resetValidation();
    }

    /**
     * @param array<int, int|string> $academicDetailIds
     */
    public function toggleCurrentPageSelection(array $academicDetailIds): void
    {
        $currentPageIds = array_map('strval', $academicDetailIds);
        $selectedIds = array_map('strval', $this->selectedAcademicDetailIds);
        $hasSelectedEveryRow = $currentPageIds !== []
            && array_diff($currentPageIds, $selectedIds) === [];

        $this->selectedAcademicDetailIds = $hasSelectedEveryRow
            ? array_values(array_diff($selectedIds, $currentPageIds))
            : array_values(array_unique([...$selectedIds, ...$currentPageIds]));
    }

    public function updatingFilterDepartmentId(): void
    {
        $this->resetReviewPage();
    }

    public function updatingFilterAdmissionSession(): void
    {
        $this->resetReviewPage();
    }

    public function updatingSearch(): void
    {
        $this->resetReviewPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetReviewPage();
    }

    public function openConfirmationModal(): void
    {
        if ($this->selectedAcademicDetailIds === []) {
            $this->addError('selectedAcademicDetailIds', 'Select at least one student to synchronize.');

            return;
        }

        $this->showConfirmationModal = true;
    }

    public function closeConfirmationModal(): void
    {
        $this->showConfirmationModal = false;
    }

    public function synchronizeSelected(): void
    {
        if ($this->selectedAcademicDetailIds === []) {
            $this->addError('selectedAcademicDetailIds', 'Select at least one student to synchronize.');

            return;
        }

        $updated = DB::transaction(function (): int {
            $academicDetailsQuery = AcademicDetail::query()
                ->whereIn('id', $this->selectedAcademicDetailIds)
                ->whereNull('admission_session');

            if (DB::getDriverName() === 'sqlite') {
                $academicDetailsQuery->whereRaw("matric_no GLOB '[0-9][0-9]*'");
            } else {
                $academicDetailsQuery->whereRaw("matric_no REGEXP '^[0-9]{2}'");
            }

            $academicDetails = $academicDetailsQuery->lockForUpdate()->get();

            foreach ($academicDetails as $academicDetail) {
                $academicDetail->update([
                    'admission_session' => $this->admissionSessionFromMatricNumber(
                        $academicDetail->matric_no
                    ),
                ]);
            }

            return $academicDetails->count();
        });

        $this->selectedAcademicDetailIds = [];
        $this->showConfirmationModal = false;

        session()->flash(
            'success',
            "{$updated} student admission session(s) synchronized."
        );
    }

    public function render()
    {
        $academicDetailsQuery = AcademicDetail::query()
            ->with(['user', 'department'])
            ->whereNull('admission_session');

        if (DB::getDriverName() === 'sqlite') {
            $academicDetailsQuery->whereRaw("matric_no GLOB '[0-9][0-9]*'");
        } else {
            $academicDetailsQuery->whereRaw("matric_no REGEXP '^[0-9]{2}'");
        }

        $academicDetails = $academicDetailsQuery
            ->when(
                $this->filterDepartmentId !== '',
                fn ($query) => $query->where('department_id', $this->filterDepartmentId)
            )
            ->when(
                $this->filterAdmissionSession !== '',
                function ($query) {
                    if (DB::getDriverName() === 'sqlite') {
                        $query->whereRaw(
                            "('20' || SUBSTR(matric_no, 1, 2) || '/20' || PRINTF('%02d', CAST(SUBSTR(matric_no, 1, 2) AS INTEGER) + 1)) = ?",
                            [$this->filterAdmissionSession]
                        );
                    } else {
                        $query->whereRaw(
                            "CONCAT('20', LEFT(matric_no, 2), '/20', LPAD(CAST(LEFT(matric_no, 2) AS UNSIGNED) + 1, 2, '0')) = ?",
                            [$this->filterAdmissionSession]
                        );
                    }
                }
            )
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';

                $query->where(function ($query) use ($search) {
                    $query->where('matric_no', 'like', $search)
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('surname', 'like', $search)
                                ->orWhere('firstname', 'like', $search)
                                ->orWhere('m_name', 'like', $search);
                        });
                });
            })
            ->orderBy('matric_no')
            ->paginate($this->perPage)
            ->through(function (AcademicDetail $academicDetail): AcademicDetail {
                $academicDetail->proposed_admission_session = $this->admissionSessionFromMatricNumber(
                    $academicDetail->matric_no
                );

                return $academicDetail;
            });

        return view('livewire.admin.admission-session-synchronizer', [
            'academicDetails' => $academicDetails,
            'departments' => Department::query()->orderBy('name')->get(),
            'availableAdmissionSessions' => $this->availableAdmissionSessions(),
        ])->layout('layouts.app');
    }

    private function resetReviewPage(): void
    {
        $this->selectedAcademicDetailIds = [];
        $this->resetPage();
    }

    private function availableAdmissionSessions(): array
    {
        $query = AcademicDetail::query()->whereNull('admission_session');

        if (DB::getDriverName() === 'sqlite') {
            $query->whereRaw("matric_no GLOB '[0-9][0-9]*'")
                ->selectRaw('DISTINCT SUBSTR(matric_no, 1, 2) as admission_year');
        } else {
            $query->whereRaw("matric_no REGEXP '^[0-9]{2}'")
                ->selectRaw('DISTINCT LEFT(matric_no, 2) as admission_year');
        }

        return $query->orderByDesc('admission_year')
            ->pluck('admission_year')
            ->map(function (string $admissionYear): string {
                $year = 2000 + (int) $admissionYear;

                return $year . '/' . ($year + 1);
            })
            ->values()
            ->all();
    }

    private function admissionSessionFromMatricNumber(string $matricNumber): string
    {
        $admissionYear = 2000 + (int) substr($matricNumber, 0, 2);

        return $admissionYear . '/' . ($admissionYear + 1);
    }
}
