<?php

namespace App\Http\Livewire\Admin;

use App\Services\ApplicantCleanupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePostgraduateApplicants extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $selectedApplicants = [];
    public $selectAll = false;
    public $selectAllOnPage = false;

    // Store IDs for current page to enable "Select All on Page"
    public $pageApplicantIds = [];

    // Preview modal
    public $showPreviewModal = false;
    public $previewData = [];

    // Confirmation modal
    public $showConfirmModal = false;
    public $confirmText = '';
    public $isDeleting = false;
    public $deletionProgress = 0;

    // Success/Error messages
    public $successMessage = '';
    public $errorMessage = '';

    // Export
    public $exportPath = '';

    protected $listeners = [
        'refreshApplicants' => '$refresh',
    ];

    protected $rules = [
        'confirmText' => 'required',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
        $this->selectedApplicants = [];
        $this->selectAll = false;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->selectAllOnPage = false;
    }

    public function updatedSelectAllOnPage($value)
    {
        if ($value) {
            // Add all current page IDs to selection
            $this->selectedApplicants = array_unique(array_merge(
                $this->selectedApplicants,
                $this->pageApplicantIds
            ));
        } else {
            // Remove only current page IDs from selection
            $this->selectedApplicants = array_diff(
                $this->selectedApplicants,
                $this->pageApplicantIds
            );
        }
        $this->selectAll = false;
    }

    /**
     * Toggle a single applicant selection (called from Alpine)
     */
    public function toggleSelection($applicantId)
    {
        if (in_array($applicantId, $this->selectedApplicants)) {
            $this->selectedApplicants = array_diff($this->selectedApplicants, [$applicantId]);
        } else {
            $this->selectedApplicants[] = $applicantId;
        }
        $this->selectAllOnPage = false;
        $this->selectAll = false;
    }

    /**
     * Select all applicants across all pages
     */
    public function selectAllApplicants()
    {
        $applicants = $this->getApplicantsQuery()->pluck('id')->toArray();
        $this->selectedApplicants = $applicants;
        $this->selectAll = true;
        $this->selectAllOnPage = false;
    }

    /**
     * Clear all selections
     */
    public function clearSelection()
    {
        $this->selectedApplicants = [];
        $this->selectAll = false;
        $this->selectAllOnPage = false;
    }

    private function getApplicantsQuery()
    {
        $service = new ApplicantCleanupService();
        $applicants = $service->getPostgraduateApplicants();

        if ($this->search) {
            $search = strtolower($this->search);
            $applicants = $applicants->filter(function ($applicant) use ($search) {
                return str_contains(strtolower($applicant->full_name), $search) ||
                       str_contains(strtolower($applicant->email), $search) ||
                       str_contains(strtolower($applicant->jamb_no ?? ''), $search);
            });
        }

        return $applicants;
    }

    /**
     * Review a single applicant
     */
    public function reviewSingleApplicant($applicantId)
    {
        $this->selectedApplicants = [$applicantId];
        $this->selectAll = false;
        $this->selectAllOnPage = false;
        $this->openPreviewModal();
    }

    public function openPreviewModal()
    {
        if (empty($this->selectedApplicants)) {
            $this->errorMessage = 'Please select at least one applicant to review.';
            return;
        }

        $service = new ApplicantCleanupService();
        $this->previewData = $service->getDeletionPreview($this->selectedApplicants);
        $this->showPreviewModal = true;
        $this->showConfirmModal = false;
        $this->confirmText = '';
    }

    public function closePreviewModal()
    {
        $this->showPreviewModal = false;
        $this->previewData = [];
    }

    public function proceedToConfirmation()
    {
        $this->showPreviewModal = false;
        $this->showConfirmModal = true;
        $this->confirmText = '';
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmText = '';
    }

    public function exportSelected()
    {
        if (empty($this->selectedApplicants)) {
            $this->errorMessage = 'Please select at least one applicant to export.';
            return;
        }

        $service = new ApplicantCleanupService();
        $this->exportPath = $service->exportApplicants($this->selectedApplicants);

        $this->successMessage = 'Applicants exported successfully. File: ' . $this->exportPath;

        return response()->download(
            Storage::disk('local')->path($this->exportPath),
            basename($this->exportPath)
        );
    }

    public function deleteApplicants()
    {
        $expectedText = 'DELETE ' . count($this->selectedApplicants);

        if ($this->confirmText !== $expectedText) {
            $this->errorMessage = 'Please type "' . $expectedText . '" exactly to confirm deletion.';
            return;
        }

        if (empty($this->selectedApplicants)) {
            $this->errorMessage = 'No applicants selected for deletion.';
            return;
        }

        $this->isDeleting = true;
        $this->deletionProgress = 0;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $service = new ApplicantCleanupService();

            // Export before deletion if not already exported
            if (empty($this->exportPath)) {
                $this->exportPath = $service->exportApplicants($this->selectedApplicants);
            }

            // Get preview data for logging
            // Delete applicants
            $results = $service->bulkDeleteApplicants($this->selectedApplicants);

            // Log the deletion
            $service->logDeletion(
                $this->selectedApplicants,
                Auth::id(),
                $results['counts'],
                $this->exportPath
            );

            if (count($results['failed']) > 0) {
                $firstFailure = $results['failed'][0]['error'] ?? 'Unknown database error.';
                $this->errorMessage = 'Some deletions failed: ' . count($results['failed']) . ' errors. First error: ' . $firstFailure;
            } else {
                $this->successMessage = 'Successfully deleted ' . $results['counts']['users'] . ' applicants with ' . array_sum($results['counts']) . ' related records.';
            }

            // Reset selections
            $this->selectedApplicants = [];
            $this->selectAll = false;
            $this->showConfirmModal = false;
            $this->confirmText = '';
            $this->exportPath = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'Error during deletion: ' . $e->getMessage();
        } finally {
            $this->isDeleting = false;
            $this->deletionProgress = 0;
        }
    }

    public function dismissMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $applicants = $this->getApplicantsQuery();
        $totalCount = $applicants->count();

        // Manual pagination for collection
        $currentPage = $this->getPage();
        $items = $applicants->forPage($currentPage, $this->perPage)->values();

        // Store current page IDs for "Select All on Page" functionality
        $this->pageApplicantIds = $items->pluck('id')->toArray();

        // Update selectAllOnPage checkbox state based on current selection
        $this->selectAllOnPage = !empty($this->pageApplicantIds) &&
            count(array_diff($this->pageApplicantIds, $this->selectedApplicants)) === 0;

        // Create proper paginator for ->links() support
        $paginatedApplicants = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $totalCount,
            $this->perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return view('livewire.admin.manage-postgraduate-applicants', [
            'applicants' => $paginatedApplicants,
            'totalCount' => $totalCount,
            'perPage' => $this->perPage,
            'currentPage' => $currentPage,
        ]);
    }
}
