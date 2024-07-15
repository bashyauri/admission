<?php

namespace App\Http\Livewire\Hod;

use App\Models\User;
use Livewire\Component;
use App\Livewire\Forms\UpdateHodNameForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;

class HodProfile extends Component
{
    use LivewireAlert;
    public UpdateHodNameForm $form;
    public function mount()
    {
        $hod = auth()->user();

        $this->form->setHodName($hod);
    }
    public function update()
    {
        try {
            $this->form->update();
            $this->alert('success', 'Hod Name updated successfully');
        } catch (ValidationException $e) {

            // Display validation errors
            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', "$errorMessages");


            // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Failed to update Hod Name');
        }
    }
    public function render()
    {
        return view('livewire.hod.hod-profile');
    }
}
