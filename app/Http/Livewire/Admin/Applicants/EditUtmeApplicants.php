<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Applicants;

use App\Models\User;
use Livewire\Component;
use App\Enums\ApplicationStatus;
use App\Http\Livewire\Hod\Applicants\ApplicantEdit;
use App\Models\ProposedCourse;
use App\Services\SendSMS;
use App\Services\UTMEApplicantService;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;

class EditUtmeApplicants extends Component
{
    use LivewireAlert;

    public $userId;
    public $user;
    public $proposedCourse;
    public $remark;
    public $comment;
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
        $attributes = [
            'remark' => $this->remark,
            'comment' => $this->comment,
            'status' => ApplicationStatus::RECOMMENDED,
            'proposed_course_id' => ProposedCourse::where('user_id', $this->userId)->firstOrFail()->id,
        ];


        try {


            $this->user->isRecommended()
                ? $uTMEApplicantService->dropApplicant($this->userId)
                : $uTMEApplicantService->recommendApplicant($this->userId, $attributes);

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