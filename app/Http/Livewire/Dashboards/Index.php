<?php

namespace App\Http\Livewire\Dashboards;

use App\Livewire\Forms\AcademicDetailForm;
use App\Models\User;
use Livewire\Component;
use App\Models\Transaction;
use App\Services\PaymentService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;

class Index extends Component
{
    public AcademicDetailForm $form;

    public function mount(PaymentService $paymentService)
    {
        if (!auth()->user()->hasPaid($paymentService->getAdmissionResource())) {
            to_route('transactions');
        }
    }
    public function addStudent()
    {
        if (auth()->user()->isPosgraduate()) {
            $this->form->store();
            $this->redirect(route('student.dashboard'));
        }
    }
    #[Computed(persist: true)]
    public function transactions()
    {
        return Transaction::where(['user_id' => auth()->id()])->get();
    }
    public function render()
    {

        return view('livewire.dashboards.index');
    }
}
