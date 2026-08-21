<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Services\CompanyContext;

class ClientPolicy
{
    public function __construct(
        private CompanyContext $context,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageClients($user);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->canManageClients($user, (int) $client->company_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageClients($user);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->canManageClients($user, (int) $client->company_id);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->canManageClients($user, (int) $client->company_id);
    }

    public function restore(User $user, Client $client): bool
    {
        return $this->canManageClients($user, (int) $client->company_id);
    }

    private function canManageClients(User $user, ?int $resourceCompanyId = null): bool
    {
        if (!$this->context->isResolved()) {
            return false;
        }

        $membership = $this->context->getMembershipOrNull();
        if (!$membership) {
            return false;
        }

        $activeCompanyId = $this->context->getCompanyId();

        return (int) $membership->user_id === (int) $user->id
            && ($resourceCompanyId === null || $resourceCompanyId === $activeCompanyId)
            && $membership->hasPermission('clients.manage');
    }
}
