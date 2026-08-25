<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use App\Policies\Concerns\ChecksCompanyPermission;
use App\Services\CompanyContext;

class SalePolicy
{
    use ChecksCompanyPermission;

    public function __construct(private CompanyContext $context) {}

    public function viewAny(User $user): bool { return $this->allowed($user, 'sales.manage'); }
    public function view(User $user, Sale $sale): bool { return $this->allowed($user, 'sales.manage', (int) $sale->company_id); }
    public function create(User $user): bool { return $this->allowed($user, 'sales.manage'); }
    public function export(User $user, ?Sale $sale = null): bool
    {
        return $this->allowed($user, 'sales.manage', $sale ? (int) $sale->company_id : null);
    }
}
