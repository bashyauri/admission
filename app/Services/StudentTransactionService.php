<?php

namespace App\Services;


use App\Models\AcademicDetail;
use App\Enums\TransactionStatus;
use App\Models\SchoolFeesPayment;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

/**
 * Class StudentTransactionService.
 */
class StudentTransactionService extends TransactionService
{

    public function generateInvoice(array $data)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'remitaConsumerKey=' . config('remita.settings.merchantid') . ',remitaConsumerToken=' . $data['apiHash']
        ])
            ->post(config('remita.settings.invoice_url'), [
                "serviceTypeId" => config('remita.settings.serviceid'),
                "amount" => $data['amount'],
                "orderId" => $data['transactionId'],
                "payerName" => $data['payerName'],
                "payerEmail" => $data['payerEmail'],
                "payerPhone" => $data['payerPhone'],
                "description" => $data['description']
            ]);

        return TransactionService::convertJsonToArray($response->body());
    }
    public function getSchoolFees($department, $level)
    {
        $fees = Config::get('schoolfees');

        if (!isset($fees[$department]) || !isset($fees[$department][$level])) {
            return null;
        }

        return (float) $fees[$department][$level];
    }
    public function getLevelToPay($departmentId)
    {
        $fees = Config::get('schoolfees');
        $levelCount = SchoolFeesPayment::where('user_id', auth()->user()->id)->count() + 1;


        foreach ($fees[$departmentId] as $level => $amount) {
            if ($level == $levelCount) {
                return $level;
            }
        }

        // If the levelCount is not found, return the highest level
        return max(array_keys($fees[$departmentId]));
    }
    public function hasInvoice(string $paymentType)
    {
        return StudentTransaction::where('user_id', auth()->id())
            ->where('resource', $paymentType)
            ->where('status', '!=', TransactionStatus::APPROVED)
            ->first();
    }
    public function createPayment($data)
    {
        $values = $this->generateInvoice($data);
        if (!empty($values)) {
            return  StudentTransaction::create(
                [
                    'transaction_id' => $data['transactionId'],
                    'user_id' => auth()->user()->id,
                    'student_levels_id' => $data['student_level_id'],
                    'amount' => $data['amount'],
                    'date' => now(),
                    'status' => $data['statuscode'],
                    'resource' => $data['description'],
                    'RRR' => $data['RRR'],
                    'acad_session' => config('remita.settings.academic_session')
                ]
            );
        }
    }
    public function updateTransactionStatus(string $status, string $rrr)
    {
        // Check if transaction exists and update status if it does
        $transaction = StudentTransaction::where('RRR', $rrr)->firstOrFail();
        $transaction->update(['status' => $status]);

        // If transaction is approved, check if it's a valid payment and add to school fees if necessary
        if ($status !== TransactionStatus::APPROVED) {
            return;
        }

        $student = AcademicDetail::where('user_id', auth()->id())->firstOrFail();

        $totalPaid = StudentTransaction::where([
            'user_id' => $student->user_id,
            'student_levels_id' => $student->student_level_id,
            'status' => TransactionStatus::APPROVED,
        ])->sum('amount');

        // Check if the total amount paid is equal to or more than the required amount for the current level
        $totalLevelAmount = $this->getSchoolFees($student->department_id, $transaction->student_levels_id);

        if ($totalPaid >= $totalLevelAmount) {
            $this->addSchoolFeesPayment($transaction->student_levels_id);
        }
    }


    public function addSchoolFeesPayment($level)
    {
        DB::transaction(function () use ($level) {
            SchoolFeesPayment::create([
                'user_id' => auth()->id(),
                'student_level_id' => $level,
            ]);
            $this->updateStudentLevel($level);
        });
    }
    private function updateStudentLevel($level)
    {
        auth()->user()->AcademicDetail->update(['student_level_id' => $level]);
    }
}
