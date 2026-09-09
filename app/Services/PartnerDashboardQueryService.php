<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAttribution;
use App\Models\PartnerCommission;
use App\Models\PartnerWalletEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class PartnerDashboardQueryService
{
    public function dashboard(Partner $partner, int $days = 30): array
    {
        $days = max(7, min(30, $days));
        $start = now()->subDays($days - 1)->startOfDay();
        $balances = PartnerWalletEntry::query()
            ->where('partner_id', $partner->id)
            ->selectRaw("bucket, COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) AS balance")
            ->groupBy('bucket')
            ->pluck('balance', 'bucket')
            ->map(fn ($value) => (int) $value)
            ->all();

        $daily = PartnerCommission::query()
            ->where('partner_id', $partner->id)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) AS day, COALESCE(SUM(commission_amount), 0) AS amount')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('amount', 'day')
            ->map(fn ($value) => (int) $value)
            ->all();

        $chart = [];
        for ($offset = 0; $offset < $days; $offset++) {
            $date = CarbonImmutable::instance($start)->addDays($offset);
            $chart[] = ['label' => $date->translatedFormat('d M'), 'amount' => $daily[$date->toDateString()] ?? 0];
        }

        $activeClients = PartnerAttribution::query()
            ->where('partner_id', $partner->id)
            ->where('status', 'active')
            ->whereHas('subscriptionAccount.latestSubscription', fn (Builder $query) => $query->where('status', 'active'))
            ->count();

        return [
            'balances' => array_merge(['pending' => 0, 'available' => 0, 'reserved' => 0, 'paid' => 0], $balances),
            'qualifiedClients' => (int) $partner->qualified_clients_count,
            'activeClients' => $activeClients,
            'progress' => $this->progress((int) $partner->qualified_clients_count, (int) $partner->current_rate_bps),
            'chart' => $chart,
            'recentCommissions' => $this->commissionQuery($partner, [])
                ->with(['subscriptionPayment.plan:id,name'])
                ->latest('created_at')
                ->limit(5)
                ->get(),
        ];
    }

    public function clients(Partner $partner, array $filters): LengthAwarePaginator
    {
        $query = PartnerAttribution::query()
            ->where('partner_id', $partner->id)
            ->with([
                'subscriptionAccount.billingCompany:id,name,country_code',
                'subscriptionAccount.latestSubscription.plan:id,name',
            ])
            ->withSum('commissions as commissions_total', 'commission_amount')
            ->withMax('commissions as last_commission_at', 'created_at')
            ->latest('attributed_at');

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }
        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $query->whereHas('subscriptionAccount.billingCompany', fn (Builder $builder) => $builder->where('name', 'like', '%'.$search.'%'));
        }

        return $query->paginate(20)->withQueryString();
    }

    public function commissionQuery(Partner $partner, array $filters): Builder
    {
        $query = PartnerCommission::query()->where('partner_id', $partner->id);

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }
        if ($from = $filters['from'] ?? null) {
            $query->where('created_at', '>=', CarbonImmutable::parse($from)->startOfDay());
        }
        if ($to = $filters['to'] ?? null) {
            $query->where('created_at', '<=', CarbonImmutable::parse($to)->endOfDay());
        }

        return $query;
    }

    public function commissions(Partner $partner, array $filters): LengthAwarePaginator
    {
        return $this->commissionQuery($partner, $filters)
            ->with(['subscriptionPayment.plan:id,name', 'attribution.subscriptionAccount.billingCompany:id,name,country_code'])
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();
    }

    public function writeCommissionCsv(Partner $partner, array $filters, string $path): void
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Date', 'Type', 'Statut', 'Entreprise', 'Pays', 'Plan', 'Brut XOF', 'Remise XOF', 'Net encaissé XOF', 'Taux', 'Commission XOF', 'Devise'], ';');

        $this->commissionQuery($partner, $filters)
            ->with(['subscriptionPayment.plan:id,name', 'attribution.subscriptionAccount.billingCompany:id,name,country_code'])
            ->orderBy('id')
            ->chunkById(250, function ($commissions) use ($stream): void {
                foreach ($commissions as $commission) {
                    $company = $commission->attribution?->subscriptionAccount?->billingCompany;
                    fputcsv($stream, [
                        optional($commission->created_at)->format('Y-m-d H:i'),
                        $this->typeLabel($commission->type),
                        $this->statusLabel($commission->status),
                        $company?->name ?: '—',
                        $company?->country_code ?: '—',
                        $commission->subscriptionPayment?->plan?->name ?: '—',
                        $commission->gross_amount,
                        $commission->discount_amount,
                        $commission->net_paid_amount,
                        number_format($commission->commission_rate_bps / 100, 2, ',', ' ').' %',
                        $commission->commission_amount,
                        $commission->currency,
                    ], ';');
                }
            });

        rewind($stream);
        Storage::disk('local')->writeStream($path, $stream);
        fclose($stream);
    }

    public function typeLabel(string $type): string
    {
        return match ($type) {
            'acquisition' => 'Acquisition',
            'renewal' => 'Renouvellement',
            'upgrade' => 'Montée de plan',
            default => ucfirst($type),
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'Disponible',
            'pending' => 'En attente',
            'reserved' => 'Réservée',
            'paid' => 'Payée',
            'reversed' => 'Annulée',
            default => ucfirst($status),
        };
    }

    private function progress(int $qualifiedClients, int $rateBps): array
    {
        $nextRank = max(1, $qualifiedClients + 1);
        $nextChangeRank = $nextRank;
        while ($nextChangeRank < 176 && PartnerPromotionService::rateForRank($nextChangeRank) <= $rateBps) {
            $nextChangeRank++;
        }
        if ($rateBps >= 2500) {
            return ['percent' => 100, 'remaining' => 0, 'label' => 'Plafond de 25 % atteint'];
        }

        $nextRate = PartnerPromotionService::rateForRank($nextChangeRank);

        $previousChangeRank = max(1, $nextChangeRank - $this->tierSize($nextChangeRank));
        $size = max(1, $nextChangeRank - $previousChangeRank);
        $completed = max(0, min($size, $qualifiedClients - $previousChangeRank + 1));

        return [
            'percent' => (int) round($completed / $size * 100),
            'remaining' => max(0, $nextChangeRank - $qualifiedClients),
            'label' => sprintf('%d client%s avant %s %%', max(0, $nextChangeRank - $qualifiedClients), max(0, $nextChangeRank - $qualifiedClients) > 1 ? 's' : '', number_format($nextRate / 100, 0, ',', ' ')),
        ];
    }

    private function tierSize(int $rank): int
    {
        return match (true) {
            $rank <= 25 => 5,
            $rank <= 75 => 10,
            default => 20,
        };
    }
}
