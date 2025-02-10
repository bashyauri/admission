<?php

namespace App\Livewire\Forms;

use App\Models\DepartmentMaxUnit;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DepartmentLevelUnitsForm extends Form
{
    #[Validate('required')]
    public $level;
    #[Validate('required')]
    public $units;
    public function store(): void
    {
        $this->validate();
        DepartmentMaxUnit::create([
            'department_id' => auth()->user()->coordinator->department_id,
            'student_level_id' => $this->level['value'],
            'max_units' => $this->units,
        ]);
    }
}
