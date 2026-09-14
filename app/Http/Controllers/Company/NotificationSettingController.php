<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NotificationSettingController extends Controller
{
    private const GLOBAL_SETTINGS = [
        'sale_email_enabled',
        'sale_whatsapp_enabled',
        'sale_sms_enabled',
        'invoice_whatsapp_enabled',
        'invoice_sms_enabled',
        'inventory_email_enabled',
        'inventory_whatsapp_enabled',
        'inventory_sms_enabled',
    ];

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

    /**
     * Persist a single notification switch without submitting the whole page.
     */
    public function toggle(Request $request, CompanyContext $context)
    {
        $company = Company::findOrFail($context->getCompanyId());
        $validated = $request->validate([
            'scope' => ['required', Rule::in(['global', 'recipient'])],
            'enabled' => ['required', 'boolean'],
            'setting' => ['required_if:scope,global', Rule::in(self::GLOBAL_SETTINGS)],
            'category' => ['required_if:scope,recipient', Rule::in(['sale', 'inventory'])],
            'user_id' => ['required_if:scope,recipient', 'integer'],
            'channel' => ['required_if:scope,recipient', Rule::in(['email', 'whatsapp', 'sms'])],
        ]);

        $enabled = (bool) $validated['enabled'];

        if ($validated['scope'] === 'global') {
            $setting = $validated['setting'];
            $company->update([$setting => $enabled]);

            return response()->json([
                'status' => true,
                'title' => 'Configuration enregistrée',
                'msg' => $enabled ? 'Canal activé.' : 'Canal désactivé.',
                'enabled' => (bool) $company->fresh()->{$setting},
            ]);
        }

        $user = User::where('status', 1)
            ->whereKey($validated['user_id'])
            ->whereHas('memberships', function ($query) use ($company) {
                $query->where('company_id', $company->id)->where('status', 'active');
            })->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'title' => 'Destinataire introuvable',
                'msg' => 'Cet utilisateur ne peut pas recevoir les notifications de cette entreprise.',
            ], 422);
        }

        $channel = $validated['channel'];
        if ($enabled && in_array($channel, ['sms', 'whatsapp'], true) && !$user->phone) {
            return response()->json([
                'status' => false,
                'title' => 'Numéro requis',
                'msg' => 'Veuillez renseigner un numéro de téléphone avant d’activer WhatsApp ou SMS.',
            ], 422);
        }

        NotificationRecipient::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id, 'category' => $validated['category']],
            [$channel.'_enabled' => $enabled]
        );

        return response()->json([
            'status' => true,
            'title' => 'Configuration enregistrée',
            'msg' => $enabled ? 'Préférence activée.' : 'Préférence désactivée.',
            'enabled' => $enabled,
        ]);
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
            })->get(['id', 'phone']);

        foreach ($allowedUsers as $allowedUser) {
            foreach (['sale', 'inventory'] as $category) {
                $channels = $request->input("recipients.$category.{$allowedUser->id}", []);
                if (!$allowedUser->phone && (isset($channels['sms']) || isset($channels['whatsapp']))) {
                    return back()->with('error', 'Veuillez renseigner un numéro de téléphone pour cet utilisateur avant d’activer SMS ou WhatsApp.');
                }
            }
        }

        DB::transaction(function () use ($request, $company, $allowedUsers) {
            $company->update([
                'sale_email_enabled' => $request->boolean('sale_email_enabled'),
                'sale_whatsapp_enabled' => $request->boolean('sale_whatsapp_enabled'),
                'sale_sms_enabled' => $request->boolean('sale_sms_enabled'),
                'invoice_whatsapp_enabled' => $request->boolean('invoice_whatsapp_enabled'),
                'invoice_sms_enabled' => $request->boolean('invoice_sms_enabled'),
                'inventory_email_enabled' => $request->boolean('inventory_email_enabled'),
                'inventory_whatsapp_enabled' => $request->boolean('inventory_whatsapp_enabled'),
                'inventory_sms_enabled' => $request->boolean('inventory_sms_enabled'),
            ]);
            foreach ($allowedUsers as $allowedUser) {
                $userId = $allowedUser->id;
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
