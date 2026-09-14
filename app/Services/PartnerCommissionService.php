<?php

namespace App\Services;

use App\Models\PartnerAttribution;
use App\Models\PartnerCommission;
use App\Models\PartnerWalletEntry;
use App\Models\SubscriptionPayment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PartnerCommissionService
{
    public const SOURCE_TYPE = 'partner_commission';

    public function __construct(private PlatformConfigurationService $configuration) {}

    /**
     * Crée la commission et son écriture de portefeuille dans la transaction du paiement confirmé.
     *
     * Le réglage partners.commission_hold_days reste disponible pour une éventuelle
     * politique de réserve, mais la valeur par défaut de production est désormais 0 :
     * la commission est donc immédiatement disponible après le webhook confirmé.
     */
    public function recordForPaidSubscription(SubscriptionPayment $payment, PartnerAttribution $attribution, CarbonInterface $settledAt): PartnerCommission
    {
        $existing = PartnerCommission::query()->where('subscription_payment_id', $payment->id)->first();
        if ($existing) return $existing;

        if ($attribution->status !== 'active') {
            throw new RuntimeException('ATTRIBUTION_NOT_ACTIVE');
        }
        if ((int) $attribution->partner_id < 1 || (int) $attribution->subscription_account_id !== (int) $payment->subscription_account_id) {
            throw new RuntimeException('ATTRIBUTION_PAYMENT_MISMATCH');
        }

        $gross = (int) ($payment->gross_amount ?? data_get($payment->snapshot, 'gross_amount', 0));
        $discount = (int) ($payment->discount_amount ?? data_get($payment->snapshot, 'discount_amount', 0));
        $net = (int) $payment->amount;
        $rate = (int) $attribution->commission_rate_bps;
        $maxRate = $this->configuration->integer('partners.max_commission_bps', 2500);

        if ($gross < 1 || $net < 1 || $discount < 0 || $rate < 1 || $rate > $maxRate || $maxRate > 10000) {
            throw new RuntimeException('COMMISSION_SNAPSHOT_INVALID');
        }

        $commissionAmount = intdiv($gross * $rate, 10000);
        if ($commissionAmount < 1) {
            throw new RuntimeException('COMMISSION_AMOUNT_INVALID');
        }

        $holdDays = $this->configuration->integer('partners.commission_hold_days', 0);
        if ($holdDays < 0 || $holdDays > 3650) {
            throw new RuntimeException('COMMISSION_HOLD_DAYS_INVALID');
        }

        $type = (int) $attribution->first_subscription_payment_id === (int) $payment->id
            ? 'acquisition'
            : ((string) $payment->operation === 'upgrade' ? 'upgrade' : 'renewal');
        $availableAt = $settledAt->copy()->addDays($holdDays);
        $status = $holdDays === 0 ? 'available' : 'pending';
        $commission = PartnerCommission::create([
            'partner_id' => $attribution->partner_id,
            'partner_attribution_id' => $attribution->id,
            'subscription_payment_id' => $payment->id,
            'type' => $type,
            'gross_amount' => $gross,
            'discount_amount' => $discount,
            'net_paid_amount' => $net,
            'commission_rate_bps' => $rate,
            'commission_amount' => $commissionAmount,
            'currency' => $payment->currency,
            'status' => $status,
            'available_at' => $availableAt,
            'rule_version' => $attribution->rule_version,
            'calculation_snapshot' => [
                'formula' => 'floor(gross_amount * commission_rate_bps / 10000)',
                'subscription_transaction_id' => $payment->transaction_id,
                'gross_amount' => $gross,
                'discount_amount' => $discount,
                'net_paid_amount' => $net,
                'commission_rate_bps' => $rate,
                'commission_amount' => $commissionAmount,
                'attribution_id' => $attribution->id,
                'rule_version' => $attribution->rule_version,
            ],
        ]);

        $this->appendEntry($commission, 'commission_credit', $status, 'credit', $settledAt, 'commission-credit');
        app(PartnerPlatformAlertService::class)->dispatch('commission_created', $attribution->partner, 'commission:'.$commission->id, [
            'type' => $type,
            'gross_amount' => $gross,
            'discount_amount' => $discount,
            'net_paid_amount' => $net,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
        ]);

        return $commission;
    }

    /** @return int Nombre de commissions rendues disponibles. */
    public function matureDue(int $limit = 200, ?CarbonInterface $now = null): int
    {
        $now ??= now();
        $dueIds = PartnerCommission::query()
            ->where('status', 'pending')
            ->whereNotNull('available_at')
            ->where('available_at', '<=', $now)
            ->orderBy('available_at')
            ->limit(max(1, min(1000, $limit)))
            ->pluck('id');

        $matured = 0;
        foreach ($dueIds as $id) {
            $changed = DB::transaction(function () use ($id, $now): bool {
                $commission = PartnerCommission::query()->whereKey($id)->lockForUpdate()->first();
                if (!$commission || $commission->status !== 'pending' || !$commission->available_at || $commission->available_at->gt($now)) return false;

                $this->appendEntry($commission, 'maturity', 'pending', 'debit', $now, 'maturity-pending-debit');
                $this->appendEntry($commission, 'maturity', 'available', 'credit', $now, 'maturity-available-credit');
                $commission->update(['status' => 'available']);
                return true;
            }, 3);
            if ($changed) $matured++;
        }

        return $matured;
    }

    private function appendEntry(PartnerCommission $commission, string $entryType, string $bucket, string $direction, CarbonInterface $occurredAt, string $suffix): PartnerWalletEntry
    {
        return PartnerWalletEntry::firstOrCreate(
            ['idempotency_key' => "partner-commission:{$commission->id}:{$suffix}"],
            [
                'partner_id' => $commission->partner_id,
                'entry_type' => $entryType,
                'bucket' => $bucket,
                'direction' => $direction,
                'amount' => $commission->commission_amount,
                'currency' => $commission->currency,
                'source_type' => self::SOURCE_TYPE,
                'source_id' => $commission->id,
                'occurred_at' => $occurredAt,
                'metadata' => [
                    'commission_id' => $commission->id,
                    'subscription_payment_id' => $commission->subscription_payment_id,
                    'commission_type' => $commission->type,
                ],
            ],
        );
    }
}
