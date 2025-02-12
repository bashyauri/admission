<?php

declare(strict_types=1);

namespace App\Http\Livewire\Transactions;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Enums\TransactionStatus;
use App\Services\PaymentService;
use App\Models\StudentTransaction;
use Illuminate\Contracts\View\View;
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
    private $paymentService;
    private $transactionService;


    public function mount(User $user): void
    {
        $this->paymentService = new PaymentService();
        $this->transactionService = new StudentTransactionService();
        $this->user = $user;
        $this->description = config('remita.schoolfees.ug_schoolfees_description');





        if ($this->paymentService->hasInvoice($this->description, $this->user->id)) {
            $data =
                StudentTransaction::where('user_id', $this->user->id)
                ->where([
                    'resource' => config('remita.schoolfees.ug_schoolfees_description'),
                    'acad_session' => config('remita.settings.academic_session')
                ])
                ->first();
            if ($user->isStudent()) {
                to_route('student.ug-payment', ['studenttransaction' => $data])->with('success', $data->status);
            } else {
                to_route('cit.payment', ['studenttransaction' => $data])->with('success', $data->status);
            }
        } else {
            $this->currentLevel = $this->paymentService->getUgStudentLevel($this->user->id);
            $paymentDetail = $this->paymentService->getStudentFee($this->user->id);
            $this->amount = $paymentDetail->fee_amount;
            $this->transactionId = $this->transactionService->generateTransactionId("WUFPDHS");
        }
    }


    public function render(): View
    {
        return view('livewire.transactions.utme-school-fees-invoice');
    }
}