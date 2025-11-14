<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\Log;
use App\Services\TransactionService;
use App\Services\StudentTransactionService;

class UgSchoolFeesController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}
    public function index(?StudentTransaction $studenttransaction)
    {



        $valuesToHash  = config('remita.settings.merchantid') . $studenttransaction->RRR . config('remita.settings.apikey');
        $studenttransaction->apiHash = hash('sha512', $valuesToHash);
        $studenttransaction->user = User::where('id', $studenttransaction->user_id)->first();


        return view('payment.ugpayment-slip')->with(['studenttransaction' => $studenttransaction]);
    }
    public function generateInvoice(Request $request)
    {


        $data = $request->only(['userId', 'transactionId', 'amount', 'description', 'payerName', 'payerPhone', 'payerEmail', 'student_level_id']);


        $valuesToHash = config('remita.settings.merchantid') . config('remita.settings.serviceid') .
            $data['transactionId'] . $data['amount'] . config('remita.settings.apikey');
        $data['apiHash'] = hash('sha512', $valuesToHash);



        try {
            $customFields = $this->paymentService->getSchoolFeesCustomFields($data['userId']);

            $response = $this->paymentService->generateInvoice($data, $customFields);


            $data['RRR'] = $response->RRR;
            $data['statuscode'] = $response->statuscode;
            $data['status'] = $response->status;

            $studenttransaction = $this->paymentService->createPayment($data);





            return to_route('cit.payment', ['studenttransaction' => $studenttransaction])->with('success', 'Remita Generated ' . $response->status);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong:');
        }
    }
    // public function checkTransactionStatus($rrr, StudentTransactionService $service)
    // {



    //     try {
    //         $studenttransaction = StudentTransaction::where('RRR', $rrr)->first();


    //         $response = $service->getTransactionStatus($rrr);

    //         $this->paymentService->updateTransactionStatus($response->status, $response->rrr);



    //         return to_route('admin.payment', ['studenttransaction' => $studenttransaction])->with('info', $response->message);
    //     } catch (\Exception $ex) {
    //         Log::alert($ex->getMessage());
    //         return redirect()->back()->with('error', 'Something went wrong: Try again later');
    //     }
    // }
    public function checkTransactionStatus($rrr, TransactionService $service)
    {
        try {
            // 1️⃣ Try to find in StudentTransaction
            $studentTransaction = StudentTransaction::where('RRR', $rrr)->first();

            // 2️⃣ If not found, fallback to Transaction table
            if (!$studentTransaction) {
                $studentTransaction = Transaction::where('RRR', $rrr)->first();

                if (!$studentTransaction) {
                    return redirect()->back()->with('error', 'Transaction with this RRR not found.');
                }
            }

            // 3️⃣ Get transaction status from service
            $response = $service->getTransactionStatus($rrr);

            // 4️⃣ Update status
            $this->paymentService->updateTransactionStatus($response->status, $response->rrr);

            // 5️⃣ Redirect to appropriate page (admin example)
            return to_route('cit.payment', ['studenttransaction' => $studentTransaction])
                ->with('info', $response->message);
        } catch (\Exception $ex) {
            Log::error('Transaction check failed: ' . $ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: Try again later.');
        }
    }
}
