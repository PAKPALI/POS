<?php

namespace Tests\Feature;

use App\Models\Action;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_one_company_creates_a_tenant_scoped_action(): void
    {
        $user = $this->user('single-company@test.local');
        $company = $this->company('Compagnie unique');
        $this->membership($user, $company);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertDatabaseHas('actions', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'function' => 'CONNEXION',
        ]);
        $this->assertSame(0, Action::withoutCompanyScope()->whereNull('company_id')->count());
    }

    public function test_multi_company_login_is_logged_only_after_the_company_is_selected(): void
    {
        $user = $this->user('multi-company@test.local');
        $first = $this->company('Première compagnie');
        $second = $this->company('Deuxième compagnie');
        $this->membership($user, $first);
        $this->membership($user, $second);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseCount('actions', 0);

        $this->post(route('companies.switch', $second->id))->assertRedirect();
        $this->assertDatabaseHas('actions', [
            'company_id' => $second->id,
            'user_id' => $user->id,
            'function' => 'CONNEXION',
        ]);
        $this->assertSame(0, Action::withoutCompanyScope()->whereNull('company_id')->count());
    }

    public function test_retention_is_tenant_scoped_supports_preview_and_keeps_legacy_archive(): void
    {
        $user = $this->user('retention@test.local');
        $companyA = $this->company('Conservation A');
        $companyB = $this->company('Conservation B');
        $oldA = $this->action($user, $companyA->id, now()->subDays(400));
        $recentA = $this->action($user, $companyA->id, now()->subDays(20));
        $oldB = $this->action($user, $companyB->id, now()->subDays(400));
        DB::table('legacy_tenant_records')->insert([
            'source_table' => 'actions',
            'source_id' => 999999,
            'payload' => json_encode(['record' => ['function' => 'CONNEXION']], JSON_THROW_ON_ERROR),
            'archived_at' => now()->subDays(500),
        ]);

        $this->artisan('actions:clean', ['--days' => 365, '--company' => $companyA->id, '--pretend' => true])
            ->assertSuccessful();
        $this->assertDatabaseHas('actions', ['id' => $oldA->id]);

        $this->artisan('actions:clean', ['--days' => 365, '--company' => $companyA->id])
            ->assertSuccessful();

        $this->assertDatabaseMissing('actions', ['id' => $oldA->id]);
        $this->assertDatabaseHas('actions', ['id' => $recentA->id]);
        $this->assertDatabaseHas('actions', ['id' => $oldB->id]);
        $this->assertDatabaseHas('legacy_tenant_records', ['source_table' => 'actions', 'source_id' => 999999]);
    }

    private function user(string $email): User
    {
        return User::factory()->create([
            'email' => $email, 'password' => bcrypt('password'), 'user_type' => 1, 'status' => 1,
        ]);
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'email' => str($name)->slug().'-'.uniqid().'@test.local',
            'number1' => '000000000',
        ]);
    }

    private function membership(User $user, Company $company): CompanyUser
    {
        $role = Role::create([
            'company_id' => $company->id,
            'name' => 'Propriétaire',
            'key' => 'owner',
            'is_system' => true,
        ]);

        return CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    private function action(User $user, ?int $companyId, $createdAt): Action
    {
        $action = Action::withoutCompanyScope()->create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'function' => 'TEST',
            'text' => 'Action de test',
        ]);
        $action->timestamps = false;
        $action->created_at = $createdAt;
        $action->updated_at = $createdAt;
        $action->save();

        return $action;
    }
}
