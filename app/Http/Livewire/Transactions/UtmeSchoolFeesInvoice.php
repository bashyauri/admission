<?php

declare(strict_types=1);

namespace App\Http\Livewire\Transactions;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Services\PaymentService;
use App\Models\StudentTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\AcademicSessionService;
use App\Services\StudentTransactionService;

class UtmeSchoolFeesInvoice extends Component
{
    #[Locked]
    public $user;
    #[Locked]
    public $currentLevel;
    #[Locked]
    public $description;
    #[Locked]
    public $amount;
    #[Locked]
    public $transactionId;

    private PaymentService $paymentService;
    private StudentTransactionService $transactionService;

    public function mount(User $user): void
    {

        $this->paymentService = new PaymentService();
        $this->transactionService = new StudentTransactionService();
        $this->user = $user;
        $this->description = config('remita.schoolfees.ug_schoolfees_description');

        $existingInvoice = $this->getExistingInvoice();

        if ($existingInvoice) {

            $this->redirectToPayment($existingInvoice);
        } else {


            $this->generateNewInvoice();
        }
    }

    private function getExistingInvoice(): ?StudentTransaction
    {
        return StudentTransaction::where('user_id', $this->user->id)
            ->where([
                'resource' => $this->description,
                'acad_session' => app(AcademicSessionService::class)->getAcademicSession(Auth::user())
            ])
            ->first();
    }

    private function redirectToPayment(StudentTransaction $transaction): void
    {
        if ($this->user->isStudent()) {
            to_route('student.ug-payment', ['studenttransaction' => $transaction])
                ->with('success', $transaction->status);
        } else {
            to_route('cit.payment', ['studenttransaction' => $transaction])
                ->with('success', $transaction->status);
        }
    }

    private function generateNewInvoice(): void
    {
        $this->currentLevel = $this->paymentService->getUgStudentLevel($this->user->id);
        $paymentDetail = $this->paymentService->getStudentFee($this->user->id);
        $this->amount = $paymentDetail->fee_amount;

        $this->transactionId = $this->transactionService->generateTransactionId("WUFPDHS");
    }

    public function render(): View
    {
        if (Auth::user()->isCit()) {
            return view('livewire.transactions.cit-school-fees-invoice');
        }
        return view('livewire.transactions.utme-school-fees-invoice');
    }
}
