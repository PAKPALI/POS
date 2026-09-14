<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
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
            'payoutFeeBps' => $configuration->integer('partners.payout_fee_bps', (int) config('partners.payout_fee_bps', 100)),
            'autoApprovalMaxXof' => $configuration->integer('partners.auto_approval_max_xof', 0),
            'payoutGatewayCatalog' => config('partners.payout_gateway_catalog', []),
            'activePayoutGateways' => $this->payoutGateways($configuration),
            'partnerAlertAdmins' => PlatformAdmin::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'partnerAlertSettings' => $this->partnerAlertSettings($configuration),
        ]);
    }

    public function update(Request $request, PartnerCountryService $countries, PlatformConfigurationService $configuration)
    {
        $catalogCodes = array_keys($countries->catalog());
        $data = $request->validate([
            'partners_enabled' => ['nullable', 'boolean'],
            'registration_enabled' => ['nullable', 'boolean'],
            'payouts_enabled' => ['nullable', 'boolean'],
            'alerts_enabled' => ['nullable', 'boolean'],
            'alert_recipient_admin_ids' => ['nullable', 'array'],
            'alert_recipient_admin_ids.*' => ['integer', 'exists:platform_admins,id'],
            'alert_partner_registered' => ['nullable', 'boolean'],
            'alert_email_verified' => ['nullable', 'boolean'],
            'alert_code_changed' => ['nullable', 'boolean'],
            'alert_commission_created' => ['nullable', 'boolean'],
            'alert_withdrawal_requested' => ['nullable', 'boolean'],
            'alert_withdrawal_succeeded' => ['nullable', 'boolean'],
            'alert_withdrawal_failed' => ['nullable', 'boolean'],
            'alert_withdrawal_unknown' => ['nullable', 'boolean'],
            'countries' => ['required', 'array', 'min:1'],
            'countries.*' => ['required', Rule::in($catalogCodes)],
            'code_cooldown_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'payout_min_xof' => ['sometimes', 'integer', 'min:1', 'max:100000000'],
            'payout_min_qualified_clients' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'payout_fee_percent' => ['sometimes', 'numeric', 'min:0', 'max:50'],
            'auto_approval_max_xof' => ['sometimes', 'integer', 'min:0', 'max:100000000'],
            'payout_gateways' => ['nullable', 'array'],
            'payout_gateways.*' => ['array'],
            'payout_gateways.*.*' => ['string', 'max:40'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], [
            'partners_enabled.boolean' => 'Le réglage du portail partenaire est invalide.',
            'registration_enabled.boolean' => 'Le réglage des inscriptions partenaires est invalide.',
            'payouts_enabled.boolean' => 'Le réglage des retraits partenaires est invalide.',
            'alerts_enabled.boolean' => 'Le réglage des alertes partenaires est invalide.',
            'alert_recipient_admin_ids.array' => 'La liste des destinataires d’alertes est invalide.',
            'alert_recipient_admin_ids.*.exists' => 'Un destinataire sélectionné n’existe plus.',
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
        $payoutsEnabled = $request->boolean('payouts_enabled');
        if ($registrationEnabled && !$partnersEnabled) {
            return back()->withErrors(['registration_enabled' => 'L’inscription ne peut être ouverte que lorsque le portail partenaire est activé.'])->withInput();
        }

        $activeCodes = array_values(array_unique(array_map('strtoupper', $data['countries'])));
        $admin = Auth::guard('platform')->user();
        $changes = [
            'partners.enabled' => ['value' => $partnersEnabled ? 'true' : 'false', 'type' => 'boolean'],
            'partners.registration_enabled' => ['value' => $registrationEnabled ? 'true' : 'false', 'type' => 'boolean'],
            'partners.payouts_enabled' => ['value' => $payoutsEnabled ? 'true' : 'false', 'type' => 'boolean'],
            'partners.active_countries' => ['value' => json_encode($activeCodes), 'type' => 'json'],
        ];
        if ($request->boolean('partner_alerts_form')) {
            $changes['partners.alerts.enabled'] = ['value' => $request->boolean('alerts_enabled') ? 'true' : 'false', 'type' => 'boolean'];
            $changes['partners.alerts.recipient_admin_ids'] = ['value' => json_encode(array_values(array_unique(array_map('intval', (array) ($data['alert_recipient_admin_ids'] ?? []))))), 'type' => 'json'];
            foreach ([
                'alert_partner_registered' => 'partners.alerts.partner_registered',
                'alert_email_verified' => 'partners.alerts.email_verified',
                'alert_code_changed' => 'partners.alerts.code_changed',
                'alert_commission_created' => 'partners.alerts.commission_created',
                'alert_withdrawal_requested' => 'partners.alerts.withdrawal_requested',
                'alert_withdrawal_succeeded' => 'partners.alerts.withdrawal_succeeded',
                'alert_withdrawal_failed' => 'partners.alerts.withdrawal_failed',
                'alert_withdrawal_unknown' => 'partners.alerts.withdrawal_unknown',
            ] as $field => $key) {
                $changes[$key] = ['value' => $request->boolean($field) ? 'true' : 'false', 'type' => 'boolean'];
            }
        }
        if (array_key_exists('code_cooldown_days', $data)) {
            $changes['partners.code_change_cooldown_days'] = ['value' => (string) $data['code_cooldown_days'], 'type' => 'integer'];
        }
        foreach (['payout_min_xof' => 'partners.payout_min_xof', 'payout_min_qualified_clients' => 'partners.payout_min_qualified_clients', 'auto_approval_max_xof' => 'partners.auto_approval_max_xof'] as $field => $key) {
            if (array_key_exists($field, $data)) $changes[$key] = ['value' => (string) $data[$field], 'type' => 'integer'];
        }
        if (array_key_exists('payout_fee_percent', $data)) {
            $changes['partners.payout_fee_bps'] = ['value' => (string) ((int) round(((float) $data['payout_fee_percent']) * 100)), 'type' => 'integer'];
        }
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

    private function partnerAlertSettings(PlatformConfigurationService $configuration): array
    {
        $recipientIds = $configuration->get('partners.alerts.recipient_admin_ids', '[]');
        if (!is_array($recipientIds)) {
            $recipientIds = json_decode((string) $recipientIds, true) ?: [];
        }

        return [
            'enabled' => $configuration->boolean('partners.alerts.enabled', true),
            'recipient_admin_ids' => array_values(array_map('intval', $recipientIds)),
            'partner_registered' => $configuration->boolean('partners.alerts.partner_registered', true),
            'email_verified' => $configuration->boolean('partners.alerts.email_verified', true),
            'code_changed' => $configuration->boolean('partners.alerts.code_changed', false),
            'commission_created' => $configuration->boolean('partners.alerts.commission_created', false),
            'withdrawal_requested' => $configuration->boolean('partners.alerts.withdrawal_requested', true),
            'withdrawal_succeeded' => $configuration->boolean('partners.alerts.withdrawal_succeeded', true),
            'withdrawal_failed' => $configuration->boolean('partners.alerts.withdrawal_failed', true),
            'withdrawal_unknown' => $configuration->boolean('partners.alerts.withdrawal_unknown', true),
        ];
    }
}
