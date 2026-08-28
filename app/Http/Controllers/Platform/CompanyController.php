<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\PlatformAuditLog;
use App\Models\Product;
use App\Models\QuotaPayment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $companies = Company::query()
            ->withCount(['memberships', 'orders'])
            ->with(['memberships' => fn ($query) => $query
                ->whereHas('role', fn ($role) => $role->where('key', 'owner'))
                ->with('user:id,name,email')])
            ->when($validated['q'] ?? null, function ($query, $search) {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($search)).'%';
                $query->where(fn ($nested) => $nested
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('public_id', 'like', $term));
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $status === 'active'
                ? $query->where('status', 'active')
                : $query->where('status', '<>', 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('platform.companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        $company->load(['memberships' => fn ($query) => $query
            ->with(['user:id,name,email,phone,status', 'role:id,name,key'])
            ->latest('last_accessed_at')]);

        $stats = [
            'sales' => Sale::withoutCompanyScope()->where('company_id', $company->id)->count(),
            'sales_amount' => Sale::withoutCompanyScope()->where('company_id', $company->id)->sum('total_amount'),
            'orders' => $company->orders()->count(),
            'products' => Product::withoutCompanyScope()->where('company_id', $company->id)->count(),
            'inventories' => Inventory::withoutCompanyScope()->where('company_id', $company->id)->count(),
            'communications' => CommunicationLog::withoutCompanyScope()->where('company_id', $company->id)->count(),
            'payments' => QuotaPayment::withoutCompanyScope()->where('company_id', $company->id)->count(),
        ];

        $payments = QuotaPayment::withoutCompanyScope()
            ->where('company_id', $company->id)->latest()->limit(10)->get();

        return view('platform.companies.show', compact('company', 'stats', 'payments'));
    }

    public function updateStatus(Request $request, Company $company)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Indiquez la raison de cette opération.',
            'reason.min' => 'La raison doit contenir au moins 5 caractères.',
        ]);

        $oldStatus = $company->status;
        if ($oldStatus === $validated['status']) {
            return back()->with('info', 'Le statut de cette entreprise est déjà à jour.');
        }

        $admin = Auth::guard('platform')->user();
        DB::transaction(function () use ($request, $validated, $company, $admin, $oldStatus) {
            $company->update(['status' => $validated['status']]);
            PlatformAuditLog::create([
                'platform_admin_id' => $admin->id,
                'action' => $validated['status'] === 'active' ? 'company.reactivated' : 'company.suspended',
                'target_type' => Company::class,
                'target_id' => (string) $company->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $validated['status']],
                'reason' => $validated['reason'],
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            ]);
        });

        return back()->with('success', $validated['status'] === 'active'
            ? 'L’entreprise a été réactivée.'
            : 'L’entreprise a été suspendue.');
    }
}
