<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use App\Models\Department;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;
use App\Livewire\Forms\CreateHodForm;

class AdminIndex extends Component
{
    use LivewireAlert;
    public CreateHodForm $form;
    public function createHod()
    {
        try {
            $this->form->store();
            $this->alert('success', 'User Created', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);
            $this->form->reset();
        } catch (ValidationException $e) {

            // Display validation errors
            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', "$errorMessages", [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,


            ]);


            // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }
    public function render()
    {

        return view('livewire.dashboards.admin-index', [
            'departments' => Department::get(['id', 'name'])
        ]);
    }
}
