<?php

declare(strict_types=1);

namespace App\Http\Livewire\Hod;

use App\Enums\ProgrammesEnum;
use App\Models\CarryOverCourse;
use App\Models\DepartmentCourse;
use App\Services\CarryOverRegistrationService;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CarryOverReview extends Component
{
    use LivewireAlert;

    public array $successorDepartmentCourseIds = [];
    public array $reviewNotes = [];

    public function mount(): void
    {
        abort_unless(Auth::user()?->canActAsHod(), 403);
    }

    public function approveSuccessor(int $carryOverId): void
    {
        $reviewer = Auth::user();
        abort_unless($reviewer?->canActAsHod(), 403);

        $carryOver = $this->visibleCarryOvers()->findOrFail($carryOverId);
        $successorId = filter_var($this->successorDepartmentCourseIds[$carryOverId] ?? null, FILTER_VALIDATE_INT);
        if (!$successorId) {
            $this->addError("successorDepartmentCourseIds.{$carryOverId}", 'Select an offered successor course.');
            return;
        }

        $successor = DepartmentCourse::with('studentCourse')->findOrFail($successorId);
        try {
            app(CarryOverRegistrationService::class)->approveDepartmentSuccessor(
                $carryOver,
                $successor,
                $reviewer,
                trim((string) ($this->reviewNotes[$carryOverId] ?? '')),
            );
        } catch (\InvalidArgumentException $exception) {
            $this->addError("reviewNotes.{$carryOverId}", $exception->getMessage());
            return;
        }

        unset(
            $this->successorDepartmentCourseIds[$carryOverId],
            $this->reviewNotes[$carryOverId],
        );

        $this->alert('success', 'Successor approved. The retake will be registered when the student next opens course registration for the active academic session.');
    }

    private function visibleCarryOvers()
    {
        $query = CarryOverCourse::query()
            ->with(['user.academicDetail.department', 'registeredCourse', 'departmentCourse.studentCourse'])
            ->where('is_cleared', false)
            ->where('registration_status', 'review_required')
            ->whereHas('user', fn ($user) => $user->where('programme_id', ProgrammesEnum::Undergraduate->value));

        $reviewer = Auth::user();
        if (!$reviewer->canActAsAdmin() && !$reviewer->canActAsCit()) {
            $departmentIds = array_filter([
                $reviewer->hodDetails?->department_id,
                ...$reviewer->capabilityDepartments('hod'),
            ]);
            if ($departmentIds === []) {
                return $query->whereRaw('1 = 0');
            }
            $query->whereHas('user.academicDetail', fn ($detail) => $detail->whereIn('department_id', $departmentIds));
        }

        return $query;
    }

    public function render()
    {
        $carryOvers = $this->visibleCarryOvers()->orderBy('failed_session')->get();
        $departmentIds = $carryOvers->pluck('user.academicDetail.department_id')->filter()->unique();
        $successors = DepartmentCourse::query()
            ->with('studentCourse')
            ->whereIn('department_id', $departmentIds)
            ->get()
            ->groupBy('department_id');

        return view('livewire.hod.carry-over-review', [
            'carryOvers' => $carryOvers,
            'successorsByDepartment' => $successors,
        ])->layout('layouts.app');
    }
}
