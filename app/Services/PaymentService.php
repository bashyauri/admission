<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\FeeStructure;
use App\Enums\TransactionStatus;
use App\Models\SchoolFeesPayment;
use App\Models\StudentTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

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
    public function getAcceptanceFee(): string
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.acceptance_fee');
        } else {
            return config('remita.acceptance.fee'); //for postgraduate
        }
    }
    public function getSchoolFeesResource(): string
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.schoolfees.ug_schoolfees_description');
        } else {
            return config('remita.schoolfees.description'); //for postgraduate
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
    public function hasInvoice(string $paymentType, string $userId)
    {
        return StudentTransaction::where(['user_id' => $userId, 'resource' => $paymentType, 'acad_session' => config('remita.settings.academic_session')])->exists();
    }
    public function getUgStudentLevel(string $userId): int
    {
        return SchoolFeesPayment::where('user_id', $userId)->count() + 1;
    }
    public function getStudentFee(string $userId): FeeStructure
    {
        $student = User::find($userId);
        if ($student->isApplicant()) {
            $departmentId = $student->proposedCourse->department_id;
            $programmeId = $student->programme_id;
            $level = $this->getUgStudentLevel($student->id);
        } else {
            // Student that are not freshers (100,or 200 level DE)
            $departmentId = $student->academicDetail->department_id;
            $programmeId = $student->programme_id;
            $level = $student->proposedCourse->student_levels_id;
        }
        return  FeeStructure::where(['department_id' => $departmentId, 'programme_id' => $programmeId, 'student_level_id' => $level])->first();
    }
    public function getStudentInvoice(string $studentId, string $paymentType): StudentTransaction |null
    {

        return StudentTransaction::where('user_id', $studentId)
            ->where('resource', $paymentType)
            ->where('status', '!=', TransactionStatus::APPROVED->value)
            ->first();
    }
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
                "description" => $data['description'],
                "customFields" => $customFields


            ]);

        return TransactionService::convertJsonToArray($response->body());
    }
    public function createPayment($data)
    {
        $values = $this->generateInvoice($data);
        if (!empty($values)) {
            return  StudentTransaction::create(
                [
                    'transaction_id' => $data['transactionId'],
                    'user_id' => $data['userId'],
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

        StudentTransaction::where('RRR', $rrr)->update(['status' => $status]);
    }
    public function getSchoolFeesCustomFields($userId): array
    {

        $user = User::find($userId);
        return
            [
                [
                    "name" => "Academic Session",
                    "value" => config('remita.settings.academic_session'),
                    "type" => "ALL",
                ],
                [
                    "name" => "Course",
                    "value" => $user->proposedCourse->course->name,
                    "type" => "ALL",
                ],
                [
                    "name" => "Department",
                    "value" => $user->proposedCourse->department->name,
                    "type" => "ALL",
                ],
                [
                    "name" => "Payment",
                    "value" => config('remita.schoolfees.ug_schoolfees_description'),
                    "type" => "ALL",
                ],



            ];
    }
    public function getStudentPayments(string $studentId): Collection
    {
        return StudentTransaction::where('user_id', $studentId)->get();
    }
}