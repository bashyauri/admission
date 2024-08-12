<?php

namespace App\Http\Livewire\Hod\Applicants;

use App\Models\User;
use Livewire\Component;
use App\Notifications\Reject;
use App\Enums\ApplicationStatus;
use App\Notifications\Shortlist;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ApplicantEdit extends Component
{
    use LivewireAlert;
    public $user;
    public function mount(User $user)
    {
        $this->user = $user;
    }
    public function shortlist()
    {
        DB::transaction(function () {
            $this->toggleStatus();
            $this->notifyUser();
            $this->alert('success', 'Updated Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        });
    }
    private function notifyUser()
    {
        $this->user->isShortlisted() ?  $this->user->notify(new Shortlist()) : $this->user->notify(new Reject());
    }
    private function toggleStatus()
    {
        $this->user->proposedCourse()->update(
            ['status' => $this->user->isShortlisted() ? ApplicationStatus::PENDING : ApplicationStatus::SHORTLISTED]
        );
        $this->alert('success', 'Status Updated Successfully', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }
    public function render()
    {
        return view('livewire.hod.applicants.applicant-edit');
    }
}