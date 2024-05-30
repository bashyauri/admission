<?php

namespace App\Http\Livewire\Applications;

use App\Livewire\Forms\SchoolAttendedForm;
use App\Models\School;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SchoolAttended extends Component
{
    use LivewireAlert;
    public SchoolAttendedForm $form;

    public $editingSchoolId;


    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
    }

    public function save()
    {

        try {
            $this->form->store();

            $this->alert('success', 'School Added', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);

            return to_route('school-attended');
        } catch (ValidationException $e) {

            // Display validation errors
            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', "$errorMessages", [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed'

            ]);


            // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
            $this->redirect(route('school-attended'));
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

    public function confirmed()
    {

        $this->redirect(route('school-attended'));
    }
    public function getListeners()
    {
        return [
            'confirmed'
        ];
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
        return School::where('user_id', auth()->user()->id)->get();
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
