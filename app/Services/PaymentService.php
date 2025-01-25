<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Models\SchoolFeesPayment;
use Illuminate\Support\Collection;

/**
 * Class PaymentService.
 */
class PaymentService
{
    public function getAdmissionResource(): string
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.description');
        } else {
            return config('remita.admission.description'); //for postgraduate
        }
    }
    public function getAcceptanceResource(): string
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.acceptance_description');
        } else {
            return config('remita.acceptance.description'); //for postgraduate
        }
    }
    public function getAcceptanceFee(): int
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.acceptance_fee');
        } else {
            return config('remita.acceptance.fee'); //for postgraduate
        }
    }

    public function getPaidAcceptanceFeePayments(): Collection
    {
        return Transaction::select('transactions.*', 'users.surname', 'users.firstname', 'users.m_name', 'users.jamb_no', 'users.phone')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where([
                'transactions.resource' => config('remita.postutme.acceptance_description'),
                'transactions.status' => TransactionStatus::APPROVED->toString()
            ])->get();
    }
    public function getUgStudentLevel(string $userId): int
    {
        return SchoolFeesPayment::where('user_id', $userId)->count() + 1;
    }
}
