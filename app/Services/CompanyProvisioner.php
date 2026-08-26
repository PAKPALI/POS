<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\NotificationRecipient;
use Illuminate\Support\Facades\DB;

class CompanyProvisioner
{
    public const PERMISSIONS = [
        'dashboard.view' => 'Tableau de bord',
        'catalog.manage' => 'Catalogue',
        'inventory.manage' => 'Inventaire',
        'sales.manage' => 'Ventes',
        'clients.manage' => 'Clients',
        'cash.manage' => 'Comptabilité',
        'ecommerce.manage' => 'E-commerce',
        'members.manage' => 'Utilisateurs',
        'company.manage' => 'Paramètres de la compagnie',
        'notifications.manage' => 'Gérer les notifications',
        'quota.manage' => 'Acheter des quotas SMS et WhatsApp',
        'reports.view_margin' => 'Marges et bénéfices',
    ];

    public function provision(Company $company, User $owner, ?float $defaultTax = null): CompanyUser
    {
        return DB::transaction(function () use ($company, $owner, $defaultTax) {
            $permissions = collect(self::PERMISSIONS)->map(function ($description, $key) {
                return Permission::firstOrCreate(
                    ['key' => $key],
                    ['module' => str($key)->before('.')->toString(), 'description' => $description]
                );
            });

            $ownerRole = Role::firstOrCreate(
                ['company_id' => $company->id, 'key' => 'owner'],
                ['name' => 'Propriétaire', 'is_system' => true]
            );
            $ownerRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));

            $adminRole = Role::firstOrCreate(
                ['company_id' => $company->id, 'key' => 'admin'],
                ['name' => 'Administrateur', 'is_system' => true]
            );
            $adminRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));

            $cashierRole = Role::firstOrCreate(
                ['company_id' => $company->id, 'key' => 'cashier'],
                ['name' => 'Caissier', 'is_system' => true]
            );
            $cashierRole->permissions()->syncWithoutDetaching(
                $permissions->whereIn('key', ['dashboard.view', 'sales.manage', 'clients.manage'])->pluck('id')
            );

            $membership = CompanyUser::updateOrCreate(
                ['company_id' => $company->id, 'user_id' => $owner->id],
                ['role_id' => $ownerRole->id, 'status' => 'active', 'joined_at' => now()]
            );

            $mainCash = CashAccount::withoutCompanyScope()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MAIN-'.$company->id],
                [
                    'name' => 'Caisse principale', 'balance' => 0, 'currency' => 'F CFA',
                    'is_default' => true, 'is_tax' => false, 'status' => true,
                    'description' => 'Caisse principale créée automatiquement', 'created_by' => $owner->id,
                ]
            );
            $taxCash = CashAccount::withoutCompanyScope()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'TAX-'.$company->id],
                [
                    'name' => 'Caisse de taxe', 'balance' => 0, 'currency' => 'F CFA',
                    'is_default' => false, 'is_tax' => true, 'status' => true,
                    'description' => 'Caisse de taxe créée automatiquement', 'created_by' => $owner->id,
                ]
            );
            Setting::withoutCompanyScope()->updateOrCreate(
                ['company_id' => $company->id],
                [
                    'default_cash_id' => $mainCash->id,
                    'tax_cash_id' => $taxCash->id,
                    'default_tax' => $defaultTax ?? 0,
                ]
            );

            foreach (['sale', 'inventory'] as $category) {
                NotificationRecipient::updateOrCreate(
                    ['company_id' => $company->id, 'user_id' => $owner->id, 'category' => $category],
                    ['email_enabled' => true, 'whatsapp_enabled' => true, 'sms_enabled' => false]
                );
            }

            return $membership;
        });
    }
}
