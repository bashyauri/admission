<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
    }
    public function index(?Transaction $transaction)
    {

        $valuesToHash  = config('remita.settings.merchantid') . $transaction->RRR . config('remita.settings.apikey');
        $transaction['apiHash'] = hash('sha512', $valuesToHash);


        return view('payment.payment-slip')->with(json_decode($transaction, true));
    }
    public function generateInvoice(Request $request)
    {

        $data = $request->only(['transactionId', 'amount', 'description', 'payerName', 'payerPhone', 'payerEmail']);


        $valuesToHash = config('remita.settings.merchantid') . config('remita.settings.serviceid') .
            $data['transactionId'] . $data['amount'] . config('remita.settings.apikey');
        $data['apiHash'] = hash('sha512', $valuesToHash);


        try {
            $response = $this->transactionService->generateInvoice($data);


            $data['RRR'] = $response->RRR;
            $data['statuscode'] = $response->statuscode;
            $data['status'] = $response->status;
            $transaction = $this->transactionService->createPayment($data);



            return to_route('payment', ['transaction' => $transaction])->with('success', 'Remita Generated ', $response->status);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong:');
        }
    }
    public function checkTransactionStatus($rrr)
    {



        try {
            $transaction = Transaction::where('RRR', $rrr)->first();


            $response = $this->transactionService->getTransactionStatus($rrr);

            $this->transactionService->updateTransactionStatus($response->status, $response->RRR);



            return to_route('payment', ['transaction' => $transaction])->with('info', $response->message);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: Try again later');
        }
    }
}