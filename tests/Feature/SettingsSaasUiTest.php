<?php

namespace Tests\Feature;

use Tests\TestCase;

class SettingsSaasUiTest extends TestCase
{
    public function test_company_settings_hub_uses_shared_saas_contract(): void
    {
        $view = file_get_contents(resource_path('views/company/index.blade.php'));

        $this->assertStringContainsString("@extends('layouts.saas')", $view);
        $this->assertStringContainsString('saas-settings-grid', $view);
        $this->assertStringContainsString('saas-settings-links', $view);
        $this->assertStringContainsString('saas-modal-content', $view);
        $this->assertStringContainsString('data-loading-text', $view);
        $this->assertStringNotContainsString("@extends('layouts.layout')", $view);
        $this->assertStringNotContainsString('card-arrow', $view);
        $this->assertStringNotContainsString('type="3e072b31e4d62a351cb180e3-text/javascript"', $view);
    }

    public function test_settings_keep_personal_company_and_platform_boundaries_visible(): void
    {
        $company = file_get_contents(resource_path('views/company/index.blade.php'));
        $platform = file_get_contents(resource_path('views/platform/settings/general.blade.php'));

        $this->assertStringContainsString('Préférences personnelles', $company);
        $this->assertStringContainsString('services externes', mb_strtolower($platform));
        $this->assertStringContainsString('secrets restent', mb_strtolower($platform));
        $this->assertStringNotContainsString('smtp_password', $platform);
        $this->assertStringNotContainsString('KPRIMEPAY_TOKEN', $platform);
    }

    public function test_company_settings_keep_sensitive_actions_and_datatables_in_stacks(): void
    {
        $view = file_get_contents(resource_path('views/company/index.blade.php'));

        $this->assertStringContainsString("@push('scripts')", $view);
        $this->assertStringContainsString('dataTables.min.js', $view);
        $this->assertStringContainsString('buttonsStyling:false', $view);
        $this->assertStringContainsString('ServerButtonLoader.withLoader', $view);
        $this->assertStringContainsString("columns:[{data:'id'", $view);
    }

    public function test_shared_modal_contract_keeps_only_the_body_scrollable_on_mobile(): void
    {
        $styles = file_get_contents(public_path('hub/assets/css/design-system.css'));

        $this->assertStringContainsString('max-height: 100%;', $styles);
        $this->assertStringContainsString('overflow-y: auto;', $styles);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $styles);
        $this->assertStringContainsString('max-height: 100dvh;', $styles);
    }
}
