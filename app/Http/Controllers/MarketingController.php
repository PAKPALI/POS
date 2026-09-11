<?php

namespace App\Http\Controllers;

use App\Services\MarketingPlanCatalogService;
use App\Services\PartnerCountryService;
use App\Services\PlatformConfigurationService;
use Illuminate\View\View;

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
        ]);
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
