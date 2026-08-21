<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class CompanyInvitationFlowTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private function role(Company $company, string $key = 'manager'): Role
    {
        return Role::create(['company_id' => $company->id, 'name' => ucfirst($key), 'key' => $key, 'is_system' => false]);
    }

    private function invitation(Company $company, Role $role, User $inviter, string $email, string $token, array $overrides = []): CompanyInvitation
    {
        return CompanyInvitation::create(array_merge([
            'company_id' => $company->id, 'email' => $email, 'role_id' => $role->id,
            'invited_by' => $inviter->id, 'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(48), 'last_sent_at' => now(),
        ], $overrides));
    }

    public function test_manager_can_create_a_hashed_pending_invitation_for_active_company(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'invite-create');
        $role = $this->role($company);

        $this->actingAs($owner)->withSession(['active_company_id' => $company->id])
            ->postJson(route('user.invitations.store'), ['email' => 'invitee@test.local', 'role_id' => $role->id])
            ->assertOk()->assertJson(['status' => true]);

        $invitation = CompanyInvitation::firstOrFail();
        $this->assertSame($company->id, $invitation->company_id);
        $this->assertSame($owner->id, $invitation->invited_by);
        $this->assertSame(64, strlen($invitation->token_hash));
        $this->assertTrue($invitation->isPending());
        $this->assertTrue($invitation->expires_at->between(now()->addHours(47)->addMinutes(59), now()->addHours(48)->addMinute()));
        $this->actingAs($owner)->withSession(['active_company_id' => $company->id])
            ->get(route('user.index'))->assertOk()->assertSee('invitee@test.local');
    }

    public function test_existing_user_accepts_and_keeps_membership_in_other_company(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $target = $this->activateCompanyFor($owner, 'invite-target');
        $targetRole = $this->role($target, 'stock-manager');
        $user = User::factory()->create(['user_type' => 3, 'status' => 1, 'email' => 'existing@test.local']);
        $source = Company::create(['name' => 'Source', 'email' => 'source@test.local', 'number1' => '1']);
        $sourceRole = $this->role($source, 'cashier');
        CompanyUser::create(['company_id' => $source->id, 'user_id' => $user->id, 'role_id' => $sourceRole->id, 'status' => 'active', 'joined_at' => now()]);
        $token = 'existing-token';
        $invitation = $this->invitation($target, $targetRole, $owner, $user->email, $token);

        $this->post(route('invitations.accept', $token))->assertRedirect(route('companies.select'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('company_user', ['company_id' => $source->id, 'user_id' => $user->id, 'role_id' => $sourceRole->id]);
        $this->assertDatabaseHas('company_user', ['company_id' => $target->id, 'user_id' => $user->id, 'role_id' => $targetRole->id, 'status' => 'active']);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_new_user_creates_account_only_when_accepting(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'invite-new-user');
        $role = $this->role($company);
        $token = 'new-user-token';
        $this->invitation($company, $role, $owner, 'new-user@test.local', $token);
        $this->assertDatabaseMissing('users', ['email' => 'new-user@test.local']);
        $this->get(route('invitations.show', $token))->assertOk()
            ->assertSee($company->name)->assertSee('Créez votre accès');

        $this->post(route('invitations.accept', $token), [
            'name' => 'Nouvel utilisateur', 'phone' => '90000000',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
        ])->assertRedirect(route('profil'));

        $user = User::where('email', 'new-user@test.local')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'user_id' => $user->id, 'role_id' => $role->id]);
    }

    public function test_expired_and_revoked_invitations_cannot_be_accepted_and_valid_link_switches_to_invited_account(): void
    {
        $owner = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $company = $this->activateCompanyFor($owner, 'invite-invalid');
        $role = $this->role($company);
        $expired = $this->invitation($company, $role, $owner, 'expired@test.local', 'expired-token', ['expires_at' => now()->subMinute()]);
        $revoked = $this->invitation($company, $role, $owner, 'revoked@test.local', 'revoked-token', ['revoked_at' => now()]);
        $wrongUser = User::factory()->create(['user_type' => 3, 'status' => 1, 'email' => 'wrong@test.local']);

        $this->actingAs($wrongUser)->post(route('invitations.accept', 'expired-token'))->assertStatus(410);
        $this->actingAs($wrongUser)->post(route('invitations.accept', 'revoked-token'))->assertStatus(410);
        $invitedUser = User::factory()->create(['user_type' => 3, 'status' => 1, 'email' => 'right@test.local']);
        $valid = $this->invitation($company, $role, $owner, $invitedUser->email, 'right-token');
        $this->actingAs($wrongUser)->post(route('invitations.accept', 'right-token'))->assertRedirect(route('profil'));

        $this->assertDatabaseMissing('company_user', ['company_id' => $company->id, 'user_id' => $wrongUser->id]);
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'user_id' => $invitedUser->id, 'role_id' => $role->id]);
        $this->assertAuthenticatedAs($invitedUser);
        $this->assertNull($expired->fresh()->accepted_at);
        $this->assertNull($revoked->fresh()->accepted_at);
        $this->assertNotNull($valid->fresh()->accepted_at);
    }

    public function test_invitation_from_another_company_cannot_be_resent_or_revoked(): void
    {
        Mail::fake();
        $ownerA = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $companyA = $this->activateCompanyFor($ownerA, 'invite-a');
        $roleA = $this->role($companyA);
        $invitation = $this->invitation($companyA, $roleA, $ownerA, 'cross@test.local', 'cross-token');

        $ownerB = User::factory()->create(['user_type' => 2, 'status' => 1]);
        $companyB = $this->activateCompanyFor($ownerB, 'invite-b');

        $this->actingAs($ownerB)->withSession(['active_company_id' => $companyB->id])
            ->postJson(route('user.invitations.resend', $invitation))->assertNotFound();
        $this->actingAs($ownerB)->withSession(['active_company_id' => $companyB->id])
            ->deleteJson(route('user.invitations.destroy', $invitation))->assertNotFound();
        $this->assertNull($invitation->fresh()->revoked_at);
    }
}
