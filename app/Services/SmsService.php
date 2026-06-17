<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    protected function getCompanySetting(): ?CompanySetting
    {
        return CompanySetting::first();
    }

    public function sendSms($phoneNumber, $message)
    {
        $company = $this->getCompanySetting();
        if (!$company || $company->sms_count <= 0) {
            Log::warning('SMS non envoyé : quota SMS épuisé.');
            return ['status' => false, 'message' => 'Quota SMS épuisé'];
        }

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

        if ($response->successful()) {
            $company->decrement('sms_count');
        } else {
            Log::warning('Erreur SMS API: ' . json_encode($response->json()));
        }

        return $response->json();
    }

    public function sendWhatsappSms($phoneNumber, $title, $message)
    {
        $company = $this->getCompanySetting();
        if (!$company || $company->whatsapp_count <= 0) {
            Log::warning('WhatsApp non envoyé : quota WhatsApp épuisé.');
            return ['status' => false, 'message' => 'Quota WhatsApp épuisé'];
        }

        Log::info("Sending WhatsApp message to $phoneNumber: $message");
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'token' => $this->token,
                'key' => $this->key,
            ])->post($this->baseUrl . '/whatsapp/template/text-message', [
                'country' => 'TG',
                'phone_number' => $phoneNumber,
                'title' => $title,
                'content' => $message,
            ]);

            Log::info('WhatsApp API response: ' . json_encode($response->json()));

            if ($response->successful()) {
                $company->decrement('whatsapp_count');
            } else {
                Log::warning('Erreur WhatsApp API: ' . json_encode($response->json()));
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::warning('Error sending WhatsApp message: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Erreur d envoi WhatsApp'];
        }
    }

    public function uploadWhatsappDocument(string $filePath)
    {
        try {
            $response = Http::withHeaders([
                'token' => $this->token,
                'key' => $this->key,
            ])
            ->attach(
                'attachment_file',
                fopen($filePath, 'r'),
                basename($filePath)
            )
            ->post(
                $this->baseUrl . '/whatsapp/upload-document'
            );

            Log::info(
                'WhatsApp document upload response : '. json_encode($response->json())
            );
            return $response->json();
        } catch (\Throwable $e) {
            Log::error(
                'WhatsApp document upload failed : '
                . $e->getMessage()
            );
            return null;
        }
    }

    public function sendWhatsappDocument(string $phoneNumber, string $mediaId, string $message)
    {
        try {

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'token' => $this->token,
                'key' => $this->key,
            ])->post(
                $this->baseUrl . '/whatsapp/template/document-message',
                [
                    'media_id' => $mediaId,
                    'country' => 'TG',
                    'phone_number' => $phoneNumber,
                    'content' => $message,
                ]
            );

            Log::info(
                'WhatsApp document send response : '
                . json_encode($response->json())
            );
            return $response->json();
        } catch (\Throwable $e) {

            Log::error(
                'WhatsApp document send failed : '
                . $e->getMessage()
            );

            return null;
        }
    }
}
