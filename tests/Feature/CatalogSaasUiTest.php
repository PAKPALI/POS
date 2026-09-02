<?php

namespace Tests\Feature;

use Tests\TestCase;

class CatalogSaasUiTest extends TestCase
{
    /** @dataProvider catalogIndexViews */
    public function test_catalog_indexes_use_the_saas_shell(string $view): void
    {
        $contents = file_get_contents(resource_path("views/component/{$view}/index.blade.php"));

        $this->assertStringContainsString("@extends('layouts.saas')", $contents);
        $this->assertStringContainsString('saas-page-heading', $contents);
        $this->assertStringContainsString('saas-card', $contents);
        $this->assertStringNotContainsString("@extends('layouts.layout')", $contents);
    }

    public static function catalogIndexViews(): array
    {
        return [
            'products' => ['product'],
            'categories' => ['category'],
            'menus' => ['menu'],
            'suppliers' => ['supplier'],
        ];
    }

    public function test_sidebar_follows_catalog_first_order_and_marks_active_modules(): void
    {
        $contents = file_get_contents(resource_path('views/partials/saas-sidebar.blade.php'));

        $this->assertLessThan(strpos($contents, "@if(\$allowed('sales.manage'))"), strpos($contents, "@if(\$allowed('catalog.manage'))"));
        $this->assertLessThan(strpos($contents, '>Produits</a>'), strpos($contents, '>Catégories</a>'));
        $this->assertStringContainsString('summary class="{{ $catalogActive ? \'is-active\' : \'\' }}"', $contents);
        $this->assertStringContainsString('summary class="{{ $salesActive ? \'is-active\' : \'\' }}"', $contents);
        $this->assertStringContainsString("request()->routeIs('history') ? 'is-active' : ''", $contents);
    }

    public function test_catalog_server_actions_expose_loading_feedback(): void
    {
        foreach (['product', 'category', 'menu', 'supplier'] as $view) {
            $contents = file_get_contents(resource_path("views/component/{$view}/index.blade.php"));

            $this->assertTrue(
                str_contains($contents, 'data-loading-text')
                    || str_contains($contents, 'ServerButtonLoader'),
                "The {$view} catalog screen must expose server loading feedback."
            );
        }
    }

    public function test_category_uses_the_reusable_saas_modal_and_destructive_confirmation(): void
    {
        $contents = file_get_contents(resource_path('views/component/category/index.blade.php'));

        $this->assertSame(3, substr_count($contents, '<x-ui.modal'));
        $this->assertStringContainsString("showLoaderOnConfirm: true", $contents);
        $this->assertStringContainsString("customClass: { popup: 'saas-swal saas-swal-danger'", $contents);
    }

    public function test_product_and_supplier_edit_modals_use_shared_saas_actions(): void
    {
        foreach (['product', 'supplier'] as $view) {
            $index = file_get_contents(resource_path("views/component/{$view}/index.blade.php"));
            $edit = file_get_contents(resource_path("views/component/{$view}/edit.blade.php"));

            $this->assertStringContainsString('saas-modal-content', $index);
            $this->assertStringContainsString('saas-modal-header', $index);
            $this->assertStringContainsString('saas-modal-close', $index);
            $this->assertStringContainsString('saas-btn saas-btn-ghost', $edit);
            $this->assertStringContainsString('saas-btn saas-btn-warning', $edit);
            $this->assertStringContainsString('saas-modal-actions', $edit);
            $this->assertStringContainsString('data-loading-text="Enregistrement…"', $edit);
            $this->assertStringNotContainsString('spinner-grow', $edit);
            $this->assertStringNotContainsString('submit_text', $edit);
        }
    }

    public function test_update_forms_use_cancel_then_save_actions(): void
    {
        foreach ([
            'component/client/edit.blade.php',
            'component/menu/edit.blade.php',
            'ams/cash/edit.blade.php',
            'code/edit.blade.php',
            'user/edit.blade.php',
        ] as $view) {
            $contents = file_get_contents(resource_path("views/{$view}"));

            $this->assertStringContainsString('saas-modal-actions', $contents, "{$view} must use the shared modal action row.");
            $this->assertStringContainsString('saas-btn saas-btn-ghost', $contents, "{$view} must expose Annuler.");
            $this->assertStringContainsString('>Annuler</button>', preg_replace('/\s+/', '', $contents));
            $this->assertStringContainsString('Enregistrer', $contents, "{$view} must expose Enregistrer.");
            $this->assertStringContainsString('data-loading-text="Enregistrement…"', $contents, "{$view} must expose server loading feedback.");
        }
    }

    public function test_catalog_styles_include_mobile_full_screen_modals(): void
    {
        $contents = file_get_contents(public_path('hub/assets/css/saas-pages.css'));

        $this->assertStringContainsString('.saas-body .modal:not(.show)', $contents);
        $this->assertStringContainsString('@media (max-width: 767px)', $contents);
        $this->assertStringContainsString('min-height: 100dvh', $contents);
        $this->assertStringContainsString('Short modals keep their natural height', $contents);
        $this->assertStringContainsString('max-height: calc(100dvh - 8rem)', $contents);
        $this->assertStringContainsString('height: auto;', $contents);
    }

    public function test_catalog_plugins_are_loaded_after_the_saas_vendor_bundle(): void
    {
        foreach (['product', 'menu', 'supplier', 'client', 'inventory'] as $view) {
            $contents = file_get_contents(resource_path("views/component/{$view}/index.blade.php"));

            $this->assertStringContainsString("@push('scripts')", $contents);
            $this->assertStringContainsString('dataTables.min.js', $contents);
            $this->assertLessThan(
                strpos($contents, 'dataTables.min.js'),
                strpos($contents, "@push('scripts')"),
                "The {$view} DataTables bundle must stay inside the scripts stack."
            );
        }
    }

    public function test_datatable_loading_state_is_shared_by_every_design_system_layout(): void
    {
        $head = file_get_contents(resource_path('views/partials/design-system-head.blade.php'));
        $styles = file_get_contents(public_path('hub/assets/css/datatable-loading.css'));
        $script = file_get_contents(public_path('hub/assets/js/design-system.js'));

        $this->assertStringContainsString('datatable-loading.css', $head);
        $this->assertStringContainsString('.dataTables_wrapper .dataTables_processing', $styles);
        $this->assertStringContainsString('.dt-container .dt-processing', $styles);
        $this->assertStringContainsString('ds-datatable-loading', $styles);
        $this->assertStringContainsString('prefers-reduced-motion', $styles);
        $this->assertStringContainsString('Chargement des données…', $script);
        $this->assertStringContainsString("aria-live", $script);
    }

    public function test_catalog_ajax_details_use_the_shared_modern_layout(): void
    {
        foreach (['product', 'menu', 'supplier'] as $view) {
            $contents = file_get_contents(resource_path("views/component/{$view}/show.blade.php"));

            $this->assertStringContainsString('saas-detail-hero', $contents);
            $this->assertStringContainsString('saas-detail-list', $contents);
            $this->assertStringNotContainsString('card-arrow', $contents);
            $this->assertStringNotContainsString('hljs-container', $contents);
        }
    }
}
