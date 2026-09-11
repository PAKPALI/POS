<?php

namespace App\Services;

use App\Jobs\ExecutePlatformWithdrawal;
use App\Mail\PlatformSecurityMail;
use App\Models\PartnerWalletEntry;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\PlatformWithdrawal;
use App\Models\PlatformWithdrawalAccount;
use App\Models\PlatformWithdrawalChallenge;
use App\Models\QuotaPayment;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Livre de bord de la trésorerie plateforme.
 *
 * Les chiffres sont des engagements comptables internes : la balance de
 * collecte renvoyée par KPrimePay reste le contrôle final avant transfert.
 */
class PlatformTreasuryService
{
    public function __construct(
        private PartnerWithdrawalService $partnerWithdrawals,
        private PartnerCountryService $countries,
    ) {}

    public function overview(): array
    {
        $quota = (int) QuotaPayment::withoutCompanyScope()->where('status', 'paid')->sum('amount');
        $subscriptions = (int) SubscriptionPayment::query()->where('status', 'paid')->sum('amount');
        $wallet = PartnerWalletEntry::query()
            ->selectRaw("bucket, COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) AS balance")
            ->groupBy('bucket')->pluck('balance', 'bucket')->map(fn ($value) => (int) $value)->all();
        $partner = array_merge(['available' => 0, 'reserved' => 0, 'paid' => 0], $wallet);

        $adminPaid = (int) PlatformWithdrawal::query()->where('status', 'succeeded')->selectRaw('COALESCE(SUM(amount + fees), 0) AS total')->value('total');
        $adminReserved = (int) PlatformWithdrawal::query()->whereIn('status', PlatformWithdrawal::OPEN_STATUSES)->selectRaw('COALESCE(SUM(amount + estimated_fees), 0) AS total')->value('total');
        $collections = $quota + $subscriptions;
        $partnerCommitments = $partner['available'] + $partner['reserved'] + $partner['paid'];

        return [
            'collections_total' => $collections,
            'quota_collections' => $quota,
            'subscription_collections' => $subscriptions,
            'partner_available' => $partner['available'],
            'partner_reserved' => $partner['reserved'],
            'partner_paid' => $partner['paid'],
            'partner_commitments' => $partnerCommitments,
            'admin_paid' => $adminPaid,
            'admin_reserved' => $adminReserved,
            'admin_withdrawable' => max(0, $collections - $partnerCommitments - $adminPaid - $adminReserved),
            'fee_bps' => $this->partnerWithdrawals->payoutFeeBps(),
        ];
    }

    public function registerAccount(PlatformAdmin $admin, array $attributes, Request $request): PlatformWithdrawalAccount
    {
        $country = $this->countries->activeCountry((string) $attributes['country_code']);
        if (!$country) throw new RuntimeException('PAYOUT_COUNTRY_INACTIVE');
        $gateway = strtoupper(trim((string) $attributes['gateway']));
        if (!in_array($gateway, $this->partnerWithdrawals->gatewaysFor($country['code']), true)) throw new RuntimeException('PAYOUT_GATEWAY_INVALID');
        $local = preg_replace('/\D+/', '', (string) $attributes['phone_number']);
        if (strlen($local) !== 8) throw new RuntimeException('PAYOUT_PHONE_LENGTH_INVALID');
        if (!in_array(substr($local, 0, 2), $this->partnerWithdrawals->gatewayPrefixes($country['code'], $gateway), true)) throw new RuntimeException('PAYOUT_PHONE_PREFIX_INVALID');
        $name = trim((string) $attributes['beneficiary_name']);
        if (count(preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: []) < 2) throw new RuntimeException('PAYOUT_BENEFICIARY_NAME_INVALID');
        $phone = $this->countries->normalizePhone($country['code'], $local);

        $phoneFingerprint = hash('sha256', $phone);
        return DB::transaction(function () use ($admin, $country, $gateway, $phone, $phoneFingerprint, $name, $request): PlatformWithdrawalAccount {
            $duplicate = PlatformWithdrawalAccount::query()
                ->where('platform_admin_id', $admin->id)
                ->where('phone_fingerprint', $phoneFingerprint)
                ->lockForUpdate()
                ->exists();
            if ($duplicate) throw new RuntimeException('PAYOUT_PHONE_DUPLICATE');

            $account = PlatformWithdrawalAccount::create([
                'platform_admin_id' => $admin->id, 'country_code' => $country['code'], 'gateway' => $gateway,
                'phone_e164' => $phone, 'phone_fingerprint' => $phoneFingerprint, 'beneficiary_name' => $name,
                'status' => 'pending_verification', 'verified_at' => null, 'is_primary' => true,
            ]);
            PlatformWithdrawalAccount::where('platform_admin_id', $admin->id)->where('id', '<>', $account->id)->update(['is_primary' => false]);
            $this->audit($admin, 'platform.treasury.account.registered', $account, $request, ['gateway' => $gateway, 'country_code' => $country['code'], 'status' => $account->status]);
            return $account;
        }, 3);
    }

    public function sendChallenge(PlatformAdmin $admin, string $purpose, array $payload, Request $request): void
    {
        $code = (string) random_int(100000, 999999);
        DB::transaction(function () use ($admin, $purpose, $payload, $request, $code): void {
            PlatformWithdrawalChallenge::query()->where('platform_admin_id', $admin->id)->where('purpose', $purpose)->whereNull('consumed_at')->update(['consumed_at' => now()]);
            PlatformWithdrawalChallenge::create(['platform_admin_id' => $admin->id, 'purpose' => $purpose, 'code_hash' => Hash::make($code), 'payload' => $payload, 'expires_at' => now()->addMinutes(10), 'request_ip' => $request->ip()]);
        }, 3);
        $intro = $purpose === 'account'
            ? 'Saisissez ce code pour vérifier votre compte Mobile Money de trésorerie.'
            : 'Saisissez ce code pour confirmer ce retrait de trésorerie. Ne le communiquez à personne.';
        Mail::to($admin->email)->send(new PlatformSecurityMail('Confirmation de trésorerie', $intro, $code));
        $this->audit($admin, 'platform.treasury.challenge.sent', $admin, $request, ['purpose' => $purpose]);
    }

    public function verifyAccount(PlatformAdmin $admin, string $code, Request $request): PlatformWithdrawalAccount
    {
        $challenge = $this->consumeChallenge($admin, 'account', $code);
        $accountId = (int) data_get($challenge->payload, 'account_id');
        return DB::transaction(function () use ($admin, $accountId, $request): PlatformWithdrawalAccount {
            $account = PlatformWithdrawalAccount::query()->whereKey($accountId)->where('platform_admin_id', $admin->id)->lockForUpdate()->firstOrFail();
            $account->update(['status' => 'verified', 'verified_at' => now()]);
            $this->audit($admin, 'platform.treasury.account.verified', $account, $request, ['status' => 'verified']);
            return $account;
        }, 3);
    }

    public function createWithdrawal(PlatformAdmin $admin, string $code, Request $request): PlatformWithdrawal
    {
        $challenge = $this->consumeChallenge($admin, 'withdrawal', $code);
        $amount = (int) data_get($challenge->payload, 'amount');
        $accountId = (int) data_get($challenge->payload, 'account_id');
        if ($amount < 1 || $accountId < 1) throw new RuntimeException('PAYOUT_CHALLENGE_INVALID');

        return DB::transaction(function () use ($admin, $amount, $accountId, $request): PlatformWithdrawal {
            $account = PlatformWithdrawalAccount::query()->whereKey($accountId)->where('platform_admin_id', $admin->id)->lockForUpdate()->firstOrFail();
            if ($account->status !== 'verified' || !$account->verified_at) throw new RuntimeException('PAYOUT_ACCOUNT_NOT_VERIFIED');
            if (!$this->partnerWithdrawals->gatewayIsEnabled($account->country_code, $account->gateway)) throw new RuntimeException('PAYOUT_GATEWAY_DISABLED');
            if (PlatformWithdrawal::query()->whereIn('status', PlatformWithdrawal::OPEN_STATUSES)->lockForUpdate()->exists()) throw new RuntimeException('PLATFORM_PAYOUT_ALREADY_OPEN');
            $overview = $this->overview();
            $estimatedFees = $this->partnerWithdrawals->feesForAmount($amount);
            if ($amount + $estimatedFees > $overview['admin_withdrawable']) throw new RuntimeException('PLATFORM_PAYOUT_AMOUNT_OUT_OF_RANGE');
            $withdrawal = PlatformWithdrawal::create([
                'platform_admin_id' => $admin->id, 'platform_withdrawal_account_id' => $account->id,
                'transaction_id' => (string) Str::uuid(), 'idempotency_key' => 'platform-withdrawal:'.Str::uuid(),
                'amount' => $amount, 'estimated_fees' => $estimatedFees, 'fees' => 0, 'currency' => 'XOF',
                'with_fees' => false, 'status' => 'otp_verified', 'requested_at' => now(),
                'account_snapshot' => ['country_code' => $account->country_code, 'gateway' => $account->gateway, 'phone' => $account->maskedPhone(), 'beneficiary_name' => $account->beneficiary_name],
                'funding_snapshot' => $overview,
            ]);
            $this->audit($admin, 'platform.treasury.withdrawal.requested', $withdrawal, $request, ['amount' => $amount, 'estimated_fees' => $estimatedFees, 'funding_snapshot' => $overview]);
            ExecutePlatformWithdrawal::dispatch($withdrawal->id)->onQueue('withdrawals')->afterCommit();
            return $withdrawal;
        }, 3);
    }

    private function consumeChallenge(PlatformAdmin $admin, string $purpose, string $code): PlatformWithdrawalChallenge
    {
        return DB::transaction(function () use ($admin, $purpose, $code): PlatformWithdrawalChallenge {
            $challenge = PlatformWithdrawalChallenge::query()->where('platform_admin_id', $admin->id)->where('purpose', $purpose)->whereNull('consumed_at')->latest('id')->lockForUpdate()->first();
            if (!$challenge || $challenge->expires_at->isPast()) throw new RuntimeException('PAYOUT_CODE_EXPIRED');
            if ($challenge->attempts >= 5) throw new RuntimeException('PAYOUT_CODE_LOCKED');
            if (!Hash::check($code, $challenge->code_hash)) {
                $challenge->increment('attempts');
                throw new RuntimeException('PAYOUT_CODE_INVALID');
            }
            $challenge->update(['consumed_at' => now()]);
            return $challenge;
        }, 3);
    }

    private function audit(PlatformAdmin $admin, string $action, object $target, Request $request, array $values = []): void
    {
        PlatformAuditLog::create(['platform_admin_id' => $admin->id, 'action' => $action, 'target_type' => $target::class, 'target_id' => (string) $target->id, 'new_values' => $values, 'ip_address' => $request->ip(), 'user_agent' => Str::limit((string) $request->userAgent(), 1000, '')]);
    }
}
