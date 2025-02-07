<?php

namespace App\Livewire\Forms;

use App\Models\DepartmentCourse;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditUnitForm extends Form
{
    #[Validate('required')]
    public $unit;

    public function update($id)
    {
        DepartmentCourse::find($id)->update(
            [
                'units' => $this->unit,

            ]
        );
    }
}
