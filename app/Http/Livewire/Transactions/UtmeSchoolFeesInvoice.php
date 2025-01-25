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
    public  $user;
    #[Locked]
    public  $currentLevel;
    #[Locked]
    public $description;
    private $paymentService;
    private $transactionService;

    public function mount(User $user): void
    {
        $this->paymentService = new PaymentService();
        $this->transactionService = new StudentTransactionService();
        $this->user = $user;
        $this->description = config('remita.schoolfees.ug_schoolfees_description');



        if ($this->transactionService->hasInvoice($this->description)) {
            $data =
                StudentTransaction::where('user_id', $this->user->id)
                ->where('resource', $this->description)
                ->where('status', '!=', TransactionStatus::APPROVED)
                ->first();

            to_route('student.payment', ['studenttransaction' => $data])->with('success', $data->status);
        }

        $this->currentLevel = $this->paymentService->getUgStudentLevel($this->user->id);
    }

    public function render(): View
    {
        return view('livewire.transactions.utme-school-fees-invoice');
    }
}
