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
    public function index()
    {
        $data = Transaction::where([
            'user_id' => auth()->user()->id,
            'resource' => config('remita.admission.description')
        ])->first();


        return view('payment.payment-slip')->with(json_decode($data, true));
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
            $this->transactionService->createPayment($data);


            return redirect()->route('admission-invoice')->with('success', $response->status);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->withErrors(['msgError' => 'Something went wrong:' . $response->status]);
        }
    }
    public function checkTransactionStatus($rrr)
    {


        try {


            $response = $this->transactionService->getTransactionStatus($rrr);

            $this->transactionService->updateTransactionStatus($response->status, $response->RRR);

            // return view('nds.payment')->with($data);

            return redirect()->route('invoice')->with('success', $response->message);
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->withErrors(['msgError' => 'Something went wrong:' . $response->message]);
        }
    }
}
