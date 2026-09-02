<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemCompletionTest extends TestCase
{
    public function test_required_ui_component_library_is_complete(): void
    {
        foreach (['alert','badge','button','card','company-card','empty-state','export-panel','filter-panel','glass-panel','input','modal','password','permission-denied','select','skeleton','stat-card','status','table-shell','textarea'] as $component) {
            $this->assertFileExists(resource_path("views/components/ui/{$component}.blade.php"), "Missing x-ui.{$component}");
        }
    }

    public function test_public_access_screens_use_shared_design_system_shells(): void
    {
        foreach (['auth/invitation.blade.php','auth/invitation-result.blade.php','auth/login.blade.php','auth/register.blade.php','auth/verify.blade.php','auth/passwords/email.blade.php','auth/passwords/reset.blade.php','auth/passwords/confirm.blade.php','admin/login.blade.php','admin/register.blade.php'] as $view) {
            $contents = file_get_contents(resource_path("views/{$view}"));
            $this->assertStringContainsString("@extends('layouts.public-auth')", $contents, $view);
            $this->assertStringNotContainsString('app.min.css', $contents, $view);
        }
    }

    public function test_platform_access_uses_shared_platform_auth_shell(): void
    {
        foreach (['login','two-factor','forgot-password','reset-password'] as $view) {
            $contents = file_get_contents(resource_path("views/platform/auth/{$view}.blade.php"));
            $this->assertStringContainsString("@extends('layouts.platform-auth')", $contents);
            $this->assertStringNotContainsString('app.min.css', $contents);
            $this->assertStringNotContainsString('<style>', $contents);
        }
    }

    public function test_platform_shell_no_longer_loads_legacy_application_theme(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/platform.blade.php'));
        $this->assertStringNotContainsString('app.min.css', $layout);
        $this->assertStringNotContainsString('app.min.js', $layout);
        $this->assertStringContainsString('platform-components.css', $layout);
        $this->assertStringContainsString('design-system.js', $layout);
    }

    public function test_every_password_gets_an_accessible_reveal_control(): void
    {
        $script = file_get_contents(public_path('hub/assets/js/design-system.js'));
        $styles = file_get_contents(public_path('hub/assets/css/password-toggle.css'));
        $this->assertStringContainsString("querySelectorAll('input[type=\"password\"]')", $script);
        $this->assertStringContainsString("setAttribute('aria-label', 'Afficher le mot de passe')", $script);
        $this->assertStringContainsString('data-password-toggle', file_get_contents(resource_path('views/components/ui/password.blade.php')));
        $this->assertStringContainsString('.ds-password-toggle', $styles);
    }

    public function test_codes_promotions_and_company_selection_use_shared_components(): void
    {
        $codes = file_get_contents(resource_path('views/code/index.blade.php'));
        $companies = file_get_contents(resource_path('views/company/select.blade.php'));
        $this->assertStringContainsString("@extends('layouts.saas')", $codes);
        $this->assertStringContainsString('<x-ui.table-shell', $codes);
        $this->assertStringContainsString('<x-ui.company-card', $companies);
        $this->assertStringContainsString('<x-ui.empty-state', $companies);
    }

    public function test_design_assets_remain_within_compressed_budgets(): void
    {
        $css = ['design-system.css','saas-shell.css','saas-pages.css','saas-pos.css','public-auth.css','platform.css','platform-components.css','platform-auth.css','company-select.css','invitation.css','password-toggle.css','navigation-loader.css'];
        $js = ['design-system.js','saas-shell.js','navigation-loader.js'];
        $cssBytes = array_sum(array_map(fn ($file) => strlen(gzencode(file_get_contents(public_path("hub/assets/css/{$file}")), 9)), $css));
        $jsBytes = array_sum(array_map(fn ($file) => strlen(gzencode(file_get_contents(public_path("hub/assets/js/{$file}")), 9)), $js));
        $this->assertLessThanOrEqual(60 * 1024, $cssBytes);
        $this->assertLessThanOrEqual(40 * 1024, $jsBytes);
    }
}
