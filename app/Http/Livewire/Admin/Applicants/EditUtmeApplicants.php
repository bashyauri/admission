<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Applicants;

use App\Models\User;
use Livewire\Component;
use App\Enums\ApplicationStatus;
use App\Models\ProposedCourse;
use App\Services\UTMEApplicantService;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class EditUtmeApplicants extends Component
{
    use LivewireAlert;

    public $userId;
    public $user;
    public $proposedCourse;
    public $remark;
    public $utmeService;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->userId = $this->user->id;
    }

    public function recommendUTMEApplicant(UTMEApplicantService $uTMEApplicantService)
    {
        if (!$this->user->isRecommended()) {
            $this->validate([
                'remark' => 'required',
            ]);
        }


        try {


            $this->user->isRecommended()
                ? $uTMEApplicantService->dropApplicant($this->userId)
                : $uTMEApplicantService->recommendApplicant($this->userId, $this->remark);

            $this->alert('success', 'Updated Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error recommending UTME applicant: ' . $e->getMessage());

            // Show a failure message to the user
            $this->alert('error', 'An error occurred while updating. Please try again later.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
        $this->reset('remark');
    }

    public function render()
    {
        return view('livewire.admin.applicants.edit-utme-applicants');
    }
}
