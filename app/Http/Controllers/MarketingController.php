<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Partner;
use App\Models\PartnerAttribution;
use App\Models\PartnerWithdrawal;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MarketingPlanCatalogService;
use App\Services\PartnerCountryService;
use App\Services\PlatformConfigurationService;
use Illuminate\View\View;
use Throwable;

class MarketingController extends Controller
{
    public function __construct(
        private MarketingPlanCatalogService $plans,
        private PlatformConfigurationService $configuration,
        private PartnerCountryService $partnerCountries,
    ) {}

    public function home(): View
    {
        return view('marketing.home', [
            'pricing' => $this->plans->plans(),
            'pricingNote' => config('marketing.pricing_note'),
            'publicStats' => $this->publicStats(),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'pricing' => $this->plans->plans(),
            'pricingNote' => config('marketing.pricing_note'),
        ]);
    }

    public function page(string $page): View
    {
        abort_unless(array_key_exists($page, config('marketing.pages')), 404);

        $content = config('marketing.pages.'.$page);

        return view('marketing.page', [
            'page' => $page,
            'content' => $content,
            'pricing' => $this->plans->plans(),
            'pricingNote' => config('marketing.pricing_note'),
            'partnerProgram' => $page === 'partenaires' ? $this->partnerProgram() : null,
            'publicStats' => $page === 'partenaires' ? $this->publicStats() : null,
        ]);
    }

    /**
     * Return only anonymous platform totals suitable for the public website.
     * No names, contacts, financial amounts or per-account data are exposed.
     */
    private function publicStats(): array
    {
        $empty = [
            'users' => 0, 'companies' => 0, 'active_companies' => 0,
            'active_subscriptions' => 0, 'active_paid_subscriptions' => 0, 'active_trial_subscriptions' => 0,
            'partners' => 0, 'active_partners' => 0, 'partner_clients' => 0, 'completed_withdrawals' => 0,
        ];

        try {
            $now = now();
            $companyStats = Company::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
                ->first();
            $subscriptionStats = Subscription::query()
                ->whereIn('status', ['trial', 'active'])
                ->where('ends_at', '>', $now)
                ->selectRaw('COUNT(*) as current')
                ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as paid")
                ->selectRaw("SUM(CASE WHEN status = 'trial' THEN 1 ELSE 0 END) as trials")
                ->first();
            $partnerStats = Partner::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
                ->first();

            return [
                'users' => (int) User::query()->count(),
                'companies' => (int) ($companyStats->total ?? 0),
                'active_companies' => (int) ($companyStats->active ?? 0),
                'active_subscriptions' => (int) ($subscriptionStats->current ?? 0),
                'active_paid_subscriptions' => (int) ($subscriptionStats->paid ?? 0),
                'active_trial_subscriptions' => (int) ($subscriptionStats->trials ?? 0),
                'partners' => (int) ($partnerStats->total ?? 0),
                'active_partners' => (int) ($partnerStats->active ?? 0),
                'partner_clients' => (int) PartnerAttribution::query()->where('status', 'active')->count(),
                'completed_withdrawals' => (int) PartnerWithdrawal::query()->where('status', 'succeeded')->count(),
            ];
        } catch (Throwable) {
            // Le site marketing reste disponible même lorsqu'une ancienne
            // installation n'a pas encore les tables de statistiques.
            return $empty;
        }
    }

    private function partnerProgram(): array
    {
        return [
            'enabled' => $this->configuration->boolean('partners.enabled', (bool) config('partners.enabled', false)),
            'registration_enabled' => $this->configuration->boolean('partners.registration_enabled', (bool) config('partners.registration_enabled', false)),
            'payouts_enabled' => $this->configuration->boolean('partners.payouts_enabled', (bool) config('partners.payouts_enabled', false)),
            'discount_percent' => intdiv($this->configuration->integer('partners.first_discount_bps', (int) config('partners.first_discount_bps', 1000)), 100),
            'hold_days' => $this->configuration->integer('partners.commission_hold_days', (int) config('partners.commission_hold_days', 0)),
            'payout_min_xof' => $this->configuration->integer('partners.payout_min_xof', (int) config('partners.payout_min_xof', 5000)),
            'required_clients' => $this->configuration->integer('partners.payout_min_qualified_clients', (int) config('partners.payout_min_qualified_clients', 3)),
            'countries' => collect($this->partnerCountries->activeCountries())->pluck('name')->values()->all(),
        ];
    }
}
