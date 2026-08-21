<?php

namespace App\Services;

use App\Models\CompanyUser;
use App\Models\User;

class AuthorizedLandingPage
{
    private const DESTINATIONS = [
        'dashboard.view' => 'dashboard',
        'sales.manage' => 'sale.index',
        'clients.manage' => 'client.index',
        'inventory.manage' => 'inventory.index',
        'catalog.manage' => 'product.index',
        'cash.manage' => 'ams.dashboard',
        'ecommerce.manage' => 'ecommerce.orders.index',
        'members.manage' => 'user.index',
        'company.manage' => 'company.index',
        'notifications.manage' => 'notifications.index',
    ];

    public function forMembership(CompanyUser $membership): string
    {
        $membership->loadMissing('role.permissions');

        foreach (self::DESTINATIONS as $permission => $routeName) {
            if ($membership->hasPermission($permission)) {
                return route($routeName);
            }
        }

        return route('profil');
    }

    public function forUser(User $user, ?int $activeCompanyId = null): string
    {
        $memberships = $user->activeMemberships()
            ->whereHas('company', fn ($query) => $query->where('status', 'active'))
            ->with('role.permissions')
            ->get();

        $membership = $activeCompanyId
            ? $memberships->firstWhere('company_id', $activeCompanyId)
            : null;

        if ($membership) {
            return $this->forMembership($membership);
        }

        if ($memberships->count() === 1) {
            return $this->forMembership($memberships->first());
        }

        return route('companies.select');
    }
}
