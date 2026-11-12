<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\Department;
use App\Services\DeMatricNumberReviewService;
use App\Services\AcademicSessionService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class DeMatricNumberReview extends Component
{
    use WithPagination;

    public string $search = '';

    public string $departmentId = '';

    #[Locked]
    public string $currentSession = '';

    public int $perPage = 25;

    public array $selectedAcademicDetailIds = [];

    public bool $showConfirmationModal = false;

    public function mount(AcademicSessionService $academicSessionService): void
    {
        $user = auth()->user();
        $this->currentSession = $user
            ? $academicSessionService->getAcademicSession($user)
            : (config('remita.settings.admin_academic_session') ?? config('remita.settings.academic_session') ?? '2026/2027');
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDepartmentId(): void
    {
        $this->resetPage();
    }

    public function openConfirmationModal(): void
    {
        if ($this->selectedAcademicDetailIds === []) {
            $this->addError('selectedAcademicDetailIds', 'Select at least one record to update.');

            return;
        }

        $this->showConfirmationModal = true;
    }

    public function closeConfirmationModal(): void
    {
        $this->showConfirmationModal = false;
    }

    public function updateSingleMatricNumber(int $academicDetailId, DeMatricNumberReviewService $reviewService): void
    {
        $academicDetail = AcademicDetail::query()
            ->with(['user.proposedCourse', 'user.postUtmeUpload', 'department'])
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->where(function ($query): void {
                $query->where('admission_session', $this->currentSession)
                    ->orWhere(function ($query): void {
                        $query->whereNull('admission_session')->where('acad_session', $this->currentSession);
                    });
            })
            ->find($academicDetailId);

        if (! $academicDetail || ! $reviewService->updateMatricNumber($academicDetail, $this->currentSession)) {
            session()->flash('error', 'No update was needed or the record could not be corrected.');

            return;
        }

        session()->flash('success', 'The DE matric number was updated successfully.');
    }

    public function updateSelectedMatricNumbers(DeMatricNumberReviewService $reviewService): void
    {
        if ($this->selectedAcademicDetailIds === []) {
            $this->addError('selectedAcademicDetailIds', 'Select at least one DE record to update.');

            return;
        }

        $updated = 0;

        foreach (AcademicDetail::query()
            ->with(['user.proposedCourse', 'user.postUtmeUpload', 'department'])
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->where(function ($query): void {
                $query->where('admission_session', $this->currentSession)
                    ->orWhere(function ($query): void {
                        $query->whereNull('admission_session')->where('acad_session', $this->currentSession);
                    });
            })
            ->whereIn('id', $this->selectedAcademicDetailIds)
            ->get() as $academicDetail) {
            if ($reviewService->updateMatricNumber($academicDetail, $this->currentSession)) {
                $updated++;
            }
        }

        $this->selectedAcademicDetailIds = [];
        $this->showConfirmationModal = false;

        session()->flash('success', "{$updated} DE matric number(s) were updated.");
    }

    public function render(DeMatricNumberReviewService $reviewService)
    {
        $query = AcademicDetail::query()
            ->with(['user.proposedCourse', 'user.postUtmeUpload', 'department'])
            ->whereNotNull('matric_no')
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value));

        $query->where(function ($query): void {
            $query->where('admission_session', $this->currentSession)
                ->orWhere(function ($query): void {
                    $query->whereNull('admission_session')->where('acad_session', $this->currentSession);
                });
        });

        if ($this->departmentId !== '') {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->search !== '') {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($query) use ($search): void {
                $query->where('matric_no', 'like', $search)
                    ->orWhereHas('user', function ($query) use ($search): void {
                        $query->where('surname', 'like', $search)
                            ->orWhere('firstname', 'like', $search)
                            ->orWhere('m_name', 'like', $search);
                    });
            });
        }

        $academicDetails = $query
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($this->perPage);

        $academicDetails->setCollection(
            $academicDetails->getCollection()->filter(function (AcademicDetail $academicDetail) use ($reviewService): bool {
                $suggestedMatricNo = $reviewService->getSuggestedMatricNumber($academicDetail, $this->currentSession);
                $academicDetail->suggested_matric_no = $suggestedMatricNo;
                $academicDetail->is_matric_number_valid = $suggestedMatricNo !== null && $academicDetail->matric_no === $suggestedMatricNo;

                return $suggestedMatricNo !== null && $academicDetail->matric_no !== $suggestedMatricNo;
            })->values()
        );

        return view('livewire.admin.de-matric-number-review', [
            'academicDetails' => $academicDetails,
            'departments' => Department::query()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
