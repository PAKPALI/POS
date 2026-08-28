<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'kprimesms' => [
        'base_url' => env('KPRIME_SMS_BASE_URL'),
        'token' => env('KPRIME_SMS_TOKEN'),
        'key' => env('KPRIME_SMS_KEY'),
        'sender' => env('KPRIME_SMS_SENDER'),
        'sender_id' => env('KPRIME_SMS_SENDER_ID'),
        'response_url' => env('KPRIME_SMS_RESPONSE_URL'),
        'callback_secret' => env('KPRIME_SMS_CALLBACK_SECRET'),
    ],

    'kprimepay' => [
        'base_url' => env('KPRIMEPAY_BASE_URL', 'https://api.kprimepay.com/v2'),
        'token' => env('KPRIMEPAY_TOKEN'),
        'mode' => (int) env('KPRIMEPAY_MODE', 1),
        'with_fees' => (int) env('KPRIMEPAY_WITH_FEES', 1),
        'sms_unit_price' => (int) env('KPRIMEPAY_SMS_UNIT_PRICE', 35),
        'whatsapp_unit_price' => (int) env('KPRIMEPAY_WHATSAPP_UNIT_PRICE', 30),
        'sms_unit_cost' => (int) env('KPRIMEPAY_SMS_UNIT_COST', 15),
        'whatsapp_unit_cost' => (int) env('KPRIMEPAY_WHATSAPP_UNIT_COST', 15),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
