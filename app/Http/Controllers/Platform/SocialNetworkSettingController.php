<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialNetworkSettingController extends Controller
{
    private const NETWORKS = [
        'whatsapp' => ['label' => 'Communauté WhatsApp', 'icon' => 'bi-whatsapp', 'setting' => 'social.whatsapp_url', 'hint' => 'Lien d’invitation ou canal officiel de la communauté.'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'bi-tiktok', 'setting' => 'social.tiktok_url', 'hint' => 'Compte officiel destiné aux actualités Maxanou.'],
        'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook', 'setting' => 'social.facebook_url', 'hint' => 'Page ou groupe officiel Maxanou.'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram', 'setting' => 'social.instagram_url', 'hint' => 'Compte officiel destiné aux actualités Maxanou.'],
    ];

    public function edit(PlatformConfigurationService $configuration)
    {
        $networks = collect(self::NETWORKS)->map(function (array $network, string $key) use ($configuration) {
            return [...$network, 'key' => $key, 'url' => (string) $configuration->get($network['setting'], '')];
        })->values();

        return view('platform.settings.social-networks', compact('networks'));
    }

    public function update(Request $request, PlatformConfigurationService $configuration)
    {
        $rules = [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ];
        foreach (array_keys(self::NETWORKS) as $network) {
            $rules[$network.'_url'] = ['nullable', 'url', 'max:500', 'starts_with:https://'];
        }

        $data = $request->validate($rules, [
            'current_password.required' => 'Saisissez votre mot de passe plateforme pour confirmer cette modification.',
            'current_password.current_password' => 'Votre mot de passe plateforme est incorrect.',
            'reason.required' => 'Indiquez la raison de cette opération.',
            'reason.min' => 'Le motif doit contenir au moins 5 caractères.',
            'reason.max' => 'Le motif ne doit pas dépasser 500 caractères.',
        ]);

        $admin = Auth::guard('platform')->user();
        $changes = [];
        foreach (self::NETWORKS as $network => $details) {
            $changes[$details['setting']] = trim((string) ($data[$network.'_url'] ?? ''));
        }

        DB::transaction(function () use ($changes, $admin, $data, $request): void {
            foreach ($changes as $key => $value) {
                $old = (string) (PlatformSetting::where('key', $key)->value('value') ?? '');
                if ($old === $value) continue;

                PlatformSetting::updateOrCreate(['key' => $key], [
                    'value' => $value,
                    'type' => 'url',
                    'updated_by' => $admin->id,
                ]);
                PlatformSettingHistory::create([
                    'key' => $key,
                    'old_value' => $old,
                    'new_value' => $value,
                    'reason' => $data['reason'],
                    'platform_admin_id' => $admin->id,
                ]);
                PlatformAuditLog::create([
                    'platform_admin_id' => $admin->id,
                    'action' => 'platform.social_network_setting.updated',
                    'target_type' => PlatformSetting::class,
                    'target_id' => $key,
                    'old_values' => ['value' => $old],
                    'new_values' => ['value' => $value],
                    'reason' => $data['reason'],
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                ]);
            }
        });

        $configuration->forget(array_keys($changes));

        return back()->with('success', 'Les liens des réseaux sociaux ont été enregistrés.');
    }
}
