<?php

namespace App\Http\Livewire\Applications;

use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;
    public $picture = '';
    public $birthday;
    public $maritalStatus;
    public $gender;
    public $kinName;
    public $kinPhone;
    public $kinAddress;
    public $state;
    public $lga;
    public $homeAddress;
    public $corAddress;
    public function render()
    {
        return view('livewire.applications.profile');
    }
}
