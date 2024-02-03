<?php

namespace App\Http\Livewire\Applications;

use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;
    public $picture;
    public $birthday;
    public $maritalStatus;
    public $gender;
    public function render()
    {
        return view('livewire.applications.profile');
    }
}