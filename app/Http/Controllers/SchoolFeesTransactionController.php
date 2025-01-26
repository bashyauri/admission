<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\Log;
use App\Services\StudentTransactionService;

class SchoolFeesTransactionController extends Controller
{
    public function __construct(protected StudentTransactionService $transactionService) {}
    public function index(StudentTransaction $studenttransaction)
    {




        $valuesToHash  = config('remita.settings.merchantid') . $studenttransaction->RRR . config('remita.settings.apikey');
        $studenttransaction['apiHash'] = hash('sha512', $valuesToHash);


        return view('payment.payment-slip')->with(json_decode($studenttransaction, true));
    }
    public function generateInvoice(Request $request)
    {

        $data = $request->only(['transactionId', 'amount', 'description', 'payerName', 'payerPhone', 'payerEmail', 'student_level_id']);


        $valuesToHash = config('remita.settings.merchantid') . config('remita.settings.serviceid') .
            $data['transactionId'] . $data['amount'] . config('remita.settings.apikey');
        $data['apiHash'] = hash('sha512', $valuesToHash);


        try {
            $response = $this->transactionService->generateInvoice($data);


            $data['RRR'] = $response->RRR;
            $data['statuscode'] = $response->statuscode;
            $data['status'] = $response->status;
            $studenttransaction = $this->transactionService->createPayment($data);





            return to_route('student.payment', ['studenttransaction' => $studenttransaction])->with('success', 'Remita Generated ' . $response->status);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong:');
        }
    }
    public function checkTransactionStatus($rrr)
    {



        try {
            $studenttransaction = StudentTransaction::where('RRR', $rrr)->first();


            $response = $this->transactionService->getTransactionStatus($rrr);

            $this->transactionService->updateTransactionStatus($response->status, $response->rrr);



            return to_route('student.payment', ['studenttransaction' => $studenttransaction])->with('info', $response->message);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: Try again later');
        }
    }
}
