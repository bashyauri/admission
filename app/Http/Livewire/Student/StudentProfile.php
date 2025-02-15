<?php

declare(strict_types=1);

namespace App\Http\Livewire\Student;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class StudentProfile extends Component
{
    public $student;

    public function mount(): void
    {

        $this->student = Auth::user();
    }
    public function render()
    {
        return view('livewire.student.student-profile');
    }
}
