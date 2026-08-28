<?php

namespace App\Services;

use App\Models\QuotaPayment;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KprimePayService
{
    public function createCheckout(QuotaPayment $payment, string $returnUrl): array
    {
        if (!app(PlatformConfigurationService::class)->channelEnabled('kprimepay')) {
            throw new RuntimeException('Les paiements KPrimePay sont temporairement désactivés par la plateforme.');
        }
        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => $payment->idempotency_key])
            ->post($this->url('/checkout'), [
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'mode' => config('services.kprimepay.mode'),
                'with_fees' => config('services.kprimepay.with_fees'),
                'description' => "Achat quotas : {$payment->sms_quantity} SMS et {$payment->whatsapp_quantity} WhatsApp",
                'return_url' => $returnUrl,
                'locale' => 'fr',
                'custom_meta_data' => [
                    'quota_payment_id' => (string) $payment->id,
                    'company_id' => (string) $payment->company_id,
                ],
            ]);

        $payload = $this->validPayload($response, 'Impossible de créer le paiement KPrimePay.');
        $data = $payload['data'] ?? [];
        if (empty($data['checkout_url']) || empty($data['kpp_tx_reference'])) {
            throw new RuntimeException('KPrimePay a retourné une réponse incomplète.');
        }

        return $data;
    }

    public function paymentStatus(string $transactionId): array
    {
        $response = $this->client()->post($this->url('/transactions/debit-status'), [
            'transaction_id' => $transactionId,
        ]);

        $payload = $this->validPayload($response, 'Impossible de vérifier le paiement KPrimePay.');

        return $payload['data'] ?? [];
    }

    private function client()
    {
        $token = (string) config('services.kprimepay.token');
        if ($token === '') {
            throw new RuntimeException('La clé KPrimePay n’est pas configurée.');
        }

        return Http::acceptJson()->asJson()->withToken($token)->connectTimeout(5)->timeout(20);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.kprimepay.base_url'), '/').$path;
    }

    private function validPayload(Response $response, string $fallback): array
    {
        $payload = $response->json();
        if (!$response->successful() || !is_array($payload) || ($payload['status'] ?? false) !== true) {
            $message = is_array($payload) ? ($payload['message'] ?? $fallback) : $fallback;
            throw new RuntimeException($message);
        }

        return $payload;
    }
}
