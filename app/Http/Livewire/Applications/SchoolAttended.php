<?php

namespace App\Http\Livewire\Applications;

use App\Livewire\Forms\SchoolAttendedForm;
use App\Models\School;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SchoolAttended extends Component
{
    use LivewireAlert;
    public SchoolAttendedForm $form;

    public $editingSchoolId;




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
    public function edit($schoolId)
    {
        $this->editingSchoolId = $schoolId;
        $school = School::findOrFail($schoolId);
        $this->form->schoolName = $school->school_name;
        $this->form->certificateName = $school->certificate_name;
        $this->form->dateObtained = $school->date_obtained;
    }
    public function cancelEdit()
    {
        $this->reset();
    }
    public function update()
    {
        try {

            $this->form->update($this->editingSchoolId);
            $this->alert('success', 'Updated Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Update failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }

        $this->cancelEdit();
    }
    #[Computed()]
    public function schools()
    {
        return School::all();
    }
    public function delete(School $school)
    {
        try {
            $school->delete();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete');
            return;
        }

        $this->alert('success', 'Deleted Successfully', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        return view('livewire.applications.school-attended');
    }
}
