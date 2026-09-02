<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'pricing' => config('marketing.plans'),
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
            'pricing' => config('marketing.plans'),
            'pricingNote' => config('marketing.pricing_note'),
        ]);
    }
}
