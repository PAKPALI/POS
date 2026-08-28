<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\QuotaPayment;
use App\Services\KprimePayService;
use App\Services\QuotaPaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:created,pending,paid,failed,expired'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $base = QuotaPayment::withoutCompanyScope()
            ->with(['company:id,name,slug', 'user:id,name,email'])
            ->when($validated['q'] ?? null, function ($query, $search) {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($search)).'%';
                $query->where(fn ($nested) => $nested
                    ->where('transaction_id', 'like', $term)
                    ->orWhere('kpp_reference', 'like', $term)
                    ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $term)));
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        $payments = (clone $base)->latest()->paginate(20)->withQueryString();
        $summary = [
            'total' => (clone $base)->count(),
            'paid' => (clone $base)->where('status', 'paid')->count(),
            'pending' => (clone $base)->whereIn('status', ['created', 'pending'])->count(),
            'failed' => (clone $base)->whereIn('status', ['failed', 'expired'])->count(),
            'revenue' => (clone $base)->where('status', 'paid')->sum('amount'),
        ];

        $paidFinancials = (clone $base)->where('status', 'paid')->selectRaw(
            'COALESCE(SUM(sms_quantity * COALESCE(sms_unit_price, 35)), 0) as sms_revenue,
             COALESCE(SUM(sms_quantity * COALESCE(sms_unit_cost, 15)), 0) as sms_cost,
             COALESCE(SUM(whatsapp_quantity * COALESCE(whatsapp_unit_price, 30)), 0) as whatsapp_revenue,
             COALESCE(SUM(whatsapp_quantity * COALESCE(whatsapp_unit_cost, 15)), 0) as whatsapp_cost,
             COALESCE(SUM(amount), 0) as total_revenue'
        )->first();
        $financials = [
            'sms_revenue' => (int) $paidFinancials->sms_revenue,
            'sms_cost' => (int) $paidFinancials->sms_cost,
            'sms_profit' => (int) $paidFinancials->sms_revenue - (int) $paidFinancials->sms_cost,
            'whatsapp_revenue' => (int) $paidFinancials->whatsapp_revenue,
            'whatsapp_cost' => (int) $paidFinancials->whatsapp_cost,
            'whatsapp_profit' => (int) $paidFinancials->whatsapp_revenue - (int) $paidFinancials->whatsapp_cost,
            'total_revenue' => (int) $paidFinancials->total_revenue,
            'total_cost' => (int) $paidFinancials->sms_cost + (int) $paidFinancials->whatsapp_cost,
        ];
        $financials['total_profit'] = $financials['total_revenue'] - $financials['total_cost'];

        return view('platform.payments.index', compact('payments', 'summary', 'financials'));
    }

    public function show(QuotaPayment $payment)
    {
        $payment = QuotaPayment::withoutCompanyScope()->with(['company:id,name,slug,sms_count,whatsapp_count', 'user:id,name,email'])->findOrFail($payment->id);
        return view('platform.payments.show', compact('payment'));
    }

    public function reconcile(Request $request, QuotaPayment $payment, KprimePayService $kprimePay, QuotaPaymentSettlementService $settlement)
    {
        $payment = QuotaPayment::withoutCompanyScope()->findOrFail($payment->id);
        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Ce paiement est déjà confirmé et les quotas ont déjà été crédités.'], 422);
        }

        $validated = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $admin = Auth::guard('platform')->user();

        try {
            $verified = $kprimePay->paymentStatus($payment->transaction_id);
            $status = strtolower((string) ($verified['status'] ?? $verified['payment_status'] ?? ''));
            $action = 'payment.reconciliation.unchanged';
            $message = 'KPrimePay indique que ce paiement est toujours en attente.';

            if ($status === 'success') {
                $settlement->creditVerified($payment, $verified, null, $verified['kpp_tx_reference'] ?? null);
                $action = 'payment.reconciliation.paid';
                $message = 'Paiement confirmé : les quotas ont été crédités une seule fois.';
            } elseif (in_array($status, ['failed', 'failure', 'error', 'cancelled', 'canceled'], true)) {
                $settlement->markFailed($payment, (string) ($verified['failure_reason'] ?? $verified['message'] ?? 'Paiement refusé'));
                $action = 'payment.reconciliation.failed';
                $message = 'KPrimePay confirme l’échec du paiement.';
            } elseif ($status === 'expired') {
                $settlement->markExpired($payment);
                $action = 'payment.reconciliation.expired';
                $message = 'KPrimePay confirme l’expiration du paiement.';
            }

            PlatformAuditLog::create([
                'platform_admin_id' => $admin->id, 'action' => $action,
                'target_type' => QuotaPayment::class, 'target_id' => (string) $payment->id,
                'old_values' => ['status' => $payment->status],
                'new_values' => ['provider_status' => $status, 'status' => $payment->fresh()->status],
                'reason' => $validated['reason'], 'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            ]);

            return response()->json(['message' => $message, 'status' => $payment->fresh()->status]);
        } catch (Throwable $exception) {
            PlatformAuditLog::create([
                'platform_admin_id' => $admin->id, 'action' => 'payment.reconciliation.error',
                'target_type' => QuotaPayment::class, 'target_id' => (string) $payment->id,
                'reason' => $validated['reason'], 'result' => 'failed',
                'new_values' => ['error' => class_basename($exception)],
                'ip_address' => $request->ip(), 'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            ]);
            report($exception);
            return response()->json(['message' => 'La vérification KPrimePay a échoué. Aucun quota n’a été modifié.'], 502);
        }
    }
}
