<?php

namespace App\Http\Livewire\Student;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\RegisteredCourse;
use App\Services\CourseRegistrationService;
use Illuminate\Support\Collection;

class PrintCourseHistory extends Component
{
    public string $selectedSession = '';

    public function mount(): void
    {
        $sessions = $this->availableSessions();
        $this->selectedSession = $sessions->first() ?? '';
    }

    public function availableSessions(): Collection
    {
        $academicDetailId = Auth::user()->academicDetail?->id;

        if (!$academicDetailId) {
            return collect();
        }

        return RegisteredCourse::where('academic_detail_id', $academicDetailId)
            ->select('academic_session')
            ->distinct()
            ->orderByDesc('academic_session')
            ->pluck('academic_session');
    }

    public function render(): \Illuminate\View\View
    {
        $user = Auth::user();
        $academicDetailId = $user->academicDetail?->id;
        $courses = collect();
        $totalUnits = 0;

        if ($academicDetailId && $this->selectedSession) {
            $service = new CourseRegistrationService();
            $courses = $service->getRegisteredCourses($academicDetailId, $this->selectedSession, 'semester');
            $totalUnits = $service->getTotalUnitsOfRegisteredCourses($academicDetailId, $this->selectedSession);
        }

        return view('livewire.student.print-course-history', [
            'courses' => $courses,
            'totalUnits' => $totalUnits,
            'sessions' => $this->availableSessions(),
        ]);
    }
}
