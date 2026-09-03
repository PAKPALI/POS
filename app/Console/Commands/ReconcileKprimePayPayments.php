<?php

namespace App\Console\Commands;

use App\Models\QuotaPayment;
use App\Models\SubscriptionPayment;
use App\Services\KprimePayService;
use App\Services\QuotaPaymentSettlementService;
use App\Services\SubscriptionSettlementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcileKprimePayPayments extends Command
{
    protected $signature = 'payments:reconcile-kprimepay {--limit=100 : Nombre maximal de paiements} {--pretend : Vérifier sans modifier}';
    protected $description = 'Réconcilie auprès de KPrimePay les checkouts expirés encore en attente';

    public function handle(KprimePayService $kprimePay, QuotaPaymentSettlementService $settlement, SubscriptionSettlementService $subscriptionSettlement): int
    {
        $limit = max(1, min(1000, (int) $this->option('limit')));
        $pretend = (bool) $this->option('pretend');
        $payments = QuotaPayment::withoutCompanyScope()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->oldest('expires_at')
            ->limit($limit)
            ->get();

        $counts = ['paid' => 0, 'failed' => 0, 'expired' => 0, 'unchanged' => 0, 'errors' => 0];
        foreach ($payments as $payment) {
            try {
                $verified = $kprimePay->paymentStatus($payment->transaction_id);
                $status = strtolower((string) ($verified['status'] ?? $verified['payment_status'] ?? ''));

                if ($status === 'success') {
                    if (!$pretend) $settlement->creditVerified($payment, $verified, null, $verified['kpp_tx_reference'] ?? null);
                    $counts['paid']++;
                } elseif (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled'], true)) {
                    if (!$pretend) $settlement->markFailed($payment, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Paiement refusé'));
                    $counts['failed']++;
                } elseif ($status === 'expired' || in_array($status, ['pending', 'processing', 'initiated', ''], true)) {
                    if (!$pretend) $settlement->markExpired($payment);
                    $counts['expired']++;
                } else {
                    $counts['unchanged']++;
                    Log::warning('Statut KPrimePay inconnu pendant la réconciliation', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $payment->transaction_id,
                        'provider_status' => $status,
                    ]);
                }
            } catch (Throwable $exception) {
                $counts['errors']++;
                Log::error('Réconciliation KPrimePay impossible', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $subscriptionPayments = SubscriptionPayment::query()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->oldest('expires_at')
            ->limit($limit)
            ->get();

        foreach ($subscriptionPayments as $payment) {
            try {
                $verified = $kprimePay->paymentStatus($payment->transaction_id);
                $status = strtolower((string) ($verified['status'] ?? $verified['payment_status'] ?? ''));

                if ($status === 'success') {
                    if (!$pretend) $subscriptionSettlement->creditVerified($payment, $verified, null, $verified['kpp_tx_reference'] ?? null);
                    $counts['paid']++;
                } elseif (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled'], true)) {
                    if (!$pretend) $subscriptionSettlement->markFailed($payment, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Paiement refusé'));
                    $counts['failed']++;
                } elseif ($status === 'expired' || in_array($status, ['pending', 'processing', 'initiated', ''], true)) {
                    if (!$pretend) $subscriptionSettlement->markExpired($payment);
                    $counts['expired']++;
                } else {
                    $counts['unchanged']++;
                    Log::warning('Statut KPrimePay abonnement inconnu pendant la réconciliation', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $payment->transaction_id,
                        'provider_status' => $status,
                    ]);
                }
            } catch (Throwable $exception) {
                $counts['errors']++;
                Log::error('Réconciliation KPrimePay abonnement impossible', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $prefix = $pretend ? 'Simulation — ' : '';
        $analysed = $payments->count() + $subscriptionPayments->count();
        $this->info($prefix."analysés: {$analysed}, payés: {$counts['paid']}, échoués: {$counts['failed']}, expirés: {$counts['expired']}, inchangés: {$counts['unchanged']}, erreurs: {$counts['errors']}.");

        return $counts['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
