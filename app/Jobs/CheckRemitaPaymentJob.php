<?php

namespace App\Jobs;

use Throwable;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Models\StudentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PaymentService;
use App\Services\StudentTransactionService;
use App\Services\TransactionService;

class CheckRemitaPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 30;

    public function __construct(
        public string $type,
        public int $transactionId
    ) {
    }

    public function handle(StudentTransactionService $studentService, TransactionService $transactionService, PaymentService $paymentService): void
    {
        $transaction = $this->type === 'student'
            ? StudentTransaction::query()->find($this->transactionId)
            : Transaction::query()->find($this->transactionId);

        if (!$transaction || !$transaction->RRR || $transaction->status === TransactionStatus::APPROVED->value) {
            return;
        }

        try {
            $service = $this->type === 'student' ? $studentService : $transactionService;
            $response = $service->getTransactionStatus((string) $transaction->RRR);
            $status = $response->status ?? null;

            if ($status === TransactionStatus::APPROVED->value) {
                if ($this->type === 'student') {
                    $user = $transaction->load('user')->user;

                    if ($user && $user->isPostgraduate()) {
                        $studentService->updateTransactionStatus(TransactionStatus::APPROVED->value, (string) $transaction->RRR);
                    } else {
                        $paymentService->updateTransactionStatus(TransactionStatus::APPROVED->value, (string) $transaction->RRR);
                    }
                } else {
                    $transaction->update(['status' => TransactionStatus::APPROVED->value]);
                }
                Log::info("Remita payment approved for RRR {$transaction->RRR} ({$this->type})");
            }
        } catch (Throwable $exception) {
            Log::warning("Remita queue check failed for {$this->type} transaction {$this->transactionId}: " . $exception->getMessage());
            throw $exception;
        }
    }
}
