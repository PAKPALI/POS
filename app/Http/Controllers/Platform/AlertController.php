<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Models\PlatformAlertSetting;
use App\Models\PlatformAuditLog;
use App\Models\PlatformOperationalAlert;
use App\Services\PlatformAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['open', 'acknowledged', 'resolved'])],
            'severity' => ['nullable', Rule::in(['critical', 'warning', 'info'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);
        $settings = PlatformAlertSetting::current();
        $allAlerts = PlatformOperationalAlert::query();
        $summary = [
            'total' => (clone $allAlerts)->count(),
            'open' => (clone $allAlerts)->where('status', 'open')->count(),
            'acknowledged' => (clone $allAlerts)->where('status', 'acknowledged')->count(),
            'resolved' => (clone $allAlerts)->where('status', 'resolved')->count(),
        ];
        $alerts = (clone $allAlerts)
            ->when($filters['search'] ?? null, function ($query, $value) {
                $term = '%'.$value.'%';
                $query->where(function ($search) use ($term) {
                    $search->where('title', 'like', $term)
                        ->orWhere('message', 'like', $term)
                        ->orWhere('type', 'like', $term)
                        ->orWhere('fingerprint', 'like', $term);
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['severity'] ?? null, fn ($query, $value) => $query->where('severity', $value))
            ->latest('last_detected_at')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
        $admins = PlatformAdmin::where('is_active', true)->whereIn('role', ['super_admin', 'technical'])->orderBy('name')->get();
        return view('platform.alerts.index', compact('settings', 'alerts', 'admins', 'filters', 'summary'));
    }

    public function updateSettings(Request $request)
    {
        abort_unless(Auth::guard('platform')->user()->hasPlatformPermission('platform.admins.manage'), 403);
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'], 'recipient_admin_ids' => ['nullable', 'array'],
            'recipient_admin_ids.*' => ['integer', 'exists:platform_admins,id'],
            'failed_jobs_threshold' => ['required', 'integer', 'min:1', 'max:1000'],
            'queue_age_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'blocked_payment_minutes' => ['required', 'integer', 'min:10', 'max:10080'],
            'delivery_failure_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'delivery_minimum_volume' => ['required', 'integer', 'min:1', 'max:10000'],
            'cooldown_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'reason' => ['required', 'string', 'min:5', 'max:500'], 'current_password' => ['required', 'current_password:platform'],
        ]);
        $settings = PlatformAlertSetting::current(); $old = $settings->toArray();
        $settings->update(array_merge($data, ['enabled' => $request->boolean('enabled'), 'recipient_admin_ids' => $data['recipient_admin_ids'] ?? []]));
        $this->audit($request, 'platform.alert.settings.updated', $settings->id, $old, $settings->fresh()->toArray(), $data['reason']);
        return back()->with('success', 'La surveillance automatique a été mise à jour.');
    }

    public function check(Request $request, PlatformAlertService $service)
    {
        $count = $service->inspect();
        $this->audit($request, 'platform.alert.check.manual', null, null, ['active_count' => $count], 'Vérification manuelle');
        return back()->with('success', "$count anomalie(s) active(s) détectée(s).");
    }

    public function acknowledge(Request $request, PlatformOperationalAlert $alert)
    {
        $alert->update(['status' => 'acknowledged', 'acknowledged_by' => Auth::guard('platform')->id(), 'acknowledged_at' => now()]);
        $this->audit($request, 'platform.alert.acknowledged', $alert->id, null, ['status' => 'acknowledged'], 'Prise en charge');
        return back()->with('success', 'L’alerte est marquée comme prise en charge.');
    }

    public function resolve(Request $request, PlatformOperationalAlert $alert)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $alert->update(['status' => 'resolved', 'resolved_by' => Auth::guard('platform')->id(), 'resolved_at' => now()]);
        $this->audit($request, 'platform.alert.resolved', $alert->id, null, ['status' => 'resolved'], $data['reason']);
        return back()->with('success', 'L’alerte est résolue.');
    }

    private function audit(Request $request, string $action, $id, ?array $old, array $new, string $reason): void
    {
        PlatformAuditLog::create(['platform_admin_id' => Auth::guard('platform')->id(), 'action' => $action,
            'target_type' => PlatformOperationalAlert::class, 'target_id' => $id ? (string) $id : null,
            'old_values' => $old, 'new_values' => $new, 'reason' => $reason, 'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, '')]);
    }
}
