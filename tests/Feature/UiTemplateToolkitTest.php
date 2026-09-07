<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiTemplateToolkitTest extends TestCase
{
    public function test_saas_ui_toolkit_is_available_in_every_standard_layout(): void
    {
        $head = file_get_contents(resource_path('views/partials/design-system-head.blade.php'));
        $saasLayout = file_get_contents(resource_path('views/layouts/saas.blade.php'));

        $this->assertStringContainsString('saas-toolkit.css', $head);
        $this->assertStringContainsString('saas-toolkit.js', $head);
        $this->assertStringContainsString('saas-pages.css', $saasLayout);
    }

    public function test_ui_template_guard_passes_for_the_current_component_catalogue(): void
    {
        $this->artisan('ui:lint')->assertExitCode(0);
    }
}
