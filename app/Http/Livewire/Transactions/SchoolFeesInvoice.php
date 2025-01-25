<?php

namespace App\Http\Livewire\Transactions;

use App\Enums\TransactionStatus;

use App\Models\StudentTransaction;
use App\Services\StudentTransactionService;
use Livewire\Component;


class SchoolFeesInvoice extends Component
{
    public $transactionId;
    public $amount;
    public $description;
    public $serviceid;
    protected $transactionService;
    public $student;
    public $selectedInstallment;
    public $nextLevel;
    public $message;
    public function mount(): void
    {


        $this->transactionService = new StudentTransactionService();

        if ($this->transactionService->hasInvoice(config('remita.schoolfees.description'))) {
            $data =
                StudentTransaction::where('user_id', auth()->id())
                ->where('resource', config('remita.schoolfees.description'))
                ->where('status', '!=', TransactionStatus::APPROVED)
                ->first();

            to_route('student.payment', ['studenttransaction' => $data])->with('success', $data->status);
        }
        $this->student = auth()->user()->academicDetail;
        $this->nextLevel = $this->transactionService->getLevelToPay($this->student->department_id);
    }
    public function updatedSelectedInstallment(StudentTransactionService $service): void
    {

        $this->message = '';
        $this->transactionId = $service->generateTransactionId("WUFPDHS");

        $totalLevelAmount = $service->getSchoolFees($this->student->department_id, $this->nextLevel);
        $this->amount =  $this->selectedInstallment * $service->getSchoolFees($this->student->department_id, $this->nextLevel);
        $this->validatePayment($totalLevelAmount);
        $this->description = config('remita.schoolfees.description');
        $this->serviceid = config('remita.settings.serviceid');
    }
    public function validatePayment($totalLevelAmount): void
    {


        $totalPaid = StudentTransaction::where(['user_id' => auth()->id(), 'student_levels_id' => $this->student->student_level_id, 'status' => TransactionStatus::APPROVED])->sum('amount');

        $currentAmount = $totalPaid + $this->amount;



        // Check if the student does not overpay
        if ($currentAmount > $totalLevelAmount) {
            $this->amount = 0;
            $this->message = 'Payment failed! Your total payment exceeds the required amount.';
        }
    }

    public function render()
    {
        return view('livewire.transactions.school-fees-invoice');
    }
}
