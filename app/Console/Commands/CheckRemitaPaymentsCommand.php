<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckRemitaPaymentJob;
use App\Models\StudentTransaction;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CheckRemitaPaymentsCommand extends Command
{
    protected $signature = 'remita:check-payments {--chunk=100 : Number of records to process per chunk} {--max=500 : Maximum records to process per run}';
    protected $description = 'Dispatch Remita payment check jobs for pending transactions';

    public function handle(): int
    {
        $chunkSize = max((int) $this->option('chunk'), 1);
        $maxPerRun = max((int) $this->option('max'), 1);
        $dispatchedCount = 0;

        $this->info("Dispatching Remita payment jobs (chunk={$chunkSize}, max={$maxPerRun})...");

        $processedCount = 0;

        $processedCount += $this->processPendingTransactions(
            StudentTransaction::query()
                ->where(function (Builder|QueryBuilder $query): void {
                    $query->where('status', '!=', TransactionStatus::APPROVED->value)
                        ->orWhereNull('status');
                })
                ->whereNotNull('RRR')
                ->where('RRR', '!=', '')
                ->orderBy('id'),
            'student',
            $chunkSize,
            $maxPerRun - $processedCount,
            $dispatchedCount
        );

        if ($processedCount < $maxPerRun) {
            $processedCount += $this->processPendingTransactions(
                Transaction::query()
                    ->where(function (Builder|QueryBuilder $query): void {
                        $query->where('status', '!=', TransactionStatus::APPROVED->value)
                            ->orWhereNull('status');
                    })
                    ->whereNotNull('RRR')
                    ->where('RRR', '!=', '')
                    ->orderBy('id'),
                'normal',
                $chunkSize,
                $maxPerRun - $processedCount,
                $dispatchedCount
            );
        }

        $this->info("Remita payment dispatch completed. Processed {$processedCount}, dispatched {$dispatchedCount}.");
        return 0;
    }

    private function processPendingTransactions(Builder|QueryBuilder $query, string $type, int $chunkSize, int $maxRecords, int &$dispatchedCount): int
    {
        if ($maxRecords <= 0) {
            return 0;
        }

        $processedCount = 0;

        $query->select(['id', 'RRR', 'status'])
            ->chunkById($chunkSize, function ($transactions) use ($type, $maxRecords, &$processedCount, &$dispatchedCount) {
                foreach ($transactions as $transaction) {
                    $processedCount++;

                    CheckRemitaPaymentJob::dispatch($type, (int) $transaction->id)->onQueue('remita');
                    $dispatchedCount++;

                    if ($processedCount >= $maxRecords) {
                        return false;
                    }
                }

                return true;
            });

        return $processedCount;
    }
}
