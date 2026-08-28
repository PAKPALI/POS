<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\QuotaPayment;
use App\Models\Sale;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $companyStats = Company::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status <> 'active' THEN 1 ELSE 0 END) as inactive")
            ->first();

        $paymentStats = QuotaPayment::withoutCompanyScope()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('paid', 'success', 'completed') THEN 1 ELSE 0 END) as paid")
            ->selectRaw("SUM(CASE WHEN status IN ('created', 'pending', 'processing') THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status IN ('failed', 'refused', 'expired', 'cancelled') THEN 1 ELSE 0 END) as failed")
            ->first();

        $stats = [
            'companies' => (int) ($companyStats->total ?? 0),
            'active_companies' => (int) ($companyStats->active ?? 0),
            'inactive_companies' => (int) ($companyStats->inactive ?? 0),
            'users' => User::count(),
            'active_memberships' => CompanyUser::where('status', 'active')->count(),
            'pending_invitations' => CompanyInvitation::query()
                ->whereNull('accepted_at')
                ->whereNull('declined_at')
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->count(),
            'sales' => Sale::withoutCompanyScope()->count(),
            'orders' => Order::withoutCompanyScope()->count(),
            'communications' => CommunicationLog::withoutCompanyScope()->count(),
            'payments' => (int) ($paymentStats->total ?? 0),
            'paid_payments' => (int) ($paymentStats->paid ?? 0),
            'pending_payments' => (int) ($paymentStats->pending ?? 0),
            'failed_payments' => (int) ($paymentStats->failed ?? 0),
        ];

        $recentCompanies = Company::query()
            ->withCount(['memberships', 'orders'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = QuotaPayment::withoutCompanyScope()
            ->with([])
            ->latest()
            ->limit(8)
            ->get();

        return view('platform.dashboard', compact('stats', 'recentCompanies', 'recentPayments'));
    }
}
