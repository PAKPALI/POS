<?php

namespace App\Http\Controllers;

use App\Services\CompanyContext;
use App\Services\EntitlementService;
use Illuminate\View\View;

class SubscriptionGuideController extends Controller
{
    public function index(CompanyContext $context, EntitlementService $entitlements): View
    {
        $company = $context->getCompany();

        return view('subscription.guide', [
            'company' => $company,
            'hasEcommerce' => $entitlements->feature($company, 'ecommerce'),
            'hasPromoCodes' => $entitlements->feature($company, 'promo_codes'),
            'hasSuppliers' => $entitlements->feature($company, 'suppliers'),
        ]);
    }
}
