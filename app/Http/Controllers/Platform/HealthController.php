<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Models\PlatformAuditLog;
use App\Models\PlatformSystemHeartbeat;
use App\Models\QuotaPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HealthController extends Controller
{
    public function index()
    {
        $heartbeat = PlatformSystemHeartbeat::where('key', 'scheduler')->first();
        $heartbeatAge = $heartbeat?->last_seen_at?->diffInMinutes(now());
        $schedulerStatus = $heartbeatAge === null ? 'unknown' : ($heartbeatAge <= 3 ? 'healthy' : ($heartbeatAge <= 10 ? 'warning' : 'critical'));

        $queue = ['pending' => 0, 'failed' => 0, 'oldest_minutes' => null];
        if (Schema::hasTable('jobs')) {
            $queue['pending'] = DB::table('jobs')->count();
            $oldest = DB::table('jobs')->min('created_at');
            $queue['oldest_minutes'] = $oldest ? now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp($oldest)) : null;
        }
        if (Schema::hasTable('failed_jobs')) $queue['failed'] = DB::table('failed_jobs')->count();

        $deliveryStats = NotificationDelivery::query()->where('created_at', '>=', now()->subDays(7))
            ->select('channel', 'status', DB::raw('COUNT(*) as total'))->groupBy('channel', 'status')->get();
        $blockedPayments = QuotaPayment::withoutCompanyScope()->whereIn('status', ['created', 'pending'])
            ->where('created_at', '<=', now()->subHours(2))->count();
        $webhookPayments = QuotaPayment::withoutCompanyScope()->whereNotNull('event_id')->where('updated_at', '>=', now()->subDays(7))->count();
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->latest('failed_at')->limit(15)->get() : collect();

        return view('platform.health.index', compact('heartbeat', 'heartbeatAge', 'schedulerStatus', 'queue', 'deliveryStats', 'blockedPayments', 'webhookPayments', 'failedJobs'));
    }

    public function retryJob(Request $request, string $uuid)
    {
        abort_unless(Auth::guard('platform')->user()->hasPlatformPermission('platform.health.jobs.retry'), 403);
        $validated = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        abort_unless(Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
        $exitCode = Artisan::call('queue:retry', ['id' => [$uuid]]);
        PlatformAuditLog::create([
            'platform_admin_id' => Auth::guard('platform')->id(), 'action' => 'queue.job.retried',
            'target_type' => 'failed_job', 'target_id' => $uuid, 'reason' => $validated['reason'],
            'result' => $exitCode === 0 ? 'success' : 'failed', 'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
        ]);
        return response()->json(['message' => $exitCode === 0 ? 'Le job a été remis dans la file.' : 'La relance du job a échoué.'], $exitCode === 0 ? 200 : 500);
    }
}
