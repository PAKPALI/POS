<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Services\PlatformPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function edit(PlatformPricingService $pricing)
    {
        abort_unless(Auth::guard('platform')->user()->hasPlatformPermission('platform.pricing.manage'), 403);
        $smsUnitPrice = $pricing->smsUnitPrice();
        $whatsappUnitPrice = $pricing->whatsappUnitPrice();
        $smsUnitCost = $pricing->smsUnitCost();
        $whatsappUnitCost = $pricing->whatsappUnitCost();
        $history = PlatformSettingHistory::with('admin:id,name')->latest()->limit(20)->get();
        return view('platform.settings.edit', compact('smsUnitPrice', 'whatsappUnitPrice', 'smsUnitCost', 'whatsappUnitCost', 'history'));
    }

    public function update(Request $request, PlatformPricingService $pricing)
    {
        $admin = Auth::guard('platform')->user();
        abort_unless($admin->hasPlatformPermission('platform.pricing.manage'), 403);
        $validated = $request->validate([
            'sms_unit_price' => ['required', 'integer', 'min:1', 'max:10000'],
            'whatsapp_unit_price' => ['required', 'integer', 'min:1', 'max:10000'],
            'sms_unit_cost' => ['required', 'integer', 'min:0', 'max:10000', 'lte:sms_unit_price'],
            'whatsapp_unit_cost' => ['required', 'integer', 'min:0', 'max:10000', 'lte:whatsapp_unit_price'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ], [
            'sms_unit_price.min' => 'Le prix d’un SMS doit être supérieur à zéro.',
            'whatsapp_unit_price.min' => 'Le prix d’un message WhatsApp doit être supérieur à zéro.',
            'sms_unit_cost.lte' => 'Le coût SMS ne peut pas dépasser son prix de vente.',
            'whatsapp_unit_cost.lte' => 'Le coût WhatsApp ne peut pas dépasser son prix de vente.',
            'reason.required' => 'Indiquez la raison du changement tarifaire.',
            'reason.min' => 'La raison doit contenir au moins 5 caractères.',
            'current_password.current_password' => 'Le mot de passe plateforme est incorrect.',
        ]);

        $changes = [
            PlatformPricingService::SMS_KEY => (int) $validated['sms_unit_price'],
            PlatformPricingService::WHATSAPP_KEY => (int) $validated['whatsapp_unit_price'],
            PlatformPricingService::SMS_COST_KEY => (int) $validated['sms_unit_cost'],
            PlatformPricingService::WHATSAPP_COST_KEY => (int) $validated['whatsapp_unit_cost'],
        ];

        DB::transaction(function () use ($request, $admin, $validated, $changes) {
            foreach ($changes as $key => $newValue) {
                $setting = PlatformSetting::where('key', $key)->lockForUpdate()->first();
                $oldValue = $setting?->value ?? (string) match ($key) {
                    PlatformPricingService::SMS_KEY => config('services.kprimepay.sms_unit_price', 35),
                    PlatformPricingService::WHATSAPP_KEY => config('services.kprimepay.whatsapp_unit_price', 30),
                    PlatformPricingService::SMS_COST_KEY => config('services.kprimepay.sms_unit_cost', 15),
                    default => config('services.kprimepay.whatsapp_unit_cost', 15),
                };
                if ((int) $oldValue === $newValue) continue;

                PlatformSetting::updateOrCreate(['key' => $key], ['value' => (string) $newValue, 'type' => 'integer', 'updated_by' => $admin->id]);
                PlatformSettingHistory::create(['key' => $key, 'old_value' => (string) $oldValue, 'new_value' => (string) $newValue, 'reason' => $validated['reason'], 'platform_admin_id' => $admin->id]);
                PlatformAuditLog::create([
                    'platform_admin_id' => $admin->id, 'action' => 'platform.pricing.updated',
                    'target_type' => PlatformSetting::class, 'target_id' => $key,
                    'old_values' => ['value' => (int) $oldValue], 'new_values' => ['value' => $newValue],
                    'reason' => $validated['reason'], 'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                ]);
            }
        });

        $pricing->forget();
        return back()->with('success', 'Les nouveaux tarifs sont enregistrés. Ils s’appliqueront uniquement aux prochains checkouts.');
    }
}
