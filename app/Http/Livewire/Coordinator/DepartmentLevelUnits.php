<?php

namespace App\Http\Livewire\Coordinator;

use Livewire\Component;
use App\Enums\StudentLevel;

class DepartmentLevelUnits extends Component
{
    public function render()
    {
        return view('livewire.coordinator.department-level-units', ['levels' => StudentLevel::getLevels()]);
    }
}
