<?php

declare(strict_types=1);

namespace App\Http\Livewire\Coordinator;

use App\Enums\TransactionStatus;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\Approval;
use App\Models\AcademicDetail;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Services\AcademicSessionService;

class GenerateStudentPin extends Component
{
    use LivewireAlert;
    public $search = '';
    public $departmentId;
    public $generatedPin;


    public function mount(): void
    {
        $this->departmentId = Auth::user()->coordinator->department_id;
    }

    public function generatePin(AcademicDetail $academicDetail): void
    {
        $hasPaidSchoolFees = StudentTransaction::where('user_id', $academicDetail->user_id)
            ->whereIn('resource', [
                config('remita.schoolfees.description'),
                config('remita.schoolfees.ug_schoolfees_description'),
            ])
            ->where('status', TransactionStatus::APPROVED->value)
            ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession($academicDetail->user))
            ->exists();

        if (!$hasPaidSchoolFees) {
            $this->alert('error', 'Pin can only be generated after school fees payment is approved.', [
                'position' => 'top-end',
                'timer' => 3500,
                'toast' => true,
            ]);
            return;
        }

        try {
            $this->generatedPin = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
            DB::transaction(function () use ($academicDetail) {
                Approval::updateOrCreate(
                    ['academic_detail_id' => $academicDetail->id],
                    ['pin' => $this->generatedPin, 'is_used' => false, 'coordinator_id' => Auth::user()->coordinator->id, 'approval_date' => now()],

                );
                $academicDetail->update(['coordinator_id' => Auth::user()->coordinator->id]);
            });
            $this->alert('success', 'Pin Generated', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,

                'showCancelButton' => false,
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to generate pin!', [
                'position' => 'top-end',
                'timer' => 3000,
            ]);
        }
    }
    public function close(): void
    {
        $this->generatedPin = null;
        $this->search = '';
    }

    public function searchStudent(): Collection
    {
        return AcademicDetail::where('matric_no',  $this->search)
            ->where('department_id', $this->departmentId)
            ->select(['user_id', 'matric_no', 'department_id', 'id'])
            ->get();
    }

    public function render()
    {
        return view('livewire.coordinator.generate-student-pin', [
            'students' => $this->searchStudent()
        ]);
    }
}
