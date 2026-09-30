<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\StudentTransaction;
use App\Enums\AcademicActivity;
use App\Services\StudentStatusService;
use Illuminate\Support\Facades\Auth;

class StudentIndex extends Component
{
    #[Computed(persist: true)]
    public function transactions()
    {
        return StudentTransaction::where(['user_id' => auth()->id()])->get();
    }
    public function render()
    {
        $student = Auth::user();
        $statusService = app(StudentStatusService::class);
        $currentStatus = $student->isUndergraduate() ? $statusService->getCurrentStatus($student) : null;

        return view('livewire.dashboards.student-index', [
            'currentStudentStatus' => $currentStatus,
            'canStartFeePayment' => $statusService->canPerformAcademicActivity($student, AcademicActivity::SCHOOL_FEES),
            'canStartCourseRegistration' => $statusService->canPerformAcademicActivity($student, AcademicActivity::COURSE_REGISTRATION),
            'canUseExamActions' => $statusService->canPerformAcademicActivity($student, AcademicActivity::EXAM_REGISTRATION),
        ]);
    }
}
