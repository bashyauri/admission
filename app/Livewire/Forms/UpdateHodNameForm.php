<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Livewire\Attributes\Validate;

class UpdateHodNameForm extends Form
{
    #[Validate('required', message: 'Surname is required')]
    public  $surName;
    #[Validate('required')]
    public $firstName;
    public ?User $hod;
    public function setHodName(User $hod)
    {
        $this->surName = $hod->surname;
        $this->firstName = $hod->firstname;
    }

    public function update()
    {
        $this->validate();

        auth()->user()->update([
            'surname' => $this->surName,
            'firstname' => $this->firstName,

        ]);
    }
}
