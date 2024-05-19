<?php

namespace App\Http\Livewire\Applications;

use App\Livewire\Forms\SchoolAttendedForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class SchoolAttended extends Component
{
    use LivewireAlert;
    public SchoolAttendedForm $form;
    public function save()
    {

        try {

            $this->form->store();
            $this->alert('success', 'School Added', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return to_route('school-attended');
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return to_route('school-attended');
        }
    }

    public function render()
    {
        return view('livewire.applications.school-attended');
    }
}
