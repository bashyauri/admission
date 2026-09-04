<?php

namespace App\Http\Livewire\Transactions;

use App\Enums\TransactionStatus;

use App\Models\StudentTransaction;
use App\Services\StudentTransactionService;
use Livewire\Component;


use App\Services\AcademicSessionService;

class SchoolFeesInvoice extends Component
{
    public $transactionId;
    public $amount = 0;
    public $description;
    public $serviceid;
    protected $transactionService;
    public $student;
    public $selectedInstallment;
    public $nextLevel;
    public $message;
    public $totalLevelAmount = 0;
    public $totalPaid = 0;
    public $remainingBalance = 0;

    public function mount(): void
    {
        $this->transactionService = new StudentTransactionService();

        $existingInvoice = StudentTransaction::where('user_id', auth()->id())
            ->where('resource', config('remita.schoolfees.description'))
            ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession(auth()->user()))
            ->where('status', '!=', TransactionStatus::APPROVED->value)
            ->latest()
            ->first();

        if ($existingInvoice && $existingInvoice->id) {
            to_route('student.payment', ['studenttransaction' => $existingInvoice->id])->with('success', $existingInvoice->status);
            return;
        }

        $this->student = auth()->user()->academicDetail;
        $this->nextLevel = $this->transactionService->getLevelToPay($this->student->department_id);

        $this->totalLevelAmount = (float) ($this->transactionService->getSchoolFees($this->student->department_id, $this->nextLevel) ?? 0);
        $this->totalPaid = (float) StudentTransaction::where([
            'user_id' => auth()->id(),
            'student_levels_id' => $this->nextLevel,
            'status' => TransactionStatus::APPROVED->value
        ])->sum('amount');
        $this->remainingBalance = max(0, $this->totalLevelAmount - $this->totalPaid);
    }

    public function updatedSelectedInstallment(StudentTransactionService $service): void
    {
        $this->message = '';
        $this->transactionId = $service->generateTransactionId("WUFPDHS");

        $this->totalLevelAmount = (float) ($service->getSchoolFees($this->student->department_id, $this->nextLevel) ?? 0);
        
        if ($this->selectedInstallment === 'balance') {
            $this->amount = $this->remainingBalance;
        } elseif (is_numeric($this->selectedInstallment)) {
            $val = (float) $this->selectedInstallment;
            if ($val == 1) {
                // If student has already paid part fees, full payment means paying the remaining balance
                $this->amount = $this->remainingBalance > 0 ? $this->remainingBalance : $this->totalLevelAmount;
            } else {
                $this->amount = $val * $this->totalLevelAmount;
            }
        } else {
            $this->amount = 0;
        }

        $this->validatePayment($this->totalLevelAmount);
        $this->description = config('remita.schoolfees.description');
        $this->serviceid = config('remita.settings.serviceid');
    }

    public function validatePayment($totalLevelAmount): void
    {
        $this->totalPaid = (float) StudentTransaction::where([
            'user_id' => auth()->id(),
            'student_levels_id' => $this->nextLevel,
            'status' => TransactionStatus::APPROVED->value
        ])->sum('amount');

        $this->remainingBalance = max(0, $totalLevelAmount - $this->totalPaid);

        if ($this->remainingBalance <= 0) {
            $this->amount = 0;
            $this->message = 'You have already completed full school fees payment for this level.';
            return;
        }

        // Rule: Must pay 70% first before paying 30%
        if ($this->totalPaid == 0 && (string)$this->selectedInstallment === '0.3') {
            $this->amount = 0;
            $this->message = 'Payment failed! You must pay the 70% First Installment (or Full Payment) before paying the 30% Second Installment.';
            return;
        }

        // Rule: If 70% is already paid, student cannot select 70% again
        if ($this->totalPaid > 0 && (string)$this->selectedInstallment === '0.7') {
            $this->amount = 0;
            $this->message = 'You have already paid the 70% First Installment. Please select Second Installment (30%) or Remaining Balance.';
            return;
        }

        $currentAmount = $this->totalPaid + $this->amount;

        // Check if the student does not overpay
        if (round($currentAmount, 2) > round($totalLevelAmount, 2)) {
            $this->amount = 0;
            $this->message = 'Payment failed! Your selected payment exceeds the required remaining amount (Remaining: ' . config('remita.currency') . ' ' . number_format($this->remainingBalance, 2) . ').';
        }
    }

    public function render()
    {
        return view('livewire.transactions.school-fees-invoice');
    }
}
