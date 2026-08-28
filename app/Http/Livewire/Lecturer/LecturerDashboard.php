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
        $allocationSessions = CourseAllocation::query()
            ->where('lecturer_id', $user->id)
            ->pluck('academic_session')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->availableSessions = array_values(array_unique(array_merge(
            $dbSessions,
            $allocationSessions,
            [$this->defaultSession]
        )));

        sort($this->availableSessions);
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $allocations = CourseAllocation::with(['departmentCourse.studentCourse', 'department'])
            ->where('lecturer_id', $user->id)
            ->where('academic_session', $this->selectedSession)
            ->orderBy('semester')
            ->latest()
            ->get();

        return view('livewire.lecturer.lecturer-dashboard', [
            'allocations' => $allocations,
        ])->layout('layouts.app');
    }
}
