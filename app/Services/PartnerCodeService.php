<?php

namespace App\Services;

use App\Exceptions\PartnerCodeChangeTooSoon;
use App\Models\Partner;
use App\Models\PartnerAuditLog;
use App\Models\PartnerPromoCode;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PartnerCodeService
{
    private const GENERATED_SUFFIX_LENGTH = 6;

    public function __construct(private PlatformConfigurationService $configuration) {}

    public function ensurePrimaryCode(Partner $partner): PartnerPromoCode
    {
        $existing = $partner->promoCodes()->where('status', 'active')->where('is_primary', true)->latest('id')->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($partner): PartnerPromoCode {
            $current = $partner->promoCodes()->lockForUpdate()->where('status', 'active')->where('is_primary', true)->latest('id')->first();
            if ($current) {
                return $current;
            }

            for ($attempt = 0; $attempt < 8; $attempt++) {
                try {
                    $code = $this->generatedCode($partner->username);
                    $created = $partner->promoCodes()->create([
                        'code' => $code,
                        'normalized_code' => $code,
                        'status' => 'active',
                        'is_primary' => true,
                        'activated_at' => now(),
                    ]);
                    $this->audit($partner, 'partner.code.generated', $created);
                    return $created;
                } catch (QueryException $exception) {
                    if (!$this->isUniqueViolation($exception)) {
                        throw $exception;
                    }
                }
            }

            throw new RuntimeException('Impossible de générer un code partenaire unique.');
        });
    }

    public function activeCode(Partner $partner): ?PartnerPromoCode
    {
        return $partner->promoCodes()->where('status', 'active')->where('is_primary', true)->latest('id')->first();
    }

    public function history(Partner $partner, int $limit = 5)
    {
        return PartnerAuditLog::query()
            ->where('partner_id', $partner->id)
            ->where('action', 'partner.code.customized')
            ->latest('created_at')
            ->limit(max(1, min($limit, 20)))
            ->get(['old_values', 'new_values', 'created_at']);
    }

    public function availability(Partner $partner, string $code): array
    {
        $normalized = $this->normalize($code);
        if ($normalized === '') {
            return ['available' => false, 'state' => 'empty', 'message' => 'Saisissez un code partenaire.'];
        }

        if (!preg_match('/^[A-Z0-9]{4,24}$/', $normalized)) {
            return ['available' => false, 'state' => 'invalid', 'message' => 'Le code doit contenir 4 à 24 lettres ou chiffres, sans espace ni symbole.'];
        }

        if ($this->isReserved($normalized)) {
            return ['available' => false, 'state' => 'reserved', 'message' => 'Ce code est réservé par la plateforme.'];
        }

        $current = $this->activeCode($partner);
        if ($current && $current->normalized_code === $normalized) {
            return ['available' => true, 'state' => 'current', 'message' => 'Votre code actuel est disponible.'];
        }

        if (PartnerPromoCode::query()->where('normalized_code', $normalized)->exists()) {
            return ['available' => false, 'state' => 'taken', 'message' => 'Ce code partenaire est déjà utilisé ou indisponible.'];
        }

        return ['available' => true, 'state' => 'available', 'message' => 'Ce code est disponible.'];
    }

    public function findPublic(string $code): ?PartnerPromoCode
    {
        $normalized = $this->normalize($code);
        if ($normalized === '' || $this->isReserved($normalized)) {
            return null;
        }

        return PartnerPromoCode::query()
            ->where('normalized_code', $normalized)
            ->where('status', 'active')
            ->where('is_primary', true)
            ->whereHas('partner', fn ($query) => $query->where('status', 'active')->whereNotNull('email_verified_at'))
            ->first();
    }

    public function customize(Partner $partner, string $code, string $ipAddress = '', string $userAgent = ''): PartnerPromoCode
    {
        $normalized = $this->normalize($code);
        $this->assertValid($normalized);

        return DB::transaction(function () use ($partner, $normalized, $ipAddress, $userAgent): PartnerPromoCode {
            $current = $partner->promoCodes()->lockForUpdate()->where('status', 'active')->where('is_primary', true)->latest('id')->first();
            if ($current && $current->normalized_code === $normalized) {
                return $current;
            }

            if ($this->isReserved($normalized)) {
                throw new InvalidArgumentException('Ce code est réservé par la plateforme.');
            }

            if (PartnerPromoCode::query()->where('normalized_code', $normalized)->exists()) {
                throw new InvalidArgumentException('Ce code partenaire est déjà utilisé ou indisponible.');
            }

            $lastChange = $partner->promoCodes()->whereIn('status', ['disabled', 'retired'])->max('disabled_at');
            if ($lastChange) {
                $cooldownDays = $this->configuration->integer('partners.code_change_cooldown_days', (int) config('partners.code_change_cooldown_days', 30));
                $availableAt = CarbonImmutable::parse($lastChange)->addDays($cooldownDays);
                if (now()->lt($availableAt)) {
                    throw new PartnerCodeChangeTooSoon($availableAt);
                }
            }

            if ($current) {
                $current->forceFill(['status' => 'retired', 'is_primary' => false, 'disabled_at' => now()])->save();
            }

            try {
                $created = $partner->promoCodes()->create([
                    'code' => $normalized,
                    'normalized_code' => $normalized,
                    'status' => 'active',
                    'is_primary' => true,
                    'activated_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ($this->isUniqueViolation($exception)) {
                    throw new InvalidArgumentException('Ce code partenaire est déjà utilisé ou indisponible.', 0, $exception);
                }
                throw $exception;
            }

            $this->audit($partner, 'partner.code.customized', $created, $ipAddress, $userAgent, $current?->normalized_code);
            app(PartnerPlatformAlertService::class)->dispatch('code_changed', $partner, 'code:'.$created->id, [
                'old_code' => $current?->normalized_code,
                'new_code' => $created->normalized_code,
            ]);
            return $created;
        });
    }

    public function normalize(string $code): string
    {
        return Str::upper(trim($code));
    }

    public function assertValid(string $code): void
    {
        if (!preg_match('/^[A-Z0-9]{4,24}$/', $code)) {
            throw new InvalidArgumentException('Le code doit contenir 4 à 24 lettres majuscules ou chiffres.');
        }
    }

    private function generatedCode(string $username): string
    {
        $base = Str::upper((string) preg_replace('/[^A-Za-z0-9]/', '', $username));
        $base = Str::substr($base, 0, 17);
        $code = $base.$this->randomSuffix(self::GENERATED_SUFFIX_LENGTH);
        if (strlen($code) < 4 || $this->isReserved($code)) {
            $code = 'MX'.$this->randomSuffix(8);
        }
        return $code;
    }

    private function randomSuffix(int $length): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $suffix = '';
        for ($index = 0; $index < $length; $index++) {
            $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $suffix;
    }

    private function isReserved(string $code): bool
    {
        $reserved = array_map(fn ($item) => $this->normalize((string) $item), config('partners.reserved_codes', []));
        return in_array($code, $reserved, true);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'duplicate')
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }

    private function audit(Partner $partner, string $action, PartnerPromoCode $code, string $ipAddress = '', string $userAgent = '', ?string $oldCode = null): void
    {
        PartnerAuditLog::create([
            'partner_id' => $partner->id,
            'action' => $action,
            'target_type' => PartnerPromoCode::class,
            'target_id' => (string) $code->id,
            'old_values' => $oldCode ? ['code' => $oldCode] : null,
            'new_values' => ['code' => $code->normalized_code, 'status' => $code->status],
            'ip_address' => $ipAddress ?: null,
            'user_agent_hash' => $userAgent ? hash('sha256', $userAgent) : null,
        ]);
    }
}
