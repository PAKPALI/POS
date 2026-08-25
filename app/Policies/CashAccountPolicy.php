<?php

namespace App\Policies;

use App\Models\AMS\CashAccount;
use App\Models\User;
use App\Policies\Concerns\ChecksCompanyPermission;
use App\Services\CompanyContext;

class CashAccountPolicy
{
    use ChecksCompanyPermission;

    public function __construct(private CompanyContext $context) {}

    public function viewAny(User $user): bool { return $this->allowed($user, 'cash.manage'); }
    public function view(User $user, CashAccount $cash): bool { return $this->allowed($user, 'cash.manage', (int) $cash->company_id); }
    public function create(User $user): bool { return $this->allowed($user, 'cash.manage'); }
    public function update(User $user, CashAccount $cash): bool { return $this->allowed($user, 'cash.manage', (int) $cash->company_id); }
    public function delete(User $user, CashAccount $cash): bool { return $this->allowed($user, 'cash.manage', (int) $cash->company_id); }
}
