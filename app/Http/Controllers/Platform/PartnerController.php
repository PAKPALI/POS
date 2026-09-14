<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\PlatformPartnerInsightsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function __construct(private PlatformPartnerInsightsService $insights) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'pending_email', 'suspended'])],
            'country_code' => ['nullable', 'string', 'size:2'],
            'sort' => ['nullable', Rule::in(['recent', 'clients', 'commissions', 'available'])],
            'period' => ['nullable', 'integer', Rule::in(PlatformPartnerInsightsService::PERIODS)],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50])],
        ]);

        $filters['period'] = (int) ($filters['period'] ?? 30);
        $filters['sort'] = $filters['sort'] ?? 'recent';
        $filters['per_page'] = (int) ($filters['per_page'] ?? 20);

        return view('platform.partners.index', [
            'overview' => $this->insights->overview($filters['period']),
            'partners' => $this->insights->partners($filters),
            'filters' => $filters,
            'countries' => Partner::query()->select('country_code')->distinct()->orderBy('country_code')->pluck('country_code'),
        ]);
    }

    public function show(Request $request, Partner $partner)
    {
        $filters = $request->validate([
            'period' => ['nullable', 'integer', Rule::in(PlatformPartnerInsightsService::PERIODS)],
        ]);
        $period = (int) ($filters['period'] ?? 30);

        return view('platform.partners.show', array_merge(
            ['partner' => $partner, 'filters' => ['period' => $period]],
            $this->insights->detail($partner, $period),
        ));
    }
}
