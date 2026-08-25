<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $parentIndexes = [
        'categories' => 'categories_id_company_unique',
        'suppliers' => 'suppliers_id_company_unique',
        'clients' => 'clients_id_company_unique',
        'sales' => 'sales_id_company_unique',
        'products' => 'products_id_company_unique',
        'cash_accounts' => 'cash_id_company_unique',
        'orders' => 'orders_id_company_unique',
    ];

    private array $relations = [
        ['products', 'category_id', 'categories', 'products_category_id_foreign', 'products_category_company_fk', 'CASCADE'],
        ['products', 'supplier_id', 'suppliers', 'products_supplier_id_foreign', 'products_supplier_company_fk', 'RESTRICT'],
        ['sales', 'client_id', 'clients', 'sales_client_id_foreign', 'sales_client_company_fk', 'RESTRICT'],
        ['sale_details', 'sale_id', 'sales', 'sale_details_sale_id_foreign', 'sale_details_sale_company_fk', 'CASCADE'],
        ['sale_details', 'product_id', 'products', 'sale_details_product_id_foreign', 'sale_details_product_company_fk', 'CASCADE'],
        ['inventories', 'product_id', 'products', 'inventories_product_id_foreign', 'inventories_product_company_fk', 'CASCADE'],
        ['inventories', 'supplier_id', 'suppliers', 'inventories_supplier_id_foreign', 'inventories_supplier_company_fk', 'RESTRICT'],
        ['menu_products', 'menu_id', 'products', 'menu_products_menu_id_foreign', 'menu_products_menu_company_fk', 'CASCADE'],
        ['menu_products', 'product_id', 'products', 'menu_products_product_id_foreign', 'menu_products_product_company_fk', 'CASCADE'],
        ['transactions', 'from_cash_id', 'cash_accounts', 'transactions_from_cash_id_foreign', 'transactions_from_cash_company_fk', 'RESTRICT'],
        ['transactions', 'to_cash_id', 'cash_accounts', 'transactions_to_cash_id_foreign', 'transactions_to_cash_company_fk', 'RESTRICT'],
        ['settings', 'default_cash_id', 'cash_accounts', 'settings_default_cash_id_foreign', 'settings_default_cash_company_fk', 'RESTRICT'],
        ['settings', 'tax_cash_id', 'cash_accounts', 'settings_tax_cash_id_foreign', 'settings_tax_cash_company_fk', 'RESTRICT'],
        ['order_items', 'order_id', 'orders', 'order_items_order_id_foreign', 'order_items_order_company_fk', 'CASCADE'],
        ['order_items', 'product_id', 'products', 'order_items_product_id_foreign', 'order_items_product_company_fk', 'RESTRICT'],
        ['orders', 'sale_id', 'sales', 'orders_sale_id_foreign', 'orders_sale_company_fk', 'RESTRICT'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $invalid = collect($this->relations)->filter(function (array $relation) {
            [$child, $foreignColumn, $parent] = $relation;

            return DB::table($child.' as child')
                ->join($parent.' as parent', 'parent.id', '=', 'child.'.$foreignColumn)
                ->whereNotNull('child.'.$foreignColumn)
                ->whereColumn('child.company_id', '!=', 'parent.company_id')
                ->exists();
        })->map(fn (array $relation) => $relation[0].'.'.$relation[1])->values();

        if ($invalid->isNotEmpty()) {
            throw new RuntimeException(
                'Contraintes composites annulées avant modification : relations inter-compagnies détectées sur '.
                $invalid->implode(', ').'. Corrigez les données après validation métier.'
            );
        }

        foreach ($this->parentIndexes as $table => $index) {
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$index}` (`id`, `company_id`)");
        }

        foreach ($this->relations as $relation) {
            [$child, $foreignColumn, $parent, $oldConstraint, $newConstraint, $deleteRule] = $relation;
            DB::statement("ALTER TABLE `{$child}` DROP FOREIGN KEY `{$oldConstraint}`");
            DB::statement(
                "ALTER TABLE `{$child}` ADD CONSTRAINT `{$newConstraint}` " .
                "FOREIGN KEY (`{$foreignColumn}`, `company_id`) " .
                "REFERENCES `{$parent}` (`id`, `company_id`) ON DELETE {$deleteRule}"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $originalDeleteRules = [
            'products_category_id_foreign' => 'CASCADE',
            'products_supplier_id_foreign' => 'SET NULL',
            'sales_client_id_foreign' => 'CASCADE',
            'sale_details_sale_id_foreign' => 'CASCADE',
            'sale_details_product_id_foreign' => 'CASCADE',
            'inventories_product_id_foreign' => 'CASCADE',
            'inventories_supplier_id_foreign' => 'SET NULL',
            'menu_products_menu_id_foreign' => 'CASCADE',
            'menu_products_product_id_foreign' => 'CASCADE',
            'transactions_from_cash_id_foreign' => 'SET NULL',
            'transactions_to_cash_id_foreign' => 'SET NULL',
            'settings_default_cash_id_foreign' => 'SET NULL',
            'settings_tax_cash_id_foreign' => 'SET NULL',
            'order_items_order_id_foreign' => 'CASCADE',
            'order_items_product_id_foreign' => 'RESTRICT',
            'orders_sale_id_foreign' => 'RESTRICT',
        ];

        foreach (array_reverse($this->relations) as $relation) {
            [$child, $foreignColumn, $parent, $oldConstraint, $newConstraint] = $relation;
            DB::statement("ALTER TABLE `{$child}` DROP FOREIGN KEY `{$newConstraint}`");
            DB::statement(
                "ALTER TABLE `{$child}` ADD CONSTRAINT `{$oldConstraint}` " .
                "FOREIGN KEY (`{$foreignColumn}`) REFERENCES `{$parent}` (`id`) " .
                'ON DELETE '.$originalDeleteRules[$oldConstraint]
            );
        }

        foreach (array_reverse($this->parentIndexes, true) as $table => $index) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }
};
