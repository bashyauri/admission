<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentService;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService) {}

    public function generateAcceptanceInvoice(Request $request, PaymentService $paymentService)
    {
        $this->authorize('generateAcceptanceInvoice', User::class);

        $request->merge([
            'amount' => $paymentService->getAcceptanceFee(),
            'description' => $paymentService->getAcceptanceResource(),
        ]);

        return $this->generateInvoice($request);
    }

    public function index(?Transaction $transaction)
    {
        if (!$transaction || !$transaction->RRR) {
            return redirect()->route('transactions')->with('error', 'This invoice has no Remita RRR. Generate the invoice again.');
        }

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


            $data['RRR'] = $response->RRR ?? null;
            $data['statuscode'] = $response->statuscode ?? null;
            $data['status'] = $response->status ?? null;

            if (empty($data['RRR'])) {
                Log::warning('Remita invoice response did not contain an RRR.', [
                    'transaction_id' => $data['transactionId'] ?? null,
                    'status' => $data['status'] ?? null,
                    'statuscode' => $data['statuscode'] ?? null,
                ]);

                if (($data['status'] ?? null) === 'DUPLICATE_REQUEST') {
                    return redirect()->back()->with('error', 'Remita rejected this request as a duplicate. No invoice was recorded; reload the page and try again to generate a new request.');
                }

                return redirect()->back()->with('error', 'Remita could not generate an RRR. No invoice was saved. Please try again.');
            }

            $transaction = $this->transactionService->createPayment($data);

            if (!$transaction || !$transaction->id) {
                return redirect()->back()->with('error', 'Failed to record transaction.');
            }

            return to_route('payment', ['transaction' => $transaction->id])->with('success', 'Remita Generated ' . ($response->status ?? ''));
        } catch (\Exception $ex) {
            Log::error('Remita invoice generation failed.', [
                'transaction_id' => $data['transactionId'] ?? null,
                'exception' => $ex->getMessage(),
            ]);
            return redirect()->back()->with('error', 'The invoice could not be generated. Please try again.');
        }
    }
    public function checkTransactionStatus($rrr = null)
    {
        $rrr = $rrr ?? request()->query('RRR') ?? request()->query('rrr');

        if (!$rrr) {
            return redirect()->back()->with('error', 'Invalid Transaction Reference');
        }

        try {
            $transaction = Transaction::where('RRR', $rrr)->first();

            if (!$transaction) {
                return redirect()->back()->with('error', 'Transaction not found.');
            }

            $response = $this->transactionService->getTransactionStatus($rrr);

            if (in_array($response->status, [\App\Enums\TransactionStatus::APPROVED->value, \App\Enums\TransactionStatus::ACTIVATED->value])) {
                if (isset($response->amount) && $response->amount >= $transaction->amount) {
                    $this->transactionService->updateTransactionStatus(\App\Enums\TransactionStatus::APPROVED->value, $response->rrr);
                    return to_route('payment', ['transaction' => $transaction])->with('success', 'Payment successful!');
                } else {
                    $this->transactionService->updateTransactionStatus('PARTIAL_PAYMENT', $response->rrr);
                    Log::alert("Underpayment detected for RRR: {$rrr}. Expected: {$transaction->amount}, Paid: " . ($response->amount ?? 0));
                    return to_route('payment', ['transaction' => $transaction])->with('error', 'Incomplete payment amount detected.');
                }
            }

            $this->transactionService->updateTransactionStatus($response->status, $response->rrr);

            return to_route('payment', ['transaction' => $transaction])->with('info', $response->message ?? 'Transaction pending or failed.');
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: Try again later');
        }
    }
}
