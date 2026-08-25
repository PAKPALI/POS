<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;
use App\Policies\Concerns\ChecksCompanyPermission;
use App\Services\CompanyContext;

class InventoryPolicy
{
    use ChecksCompanyPermission;

    public function __construct(private CompanyContext $context) {}

    public function viewAny(User $user): bool { return $this->allowed($user, 'inventory.manage'); }
    public function view(User $user, Inventory $inventory): bool { return $this->allowed($user, 'inventory.manage', (int) $inventory->company_id); }
    public function create(User $user): bool { return $this->allowed($user, 'inventory.manage'); }
    public function export(User $user): bool { return $this->allowed($user, 'inventory.manage'); }
}
