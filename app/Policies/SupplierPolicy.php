<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Services\CompanyContext;

class SupplierPolicy
{
    public function __construct(
        private CompanyContext $context,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageCatalog($user);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $this->canManageCatalog($user, (int) $supplier->company_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageCatalog($user);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->canManageCatalog($user, (int) $supplier->company_id);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->canManageCatalog($user, (int) $supplier->company_id);
    }

    public function restore(User $user, Supplier $supplier): bool
    {
        return $this->canManageCatalog($user, (int) $supplier->company_id);
    }

    private function canManageCatalog(User $user, ?int $resourceCompanyId = null): bool
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
            && $membership->hasPermission('catalog.manage');
    }
}
