<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\CommunicationLog;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $context = app(CompanyContext::class);
        if (!$context->isResolved()) {
            Log::error('SMS impossible : aucun contexte de compagnie résolu.');
            return null;
        }

        return CompanySetting::find($context->getCompanyId());
    }

    private function providerAccepted($payload): bool
    {
        return is_array($payload) && in_array($payload['status'] ?? null, [true, 1, '1'], true);
    }

    private function consumeAndRecord(CompanySetting $company, string $channel, string $function, string $phoneNumber, string $countryCode, array $payload): void
    {
        DB::transaction(function () use ($company, $channel, $function, $phoneNumber, $countryCode, $payload) {
            $company->decrement($channel === 'sms' ? 'sms_count' : 'whatsapp_count');
            CommunicationLog::create([
                'company_id' => $company->id, 'channel' => $channel, 'function' => $function,
                'recipient' => $phoneNumber, 'country_code' => strtoupper($countryCode), 'units' => 1,
                'provider_message_id' => $payload['message_id'] ?? $payload['response_token'] ?? data_get($payload, 'data.message_id'),
                'sent_at' => now(),
            ]);
        });
    }

    public function sendSms($phoneNumber, $message, ?string $countryCode = null, string $function = 'other')
    {     
        $company = $this->getCompanySetting();
        if (!$company || $company->sms_count <= 0) {
            Log::warning('SMS non envoyé : quota SMS épuisé.');
            return ['status' => false, 'message' => 'Quota SMS épuisé'];
        }

        $message = '['.$company->name.'] '.$message;
        $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
            'Content-Type' => 'application/json',
            'token' => $this->token,
            'key' => $this->key,
        ])->post($this->baseUrl.'/sms/push', [
            'sender' => $this->sender,
            'sender_id' => $this->sender_id,
            'country' => strtoupper($countryCode ?: $company->country_code ?: 'TG'),
            'phone_number' => $phoneNumber,
            'message' => $message,
            'response_url' => $this->responseUrl,
        ]);

        $payload = $response->json();
        $accepted = $response->successful() && $this->providerAccepted($payload);
        if ($accepted) {
            $this->consumeAndRecord($company, 'sms', $function, $phoneNumber, $countryCode ?: $company->country_code ?: 'TG', $payload);
        } else {
            Log::warning('Erreur SMS API', [
                'http_status' => $response->status(),
                'company_id' => $company->id,
                'provider_status' => is_array($payload) ? ($payload['status'] ?? null) : null,
            ]);
        }

        return is_array($payload)
            ? $payload
            : ['status' => false, 'message' => 'Réponse SMS invalide', 'http_status' => $response->status()];
    }

    public function sendWhatsappSms($phoneNumber, $title, $message, ?string $countryCode = null, string $function = 'other')
    {
        $company = $this->getCompanySetting();
        if (!$company || $company->whatsapp_count <= 0) {
            Log::warning('WhatsApp non envoyé : quota WhatsApp épuisé.');
            return ['status' => false, 'message' => 'Quota WhatsApp épuisé'];
        }

        $title = $company->name.' — '.$title;
        $message = '['.$company->name.'] '.$message;
        try {
            $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
                'Content-Type' => 'application/json',
                'token' => $this->token,
                'key' => $this->key,
            ])->post($this->baseUrl . '/whatsapp/template/text-message', [
                'country' => strtoupper($countryCode ?: $company->country_code ?: 'TG'),
                'phone_number' => $phoneNumber,
                'title' => $title,
                'content' => $message,
                'response_url' => $this->responseUrl,
            ]);

            $payload = $response->json();
            $accepted = $response->successful() && $this->providerAccepted($payload);
            if ($accepted) {
                $payload['status'] = true;
                $this->consumeAndRecord($company, 'whatsapp', $function, $phoneNumber, $countryCode ?: $company->country_code ?: 'TG', $payload);
            } else {
                Log::warning('Erreur WhatsApp API', [
                    'http_status' => $response->status(),
                    'company_id' => $company->id,
                    'provider_status' => is_array($payload) ? ($payload['status'] ?? null) : null,
                    'provider_message' => is_array($payload) ? ($payload['message'] ?? data_get($payload, 'data.message')) : null,
                ]);
            }

            return is_array($payload)
                ? $payload
                : ['status' => false, 'message' => 'Réponse WhatsApp invalide', 'http_status' => $response->status()];
        } catch (\Exception $e) {
            Log::warning('Error sending WhatsApp message: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Erreur d envoi WhatsApp'];
        }
    }

    public function uploadWhatsappDocument(string $filePath)
    {
        try {
            $response = Http::connectTimeout(5)->timeout(30)->withHeaders([
                'token' => $this->token,
                'key' => $this->key,
            ])
            ->attach(
                'attachment_file',
                fopen($filePath, 'r'),
                basename($filePath)
            )
            ->post(
                $this->baseUrl . '/whatsapp/template/document-upload'
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

    public function sendWhatsappDocument(string $phoneNumber, string $mediaId, string $message, ?string $countryCode = null, string $function = 'other')
    {
        try {
            $company = $this->getCompanySetting();
            if (!$company) return ['status' => false, 'message' => 'Compagnie introuvable'];
            if ($company->whatsapp_count <= 0) return ['status' => false, 'message' => 'Quota WhatsApp épuisé'];
            $message = '['.$company->name.'] '.$message;

            $response = Http::connectTimeout(5)->timeout(20)->withHeaders([
                'Content-Type' => 'application/json',
                'token' => $this->token,
                'key' => $this->key,
            ])->post(
                $this->baseUrl . '/whatsapp/template/document-message',
                [
                    'media_id' => $mediaId,
                    'country' => strtoupper($countryCode ?: $company->country_code ?: 'TG'),
                    'phone_number' => $phoneNumber,
                    'content' => $message,
                    'response_url' => $this->responseUrl,
                ]
            );

            $payload = $response->json();
            $accepted = $response->successful() && $this->providerAccepted($payload);
            if ($accepted) {
                $payload['status'] = true;
                $this->consumeAndRecord($company, 'whatsapp', $function, $phoneNumber, $countryCode ?: $company->country_code ?: 'TG', $payload);
            }
            return is_array($payload) ? $payload : ['status' => false, 'message' => 'Réponse WhatsApp invalide'];
        } catch (\Throwable $e) {

            Log::error(
                'WhatsApp document send failed : '
                . $e->getMessage()
            );

            return null;
        }
    }
}
