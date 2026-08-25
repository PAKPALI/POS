<?php

namespace Tests\Feature;

use App\Models\CompanyUser;
use App\Models\NotificationRecipient;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationRecipientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    public function test_owner_can_manage_recipients_by_category_and_channel(): void
    {
        $owner = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($owner, 'notifications');

        $this->actingAs($owner)->withSession(['active_company_id' => $company->id])
            ->put(route('notifications.update'), [
                'sale_email_enabled' => 1,
                'sale_whatsapp_enabled' => 1,
                'inventory_sms_enabled' => 1,
                'recipients' => [
                    'sale' => [$owner->id => ['email' => 1, 'whatsapp' => 1]],
                    'inventory' => [$owner->id => ['sms' => 1]],
                ],
            ])->assertRedirect();

        $this->assertDatabaseHas('notification_recipients', [
            'company_id' => $company->id, 'user_id' => $owner->id, 'category' => 'sale',
            'email_enabled' => 1, 'whatsapp_enabled' => 1, 'sms_enabled' => 0,
        ]);
        $this->assertDatabaseHas('notification_recipients', [
            'company_id' => $company->id, 'user_id' => $owner->id, 'category' => 'inventory',
            'email_enabled' => 0, 'whatsapp_enabled' => 0, 'sms_enabled' => 1,
        ]);
        $company->refresh();
        $this->assertTrue($company->sale_email_enabled);
        $this->assertFalse($company->inventory_email_enabled);
        $this->assertTrue($company->sale_whatsapp_enabled);
        $this->assertTrue($company->inventory_sms_enabled);
    }

    public function test_any_active_user_can_become_operational_notification_recipient(): void
    {
        $owner = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($owner, 'recipient-security');
        $cashier = User::factory()->create(['status' => 1, 'user_type' => 3, 'phone' => '90000000']);
        $cashierRole = Role::create([
            'company_id' => $company->id, 'name' => 'Caissier', 'key' => 'cashier', 'is_system' => true,
        ]);
        CompanyUser::create([
            'company_id' => $company->id, 'user_id' => $cashier->id, 'role_id' => $cashierRole->id,
            'status' => 'active', 'joined_at' => now(),
        ]);
        NotificationRecipient::create([
            'company_id' => $company->id, 'user_id' => $cashier->id, 'category' => 'sale',
            'email_enabled' => true, 'whatsapp_enabled' => true, 'sms_enabled' => true,
        ]);

        $recipients = app(NotificationRecipientService::class)->users($company->id, 'sale', 'whatsapp');

        $this->assertTrue($recipients->contains('id', $cashier->id));
    }

    public function test_notification_page_requires_the_dedicated_role_permission(): void
    {
        $owner = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($owner, 'notification-permission');
        $manager = User::factory()->create(['status' => 1, 'user_type' => 3]);
        $role = Role::create([
            'company_id' => $company->id, 'name' => 'Responsable', 'key' => 'manager', 'is_system' => false,
        ]);
        CompanyUser::create([
            'company_id' => $company->id, 'user_id' => $manager->id, 'role_id' => $role->id,
            'status' => 'active', 'joined_at' => now(),
        ]);

        $this->actingAs($manager)->withSession(['active_company_id' => $company->id])
            ->get(route('notifications.index'))->assertForbidden();

        $permission = Permission::where('key', 'notifications.manage')->firstOrFail();
        $role->permissions()->attach($permission->id);
        $this->actingAs($manager)->withSession(['active_company_id' => $company->id])
            ->get(route('notifications.index'))->assertOk();
    }

    public function test_weekly_inventory_email_is_skipped_when_the_global_channel_is_disabled(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['status' => 1, 'user_type' => 2]);
        $company = $this->activateCompanyFor($owner, 'inventory-email-disabled');
        $company->update(['inventory_email_enabled' => false]);

        $this->artisan('inventory:weekly-report')->assertSuccessful();

        Mail::assertNothingOutgoing();
    }
}
