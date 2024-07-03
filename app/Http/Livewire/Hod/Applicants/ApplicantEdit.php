<?php

namespace App\Http\Livewire\Hod\Applicants;

use App\Models\User;
use Livewire\Component;
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
        $this->user->proposedCourse()->update(
            ['status' => 'shortlisted']
        );
        $this->alert('success', 'Updated Successfully', [
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
