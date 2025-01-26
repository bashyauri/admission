<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\StudentTransaction;
use App\Models\User;
use App\Services\TransactionService;

class UgSchoolFeesController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}
    public function index(StudentTransaction $studenttransaction)
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
    public function checkTransactionStatus($rrr, TransactionService $service)
    {



        try {
            $studenttransaction = StudentTransaction::where('RRR', $rrr)->first();


            $response = $service->getTransactionStatus($rrr);

            $service->updateTransactionStatus($response->status, $response->rrr);



            return to_route('cit.payment', ['studenttransaction' => $studenttransaction])->with('info', $response->message);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: Try again later');
        }
    }
}
