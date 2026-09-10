<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAuditLog;
use App\Models\PartnerCommission;
use App\Models\PartnerWalletEntry;
use App\Models\PartnerWithdrawal;
use App\Models\PartnerWithdrawalAccount;
use App\Models\PartnerWithdrawalAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PartnerWithdrawalService
{
    public const OPEN_STATUSES = ['requested', 'otp_verified', 'approved', 'processing', 'unknown'];

    public function __construct(
        private PlatformConfigurationService $configuration,
        private PartnerCountryService $countries,
    ) {}

    public function balances(Partner $partner): array
    {
        $balances = PartnerWalletEntry::query()->where('partner_id', $partner->id)
            ->selectRaw("bucket, COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) AS balance")
            ->groupBy('bucket')->pluck('balance', 'bucket')->map(fn ($value) => (int) $value)->all();
        return array_merge(['available' => 0, 'reserved' => 0, 'pending' => 0, 'paid' => 0], $balances);
    }

    public function eligibility(Partner $partner): array
    {
        $balance = $this->balances($partner);
        $minimum = $this->configuration->integer('partners.payout_min_xof', (int) config('partners.payout_min_xof', 5000));
        $requiredClients = $this->configuration->integer('partners.payout_min_qualified_clients', (int) config('partners.payout_min_qualified_clients', 3));
        $reasons = [];
        if (!$this->configuration->boolean('partners.payouts_enabled', false)) $reasons[] = 'Les retraits sont temporairement désactivés.';
        if ((int) $partner->qualified_clients_count < $requiredClients) $reasons[] = sprintf('Il faut au moins %d client(s) qualifié(s).', $requiredClients);
        if ((int) $balance['available'] < $minimum) $reasons[] = sprintf('Le solde disponible doit atteindre %s XOF.', number_format($minimum, 0, ',', ' '));
        return ['eligible' => $reasons === [], 'reasons' => $reasons, 'minimum' => $minimum, 'required_clients' => $requiredClients, 'balances' => $balance];
    }

    public function registerAccount(Partner $partner, array $attributes, Request $request): PartnerWithdrawalAccount
    {
        $country = $this->countries->activeCountry((string) $attributes['country_code']);
        if (!$country) throw new RuntimeException('PAYOUT_COUNTRY_INACTIVE');
        $gateway = strtoupper(trim((string) $attributes['gateway']));
        if (!in_array($gateway, $this->gatewaysFor($country['code']), true)) throw new RuntimeException('PAYOUT_GATEWAY_INVALID');
        $localNumber = preg_replace('/\D+/', '', (string) $attributes['phone_number']);
        if (strlen($localNumber) !== 8) throw new RuntimeException('PAYOUT_PHONE_LENGTH_INVALID');
        if (!in_array(substr($localNumber, 0, 2), $this->gatewayPrefixes($country['code'], $gateway), true)) throw new RuntimeException('PAYOUT_PHONE_PREFIX_INVALID');
        $phone = $this->countries->normalizePhone($country['code'], $localNumber);
        $fingerprint = hash('sha256', $phone);

        return DB::transaction(function () use ($partner, $country, $gateway, $phone, $fingerprint, $attributes, $request): PartnerWithdrawalAccount {
            $account = PartnerWithdrawalAccount::updateOrCreate(
                ['partner_id' => $partner->id, 'phone_fingerprint' => $fingerprint],
                ['country_code' => $country['code'], 'gateway' => $gateway, 'phone_e164' => $phone, 'beneficiary_name' => trim((string) $attributes['beneficiary_name']), 'status' => 'pending_verification', 'verified_at' => null, 'is_primary' => true],
            );
            PartnerWithdrawalAccount::where('partner_id', $partner->id)->where('id', '<>', $account->id)->update(['is_primary' => false]);
            PartnerAuditLog::create(['partner_id' => $partner->id, 'action' => 'partner.withdrawal_account.registered', 'target_type' => PartnerWithdrawalAccount::class, 'target_id' => (string) $account->id, 'new_values' => ['country_code' => $country['code'], 'gateway' => $gateway, 'phone_fingerprint' => $fingerprint, 'status' => $account->status], 'ip_address' => $request->ip(), 'user_agent_hash' => hash('sha256', (string) $request->userAgent())]);
            return $account;
        });
    }

    public function verifyAccount(PartnerWithdrawalAccount $account, Request $request): PartnerWithdrawalAccount
    {
        return DB::transaction(function () use ($account, $request): PartnerWithdrawalAccount {
            $account = PartnerWithdrawalAccount::query()->whereKey($account->id)->lockForUpdate()->firstOrFail();
            $account->update(['status' => 'verified', 'verified_at' => now()]);
            PartnerAuditLog::create(['partner_id' => $account->partner_id, 'action' => 'partner.withdrawal_account.verified', 'target_type' => PartnerWithdrawalAccount::class, 'target_id' => (string) $account->id, 'ip_address' => $request->ip(), 'user_agent_hash' => hash('sha256', (string) $request->userAgent())]);
            return $account;
        });
    }

    public function requestWithdrawal(Partner $partner, PartnerWithdrawalAccount $account, int $amount, Request $request, bool $otpVerified = false): PartnerWithdrawal
    {
        if (!$this->configuration->boolean('partners.payouts_enabled', false)) throw new RuntimeException('PAYOUTS_DISABLED');
        if (!$otpVerified) throw new RuntimeException('PAYOUT_OTP_REQUIRED');
        if ($amount < 1) throw new RuntimeException('PAYOUT_AMOUNT_INVALID');

        return DB::transaction(function () use ($partner, $account, $amount, $request): PartnerWithdrawal {
            $partner = Partner::query()->whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $account = PartnerWithdrawalAccount::query()->whereKey($account->id)->where('partner_id', $partner->id)->lockForUpdate()->firstOrFail();
            if ($account->status !== 'verified' || !$account->verified_at) throw new RuntimeException('PAYOUT_ACCOUNT_NOT_VERIFIED');
            if (PartnerWithdrawal::where('partner_id', $partner->id)->whereIn('status', self::OPEN_STATUSES)->exists()) throw new RuntimeException('PAYOUT_ALREADY_OPEN');
            $eligibility = $this->eligibility($partner);
            if (!$eligibility['eligible']) throw new RuntimeException('PAYOUT_NOT_ELIGIBLE');
            if ($amount < $eligibility['minimum'] || $amount > $eligibility['balances']['available']) throw new RuntimeException('PAYOUT_AMOUNT_OUT_OF_RANGE');

            $withdrawal = PartnerWithdrawal::create(['partner_id' => $partner->id, 'partner_withdrawal_account_id' => $account->id, 'transaction_id' => (string) Str::uuid(), 'idempotency_key' => 'partner-withdrawal:'.Str::uuid(), 'amount' => $amount, 'fees' => 0, 'currency' => 'XOF', 'with_fees' => false, 'status' => 'otp_verified', 'requested_at' => now(), 'otp_verified_at' => now(), 'account_snapshot' => ['country_code' => $account->country_code, 'gateway' => $account->gateway, 'phone' => $account->maskedPhone(), 'beneficiary_name' => $account->beneficiary_name]]);
            $this->entry($withdrawal, 'withdrawal_reservation', 'available', 'debit', $amount, 'available-debit');
            $this->entry($withdrawal, 'withdrawal_reservation', 'reserved', 'credit', $amount, 'reserved-credit');
            $this->allocate($withdrawal, $amount);
            PartnerAuditLog::create(['partner_id' => $partner->id, 'action' => 'partner.withdrawal.requested', 'target_type' => PartnerWithdrawal::class, 'target_id' => (string) $withdrawal->id, 'new_values' => ['amount' => $amount, 'status' => $withdrawal->status], 'ip_address' => $request->ip(), 'user_agent_hash' => hash('sha256', (string) $request->userAgent())]);
            return $withdrawal;
        }, 3);
    }

    public function failAndRelease(PartnerWithdrawal $withdrawal, string $reason, Request $request): PartnerWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $reason, $request): PartnerWithdrawal {
            $withdrawal = PartnerWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            // Un résultat inconnu reste réservé jusqu’à la future réconciliation KPrimePay.
            if (in_array($withdrawal->status, ['succeeded', 'failed', 'cancelled', 'unknown'], true)) return $withdrawal;
            $this->entry($withdrawal, 'withdrawal_release', 'reserved', 'debit', $withdrawal->amount, 'reserved-debit');
            $this->entry($withdrawal, 'withdrawal_release', 'available', 'credit', $withdrawal->amount, 'available-credit');
            $withdrawal->update(['status' => 'failed', 'failure_reason' => $reason, 'failed_at' => now()]);
            PartnerAuditLog::create(['partner_id' => $withdrawal->partner_id, 'action' => 'partner.withdrawal.failed_released', 'target_type' => PartnerWithdrawal::class, 'target_id' => (string) $withdrawal->id, 'reason' => $reason, 'ip_address' => $request->ip(), 'user_agent_hash' => hash('sha256', (string) $request->userAgent())]);
            return $withdrawal;
        }, 3);
    }

    public function gatewaysFor(string $countryCode): array
    {
        $fallback = config('partners.payout_gateways', []);
        $stored = $this->configuration->get('partners.payout_gateways', json_encode($fallback));
        $gateways = is_array($stored) ? $stored : json_decode((string) $stored, true);
        return array_values(array_filter($gateways[strtoupper($countryCode)] ?? [], 'is_string'));
    }

    public function gatewayPrefixes(string $countryCode, string $gateway): array
    {
        return config('partners.payout_gateway_catalog.'.strtoupper($countryCode).'.'.strtoupper($gateway).'.prefixes', []);
    }

    private function allocate(PartnerWithdrawal $withdrawal, int $amount): void
    {
        $remaining = $amount;
        $commissions = PartnerCommission::query()->where('partner_id', $withdrawal->partner_id)->where('status', 'available')->orderBy('id')->lockForUpdate()->get();
        foreach ($commissions as $commission) {
            if ($remaining <= 0) break;
            $already = (int) PartnerWithdrawalAllocation::query()->where('partner_commission_id', $commission->id)->whereHas('withdrawal', fn ($q) => $q->whereIn('status', self::OPEN_STATUSES))->sum('amount');
            $available = max(0, (int) $commission->commission_amount - $already);
            $part = min($remaining, $available);
            if ($part > 0) {
                PartnerWithdrawalAllocation::create(['partner_withdrawal_id' => $withdrawal->id, 'partner_commission_id' => $commission->id, 'amount' => $part]);
                $remaining -= $part;
            }
        }
    }

    private function entry(PartnerWithdrawal $withdrawal, string $type, string $bucket, string $direction, int $amount, string $suffix): void
    {
        PartnerWalletEntry::firstOrCreate(['idempotency_key' => "partner-withdrawal:{$withdrawal->id}:{$suffix}"], ['partner_id' => $withdrawal->partner_id, 'entry_type' => $type, 'bucket' => $bucket, 'direction' => $direction, 'amount' => $amount, 'currency' => $withdrawal->currency, 'source_type' => 'partner_withdrawal', 'source_id' => $withdrawal->id, 'occurred_at' => now(), 'metadata' => ['withdrawal_id' => $withdrawal->id, 'transaction_id' => $withdrawal->transaction_id]]);
    }
}
