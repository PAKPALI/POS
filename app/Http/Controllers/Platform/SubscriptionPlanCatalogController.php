<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionPlanCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubscriptionPlanCatalogController extends Controller
{
    public function index(SubscriptionPlanCatalogService $catalog)
    {
        abort_unless(Auth::guard('platform')->user()?->hasPlatformPermission('platform.admins.manage'), 403);
        $plans = SubscriptionPlan::with('features')->orderBy('rank')->orderBy('version')->get()
            ->groupBy(fn (SubscriptionPlan $plan) => $catalog->familyKey($plan->key));

        return view('platform.subscriptions.catalog', compact('plans'));
    }

    public function storeVersion(Request $request, SubscriptionPlan $plan, SubscriptionPlanCatalogService $catalog)
    {
        $admin = Auth::guard('platform')->user();
        abort_unless($admin?->hasPlatformPermission('platform.admins.manage'), 403);
        $data = $this->validatedData($request);
        $draft = $catalog->createVersion($plan, $data);
        $this->audit($request, $admin->id, 'subscription.plan_version.created', $draft, [], $draft->only(['key', 'name', 'version', 'monthly_price', 'annual_price', 'company_limit', 'user_limit', 'product_limit', 'sms_quota', 'whatsapp_quota']));

        return redirect()->route('platform.subscriptions.catalog')->with('success', "La version {$draft->version} de {$draft->name} est créée en brouillon. Vérifiez-la puis publiez-la explicitement.");
    }

    public function publish(Request $request, SubscriptionPlan $plan, SubscriptionPlanCatalogService $catalog)
    {
        $admin = Auth::guard('platform')->user();
        abort_unless($admin?->hasPlatformPermission('platform.admins.manage'), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500'], 'current_password' => ['required', 'current_password:platform']]);
        $old = $plan->only(['key', 'name', 'version', 'is_active']);
        $published = $catalog->publish($plan);
        $this->audit($request, $admin->id, 'subscription.plan_version.published', $published, $old, $published->only(['key', 'name', 'version', 'is_active']), $data['reason']);

        return redirect()->route('platform.subscriptions.catalog')->with('success', "{$published->name} v{$published->version} est publié. Les abonnements déjà souscrits n’ont pas été modifiés.");
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'monthly_price' => ['required', 'integer', 'min:1', 'max:10000000'],
            'annual_price' => ['required', 'integer', 'min:11', 'max:110000000'],
            'company_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'user_limit' => ['required', 'integer', 'min:1', 'max:100000'],
            'product_limit' => ['required', 'integer', 'min:1', 'max:10000000'],
            'sms_quota' => ['required', 'integer', 'min:0', 'max:10000000'],
            'whatsapp_quota' => ['required', 'integer', 'min:0', 'max:10000000'],
            'features' => ['nullable', 'array'],
            'features.suppliers' => ['nullable', 'boolean'],
            'features.ecommerce' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'current_password' => ['required', 'current_password:platform'],
        ]);
    }

    private function audit(Request $request, int $adminId, string $action, SubscriptionPlan $plan, array $old, array $new, ?string $reason = null): void
    {
        PlatformAuditLog::create(['platform_admin_id' => $adminId, 'action' => $action, 'target_type' => SubscriptionPlan::class, 'target_id' => (string) $plan->id, 'old_values' => $old, 'new_values' => $new, 'reason' => $reason ?? $request->input('reason'), 'ip_address' => $request->ip(), 'user_agent' => Str::limit((string) $request->userAgent(), 1000, '')]);
    }
}
