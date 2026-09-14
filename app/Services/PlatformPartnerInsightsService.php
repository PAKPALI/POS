<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAttribution;
use App\Models\PartnerCommission;
use App\Models\PartnerWalletEntry;
use App\Models\PartnerWithdrawal;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PlatformPartnerInsightsService
{
    public const PERIODS = [30, 90, 365];

    public function overview(int $days = 30): array
    {
        $days = in_array($days, self::PERIODS, true) ? $days : 30;
        [$start, $end] = $this->period($days);

        $partners = Partner::query();
        $partnerStats = (clone $partners)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status = 'pending_email' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended")
            ->selectRaw('SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified')
            ->selectRaw('COALESCE(SUM(qualified_clients_count), 0) as qualified_clients')
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new_in_period', [$start, $end])
            ->first();
        $partnerCount = (int) ($partnerStats->total ?? 0);
        $commission = PartnerCommission::query()
            ->where('status', '!=', 'reversed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as amount, COALESCE(SUM(gross_amount), 0) as gross')
            ->first();
        $balances = $this->balances();
        $withdrawals = PartnerWithdrawal::query()
            ->selectRaw("COUNT(*) as count, COALESCE(SUM(amount), 0) as amount, SUM(CASE WHEN status IN ('requested', 'otp_verified', 'approved', 'processing', 'unknown') THEN 1 ELSE 0 END) as open_count")
            ->first();

        return [
            'period' => $days,
            'periodStart' => $start,
            'summary' => [
                'partners' => $partnerCount,
                'active' => (int) ($partnerStats->active ?? 0),
                'pending' => (int) ($partnerStats->pending ?? 0),
                'suspended' => (int) ($partnerStats->suspended ?? 0),
                'verified_rate' => $partnerCount > 0 ? (int) round((int) ($partnerStats->verified ?? 0) * 100 / $partnerCount) : 0,
                'qualified_clients' => (int) ($partnerStats->qualified_clients ?? 0),
                'commission_count' => (int) ($commission->count ?? 0),
                'commission_amount' => (int) ($commission->amount ?? 0),
                'attributed_gross' => (int) ($commission->gross ?? 0),
                'available_balance' => (int) ($balances['available'] ?? 0),
                'reserved_balance' => (int) ($balances['reserved'] ?? 0),
                'paid_balance' => (int) ($balances['paid'] ?? 0),
                'withdrawals' => (int) ($withdrawals->count ?? 0),
                'withdrawal_amount' => (int) ($withdrawals->amount ?? 0),
                'open_withdrawals' => (int) ($withdrawals->open_count ?? 0),
                'new_in_period' => (int) ($partnerStats->new_in_period ?? 0),
            ],
            'chart' => $this->chart($start, $days),
        ];
    }

    public function partners(array $filters): LengthAwarePaginator
    {
        $available = PartnerWalletEntry::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0)")
            ->whereColumn('partner_id', 'partners.id')
            ->where('bucket', 'available');
        $commissions = PartnerCommission::query()
            ->selectRaw('COALESCE(SUM(commission_amount), 0)')
            ->whereColumn('partner_id', 'partners.id')
            ->where('status', '!=', 'reversed');
        $clients = PartnerAttribution::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('partner_id', 'partners.id')
            ->where('status', 'active');

        $query = Partner::query()
            ->select('partners.*')
            ->selectSub($available, 'available_balance')
            ->selectSub($commissions, 'commissions_total')
            ->selectSub($clients, 'active_clients_count')
            ->with(['promoCodes' => fn ($relation) => $relation->where('is_primary', true)->latest('id')]);

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('username', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['country_code'])) {
            $query->where('country_code', strtoupper((string) $filters['country_code']));
        }

        match ($filters['sort'] ?? 'recent') {
            'clients' => $query->orderByDesc('active_clients_count')->orderByDesc('created_at'),
            'commissions' => $query->orderByDesc('commissions_total')->orderByDesc('created_at'),
            'available' => $query->orderByDesc('available_balance')->orderByDesc('created_at'),
            default => $query->latest('created_at'),
        };

        return $query->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function detail(Partner $partner, int $days = 30): array
    {
        $days = in_array($days, self::PERIODS, true) ? $days : 30;
        [$start] = $this->period($days);
        $partner->load(['promoCodes' => fn ($relation) => $relation->latest('id')]);

        $commissionTotals = PartnerCommission::query()
            ->where('partner_id', $partner->id)
            ->where('status', '!=', 'reversed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as amount, COALESCE(SUM(gross_amount), 0) as gross')
            ->first();
        $balances = $this->balances($partner->id);
        $attributions = PartnerAttribution::query()->where('partner_id', $partner->id);
        $activeClients = (clone $attributions)
            ->where('status', 'active')
            ->whereHas('subscriptionAccount.latestSubscription', fn (Builder $builder) => $builder->where('status', 'active'))
            ->count();

        return [
            'period' => $days,
            'periodStart' => $start,
            'activeCode' => $partner->promoCodes->first(fn ($code) => $code->status === 'active' && $code->is_primary),
            'stats' => [
                'attributions' => (clone $attributions)->count(),
                'active_clients' => $activeClients,
                'commissions_count' => (int) ($commissionTotals->count ?? 0),
                'commission_amount' => (int) ($commissionTotals->amount ?? 0),
                'attributed_gross' => (int) ($commissionTotals->gross ?? 0),
                'withdrawals' => PartnerWithdrawal::where('partner_id', $partner->id)->count(),
                'available_balance' => (int) ($balances['available'] ?? 0),
                'reserved_balance' => (int) ($balances['reserved'] ?? 0),
                'paid_balance' => (int) ($balances['paid'] ?? 0),
            ],
            'chart' => $this->partnerChart($partner->id, $start, $days),
            'attributions' => (clone $attributions)
                ->with(['promoCode:id,code', 'subscriptionAccount.billingCompany:id,name,country_code', 'subscriptionAccount.latestSubscription.plan:id,name'])
                ->latest('attributed_at')->limit(12)->get(),
            'commissions' => PartnerCommission::query()
                ->where('partner_id', $partner->id)
                ->with(['subscriptionPayment.plan:id,name', 'attribution.subscriptionAccount.billingCompany:id,name'])
                ->latest('created_at')->limit(12)->get(),
            'withdrawals' => PartnerWithdrawal::query()
                ->where('partner_id', $partner->id)
                ->with('account')
                ->latest('requested_at')->limit(12)->get(),
            'auditLogs' => $partner->auditLogs()->latest('created_at')->limit(20)->get(),
        ];
    }

    private function chart(CarbonImmutable $start, int $days): array
    {
        $end = $start->addDays($days - 1)->endOfDay();
        $registrations = Partner::query()->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day');
        $verified = Partner::query()->whereNotNull('email_verified_at')->whereBetween('email_verified_at', [$start, $end])
            ->selectRaw('DATE(email_verified_at) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day');
        $attributions = PartnerAttribution::query()->whereBetween('attributed_at', [$start, $end])
            ->selectRaw('DATE(attributed_at) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day');
        $commissions = PartnerCommission::query()->where('status', '!=', 'reversed')->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(commission_amount), 0) as total')->groupBy('day')->pluck('total', 'day');
        $withdrawals = PartnerWithdrawal::query()->whereBetween('requested_at', [$start, $end])
            ->selectRaw('DATE(requested_at) as day, COALESCE(SUM(amount), 0) as total')->groupBy('day')->pluck('total', 'day');

        return $this->timeline($start, $days, [
            'registrations' => $registrations,
            'verified' => $verified,
            'attributions' => $attributions,
            'commissions' => $commissions,
            'withdrawals' => $withdrawals,
        ]);
    }

    private function partnerChart(int $partnerId, CarbonImmutable $start, int $days): array
    {
        $end = $start->addDays($days - 1)->endOfDay();
        $attributions = PartnerAttribution::query()->where('partner_id', $partnerId)->whereBetween('attributed_at', [$start, $end])
            ->selectRaw('DATE(attributed_at) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day');
        $commissions = PartnerCommission::query()->where('partner_id', $partnerId)->where('status', '!=', 'reversed')->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(commission_amount), 0) as total')->groupBy('day')->pluck('total', 'day');

        return $this->timeline($start, $days, ['attributions' => $attributions, 'commissions' => $commissions]);
    }

    private function timeline(CarbonImmutable $start, int $days, array $series): array
    {
        $labels = [];
        $values = array_fill_keys(array_keys($series), []);
        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->addDays($offset);
            $key = $date->toDateString();
            $labels[] = $date->translatedFormat('d M');
            foreach ($series as $name => $collection) {
                $values[$name][] = (int) ($collection[$key] ?? 0);
            }
        }

        return ['labels' => $labels] + $values;
    }

    private function balances(?int $partnerId = null): array
    {
        $query = PartnerWalletEntry::query()
            ->when($partnerId, fn (Builder $builder) => $builder->where('partner_id', $partnerId))
            ->selectRaw("bucket, COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) AS balance")
            ->groupBy('bucket')
            ->pluck('balance', 'bucket')
            ->map(fn ($value) => (int) $value)
            ->all();

        return array_merge(['pending' => 0, 'available' => 0, 'reserved' => 0, 'paid' => 0], $query);
    }

    private function period(int $days): array
    {
        $end = CarbonImmutable::now()->endOfDay();
        return [$end->subDays($days - 1)->startOfDay(), $end];
    }
}
