<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Services\CompanyContext;

class ProductPolicy
{
    public function __construct(
        private CompanyContext $context,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageCatalog($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->canManageCatalog($user, (int) $product->company_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageCatalog($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->canManageCatalog($user, (int) $product->company_id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->canManageCatalog($user, (int) $product->company_id);
    }

    public function restore(User $user, Product $product): bool
    {
        return $this->canManageCatalog($user, (int) $product->company_id);
    }

    public function export(User $user): bool
    {
        return $this->canManageCatalog($user);
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
