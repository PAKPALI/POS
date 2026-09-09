<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAuditLog;
use App\Models\PartnerTwoFactorChallenge;
use App\Notifications\PartnerEmailVerificationNotification;
use App\Notifications\PartnerTwoFactorNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerAuthenticationService
{
    public function __construct(private PartnerCodeService $codes) {}

    public function register(array $attributes, Request $request): Partner
    {
        $email = mb_strtolower(trim((string) $attributes['email']));
        $username = strtolower(trim((string) $attributes['username']));
        $partner = DB::transaction(function () use ($attributes, $email, $username): Partner {
            return Partner::create([
                'name' => trim((string) $attributes['name']),
                'username' => $username,
                'normalized_username' => $username,
                'email' => $email,
                'normalized_email' => $email,
                'phone_country_code' => strtoupper((string) $attributes['country_code']),
                'phone_e164' => (string) $attributes['phone_e164'],
                'country_code' => strtoupper((string) $attributes['country_code']),
                'password' => (string) $attributes['password'],
                'status' => 'pending_email',
            ]);
        });

        $partner->notify(new PartnerEmailVerificationNotification($partner));
        PartnerAuditLog::create([
            'partner_id' => $partner->id,
            'action' => 'partner.registered',
            'target_type' => Partner::class,
            'target_id' => (string) $partner->id,
            'ip_address' => $request->ip(),
            'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
        ]);

        return $partner;
    }

    public function issueTwoFactor(Partner $partner, Request $request): PartnerTwoFactorChallenge
    {
        $code = (string) random_int(100000, 999999);
        $challenge = DB::transaction(function () use ($partner, $code, $request): PartnerTwoFactorChallenge {
            PartnerTwoFactorChallenge::query()
                ->where('partner_id', $partner->id)
                ->where('purpose', 'login')
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            return PartnerTwoFactorChallenge::create([
                'partner_id' => $partner->id,
                'purpose' => 'login',
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
                'request_ip' => $request->ip(),
                'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
            ]);
        });

        $request->session()->put('partner_2fa_partner_id', $partner->id);
        $partner->notify(new PartnerTwoFactorNotification($code));

        return $challenge;
    }

    public function verifyTwoFactor(Partner $partner, string $code): bool
    {
        $challenge = PartnerTwoFactorChallenge::query()
            ->where('partner_id', $partner->id)
            ->where('purpose', 'login')
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$challenge || $challenge->expires_at->isPast() || $challenge->attempts >= $challenge->max_attempts) {
            return false;
        }

        if (!Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');
            return false;
        }

        $challenge->update(['consumed_at' => now()]);
        return true;
    }

    public function verifyEmail(Partner $partner): bool
    {
        if ($partner->email_verified_at) {
            return false;
        }

        $partner->forceFill(['email_verified_at' => now(), 'status' => 'active'])->save();
        $this->codes->ensurePrimaryCode($partner->fresh());
        PartnerAuditLog::create([
            'partner_id' => $partner->id,
            'action' => 'partner.email_verified',
            'target_type' => Partner::class,
            'target_id' => (string) $partner->id,
        ]);
        return true;
    }
}
