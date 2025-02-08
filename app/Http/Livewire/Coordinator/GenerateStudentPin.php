<?php

namespace App\Http\Livewire\Coordinator;

use Livewire\Component;
use App\Models\AcademicDetail;
use Illuminate\Database\Eloquent\Collection;

class GenerateStudentPin extends Component
{
    public $search = '';
    public $departmentId;

    public function mount()
    {
        $this->departmentId = auth()->user()->coordinator->department_id;
    }
    public function searchStudent(): Collection
    {
        return AcademicDetail::where(
            ['matric_no' => $this->search],
            ['department_id' => $this->departmentId],

        )->select(['user_id', 'matric_no', 'department_id'])->get();
    }


    public function render()
    {
        return view(
            'livewire.coordinator.generate-student-pin',
            [
                'students' => $this->searchStudent()
            ]
        );
    }
}
