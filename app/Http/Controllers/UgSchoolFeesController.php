<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\Log;
use App\Services\TransactionService;

class UgSchoolFeesController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(?StudentTransaction $studenttransaction)
    {
        $valuesToHash = config('remita.settings.merchantid') . $studenttransaction->RRR . config('remita.settings.apikey');
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
            $this->paymentService->ensureStudentCanGenerateSchoolFeeInvoice($data['userId'] ?? null);
            $customFields = $this->paymentService->getSchoolFeesCustomFields($data['userId']);

            $response = $this->paymentService->generateInvoice($data, $customFields);

            $data['RRR'] = $response->RRR;
            $data['statuscode'] = $response->statuscode;
            $data['status'] = $response->status;

            $studenttransaction = $this->paymentService->createPayment($data);

            if (!$studenttransaction || !$studenttransaction->id) {
                return redirect()->back()->with('error', 'Failed to record transaction.');
            }

            return to_route('cit.payment', ['studenttransaction' => $studenttransaction->id])->with('success', 'Remita Generated ' . ($response->status ?? ''));
        } catch (\Exception $ex) {
            Log::alert($ex->getMessage());
            if ($ex instanceof \InvalidArgumentException) {
                return redirect()->back()->with('error', $ex->getMessage());
            }
            return redirect()->back()->with('error', 'Something went wrong:');
        }
    }

    public function checkTransactionStatus(TransactionService $service, ?string $rrr = null)
    {
        $rrr = $rrr ?? request()->query('RRR') ?? request()->query('rrr');

        if (!$rrr) {
            return redirect()->back()->with('error', 'Invalid Transaction Reference.');
        }

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
            $response = $service->getTransactionStatus((string) $rrr);
            $verifiedRrr = (string) ($response->RRR ?? $response->rrr ?? $studentTransaction->RRR ?? $rrr);
            $responseStatus = (string) ($response->status ?? $response->statuscode ?? '');

            $isApproved = in_array($responseStatus, [
                \App\Enums\TransactionStatus::APPROVED->value,
                \App\Enums\TransactionStatus::ACTIVATED->value,
                '00',
                '01',
            ], true);

            if ($isApproved) {
                if (isset($response->amount) && (float) $response->amount < (float) $studentTransaction->amount) {
                    $newStatus = 'PARTIAL_PAYMENT';
                    Log::alert("Underpayment detected for RRR: {$verifiedRrr}. Expected: {$studentTransaction->amount}, Paid: " . ($response->amount ?? 0));

                    $this->persistTransactionStatus($studentTransaction, $newStatus, $verifiedRrr, $service);

                    return $this->redirectAfterVerification($studentTransaction, 'error', 'Incomplete payment amount detected.');
                }

                $newStatus = \App\Enums\TransactionStatus::APPROVED->value;
                $this->persistTransactionStatus($studentTransaction, $newStatus, $verifiedRrr, $service);

                return $this->redirectAfterVerification($studentTransaction, 'success', 'Payment verified and approved successfully!');
            }

            $newStatus = $responseStatus !== '' ? $responseStatus : '025';
            $this->persistTransactionStatus($studentTransaction, $newStatus, $verifiedRrr, $service);

            return $this->redirectAfterVerification(
                $studentTransaction,
                'info',
                $response->message ?? 'Transaction pending or not completed.'
            );
        } catch (\Exception $ex) {
            Log::error('Transaction check failed: ' . $ex->getMessage());

            $fallbackRoute = (auth()->user()?->role === 'admin') ? 'admin.first-school-fees' : 'cit.first-school-fees';
            $previousUrl = url()->previous();
            if ($previousUrl && $previousUrl !== url()->current()) {
                return redirect()->to($previousUrl)->with('error', 'Something went wrong: Try again later.');
            }

            return to_route($fallbackRoute)->with('error', 'Something went wrong: Try again later.');
        }
    }

    protected function persistTransactionStatus($transaction, string $status, string $rrr, TransactionService $service): void
    {
        $transaction->update(['status' => $status]);

        if ($transaction instanceof StudentTransaction) {
            $this->paymentService->updateTransactionStatus($status, $rrr);
        } else {
            $service->updateTransactionStatus($status, $rrr);
        }
    }

    protected function redirectAfterVerification($studentTransaction, string $flashType, string $message)
    {
        $user = auth()->user();

        // If the current user is a student, redirect to student payment slip
        if ($user && $user->role === 'student') {
            return to_route('student.ug-payment', ['studenttransaction' => $studentTransaction->id])
                ->with($flashType, $message);
        }

        // For admin / staff / cit, redirect back to previous list or fallback to first-school-fees
        $previousUrl = url()->previous();
        $currentUrl = url()->current();

        if ($previousUrl && $previousUrl !== $currentUrl) {
            return redirect()->to($previousUrl)->with($flashType, $message);
        }

        $fallbackRoute = ($user && $user->role === 'admin') ? 'admin.first-school-fees' : 'cit.first-school-fees';
        return to_route($fallbackRoute)->with($flashType, $message);
    }
}
