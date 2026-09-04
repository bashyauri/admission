<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentTransaction;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use App\Services\StudentTransactionService;

class SchoolFeesTransactionController extends Controller
{
    public function __construct(protected StudentTransactionService $transactionService, protected PaymentService $paymentService) {}
    public function index(?StudentTransaction $studenttransaction)
    {




        $valuesToHash  = config('remita.settings.merchantid') . $studenttransaction->RRR . config('remita.settings.apikey');
        $studenttransaction['apiHash'] = hash('sha512', $valuesToHash);


        return view('payment.payment-slip')->with(json_decode($studenttransaction, true));
    }
    public function generateInvoice(Request $request)
    {

        $data = $request->only(['userId','transactionId', 'amount', 'description', 'payerName', 'payerPhone', 'payerEmail', 'student_level_id']);


        $valuesToHash = config('remita.settings.merchantid') . config('remita.settings.serviceid') .
            $data['transactionId'] . $data['amount'] . config('remita.settings.apikey');
        $data['apiHash'] = hash('sha512', $valuesToHash);


        try {
             $customFields = $this->paymentService->getSchoolFeesCustomFields($data['userId']);
             $response = $this->paymentService->generateInvoice($data, $customFields);
            //  $response = $this->paymentService->generateInvoice($data);


            $data['RRR'] = $response->RRR ?? null;
            $data['statuscode'] = $response->statuscode ?? null;
            $data['status'] = $response->status ?? null;
            $studenttransaction = $this->transactionService->createPayment($data);

            if (!$studenttransaction || !$studenttransaction->id) {
                return redirect()->back()->with('error', 'Failed to record transaction.');
            }

            return to_route('student.payment', ['studenttransaction' => $studenttransaction->id])->with('success', 'Remita Generated ' . ($response->status ?? ''));
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong:');
        }
    }
    public function checkTransactionStatus($rrr = null)
    {
        $rrr = $rrr ?? request()->query('RRR') ?? request()->query('rrr');

        if (!$rrr) {
            return redirect()->back()->with('error', 'Invalid Transaction Reference');
        }

        try {

            $studentTransaction = StudentTransaction::where('RRR', $rrr)->first();

            if (!$studentTransaction || !$studentTransaction->id) {
                return redirect()->back()->with('error', 'Transaction not found.');
            }


            $response = $this->transactionService->getTransactionStatus($rrr);


            $isPostgraduate = $studentTransaction->load('user')->user->isPostgraduate();
            $service = $isPostgraduate ? $this->transactionService : $this->paymentService;

            if (in_array($response->status, [\App\Enums\TransactionStatus::APPROVED->value, \App\Enums\TransactionStatus::ACTIVATED->value])) {
                if (isset($response->amount) && $response->amount >= $studentTransaction->amount) {
                    $service->updateTransactionStatus(\App\Enums\TransactionStatus::APPROVED->value, $response->rrr);
                    return to_route('student.payment', ['studenttransaction' => $studentTransaction->id])->with('success', 'Payment successful!');
                } else {
                    $service->updateTransactionStatus('PARTIAL_PAYMENT', $response->rrr);
                    Log::alert("Underpayment detected for RRR: {$rrr}. Expected: {$studentTransaction->amount}, Paid: " . ($response->amount ?? 0));
                    return to_route('student.payment', ['studenttransaction' => $studentTransaction->id])->with('error', 'Incomplete payment amount detected.');
                }
            }

            $service->updateTransactionStatus($response->status, $response->rrr);


            return to_route('student.payment', ['studenttransaction' => $studentTransaction->id])
                ->with('info', $response->message ?? 'Transaction pending or failed.');
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: Try again later');
        }
    }
}
