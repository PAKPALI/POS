<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginInterfaceRegressionTest extends TestCase
{
    public function test_login_ajax_requires_json_and_handles_an_authenticated_html_fallback(): void
    {
        $contents = file_get_contents(resource_path('views/admin/login.blade.php'));

        $this->assertStringContainsString("dataType: 'json'", $contents);
        $this->assertStringContainsString("'Accept': 'application/json'", $contents);
        $this->assertStringContainsString("window.location.assign(@json(route('dashboard', [], false)))", $contents);
        $this->assertStringNotContainsString('console.log(data)', $contents);
    }
}
