<?php

declare(strict_types=1);

namespace App\Http\Livewire\Student;

use App\Services\StudentStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class StudentStatusOverview extends Component
{
    public string $reinstatementNotes = '';
    public string $successMessage = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->isUndergraduate(), 404);
        Gate::authorize('student-status.view', Auth::user());
    }

    public function requestReinstatement(): void
    {
        $this->validate([
            'reinstatementNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $student = Auth::user();
        Gate::authorize('student-status.request-reinstatement', $student);

        try {
            app(StudentStatusService::class)->requestReinstatement(
                user: $student,
                requestedBy: $student,
                notes: $this->reinstatementNotes ?: null,
            );
            $this->reinstatementNotes = '';
            $this->successMessage = 'Your reinstatement request was submitted for review.';
        } catch (\InvalidArgumentException $exception) {
            $this->addError('reinstatementNotes', $exception->getMessage());
        }
    }

    public function render()
    {
        $student = Auth::user();
        $service = app(StudentStatusService::class);
        $currentStatus = $service->getCurrentStatus($student);
        $history = $service->getStatusHistory($student);
        $canRequestReinstatement = $service->isEligibleForReinstatement($student)
            && Gate::allows('student-status.request-reinstatement', $student)
            && !$history->contains(fn ($record) => $record->reason_code === 'REINSTATEMENT_REQUEST'
                && in_array($record->senate_decision, [
                    StudentStatusService::REINSTATEMENT_REQUESTED,
                    StudentStatusService::REINSTATEMENT_DEPARTMENT_APPROVED,
                    StudentStatusService::REINSTATEMENT_FACULTY_APPROVED,
                ], true));

        return view('livewire.student.student-status-overview', compact('currentStatus', 'history', 'canRequestReinstatement'))
            ->layout('layouts.app');
    }
}
