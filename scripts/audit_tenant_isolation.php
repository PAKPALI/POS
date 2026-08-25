<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$database = DB::getDatabaseName();

$companyColumns = DB::select(
    "SELECT TABLE_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = 'company_id'
     ORDER BY TABLE_NAME",
    [$database]
);

$foreignKeys = DB::select(
    "SELECT k.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME,
            r.DELETE_RULE, r.UPDATE_RULE
     FROM information_schema.KEY_COLUMN_USAGE k
     JOIN information_schema.REFERENTIAL_CONSTRAINTS r
       ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
      AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
      AND r.TABLE_NAME = k.TABLE_NAME
     WHERE k.TABLE_SCHEMA = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL
     ORDER BY k.TABLE_NAME, k.COLUMN_NAME",
    [$database]
);

$uniqueIndexes = DB::select(
    "SELECT TABLE_NAME, INDEX_NAME,
            GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_list
     FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = ? AND NON_UNIQUE = 0
     GROUP BY TABLE_NAME, INDEX_NAME
     ORDER BY TABLE_NAME, INDEX_NAME",
    [$database]
);

$tableState = [];
foreach ($companyColumns as $column) {
    $table = $column->TABLE_NAME;
    $tableState[$table] = [
        'rows' => DB::table($table)->count(),
        'null_company_id' => DB::table($table)->whereNull('company_id')->count(),
        'nullable' => $column->IS_NULLABLE === 'YES',
        'indexed' => $column->COLUMN_KEY !== '',
    ];
}

$checks = [
    'products.category' => "SELECT COUNT(*) total FROM products c JOIN categories p ON p.id=c.category_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'products.supplier' => "SELECT COUNT(*) total FROM products c JOIN suppliers p ON p.id=c.supplier_id WHERE c.supplier_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'sales.client' => "SELECT COUNT(*) total FROM sales c JOIN clients p ON p.id=c.client_id WHERE c.client_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'sale_details.sale' => "SELECT COUNT(*) total FROM sale_details c JOIN sales p ON p.id=c.sale_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'sale_details.product' => "SELECT COUNT(*) total FROM sale_details c JOIN products p ON p.id=c.product_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'inventories.product' => "SELECT COUNT(*) total FROM inventories c JOIN products p ON p.id=c.product_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'inventories.supplier' => "SELECT COUNT(*) total FROM inventories c JOIN suppliers p ON p.id=c.supplier_id WHERE c.supplier_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'menu_products.menu' => "SELECT COUNT(*) total FROM menu_products c JOIN products p ON p.id=c.menu_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'menu_products.product' => "SELECT COUNT(*) total FROM menu_products c JOIN products p ON p.id=c.product_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'transactions.from_cash' => "SELECT COUNT(*) total FROM transactions c JOIN cash_accounts p ON p.id=c.from_cash_id WHERE c.from_cash_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'transactions.to_cash' => "SELECT COUNT(*) total FROM transactions c JOIN cash_accounts p ON p.id=c.to_cash_id WHERE c.to_cash_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'settings.default_cash' => "SELECT COUNT(*) total FROM settings c JOIN cash_accounts p ON p.id=c.default_cash_id WHERE c.default_cash_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'settings.tax_cash' => "SELECT COUNT(*) total FROM settings c JOIN cash_accounts p ON p.id=c.tax_cash_id WHERE c.tax_cash_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'order_items.order' => "SELECT COUNT(*) total FROM order_items c JOIN orders p ON p.id=c.order_id WHERE c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id",
    'order_items.product' => "SELECT COUNT(*) total FROM order_items c JOIN products p ON p.id=c.product_id WHERE c.product_id IS NOT NULL AND (c.company_id IS NULL OR p.company_id IS NULL OR c.company_id<>p.company_id)",
    'company_user.role' => "SELECT COUNT(*) total FROM company_user c JOIN roles p ON p.id=c.role_id WHERE c.role_id IS NOT NULL AND (p.company_id IS NULL OR c.company_id<>p.company_id)",
    'company_invitations.role' => "SELECT COUNT(*) total FROM company_invitations c JOIN roles p ON p.id=c.role_id WHERE c.role_id IS NOT NULL AND (p.company_id IS NULL OR c.company_id<>p.company_id)",
    'notification_recipients.membership' => "SELECT COUNT(*) total FROM notification_recipients n LEFT JOIN company_user cu ON cu.company_id=n.company_id AND cu.user_id=n.user_id AND cu.status='active' WHERE cu.id IS NULL",
    'ecommerce_managers.membership' => "SELECT COUNT(*) total FROM ecommerce_managers e LEFT JOIN company_user cu ON cu.company_id=e.company_id AND cu.user_id=e.user_id AND cu.status='active' WHERE cu.id IS NULL",
];

$relationshipState = [];
foreach ($checks as $name => $sql) {
    $relationshipState[$name] = (int) DB::selectOne($sql)->total;
}

$businessInvariants = [
    'active_memberships_without_role' => DB::table('company_user')
        ->where('status', 'active')->whereNull('role_id')->count(),
    'companies_without_active_owner' => (int) DB::selectOne(
        "SELECT COUNT(*) total FROM company_settings c
         WHERE NOT EXISTS (
             SELECT 1 FROM company_user cu
             JOIN roles r ON r.id=cu.role_id
             WHERE cu.company_id=c.id AND cu.status='active'
               AND r.company_id=c.id AND r.key='owner'
         )"
    )->total,
    'companies_without_settings' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM company_settings c WHERE NOT EXISTS (SELECT 1 FROM settings s WHERE s.company_id=c.id)'
    )->total,
    'companies_without_default_cash' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM company_settings c WHERE NOT EXISTS (SELECT 1 FROM cash_accounts a WHERE a.company_id=c.id AND a.is_default=1)'
    )->total,
    'companies_without_tax_cash' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM company_settings c WHERE NOT EXISTS (SELECT 1 FROM cash_accounts a WHERE a.company_id=c.id AND a.is_tax=1)'
    )->total,
    'companies_with_multiple_default_cash' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM (SELECT company_id FROM cash_accounts WHERE is_default=1 GROUP BY company_id HAVING COUNT(*)>1) x'
    )->total,
    'companies_with_multiple_tax_cash' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM (SELECT company_id FROM cash_accounts WHERE is_tax=1 GROUP BY company_id HAVING COUNT(*)>1) x'
    )->total,
    'cash_marked_default_and_tax' => DB::table('cash_accounts')->where('is_default', 1)->where('is_tax', 1)->count(),
    'duplicate_settings_per_company' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM (SELECT company_id FROM settings WHERE company_id IS NOT NULL GROUP BY company_id HAVING COUNT(*)>1) x'
    )->total,
    'companies_without_slug' => DB::table('company_settings')->whereNull('slug')->orWhere('slug', '')->count(),
    'companies_without_public_id' => DB::table('company_settings')->whereNull('public_id')->count(),
    'duplicate_company_slugs' => (int) DB::selectOne(
        "SELECT COUNT(*) total FROM (SELECT slug FROM company_settings WHERE slug IS NOT NULL AND slug<>'' GROUP BY slug HAVING COUNT(*)>1) x"
    )->total,
    'duplicate_company_public_ids' => (int) DB::selectOne(
        'SELECT COUNT(*) total FROM (SELECT public_id FROM company_settings WHERE public_id IS NOT NULL GROUP BY public_id HAVING COUNT(*)>1) x'
    )->total,
];

$nullCompanyDetails = [
    'roles' => DB::table('roles')
        ->leftJoin('company_user', 'company_user.role_id', '=', 'roles.id')
        ->leftJoin('company_invitations', 'company_invitations.role_id', '=', 'roles.id')
        ->leftJoin('permission_role', 'permission_role.role_id', '=', 'roles.id')
        ->whereNull('roles.company_id')
        ->groupBy('roles.id', 'roles.name', 'roles.key', 'roles.is_system')
        ->orderBy('roles.id')
        ->get([
            'roles.id', 'roles.name', 'roles.key', 'roles.is_system',
            DB::raw('COUNT(DISTINCT company_user.id) membership_count'),
            DB::raw('COUNT(DISTINCT company_invitations.id) invitation_count'),
            DB::raw('COUNT(DISTINCT permission_role.permission_id) permission_count'),
        ]),
    'actions_by_function' => DB::table('actions')->whereNull('company_id')
        ->selectRaw('`function`, COUNT(*) total, MIN(created_at) first_seen, MAX(created_at) last_seen')
        ->groupBy('function')->orderByDesc('total')->get(),
];

$result = [
    'generated_at' => now()->toIso8601String(),
    'database_driver' => DB::getDriverName(),
    'companies' => DB::table('company_settings')->count(),
    'company_columns' => $tableState,
    'cross_company_relationships' => $relationshipState,
    'business_invariants' => $businessInvariants,
    'null_company_details' => $nullCompanyDetails,
    'foreign_keys' => $foreignKeys,
    'unique_indexes' => $uniqueIndexes,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
