<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Services\CompanyContext;

class CategoryPolicy
{
    public function __construct(
        private CompanyContext $context,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageCatalog($user);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->canManageCatalog($user, (int) $category->company_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageCatalog($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->canManageCatalog($user, (int) $category->company_id);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->canManageCatalog($user, (int) $category->company_id);
    }

    public function restore(User $user, Category $category): bool
    {
        return $this->canManageCatalog($user, (int) $category->company_id);
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
