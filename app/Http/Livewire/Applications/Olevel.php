<?php

namespace App\Http\Livewire\Applications;

use App\Models\School;
use Livewire\Component;
use App\Models\OlevelExam;
use App\Services\PaymentService;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\OlevelExamForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;

class Olevel extends Component
{
    use LivewireAlert;

    public OlevelExamForm $form;
    public $editingOlevelExamId;

    public function mount(PaymentService $paymentService)
    {
        $user = auth()->user();
        if (!auth()->user()->hasPaid($paymentService->getAdmissionResource())) {
            to_route('transactions');
        }
        if ($user->isPostgraduate()) {
            if (School::where('user_id', auth()->id())->count() < 2) {

                to_route('school-attended')->with('info', 'Please Select School Attended');
            }
        }
    }
    public function save()
    {

        try {

            $this->form->store();
            $this->alert('success', 'Saved Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            to_route('olevel');
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
            $this->redirect(route('olevel'));
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return to_route('olevel');
        }
    }
    public function confirmed()
    {

        $this->redirect(route('olevel'));
    }


    #[Computed()]
    public function olevelExams()
    {
        return OlevelExam::where('user_id', auth()->user()->id)->get();
    }

    public function edit($examId)
    {
        $this->editingOlevelExamId = $examId;
        $exam = OlevelExam::findOrFail($examId);
        $this->form->examName = $exam->exam_name;
        $this->form->examNumber = $exam->exam_name;
        $this->form->examYear = $exam->exam_year;
    }
    public function update()
    {
        try {

            $this->form->update($this->editingOlevelExamId);
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

    public function cancelEdit()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.applications.olevel');
    }
}