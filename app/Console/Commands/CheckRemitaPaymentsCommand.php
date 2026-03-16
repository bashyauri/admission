<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentTransaction;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\Log;
use App\Services\StudentTransactionService;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Builder;

class CheckRemitaPaymentsCommand extends Command
{
    protected $signature = 'remita:check-payments {--chunk=100 : Number of records to process per chunk} {--max=500 : Maximum records to process per run}';
    protected $description = 'Check Remita payment status for all pending transactions and update status';

    public function handle(): int
    {
        $chunkSize = max((int) $this->option('chunk'), 1);
        $maxPerRun = max((int) $this->option('max'), 1);
        $approvedCount = 0;

        $this->info("Checking Remita payment status (chunk={$chunkSize}, max={$maxPerRun})...");

        $processedCount = 0;

        $processedCount += $this->processPendingTransactions(
            StudentTransaction::query()
                ->where('status', '!=', TransactionStatus::APPROVED->value)
                ->whereNotNull('RRR')
                ->orderBy('id'),
            'student',
            $chunkSize,
            $maxPerRun - $processedCount,
            $approvedCount
        );

        if ($processedCount < $maxPerRun) {
            $processedCount += $this->processPendingTransactions(
                Transaction::query()
                    ->where('status', '!=', TransactionStatus::APPROVED->value)
                    ->whereNotNull('RRR')
                    ->orderBy('id'),
                'normal',
                $chunkSize,
                $maxPerRun - $processedCount,
                $approvedCount
            );
        }

        $this->info("Remita payment check completed. Processed {$processedCount}, approved {$approvedCount}.");
        return 0;
    }

    private function processPendingTransactions(Builder $query, string $type, int $chunkSize, int $maxRecords, int &$approvedCount): int
    {
        if ($maxRecords <= 0) {
            return 0;
        }

        $processedCount = 0;

        $query->select(['id', 'RRR', 'status'])
            ->chunkById($chunkSize, function ($transactions) use ($type, $maxRecords, &$processedCount, &$approvedCount) {
                foreach ($transactions as $transaction) {
                    $processedCount++;

                    if ($this->checkAndUpdate($transaction, $type)) {
                        $approvedCount++;
                    }

                    if ($processedCount >= $maxRecords) {
                        return false;
                    }
                }

                return true;
            });

        return $processedCount;
    }

    private function checkAndUpdate($transaction, string $type): bool
    {
        $rrr = $transaction->RRR;
        if (!$rrr) {
            return false;
        }

        // Use the correct service for each type
        $service = $type === 'student' ? app(StudentTransactionService::class) : app(TransactionService::class);

        // Call Remita API to check status
        $response = $service->getTransactionStatus($rrr);
        $status = $response->status ?? null;

        if ($status === TransactionStatus::APPROVED->value) {
            $transaction->status = TransactionStatus::APPROVED->value;
            $transaction->save();
            Log::info("Remita payment approved for RRR {$rrr} ({$type})");
            return true;
        }

        return false;
    }
}
