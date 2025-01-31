<?php

namespace App\Http\Livewire\Admin;

use App\Enums\Role;
use App\Enums\StudentLevel;
use App\Livewire\Forms\CreateCoordinatorForm;
use Livewire\Component;
use App\Models\Department;
use App\Livewire\Forms\CreateUserForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;

class Settings extends Component
{
    use LivewireAlert;
    public CreateUserForm $form;
    public CreateCoordinatorForm $coordinatorForm;
    public function createUser()
    {
        try {
            $this->form->store();
            $this->showSuccessAlert('User Created');
            $this->form->reset();
        } catch (ValidationException $e) {
            $this->showValidationErrors($e);
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            report($e);
            $this->showErrorAlert('Save failed.');
        }
    }
    public function createCoordinator()
    {

        try {
            $this->coordinatorForm->store();
            $this->showSuccessAlert('Coordinator Created');
            $this->coordinatorForm->reset();
        } catch (ValidationException $e) {


            // // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
            $this->showValidationErrors($e);
        } catch (\Exception $e) {
            report($e);
            $this->showErrorAlert('Save failed.');
        }
    }
    private function showSuccessAlert($message)
    {
        $this->alert('success', $message, [
            'position' => 'center',
            'timer' => 1000,
            'toast' => true,
        ]);
    }

    private function showValidationErrors(ValidationException $e)
    {
        $this->alert('error', 'Validation Error', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
            'text' => $e->getMessage(),
        ]);
    }
    private function showErrorAlert($message)
    {
        $this->alert('error', $message, [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'departments' => Department::get(['id', 'name']),
            'roles' => Role::getRoles(),
            'levels' => StudentLevel::getLevels()
        ]);
    }
}