<?php

return [
    'admission' => [
        'fee' => env('ADMISSION_FEE'),
        'description' => env('ADMISSION_DESCRIPTION'),

    ],
    'settings' => [
        'serviceid' => env('SERVICEID'),
        'merchantid' => env('MERCHANTID'),
        'apikey' => env('APIKEY'),
        'academic_session' => env('ACADEMIC_SESSION'),
        'invoice_url' => env('REMITA_PAYMENT_INIT_URL'),
        'base_url' => env('BASE_URL ')
    ],
    'status' => [
        'approved' => '00',
        'pending' => null,
        'activated' => '01',
    ],

    "currency" => "₦",
];
