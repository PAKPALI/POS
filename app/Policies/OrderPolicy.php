<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Policies\Concerns\ChecksCompanyPermission;
use App\Services\CompanyContext;

class OrderPolicy
{
    use ChecksCompanyPermission;

    public function __construct(private CompanyContext $context) {}

    public function viewAny(User $user): bool { return $this->allowed($user, 'ecommerce.manage'); }
    public function view(User $user, Order $order): bool { return $this->allowed($user, 'ecommerce.manage', (int) $order->company_id); }
    public function cancel(User $user, Order $order): bool { return $this->view($user, $order); }
    public function convert(User $user, Order $order): bool
    {
        return $this->view($user, $order) && $this->allowed($user, 'sales.manage', (int) $order->company_id);
    }
}
