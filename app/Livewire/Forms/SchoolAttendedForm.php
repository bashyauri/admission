<?php

namespace App\Livewire\Forms;

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
        $data = $this->validate();


        auth()->user()->school()->create([
            'name' => $this->schoolName,
            'certificate_name' => $this->certificateName["value"],
            'date_obtained' => $this->dateObtained,
        ]);
    }
}
