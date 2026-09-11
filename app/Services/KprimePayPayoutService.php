<?php

namespace App\Services;

use App\Exceptions\KprimePayPayoutException;
use App\Models\PartnerWithdrawal;
use App\Models\PlatformWithdrawal;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KprimePayPayoutService
{
    public function transferFromCollectionBalance(PartnerWithdrawal $withdrawal): array
    {
        $withdrawal->loadMissing(['account', 'partner']);
        $account = $withdrawal->account;
        if (!$account || !$withdrawal->partner) {
            throw new RuntimeException('Le compte de retrait est introuvable.');
        }

        [$firstName, $lastName] = $this->beneficiaryNames((string) $account->beneficiary_name);
        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => $withdrawal->idempotency_key])
            ->post($this->url('/payouts/from-collection-balance'), [
                'transaction_id' => $withdrawal->transaction_id,
                'amount' => (int) $withdrawal->amount,
                'with_fees' => 0,
                'receiver_first_name' => $firstName,
                'receiver_last_name' => $lastName,
                'receiver_email' => $withdrawal->partner->email,
                'country_code' => $account->country_code,
                'gateway' => $account->gateway,
                'phone_number' => $this->localPhone($account->country_code, (string) $account->phone_e164),
                'description' => 'Versement de commission partenaire #'.$withdrawal->id,
            ]);

        return $this->validPayload($response, 'Le fournisseur n’a pas pu accepter le retrait.');
    }

    public function transferStatus(string $transactionId): array
    {
        $response = $this->client()->post($this->url('/transactions/credit-status'), [
            'transaction_id' => $transactionId,
        ]);

        return $this->validPayload($response, 'Impossible de vérifier le statut du retrait.')['data'] ?? [];
    }

    /** Retrait du coffre plateforme depuis la balance de collecte mutualisée. */
    public function transferPlatformWithdrawal(PlatformWithdrawal $withdrawal): array
    {
        $withdrawal->loadMissing(['account', 'admin']);
        $account = $withdrawal->account;
        if (!$account || !$withdrawal->admin) {
            throw new RuntimeException('Le compte de retrait administrateur est introuvable.');
        }

        [$firstName, $lastName] = $this->beneficiaryNames((string) $account->beneficiary_name);
        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => $withdrawal->idempotency_key])
            ->post($this->url('/payouts/from-collection-balance'), [
                'transaction_id' => $withdrawal->transaction_id,
                'amount' => (int) $withdrawal->amount,
                // Le bénéficiaire reçoit le montant demandé. Les frais KPrimePay sont
                // contrôlés et imputés au coffre après confirmation du statut.
                'with_fees' => 0,
                'receiver_first_name' => $firstName,
                'receiver_last_name' => $lastName,
                'receiver_email' => $withdrawal->admin->email,
                'country_code' => $account->country_code,
                'gateway' => $account->gateway,
                'phone_number' => $this->localPhone($account->country_code, (string) $account->phone_e164),
                'description' => 'Retrait de trésorerie plateforme #'.$withdrawal->id,
            ]);

        return $this->validPayload($response, 'Le fournisseur n’a pas pu accepter le retrait de trésorerie.');
    }

    private function client()
    {
        $token = (string) config('services.kprimepay_payout.token');
        if ($token === '') {
            throw new RuntimeException('La clé KPrimePay dédiée aux retraits n’est pas configurée.');
        }

        $client = Http::acceptJson()->asJson()->withToken($token)->connectTimeout(5)->timeout(20);
        $caBundle = config('services.kprimepay_payout.ca_bundle');
        if (is_string($caBundle) && $caBundle !== '' && is_file($caBundle)) {
            $client = $client->withOptions(['verify' => $caBundle]);
        }

        return $client;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.kprimepay_payout.base_url'), '/').$path;
    }

    private function validPayload(Response $response, string $fallback): array
    {
        $payload = $response->json();
        if ($response->successful() && is_array($payload) && ($payload['status'] ?? false) === true) {
            return $payload;
        }

        $data = is_array($payload) ? ($payload['data'] ?? []) : [];
        $code = (string) (is_array($payload) ? ($payload['code'] ?? $payload['error_code'] ?? $payload['message'] ?? '') : '');
        $message = is_array($payload) ? (string) ($payload['message'] ?? $fallback) : $fallback;
        throw new KprimePayPayoutException($message, $code, $response->status(), is_array($data) ? $data : []);
    }

    private function beneficiaryNames(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) < 2) {
            throw new RuntimeException('Le nom du bénéficiaire doit comporter au moins un prénom et un nom.');
        }

        return [$parts[0], implode(' ', array_slice($parts, 1))];
    }

    private function localPhone(string $countryCode, string $e164): string
    {
        $digits = preg_replace('/\D+/', '', $e164);
        $dialCode = ltrim((string) config('partners.country_catalog.'.strtoupper($countryCode).'.dial_code', ''), '+');
        $local = $dialCode !== '' && str_starts_with($digits, $dialCode) ? substr($digits, strlen($dialCode)) : $digits;
        if (strlen($local) !== 8) {
            throw new RuntimeException('Le numéro Mobile Money enregistré est invalide.');
        }

        return $local;
    }
}
