<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\Department;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class ManageUserCapabilities extends Component
{
    use WithPagination;
    use LivewireAlert;

    // Form inputs for new capability assignment
    public string $selectedUserId = '';
    public string $capability = 'exam_officer';
    public ?int $departmentId = null;
    public string $reason = '';
    public string $staffSearch = '';

    // Filters for capabilities table
    public string $filterCapability = '';
    public string $filterDepartment = '';
    public string $filterStatus = '';
    public string $searchQuery = '';

    // Modal state
    public bool $showAssignModal = false;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'selectedUserId' => 'required|exists:users,id',
            'capability'     => 'required|in:exam_officer,lecturer,hod,cit,coordinator,idcard_officer',
            'departmentId'   => 'nullable|exists:departments,id',
            'reason'         => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'selectedUserId.required' => 'Please select a staff member / lecturer.',
        'selectedUserId.exists'   => 'The selected user is invalid.',
        'capability.required'     => 'Please choose a capability role.',
        'capability.in'           => 'Invalid capability selected.',
    ];

    public function updatingSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCapability(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openAssignModal(): void
    {
        $this->resetValidation();
        $this->reset(['selectedUserId', 'departmentId', 'reason', 'staffSearch']);
        $this->capability = 'exam_officer';
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
    }

    public function assignCapability(): void
    {
        $this->validate();

        $existing = UserCapability::where('user_id', $this->selectedUserId)
            ->where('capability', $this->capability)
            ->where('department_id', $this->departmentId ?: null)
            ->first();

        if ($existing) {
            if ($existing->is_active) {
                $this->alert('warning', 'Duplicate Assignment', [
                    'position' => 'center',
                    'timer' => 3000,
                    'toast' => true,
                    'text' => 'This user already has this active capability in this department.',
                ]);
                return;
            }

            // Reactivate existing record
            $existing->update([
                'is_active' => true,
                'granted_at' => now(),
                'revoked_at' => null,
                'granted_by' => Auth::id(),
                'reason' => $this->reason ?: $existing->reason,
            ]);

            $this->alert('success', 'Capability Reactivated', [
                'position' => 'center',
                'timer' => 2500,
                'toast' => true,
                'text' => 'User capability successfully reactivated.',
            ]);
        } else {
            UserCapability::create([
                'user_id' => $this->selectedUserId,
                'capability' => $this->capability,
                'department_id' => $this->departmentId ?: null,
                'is_active' => true,
                'granted_at' => now(),
                'granted_by' => Auth::id(),
                'reason' => $this->reason ?: null,
            ]);

            $this->alert('success', 'Capability Granted', [
                'position' => 'center',
                'timer' => 2500,
                'toast' => true,
                'text' => 'Capability successfully assigned to user.',
            ]);
        }

        $this->closeAssignModal();
    }

    public function toggleStatus(int $capabilityId): void
    {
        $cap = UserCapability::findOrFail($capabilityId);
        $newStatus = !$cap->is_active;

        $cap->update([
            'is_active' => $newStatus,
            'revoked_at' => $newStatus ? null : now(),
        ]);

        $statusText = $newStatus ? 'activated' : 'deactivated';
        $this->alert('info', "Capability {$statusText}", [
            'position' => 'center',
            'timer' => 2000,
            'toast' => true,
        ]);
    }

    public function revokeCapability(int $capabilityId): void
    {
        $cap = UserCapability::findOrFail($capabilityId);
        $cap->delete();

        $this->alert('success', 'Capability Revoked', [
            'position' => 'center',
            'timer' => 2000,
            'toast' => true,
            'text' => 'Capability record permanently removed.',
        ]);
    }

    public function render()
    {
        // Query for staff list in modal dropdown/search
        $staffQuery = User::query()
            ->whereIn('role', ['hod', 'lecturer', 'exam_officer', 'admin', 'coordinator', 'cit']);

        if (!empty($this->staffSearch)) {
            $searchTerm = '%' . $this->staffSearch . '%';
            $staffQuery->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', $searchTerm)
                    ->orWhere('surname', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm);
            });
        }

        $staffList = $staffQuery->orderBy('surname')->limit(50)->get();

        // Query capabilities table with filters
        $capabilitiesQuery = UserCapability::with(['user', 'department', 'grantedBy'])
            ->latest('granted_at');

        if (!empty($this->searchQuery)) {
            $q = '%' . $this->searchQuery . '%';
            $capabilitiesQuery->whereHas('user', function ($uq) use ($q) {
                $uq->where('firstname', 'like', $q)
                    ->orWhere('surname', 'like', $q)
                    ->orWhere('email', 'like', $q)
                    ->orWhere('phone', 'like', $q);
            });
        }

        if (!empty($this->filterCapability)) {
            $capabilitiesQuery->where('capability', $this->filterCapability);
        }

        if (!empty($this->filterDepartment)) {
            $capabilitiesQuery->where('department_id', $this->filterDepartment);
        }

        if ($this->filterStatus !== '') {
            $capabilitiesQuery->where('is_active', (bool) $this->filterStatus);
        }

        $capabilities = $capabilitiesQuery->paginate(15);
        $departments = Department::orderBy('name')->get();

        $activeExamOfficersCount = UserCapability::where('capability', 'exam_officer')->where('is_active', true)->count();
        $activeLecturersCount = UserCapability::where('capability', 'lecturer')->where('is_active', true)->count();
        $totalAssignmentsCount = UserCapability::count();

        return view('livewire.admin.manage-user-capabilities', [
            'capabilities' => $capabilities,
            'departments' => $departments,
            'staffList' => $staffList,
            'activeExamOfficersCount' => $activeExamOfficersCount,
            'activeLecturersCount' => $activeLecturersCount,
            'totalAssignmentsCount' => $totalAssignmentsCount,
        ]);
    }
}
