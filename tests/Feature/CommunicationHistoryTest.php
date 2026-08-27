<?php
namespace Tests\Feature;

use App\Models\CommunicationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithCompanies;
use Tests\TestCase;

class CommunicationHistoryTest extends TestCase
{
    use RefreshDatabase, InteractsWithCompanies;

    public function test_history_is_tenant_scoped_permission_protected_and_filterable(): void
    {
        $owner = User::create(['name'=>'Owner','email'=>Str::random(8).'@test.local','password'=>'password','status'=>1]);
        $this->activateCompanyFor($owner, 'history');
        CommunicationLog::create(['channel'=>'sms','function'=>'invoice','recipient'=>'90000000','country_code'=>'TG','units'=>1,'sent_at'=>now()]);
        CommunicationLog::create(['channel'=>'whatsapp','function'=>'sale','recipient'=>'91000000','country_code'=>'BJ','units'=>1,'sent_at'=>now()]);
        foreach (range(1, 11) as $number) {
            CommunicationLog::create(['channel'=>'sms','function'=>'invoice','recipient'=>'900000'.str_pad((string)$number, 2, '0', STR_PAD_LEFT),'country_code'=>'TG','units'=>1,'sent_at'=>now()->subSeconds($number)]);
        }

        $this->actingAs($owner)->get(route('communications.index', ['channel'=>'sms','function'=>'invoice']))
            ->assertOk()
            ->assertSee('90000000')->assertDontSee('91000000')
            ->assertSee('SMS & WhatsApp', false)
            ->assertSeeText('Configuration')
            ->assertSeeText('Quota')
            ->assertSeeText('Consommation')
            ->assertSeeText('Affichage de 1 à 10 sur 12 envoi(s)')
            ->assertSee('page=2', false);

        $this->actingAs($owner)->get(route('communications.index', ['channel'=>'sms','function'=>'invoice','page'=>2]))
            ->assertOk()->assertSeeText('Affichage de 11 à 12 sur 12 envoi(s)');

        $member = User::create(['name'=>'Member','email'=>Str::random(8).'@test.local','password'=>'password','status'=>1]);
        $role = $this->company->roles()->create(['name'=>'Sans historique','key'=>'no-history']);
        $this->company->users()->attach($member->id, ['role_id'=>$role->id,'status'=>'active','joined_at'=>now()]);
        $this->actingAs($member)->withSession(['active_company_id'=>$this->company->id])
            ->get(route('communications.index'))->assertForbidden();
    }
}
