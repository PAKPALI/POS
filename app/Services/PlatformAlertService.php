<?php

namespace App\Services;

use App\Mail\PlatformSecurityMail;
use App\Models\NotificationDelivery;
use App\Models\PlatformAdmin;
use App\Models\PlatformAlertSetting;
use App\Models\PlatformOperationalAlert;
use App\Models\PlatformSystemHeartbeat;
use App\Models\QuotaPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PlatformAlertService
{
    public function inspect(): int
    {
        $settings = PlatformAlertSetting::current();
        if (!$settings->enabled) return 0;
        $detected = [];
        $heartbeat = PlatformSystemHeartbeat::where('key', 'scheduler')->first();
        if (!$heartbeat || $heartbeat->last_seen_at->lt(now()->subMinutes(10))) {
            $detected[] = ['scheduler.delayed', 'critical', 'Planificateur en retard', 'Le heartbeat du planificateur n’a pas été observé depuis plus de 10 minutes.', []];
        }
        if (Schema::hasTable('failed_jobs')) {
            $count = DB::table('failed_jobs')->count();
            if ($count >= $settings->failed_jobs_threshold) $detected[] = ['queue.failed', 'critical', 'Jobs échoués', "$count job(s) ont échoué.", ['count' => $count]];
        }
        if (Schema::hasTable('jobs')) {
            $oldest = DB::table('jobs')->min('created_at');
            $age = $oldest ? now()->diffInMinutes(Carbon::createFromTimestamp($oldest)) : 0;
            if ($age >= $settings->queue_age_minutes) $detected[] = ['queue.delayed', 'warning', 'File d’attente ralentie', "Le plus ancien job attend depuis $age minutes.", ['age_minutes' => $age]];
        }
        $blocked = QuotaPayment::withoutCompanyScope()->whereIn('status', ['created', 'pending'])
            ->where('created_at', '<=', now()->subMinutes($settings->blocked_payment_minutes))->count();
        if ($blocked > 0) $detected[] = ['payments.blocked', 'warning', 'Paiements KPrimePay bloqués', "$blocked paiement(s) dépassent le délai autorisé.", ['count' => $blocked]];

        foreach (['email', 'sms', 'whatsapp'] as $channel) {
            $stats = NotificationDelivery::where('channel', $channel)->where('created_at', '>=', now()->subHour())
                ->selectRaw('COUNT(*) total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) failed', ['failed'])->first();
            $total = (int) ($stats->total ?? 0); $failed = (int) ($stats->failed ?? 0);
            $percent = $total ? (int) round($failed * 100 / $total) : 0;
            if ($total >= $settings->delivery_minimum_volume && $percent >= $settings->delivery_failure_percent) {
                $detected[] = ["delivery.$channel", 'warning', "Échecs $channel élevés", "$percent % des $total envois de la dernière heure ont échoué.", compact('total', 'failed', 'percent')];
            }
        }

        foreach ($detected as [$type, $severity, $title, $message, $context]) $this->record($settings, $type, $severity, $title, $message, $context);
        $activeTypes = array_column($detected, 0);
        PlatformOperationalAlert::whereIn('status', ['open', 'acknowledged'])->when($activeTypes, fn($q) => $q->whereNotIn('fingerprint', $activeTypes))
            ->when(!$activeTypes, fn($q) => $q)->update(['status' => 'resolved', 'resolved_at' => now()]);
        return count($detected);
    }

    private function record(PlatformAlertSetting $settings, string $type, string $severity, string $title, string $message, array $context): void
    {
        $alert = PlatformOperationalAlert::where('fingerprint', $type)->whereIn('status', ['open', 'acknowledged'])->first();
        if (!$alert) $alert = PlatformOperationalAlert::create(['type' => $type, 'fingerprint' => $type, 'severity' => $severity,
            'status' => 'open', 'title' => $title, 'message' => $message, 'context' => $context, 'detected_at' => now(), 'last_detected_at' => now()]);
        else $alert->update(['severity' => $severity, 'message' => $message, 'context' => $context, 'last_detected_at' => now()]);
        if ($alert->last_notified_at && $alert->last_notified_at->gt(now()->subMinutes($settings->cooldown_minutes))) return;
        $recipients = PlatformAdmin::where('is_active', true)->whereIn('id', $settings->recipient_admin_ids ?: [-1])->pluck('email')->all();
        if (!$recipients) $recipients = PlatformAdmin::where('is_active', true)->whereIn('role', ['super_admin', 'technical'])->pluck('email')->all();
        $sent = false;
        foreach (array_unique($recipients) as $email) {
            try {
                Mail::to($email)->send(new PlatformSecurityMail($title, $message, actionUrl: route('platform.alerts.index'), actionLabel: 'Ouvrir les alertes', expiry: 'la résolution de l’incident'));
                $sent = true;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
        if ($sent) $alert->update(['last_notified_at' => now()]);
    }
}
