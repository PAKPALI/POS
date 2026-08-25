<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Services\CompanyContext;

trait ChecksCompanyPermission
{
    private function allowed(User $user, string $permission, ?int $resourceCompanyId = null): bool
    {
        if (! $this->context->isResolved()) {
            return false;
        }

        $membership = $this->context->getMembershipOrNull();
        if (! $membership) {
            return false;
        }

        return (int) $membership->user_id === (int) $user->id
            && ($resourceCompanyId === null || $resourceCompanyId === $this->context->getCompanyId())
            && $membership->hasPermission($permission);
    }
}
