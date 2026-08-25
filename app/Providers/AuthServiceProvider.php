<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\AMS\CashAccount;
use App\Policies\CategoryPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\SalePolicy;
use App\Policies\InventoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\CashAccountPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Client::class => ClientPolicy::class,
        Product::class => ProductPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Sale::class => SalePolicy::class,
        Inventory::class => InventoryPolicy::class,
        CashAccount::class => CashAccountPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
