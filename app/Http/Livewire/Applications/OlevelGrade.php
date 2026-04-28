<?php

namespace App\Http\Livewire\Applications;

use App\Models\Grade;
use App\Models\Subject;
use Livewire\Component;
use App\Models\OlevelExam;
use Livewire\Attributes\Computed;
use App\Models\OlevelSubjectGrade;
use App\Livewire\Forms\OlevelGradeForm;
use App\Services\PaymentService;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;

class OlevelGrade extends Component
{
    use LivewireAlert;
    public $showForm = false;
    public $successMessage = null;
    public OlevelGradeForm $form;

    public function mount(PaymentService $paymentService)
    {
        $user = auth()->user();
        if (!auth()->user()->hasPaid($paymentService->getAdmissionResource())) {
            to_route('transactions');
        }
        if (OlevelExam::where('user_id', auth()->id())->count() === 0) {


            to_route('olevel')->with('info', 'Please Select Olevel');
        }
    }
 public function save()
{
    try {
        $this->form->store();

        $this->alert('success', 'Saved Successfully', [
            'position' => 'center',
            'timer' => 1200,
            'toast' => true,
        ]);

        // Reset the form
        $this->form->reset();

        // Close modal
        $this->dispatch('close-modal');

        // Refresh the table
        $this->dispatch('$refresh');

    } catch (ValidationException $e) {
        $this->alert('error', implode('<br>', $e->validator->errors()->all()), [
            'position' => 'center',
            'timer' => 4000,
            'toast' => true,
        ]);
        $this->setErrorBag($e->validator->errors());
    } catch (\Exception $e) {
        report($e);
        $this->alert('error', 'Save failed. Please try again.', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }
}
    public function confirmed()
    {

        $this->redirect(route('olevel-grade'));
    }


    #[Computed()]
    public function exams()
    {
        return OlevelExam::where('user_id', auth()->user()->id)->get();
    }
    #[Computed()]
    public function subjects()
    {
        return Subject::all();
    }
    #[Computed()]
    public function grades()
    {
        return Grade::all();
    }
    #[Computed()]
    public function subjectGrades()
    {
        return OlevelSubjectGrade::where('user_id', auth()->user()->id)->latest()->get();
    }
    public function delete(OlevelSubjectGrade $subject)
    {

        try {
            $subject->delete();
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
        return view('livewire.applications.olevel-grade');
    }
}
