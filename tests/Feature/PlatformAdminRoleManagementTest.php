<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformAdminRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role, string $email): PlatformAdmin
    {
        return PlatformAdmin::create(['name' => ucfirst($role), 'email' => $email, 'password' => Hash::make('SecurePassword!123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false]);
    }

    public function test_each_platform_role_only_sees_and_accesses_its_modules(): void
    {
        $support = $this->admin('support', 'support@example.test');
        $this->actingAs($support, 'platform')->get(route('platform.dashboard'))->assertOk()
            ->assertSee(route('platform.companies.index'))->assertSee(route('platform.users.index'))->assertDontSee(route('platform.payments.index'))->assertDontSee(route('platform.health.index'));
        $this->actingAs($support, 'platform')->get(route('platform.companies.index'))->assertOk();
        $this->actingAs($support, 'platform')->get(route('platform.payments.index'))->assertForbidden();

        $finance = $this->admin('finance', 'finance@example.test');
        $this->actingAs($finance, 'platform')->get(route('platform.dashboard'))->assertOk()
            ->assertSee(route('platform.payments.index'))->assertDontSee(route('platform.companies.index'))->assertDontSee(route('platform.health.index'));
        $this->actingAs($finance, 'platform')->get(route('platform.payments.index'))->assertOk();
        $this->actingAs($finance, 'platform')->get(route('platform.users.index'))->assertForbidden();

        $technical = $this->admin('technical', 'technical@example.test');
        $this->actingAs($technical, 'platform')->get(route('platform.dashboard'))->assertOk()
            ->assertSee(route('platform.health.index'))->assertSee(route('platform.audit.index'))->assertDontSee(route('platform.companies.index'));
        $this->actingAs($technical, 'platform')->get(route('platform.health.index'))->assertOk();
        $this->actingAs($technical, 'platform')->get(route('platform.settings.edit'))->assertForbidden();
    }

    public function test_super_admin_can_create_a_limited_admin_and_action_is_audited(): void
    {
        $super = $this->admin('super_admin', 'super@example.test');
        $this->actingAs($super, 'platform')->post(route('platform.admins.store'), [
            'name' => 'Agent Support', 'email' => 'agent@example.test', 'role' => 'support',
            'password' => 'InitialPassword!123', 'password_confirmation' => 'InitialPassword!123',
            'reason' => 'Renforcement de l’équipe support', 'current_password' => 'SecurePassword!123',
        ])->assertRedirect()->assertSessionHas('success');

        $created = PlatformAdmin::where('email', 'agent@example.test')->firstOrFail();
        $this->assertSame('support', $created->role);
        $this->assertTrue($created->must_change_password);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.admin.created', 'target_id' => (string) $created->id]);
    }

    public function test_limited_admin_cannot_manage_platform_admins(): void
    {
        $support = $this->admin('support', 'support@example.test');
        $this->actingAs($support, 'platform')->get(route('platform.admins.index'))->assertForbidden();
    }

    public function test_last_active_super_admin_and_self_deactivation_are_protected(): void
    {
        $super = $this->admin('super_admin', 'super@example.test');

        $this->actingAs($super, 'platform')->patch(route('platform.admins.status', $super), [
            'is_active' => false, 'reason' => 'Tentative de désactivation', 'current_password' => 'SecurePassword!123',
        ])->assertStatus(422);
        $this->assertTrue($super->fresh()->is_active);

        $second = $this->admin('super_admin', 'second@example.test');
        $this->actingAs($super, 'platform')->patch(route('platform.admins.status', $second), [
            'is_active' => false, 'reason' => 'Compte temporairement suspendu', 'current_password' => 'SecurePassword!123',
        ])->assertRedirect();
        $this->assertFalse($second->fresh()->is_active);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'platform.admin.deactivated', 'target_id' => (string) $second->id]);
    }

    public function test_super_admin_list_supports_search_filters_and_server_pagination(): void
    {
        $actor = $this->admin('super_admin', 'super@example.test');
        foreach (range(1, 11) as $number) {
            PlatformAdmin::create(['name' => 'Agent '.$number, 'email' => 'agent'.$number.'@example.test',
                'password' => Hash::make('SecurePassword!123'), 'role' => 'support', 'is_active' => true,
                'must_change_password' => false]);
        }

        $response = $this->actingAs($actor, 'platform')->get(route('platform.admins.index', [
            'search' => 'Agent', 'status' => 'active', 'per_page' => 10,
        ]));

        $response->assertOk()->assertSee('Agent 1')->assertSee('Affichage de 1 à 10 sur 11')->assertSee('page=2');
    }
}
