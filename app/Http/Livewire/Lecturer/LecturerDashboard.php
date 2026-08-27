<?php

namespace App\Http\Livewire\Lecturer;

use Livewire\Component;
use App\Models\CourseAllocation;
use App\Services\AcademicSessionService;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class LecturerDashboard extends Component
{
    public string $selectedSession;
    public array $availableSessions = [];
    public string $defaultSession;

    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $service = new AcademicSessionService();
        $this->defaultSession = $service->getAcademicSession($user);
        $this->selectedSession = $this->defaultSession;

        // Build a list of all unique sessions from existing CourseAllocations for this lecturer
        // plus any from the settings table (ACADEMIC_SESSION, HOD_ACADEMIC_SESSION, PG_ACADEMIC_SESSION keys)
        $sessionKeys = ['ACADEMIC_SESSION', 'HOD_ACADEMIC_SESSION', 'PG_ACADEMIC_SESSION', 'ADMIN_ACADEMIC_SESSION'];
        $dbSessions = Setting::whereIn('key', $sessionKeys)->pluck('value')->filter()->unique()->values()->toArray();

        // Get all distinct sessions from allocations (if any exist with a direct session string field)
        // Since CourseAllocation uses foreignIdFor(AcademicSession) we'll list via names
        $this->availableSessions = array_values(array_unique(array_merge(
            $dbSessions,
            [$this->defaultSession]
        )));

        sort($this->availableSessions);
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Get allocations for the current lecturer filtered by selected session string
        // CourseAllocation has a relationship to AcademicSession via academic_session_id
        // For now we match available allocations without session filtering (session is on results/registration level)
        $allocations = CourseAllocation::with(['departmentCourse.studentCourse', 'department'])
            ->where('lecturer_id', $user->id)
            ->get();

        return view('livewire.lecturer.lecturer-dashboard', [
            'allocations' => $allocations,
        ])->layout('layouts.app');
    }
}
