<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommunicationAndSalesHistorySaasUiTest extends TestCase
{
    /** @dataProvider modernModuleViews */
    public function test_communication_and_sales_history_views_use_the_saas_contract(string $view): void
    {
        $contents = file_get_contents(resource_path("views/{$view}.blade.php"));

        $this->assertStringContainsString("@extends('layouts.saas')", $contents);
        $this->assertStringContainsString('saas-page-heading', $contents);
        $this->assertStringNotContainsString("@extends('layouts.layout')", $contents);
    }

    public static function modernModuleViews(): array
    {
        return [
            'communication consumption' => ['communications/index'],
            'communication quota' => ['sms_quota/index'],
            'communication settings' => ['company/notifications'],
            'sales history' => ['pos/sale/history'],
        ];
    }

    public function test_shared_tables_center_headers_and_cells_and_expose_semantic_statuses(): void
    {
        $styles = file_get_contents(public_path('hub/assets/css/saas-pages.css'));

        $this->assertMatchesRegularExpression('/table\.dataTable thead th\s*\{[^}]*text-align:\s*center/s', $styles);
        $this->assertMatchesRegularExpression('/table\.dataTable tbody td\s*\{[^}]*text-align:\s*center/s', $styles);
        $this->assertStringContainsString('.saas-status-badge.is-success', $styles);
        $this->assertStringContainsString('.saas-status-badge.is-danger', $styles);
        $this->assertStringContainsString('.saas-status-badge.is-info', $styles);
    }

    public function test_sales_history_no_longer_injects_legacy_black_table_styles(): void
    {
        $contents = file_get_contents(resource_path('views/pos/sale/history.blade.php'));

        $this->assertStringNotContainsString(".css('background-color', 'black')", $contents);
        $this->assertStringNotContainsString('console.error(dataServer.json)', $contents);
        $this->assertStringContainsString('saas-modal-content', $contents);
        $this->assertStringNotContainsString('card-arrow', $contents);
        $this->assertStringContainsString('saas-daterangepicker-wrap', $contents);
        $this->assertStringContainsString('sales-history-metric', $contents);
    }

    public function test_communication_controls_use_desktop_grids_switches_and_shared_calendar(): void
    {
        $settings = file_get_contents(resource_path('views/company/notifications.blade.php'));
        $quota = file_get_contents(resource_path('views/sms_quota/index.blade.php'));
        $consumption = file_get_contents(resource_path('views/communications/index.blade.php'));

        $this->assertStringContainsString('communication-settings-grid', $settings);
        $this->assertStringContainsString('role="switch"', $settings);
        $this->assertStringContainsString('saas-switch-control', $settings);
        $this->assertStringContainsString('saas-quota-form-grid', $quota);
        $this->assertStringContainsString('saas-daterangepicker-wrap', $consumption);
        $this->assertStringContainsString("$('#communication-period').daterangepicker", $consumption);
    }
}
