<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionAccount;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\PlatformConfigurationService;
use Illuminate\Support\Facades\Auth;

class SubscriptionPreflightController extends Controller
{
    /**
     * Read-only operational view used before enabling subscription enforcement.
     * No financial value or subscriber contract can be changed from this screen.
     */
    public function index(PlatformConfigurationService $configuration)
    {
        abort_unless(Auth::guard('platform')->user()?->hasPlatformPermission('platform.admins.manage'), 403);

        $now = now();
        $plans = SubscriptionPlan::with('features')->orderBy('rank')->get();
        $pendingPayments = SubscriptionPayment::with(['plan:id,key,name', 'subscriptionAccount.billingCompany:id,name'])
            ->whereIn('status', ['created', 'pending'])
            ->latest()
            ->limit(10)
            ->get();

        $summary = [
            'enforcement_enabled' => $configuration->boolean('subscriptions.enforcement_enabled', false),
            'kprimepay_enabled' => $configuration->channelEnabled('kprimepay'),
            'kprimepay_configured' => filled(config('services.kprimepay.token')),
            'companies_without_account' => Company::withoutGlobalScopes()->active()->whereNull('subscription_account_id')->count(),
            'accounts_total' => SubscriptionAccount::count(),
            'current_subscriptions' => Subscription::whereIn('status', ['trial', 'active'])->where('ends_at', '>', $now)->count(),
            'expired_subscriptions' => Subscription::whereIn('status', ['trial', 'active'])->where('ends_at', '<=', $now)->count(),
            'expiring_soon' => Subscription::whereIn('status', ['trial', 'active'])->whereBetween('ends_at', [$now, $now->copy()->addDays(3)])->count(),
            'pending_payments' => SubscriptionPayment::whereIn('status', ['created', 'pending'])->count(),
            'expired_pending_payments' => SubscriptionPayment::whereIn('status', ['created', 'pending'])->whereNotNull('expires_at')->where('expires_at', '<=', $now)->count(),
            'active_paid_plans' => $plans->where('is_active', true)->where('rank', '>', 0)->count(),
        ];

        return view('platform.subscriptions.preflight', compact('plans', 'pendingPayments', 'summary'));
    }
}
