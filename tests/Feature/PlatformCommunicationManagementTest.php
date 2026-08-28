<?php

namespace Tests\Feature;

use App\Jobs\SendSaleWhatsappJob;
use App\Models\NotificationDelivery;
use App\Models\PlatformAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class PlatformCommunicationManagementTest extends TestCase
{
    use InteractsWithCompanies, RefreshDatabase;

    private function admin(string $role = 'super_admin'): PlatformAdmin
    {
        return PlatformAdmin::create(['name'=>'Admin communications','email'=>$role.'@example.test',
            'password'=>Hash::make('SecurePassword!123'),'role'=>$role,'is_active'=>true,
            'must_change_password'=>false,'two_factor_enabled'=>false]);
    }

    public function test_global_list_is_filterable_masked_and_exportable(): void
    {
        $admin=$this->admin(); $owner=User::factory()->create(['email'=>'recipient@example.test']);
        $company=$this->activateCompanyFor($owner,'communication-company');
        NotificationDelivery::create(['company_id'=>$company->id,'user_id'=>$owner->id,'event_type'=>'sale',
            'event_key'=>'42','category'=>'sale','channel'=>'sms','status'=>'failed','attempts'=>2,'last_error'=>'ProviderException']);

        $this->actingAs($admin,'platform')->get(route('platform.communications.index',['channel'=>'sms','status'=>'failed']))
            ->assertOk()->assertSee($company->name)->assertSee('ProviderException')->assertDontSee('recipient@example.test');
        $this->actingAs($admin,'platform')->get(route('platform.communications.export',['format'=>'csv','channel'=>'sms']))
            ->assertOk()->assertDownload();
    }

    public function test_failed_safe_delivery_can_be_retried_once_and_is_audited(): void
    {
        Queue::fake(); $admin=$this->admin(); $owner=User::factory()->create();
        $company=$this->activateCompanyFor($owner,'retry-company');
        $delivery=NotificationDelivery::create(['company_id'=>$company->id,'user_id'=>$owner->id,'event_type'=>'sale',
            'event_key'=>'77','category'=>'sale','channel'=>'sms','status'=>'failed','attempts'=>1,'last_error'=>'Timeout']);

        $this->actingAs($admin,'platform')->postJson(route('platform.communications.retry',$delivery),['reason'=>'Nouvelle tentative après correction'])
            ->assertOk();
        $this->assertSame('pending',$delivery->fresh()->status);
        Queue::assertPushed(SendSaleWhatsappJob::class,fn($job)=>$job->saleId===77 && $job->companyId===$company->id);
        $this->assertDatabaseHas('platform_audit_logs',['action'=>'platform.communication.retried','target_id'=>(string)$delivery->id]);
        $this->postJson(route('platform.communications.retry',$delivery),['reason'=>'Tentative en double'])->assertStatus(422);
    }

    public function test_support_cannot_access_global_communications(): void
    {
        $this->actingAs($this->admin('support'),'platform')->get(route('platform.communications.index'))->assertForbidden();
    }
}
