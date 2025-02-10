<?php

declare(strict_types=1);

namespace App\Http\Livewire\Coordinator;

use Livewire\Component;
use App\Enums\StudentLevel;
use App\Http\Livewire\Admin\Settings;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;
use App\Livewire\Forms\DepartmentLevelUnitsForm;

class DepartmentLevelUnits extends Component
{
    use LivewireAlert;
    public DepartmentLevelUnitsForm $form;

    public function addMaxUnit(): void
    {
        try {

            $this->form->store();;
            $this->alert('success', 'Updated Successfully', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (ValidationException $e) {


            // // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
            $this->alert('error', 'Validation Error', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'text' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Update failed.', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } finally {
            $this->reset('form');
        }
    }
    public function render()
    {
        return view('livewire.coordinator.department-level-units', ['levels' => StudentLevel::getLevels()]);
    }
}
