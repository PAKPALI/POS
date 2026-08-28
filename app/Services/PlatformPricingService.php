<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PlatformPricingService
{
    public const SMS_KEY = 'communications.sms_unit_price';
    public const WHATSAPP_KEY = 'communications.whatsapp_unit_price';
    public const SMS_COST_KEY = 'communications.sms_unit_cost';
    public const WHATSAPP_COST_KEY = 'communications.whatsapp_unit_cost';

    public function smsUnitPrice(): int
    {
        return $this->integer(self::SMS_KEY, (int) config('services.kprimepay.sms_unit_price', 35));
    }

    public function whatsappUnitPrice(): int
    {
        return $this->integer(self::WHATSAPP_KEY, (int) config('services.kprimepay.whatsapp_unit_price', 30));
    }

    public function smsUnitCost(): int
    {
        return $this->integer(self::SMS_COST_KEY, (int) config('services.kprimepay.sms_unit_cost', 15));
    }

    public function whatsappUnitCost(): int
    {
        return $this->integer(self::WHATSAPP_COST_KEY, (int) config('services.kprimepay.whatsapp_unit_cost', 15));
    }

    public function forget(): void
    {
        Cache::forget('platform.pricing.'.self::SMS_KEY);
        Cache::forget('platform.pricing.'.self::WHATSAPP_KEY);
        Cache::forget('platform.pricing.'.self::SMS_COST_KEY);
        Cache::forget('platform.pricing.'.self::WHATSAPP_COST_KEY);
    }

    private function integer(string $key, int $fallback): int
    {
        if (!Schema::hasTable('platform_settings')) {
            return $fallback;
        }

        return (int) Cache::remember('platform.pricing.'.$key, now()->addMinutes(10), function () use ($key, $fallback) {
            return PlatformSetting::where('key', $key)->value('value') ?? $fallback;
        });
    }
}
