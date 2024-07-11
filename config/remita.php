<?php

return [
    'admission' => [
        'fee' => env('ADMISSION_FEE'),
        'description' => env('ADMISSION_DESCRIPTION'),
        'payment_redirect' => env('ADMISSION_PAYMENT_REDIRECT'),

    ],
    'acceptance' => [
        'fee' => env('ACCEPTANCE_FEE'),
        'description' => env('ACCEPTANCE_DESCRIPTION'),
        'payment_redirect' => env('ACCEPTANCE_PAYMENT_REDIRECT'),

    ],
    'settings' => [
        'serviceid' => env('SERVICEID'),
        'merchantid' => env('MERCHANTID'),
        'apikey' => env('APIKEY'),
        'academic_session' => env('ACADEMIC_SESSION'),
        'invoice_url' => env('REMITA_PAYMENT_INIT_URL'),
        'base_url' => env('BASE_URL '),
        'address' => env('INSTITUTION_ADDRESS'),
        'state' => env('INSTITUTION_STATE'),
        'email' => env('INSTITUTION_EMAIL'),
    ],
    'status' => [
        'approved' => '00',
        'pending' => null,
        'activated' => '01',
    ],

    "currency" => "₦",
];
