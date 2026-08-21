<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationSettingController extends Controller
{
    public function index(CompanyContext $context)
    {
        $company = Company::findOrFail($context->getCompanyId());
        $users = User::where('status', 1)
            ->whereHas('memberships', function ($query) use ($company) {
                $query->where('company_id', $company->id)->where('status', 'active');
            })->with(['memberships' => fn ($query) => $query->where('company_id', $company->id)->with('role')])
            ->get()
            ->sortBy(fn ($user) => sprintf(
                '%d-%s',
                in_array($user->memberships->first()?->role?->key, ['owner', 'admin'], true) ? 0 : 1,
                mb_strtolower($user->name)
            ))->values();
        $preferences = NotificationRecipient::where('company_id', $company->id)
            ->get()->keyBy(fn ($item) => $item->user_id.'-'.$item->category);

        return view('company.notifications', compact('company', 'users', 'preferences'));
    }

    public function update(Request $request, CompanyContext $context)
    {
        $company = Company::findOrFail($context->getCompanyId());
        $validated = $request->validate([
            'recipients' => ['nullable', 'array'],
            'recipients.*.*.*' => ['nullable', 'in:1'],
        ]);
        $allowedUsers = User::where('status', 1)
            ->whereHas('memberships', function ($query) use ($company) {
                $query->where('company_id', $company->id)->where('status', 'active');
            })->pluck('id');

        DB::transaction(function () use ($request, $company, $allowedUsers) {
            $company->update([
                'sale_whatsapp_enabled' => $request->boolean('sale_whatsapp_enabled'),
                'sale_sms_enabled' => $request->boolean('sale_sms_enabled'),
                'inventory_whatsapp_enabled' => $request->boolean('inventory_whatsapp_enabled'),
                'inventory_sms_enabled' => $request->boolean('inventory_sms_enabled'),
            ]);
            foreach ($allowedUsers as $userId) {
                foreach (['sale', 'inventory'] as $category) {
                    $channels = $request->input("recipients.$category.$userId", []);
                    NotificationRecipient::updateOrCreate(
                        ['company_id' => $company->id, 'user_id' => $userId, 'category' => $category],
                        [
                            'email_enabled' => isset($channels['email']),
                            'whatsapp_enabled' => isset($channels['whatsapp']),
                            'sms_enabled' => isset($channels['sms']),
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Les autorisations et destinataires ont été mis à jour.');
    }
}
