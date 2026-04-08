<?php

namespace App\Services;


use App\Models\AcademicDetail;
use App\Models\User;
use App\Enums\TransactionStatus;
use App\Models\SchoolFeesPayment;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

/**
 * Class StudentTransactionService.
 */
class StudentTransactionService extends TransactionService
{

    public function generateInvoice(array $data, $customFields = [])
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
        return StudentTransaction::where(['user_id' => Auth::id(), 'resource' => $paymentType, 'acad_session' => app(AcademicSessionService::class)->getAcademicSession(Auth::user())])->exists();
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
                    'acad_session' => app(AcademicSessionService::class)->getAcademicSession(auth()->user())
                ]
            );
        }
    }
    public function updateTransactionStatus(string $status, string $rrr)
    {
        $transaction = StudentTransaction::where('RRR', $rrr)->firstOrFail();
        $transaction->update(['status' => $status]);

        if ($status !== TransactionStatus::APPROVED->value) {
            return;
        }

        $user = User::findOrFail($transaction->user_id);
        $student = AcademicDetail::where('user_id', $transaction->user_id)->firstOrFail();

        $currentAcademicSession = app(AcademicSessionService::class)->getAcademicSession($user);
        if ((string) $transaction->acad_session !== (string) $currentAcademicSession) {
            return;
        }

        $paidLevel = (int) $transaction->student_levels_id;
        if ($paidLevel <= 0) {
            return;
        }

        $totalPaid = StudentTransaction::where([
            'user_id' => $student->user_id,
            'student_levels_id' => $paidLevel,
            'status' => TransactionStatus::APPROVED,
        ])->sum('amount');

        $totalLevelAmount = $this->getSchoolFees($student->department_id, $paidLevel);

        if ($totalLevelAmount !== null && $totalPaid >= $totalLevelAmount) {
            $this->addSchoolFeesPayment((string) $transaction->user_id, $paidLevel, (string) $transaction->acad_session);
        }
    }

    public function addSchoolFeesPayment(string $userId, int $level, string $acadSession): void
    {
        DB::transaction(function () use ($userId, $level, $acadSession) {
            SchoolFeesPayment::firstOrCreate([
                'user_id' => $userId,
                'student_level_id' => $level,
            ]);
            $this->updateStudentLevel($userId, $level, $acadSession);
        });
    }

    private function updateStudentLevel(string $userId, int $level, string $acadSession): void
    {
        AcademicDetail::where('user_id', $userId)->update([
            'student_level_id' => $level,
            'acad_session'     => $acadSession,
        ]);
    }
}
