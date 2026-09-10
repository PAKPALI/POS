<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Services\PartnerCountryService;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PartnerSettingController extends Controller
{
    public function edit(PartnerCountryService $countries, PlatformConfigurationService $configuration)
    {
        return view('platform.settings.partners', [
            'catalog' => $countries->catalog(),
            'activeCodes' => $countries->activeCodes(),
            'partnersEnabled' => $configuration->boolean('partners.enabled', false),
            'registrationEnabled' => $configuration->boolean('partners.registration_enabled', false),
            'codeCooldownDays' => $configuration->integer('partners.code_change_cooldown_days', 30),
            'payoutsEnabled' => $configuration->boolean('partners.payouts_enabled', false),
            'payoutMinXof' => $configuration->integer('partners.payout_min_xof', 5000),
            'payoutMinQualifiedClients' => $configuration->integer('partners.payout_min_qualified_clients', 3),
            'riskReviewEnabled' => $configuration->boolean('partners.risk_review_enabled', true),
            'autoApprovalMaxXof' => $configuration->integer('partners.auto_approval_max_xof', 0),
            'payoutGatewayCatalog' => config('partners.payout_gateway_catalog', []),
            'activePayoutGateways' => $this->payoutGateways($configuration),
        ]);
    }

    public function update(Request $request, PartnerCountryService $countries, PlatformConfigurationService $configuration)
    {
        $catalogCodes = array_keys($countries->catalog());
        $data = $request->validate([
            'partners_enabled' => ['nullable', 'boolean'],
            'registration_enabled' => ['nullable', 'boolean'],
            'countries' => ['required', 'array', 'min:1'],
            'countries.*' => ['required', Rule::in($catalogCodes)],
            'code_cooldown_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'payout_min_xof' => ['sometimes', 'integer', 'min:1', 'max:100000000'],
            'payout_min_qualified_clients' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'risk_review_enabled' => ['nullable', 'boolean'],
            'auto_approval_max_xof' => ['sometimes', 'integer', 'min:0', 'max:100000000'],
            'payout_gateways' => ['nullable', 'array'],
            'payout_gateways.*' => ['array'],
            'payout_gateways.*.*' => ['string', 'max:40'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], [
            'partners_enabled.boolean' => 'Le réglage du portail partenaire est invalide.',
            'registration_enabled.boolean' => 'Le réglage des inscriptions partenaires est invalide.',
            'countries.required' => 'Sélectionnez au moins un pays actif.',
            'countries.array' => 'La sélection des pays actifs est invalide.',
            'countries.min' => 'Sélectionnez au moins un pays actif.',
            'countries.*.required' => 'Chaque pays actif doit être renseigné.',
            'countries.*.in' => 'Ce pays n’est pas disponible dans le catalogue.',
            'code_cooldown_days.required' => 'Indiquez le délai de modification du code partenaire.',
            'code_cooldown_days.integer' => 'Le délai de modification doit être un nombre entier de jours.',
            'code_cooldown_days.min' => 'Le délai de modification doit être d’au moins 1 jour.',
            'code_cooldown_days.max' => 'Le délai de modification ne peut pas dépasser 365 jours.',
            'current_password.required' => 'Saisissez votre mot de passe plateforme pour confirmer cette modification.',
            'current_password.current_password' => 'Votre mot de passe plateforme est incorrect.',
            'reason.required' => 'Indiquez la raison de cette opération.',
            'reason.string' => 'La raison doit être renseignée sous forme de texte.',
            'reason.min' => 'La raison doit contenir au moins 5 caractères.',
            'reason.max' => 'La raison ne doit pas dépasser 500 caractères.',
        ]);

        $partnersEnabled = $request->boolean('partners_enabled');
        $registrationEnabled = $request->boolean('registration_enabled');
        if ($registrationEnabled && !$partnersEnabled) {
            return back()->withErrors(['registration_enabled' => 'L’inscription ne peut être ouverte que lorsque le portail partenaire est activé.'])->withInput();
        }

        $activeCodes = array_values(array_unique(array_map('strtoupper', $data['countries'])));
        $admin = Auth::guard('platform')->user();
        $changes = [
            'partners.enabled' => ['value' => $partnersEnabled ? 'true' : 'false', 'type' => 'boolean'],
            'partners.registration_enabled' => ['value' => $registrationEnabled ? 'true' : 'false', 'type' => 'boolean'],
            'partners.active_countries' => ['value' => json_encode($activeCodes), 'type' => 'json'],
        ];
        if (array_key_exists('code_cooldown_days', $data)) {
            $changes['partners.code_change_cooldown_days'] = ['value' => (string) $data['code_cooldown_days'], 'type' => 'integer'];
        }
        foreach (['payout_min_xof' => 'partners.payout_min_xof', 'payout_min_qualified_clients' => 'partners.payout_min_qualified_clients', 'auto_approval_max_xof' => 'partners.auto_approval_max_xof'] as $field => $key) {
            if (array_key_exists($field, $data)) $changes[$key] = ['value' => (string) $data[$field], 'type' => 'integer'];
        }
        if ($request->has('risk_review_enabled')) $changes['partners.risk_review_enabled'] = ['value' => $request->boolean('risk_review_enabled') ? 'true' : 'false', 'type' => 'boolean'];
        if ($request->has('payout_gateways')) {
            $catalog = config('partners.payout_gateway_catalog', []);
            $selected = [];
            foreach ((array) $request->input('payout_gateways', []) as $country => $gateways) {
                $country = strtoupper((string) $country);
                if (!in_array($country, $activeCodes, true) || !isset($catalog[$country])) continue;
                $allowed = array_keys($catalog[$country]);
                $valid = array_values(array_unique(array_filter((array) $gateways, fn ($gateway) => in_array($gateway, $allowed, true))));
                if ($valid !== []) $selected[$country] = $valid;
            }
            $changes['partners.payout_gateways'] = ['value' => json_encode($selected), 'type' => 'json'];
        }

        DB::transaction(function () use ($changes, $admin, $data, $request): void {
            foreach ($changes as $key => $change) {
                $old = PlatformSetting::where('key', $key)->value('value') ?? '';
                if ((string) $old === $change['value']) {
                    continue;
                }

                PlatformSetting::updateOrCreate(['key' => $key], [
                    'value' => $change['value'],
                    'type' => $change['type'],
                    'updated_by' => $admin->id,
                ]);
                PlatformSettingHistory::create([
                    'key' => $key,
                    'old_value' => (string) $old,
                    'new_value' => $change['value'],
                    'reason' => $data['reason'],
                    'platform_admin_id' => $admin->id,
                ]);
                PlatformAuditLog::create([
                    'platform_admin_id' => $admin->id,
                    'action' => 'platform.partner_setting.updated',
                    'target_type' => PlatformSetting::class,
                    'target_id' => $key,
                    'old_values' => ['value' => $old],
                    'new_values' => ['value' => $change['value']],
                    'reason' => $data['reason'],
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                ]);
            }
        });

        $configuration->forget(array_keys($changes));

        return back()->with('success', 'La configuration du programme partenaire a été enregistrée.');
    }

    private function payoutGateways(PlatformConfigurationService $configuration): array
    {
        $fallback = config('partners.payout_gateways', []);
        $stored = $configuration->get('partners.payout_gateways', json_encode($fallback));
        return is_array($stored) ? $stored : (json_decode((string) $stored, true) ?: $fallback);
    }
}
