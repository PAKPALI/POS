<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class KprimePayWebhookRelayService
{
    public function enabled(): bool
    {
        return (bool) config('services.kprimepay.webhook_bridge.enabled', false)
            && filled(config('services.kprimepay.webhook_bridge.url'))
            && filled(config('services.kprimepay.webhook_bridge.secret'));
    }

    /**
     * Validate a request sent by the production relay endpoint.
     * The body is signed as received so no payment field can be altered in transit.
     */
    public function decodeRelayRequest(Request $request): ?array
    {
        $secret = (string) config('services.kprimepay.webhook_bridge.secret', '');
        $timestamp = (string) $request->header('X-Maxanou-Relay-Timestamp', '');
        $signature = (string) $request->header('X-Maxanou-Relay-Signature', '');
        $body = (string) $request->getContent();
        $maxAge = max(30, (int) config('services.kprimepay.webhook_bridge.max_age_seconds', 300));

        if ($secret === '' || !ctype_digit($timestamp) || abs(time() - (int) $timestamp) > $maxAge || $body === '') {
            return null;
        }

        $expected = 'sha256='.hash_hmac('sha256', $timestamp."\n".$body, $secret);
        if ($signature === '' || !hash_equals($expected, $signature)) {
            return null;
        }

        try {
            $envelope = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (!is_array($envelope)
            || !is_array($envelope['payload'] ?? null)
            || !is_array($envelope['provider_headers'] ?? null)) {
            return null;
        }

        return [
            'payload' => $envelope['payload'],
            'provider_headers' => $this->allowedProviderHeaders($envelope['provider_headers']),
        ];
    }

    /**
     * Relay only the provider payload and the provider headers needed for its
     * normal validation. Cookies, authorization headers and server headers are
     * deliberately never copied.
     */
    public function forward(array $payload, array $providerHeaders): bool
    {
        $url = (string) config('services.kprimepay.webhook_bridge.url', '');
        $secret = (string) config('services.kprimepay.webhook_bridge.secret', '');

        if (!$this->enabled() || !$this->isSafeRelayUrl($url)) {
            return false;
        }

        try {
            $envelope = json_encode([
                'payload' => $payload,
                'provider_headers' => $this->allowedProviderHeaders($providerHeaders),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $timestamp = (string) time();
            $signature = 'sha256='.hash_hmac('sha256', $timestamp."\n".$envelope, $secret);

            /** @var PendingRequest $client */
            $client = Http::acceptJson()
                ->connectTimeout(2)
                ->timeout(5)
                ->withHeaders([
                    'X-Maxanou-Relay-Timestamp' => $timestamp,
                    'X-Maxanou-Relay-Signature' => $signature,
                    'X-Maxanou-Relay-Source' => (string) config('app.url'),
                ]);

            $response = $client->withBody($envelope, 'application/json')->post($url);
            if ($response->successful()) {
                return true;
            }

            Log::warning('KPrimePay webhook relay rejected by staging', [
                'status' => $response->status(),
                'transaction_hash' => $this->transactionHash($payload),
            ]);
        } catch (Throwable $exception) {
            Log::warning('KPrimePay webhook relay failed', [
                'transaction_hash' => $this->transactionHash($payload),
                'error' => class_basename($exception),
            ]);
        }

        return false;
    }

    private function allowedProviderHeaders(array $headers): array
    {
        $allowed = [];
        foreach (['X-API-BY', 'X-KPP-EVENT', 'X-KPP-EVENT-ID'] as $header) {
            $value = $headers[$header] ?? $headers[strtolower($header)] ?? null;
            if (is_string($value) && $value !== '') {
                $allowed[$header] = $value;
            }
        }

        return $allowed;
    }

    private function isSafeRelayUrl(string $url): bool
    {
        $parts = parse_url($url);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && filled($parts['host'] ?? null)
            && !isset($parts['user'], $parts['pass']);
    }

    private function transactionHash(array $payload): string
    {
        $transactionId = (string) data_get($payload, 'data.transaction_id', '');

        return $transactionId === '' ? 'unknown' : substr(hash('sha256', $transactionId), 0, 16);
    }
}
