<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PlatformConfigurationService
{
    public function get(string $key, mixed $fallback = null): mixed
    {
        if (!Schema::hasTable('platform_settings')) return $fallback;
        return Cache::remember('platform.config.'.$key, now()->addMinutes(10), fn() => PlatformSetting::where('key',$key)->value('value') ?? $fallback);
    }
    public function boolean(string $key, bool $fallback = true): bool
    {
        return filter_var($this->get($key, $fallback ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }
    public function integer(string $key, int $fallback): int { return (int) $this->get($key, $fallback); }
    public function forget(array $keys): void { foreach($keys as $key) Cache::forget('platform.config.'.$key); }
    public function appName(): string { return (string)$this->get('identity.app_name', config('app.name')); }
    public function maintenanceEnabled(): bool { return $this->boolean('maintenance.enabled', false); }
    public function channelEnabled(string $channel): bool { return $this->boolean('services.'.$channel.'.enabled', true); }
}
