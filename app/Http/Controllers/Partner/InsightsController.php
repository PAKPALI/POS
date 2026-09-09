<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePartnerCommissionExport;
use App\Models\PartnerAuditLog;
use App\Models\PartnerExport;
use App\Services\PartnerDashboardQueryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InsightsController extends Controller
{
    public function __construct(private PartnerDashboardQueryService $dashboard) {}

    public function dashboard()
    {
        $partner = Auth::guard('partner')->user();

        return view('partner.dashboard', array_merge([
            'partner' => $partner,
        ], $this->dashboard->dashboard($partner)));
    }

    public function clients(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', Rule::in(['active', 'reversed', 'fraudulent'])],
        ]);

        return view('partner.clients', [
            'partner' => $partner,
            'filters' => $filters,
            'clients' => $this->dashboard->clients($partner, $filters),
        ]);
    }

    public function commissions(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $filters = $this->validatedCommissionFilters($request);

        return view('partner.commissions', [
            'partner' => $partner,
            'filters' => $filters,
            'commissions' => $this->dashboard->commissions($partner, $filters),
            'exports' => PartnerExport::query()
                ->where('partner_id', $partner->id)
                ->where('type', 'commissions_csv')
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }

    public function requestCommissionExport(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        $filters = $this->validatedCommissionFilters($request);
        $export = PartnerExport::create([
            'partner_id' => $partner->id,
            'type' => 'commissions_csv',
            'filters' => $filters,
            'status' => 'queued',
            'requested_at' => now(),
        ]);
        $this->audit($partner->id, 'partner.commissions_export_requested', $export->id, $request);
        GeneratePartnerCommissionExport::dispatch($export->id)->afterCommit();

        return back()->with('success', 'Votre export CSV est en cours de préparation. Il apparaîtra ici dès qu’il sera prêt.');
    }

    public function downloadExport(Request $request, PartnerExport $export)
    {
        $partner = Auth::guard('partner')->user();
        abort_unless((int) $export->partner_id === (int) $partner->id, 404);
        abort_unless($export->type === 'commissions_csv' && $export->status === 'completed' && $export->path && $export->expires_at?->isFuture(), 404);
        abort_unless(Storage::disk('local')->exists($export->path), 404);

        $this->audit($partner->id, 'partner.commissions_export_downloaded', $export->id, $request);

        return Storage::disk('local')->download($export->path, 'commissions-partenaire-'.$export->id.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedCommissionFilters(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', Rule::in(['pending', 'available', 'reserved', 'paid', 'reversed'])],
        ]);

        if (! empty($filters['from']) && ! empty($filters['to'])
            && CarbonImmutable::parse($filters['from'])->diffInDays(CarbonImmutable::parse($filters['to'])) > 730) {
            throw ValidationException::withMessages(['to' => 'La période d’export et de consultation est limitée à 24 mois.']);
        }

        return $filters;
    }

    private function audit(int $partnerId, string $action, int $exportId, Request $request): void
    {
        PartnerAuditLog::create([
            'partner_id' => $partnerId,
            'action' => $action,
            'target_type' => PartnerExport::class,
            'target_id' => (string) $exportId,
            'ip_address' => $request->ip(),
            'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
        ]);
    }
}
