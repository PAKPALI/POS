<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $baseUrl;
    protected $token;
    protected $key;
    protected $sender;
    protected $sender_id;
    protected $responseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.kprimesms.base_url');
        $this->token = config('services.kprimesms.token');
        $this->key = config('services.kprimesms.key');
        $this->sender = config('services.kprimesms.sender');
        $this->sender_id = config('services.kprimesms.sender_id');
        $this->responseUrl = config('services.kprimesms.response_url');
    }

    public function send($phoneNumber, $message)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'token' => $this->token,
            'key' => $this->key,
        ])->post($this->baseUrl, [
            'sender' => $this->sender,
            'sender_id' => $this->sender_id,
            'country' => 'TG',
            'phone_number' => $phoneNumber,
            'message' => $message,
            'response_url' => $this->responseUrl,
        ]);

        return $response->json();
    }
}
