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

    /**
     * Liens publics administrés depuis la console. Une plateforme sans lien
     * configuré ne publie pas l'icône correspondante.
     */
    public function socialNetworks(): array
    {
        $networks = [
            'whatsapp' => ['label' => 'Communauté WhatsApp', 'icon' => 'bi-whatsapp', 'setting' => 'social.whatsapp_url', 'description' => 'Échanger avec la communauté Maxanou'],
            'tiktok' => ['label' => 'TikTok', 'icon' => 'bi-tiktok', 'setting' => 'social.tiktok_url', 'description' => 'Suivre les actualités Maxanou'],
            'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook', 'setting' => 'social.facebook_url', 'description' => 'Suivre les actualités Maxanou'],
            'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram', 'setting' => 'social.instagram_url', 'description' => 'Suivre les actualités Maxanou'],
        ];

        $active = [];
        foreach ($networks as $key => $network) {
            $url = trim((string) $this->get($network['setting'], ''));
            if (!$this->isPublicHttpsUrl($url)) continue;
            $active[$key] = [...$network, 'url' => $url];
        }

        return $active;
    }

    private function isPublicHttpsUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL)
            && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }
}
