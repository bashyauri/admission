<?php

declare(strict_types=1);

namespace App\Http\Livewire\Coordinator;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\Approval;
use App\Models\AcademicDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class GenerateStudentPin extends Component
{
    use LivewireAlert;
    public $search = '';
    public $departmentId;
    public $generatedPin;


    public function mount(): void
    {
        $this->departmentId = auth()->user()->coordinator->department_id;
    }

    public function generatePin(AcademicDetail $academicDetail): void
    {

        try {
            $this->generatedPin = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
            DB::transaction(function () use ($academicDetail) {
                Approval::updateOrCreate(
                    ['academic_detail_id' => $academicDetail->id],
                    ['pin' => $this->generatedPin, 'is_used' => false, 'coordinator_id' => auth()->user()->coordinator->id, 'approval_date' => now()],

                );
                $academicDetail->update(['coordinator_id' => auth()->user()->coordinator->id]);
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
