<?php

declare(strict_types=1);

namespace App\Http\Livewire\Student;

use Livewire\Component;
use App\Services\AcademicLevelService;
use Illuminate\Support\Facades\Auth;

class StudentProfile extends Component
{
    public $student;
    public string $academicLevel = 'N/A';

    public function mount(): void
    {

        $this->student = Auth::user();
        $this->student->loadMissing('academicDetail.studentLevel');
        $this->academicLevel = app(AcademicLevelService::class)->displayLabel(
            $this->student->academicDetail?->studentLevel,
            $this->student->programme_id ? (int) $this->student->programme_id : null,
        );
    }
    public function render()
    {
        return view('livewire.student.student-profile');
    }
}
