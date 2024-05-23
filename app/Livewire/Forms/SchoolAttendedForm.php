<?php

namespace App\Livewire\Forms;

use App\Models\School;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SchoolAttendedForm extends Form
{

    #[Validate('required')]
    public $schoolName = '';
    #[Validate('required')]
    public $certificateName = '';
    #[Validate('required')]
    public $dateObtained = '';

    public function store()
    {
        $this->validate();



        auth()->user()->schools()->create([
            'school_name' => $this->schoolName,
            'certificate_name' => $this->certificateName,
            'date_obtained' => $this->dateObtained,
        ]);
    }
    public function update($schoolId)
    {

        School::find($schoolId)->update(
            [
                'school_name' => $this->schoolName,
                'certificate_name' => $this->certificateName,
                'date_obtained' => $this->dateObtained,
            ]
        );
    }
}
