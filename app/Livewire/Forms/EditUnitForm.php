<?php

namespace App\Livewire\Forms;

use App\Models\DepartmentCourse;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditUnitForm extends Form
{
    #[Validate('required|integer|min:1')]
    public $unit;

    public function update(int $id, int $departmentId): void
    {
        $this->validate();

        DepartmentCourse::query()
            ->where('department_id', $departmentId)
            ->findOrFail($id)
            ->update(['units' => (int) $this->unit]);
    }
}
