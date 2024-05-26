<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class TransactionService
{
    public static function hasPaid(string $paymentType): bool
    {
        return  Transaction::where([
            'resource' => $paymentType,
            'user_id' => auth()->user()->id,
            'status' => TransactionStatus::APPROVED
        ])->exists();
    }

    public function hasInvoice(string $paymentType)
    {
        return Transaction::where(['user_id' => auth()->id(), 'resource' => $paymentType])->exists();
    }
    public function generateTransactionId(string $alias): string
    {
        $transcId = substr(md5(uniqid(rand(), true)), 0, 4);
        $tran = strtoupper($transcId);
        return $alias . date("Ymd") . $tran;
    }
    public function generateInvoice(array $data)
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
    public static function convertJsonToArray(string $response = '', bool $assoc = false): object
    {
        if ($response[0] !== '[' && $response[0] !== '{') { // we have JSONP
            $response = substr($response, strpos($response, '('));
            return json_decode(trim($response, '();'), $assoc);
        }
        return json_decode(trim($response));
    }
    public function createPayment($data)
    {
        $values = $this->generateInvoice($data);
        if (!empty($values)) {
            Transaction::create(
                [
                    'transaction_id' => $data['transactionId'],
                    'user_id' => auth()->user()->id,
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
    public function updateTransactionStatus($status, $rrr)
    {

        Transaction::where('RRR', $rrr)->update(['status' => $status]);
    }
    public static function getTransactionStatus($rrr)
    {
        $apiKey = config('remita.settings.apikey');
        $merchantId = config('remita.settings.merchantid');
        $valuesToHash = $rrr . $apiKey . $merchantId;
        $hash = hash('sha512', $valuesToHash);

        $url = 'https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/' .
            $merchantId . '/' . $rrr . '/' . $hash . '/status.reg';

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'remitaConsumerKey=' . $merchantId . ',remitaConsumerToken=' . $hash
        ];


        $response = Http::withHeaders($headers)
            ->get($url);

        return TransactionService::convertJsonToArray($response->body());
    }
}
