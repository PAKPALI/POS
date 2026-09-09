<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerPromoCode;
use App\Models\PlatformSetting;
use App\Services\PlatformConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PartnerCodeTest extends TestCase
{
    use RefreshDatabase;

    private function enablePortal(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
    }

    private function activePartner(array $attributes = []): Partner
    {
        return Partner::factory()->create(array_merge([
            'status' => 'active',
            'email_verified_at' => now(),
        ], $attributes));
    }

    public function test_verified_partner_receives_a_primary_code_on_email_verification(): void
    {
        $partner = Partner::factory()->create(['status' => 'pending_email', 'email_verified_at' => null]);
        $url = URL::temporarySignedRoute('partner.email.verify', now()->addMinutes(10), [
            'partner' => $partner->id,
            'hash' => sha1($partner->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect(route('partner.login'));

        $this->assertDatabaseHas('partner_promo_codes', [
            'partner_id' => $partner->id,
            'status' => 'active',
            'is_primary' => true,
        ]);
        $code = $partner->fresh()->promoCodes()->where('is_primary', true)->first();
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4,24}$/', $code->normalized_code);
    }

    public function test_partner_code_page_displays_and_allows_customization_with_cooldown(): void
    {
        $this->enablePortal();
        $partner = $this->activePartner(['username' => 'demo-partner']);

        $this->actingAs($partner, 'partner')->get(route('partner.code'))
            ->assertOk()
            ->assertSee('Mon code partenaire')
            ->assertSee('Copier')
            ->assertSee('Désactiver un ancien code n’annule pas les paiements déjà liés')
            ->assertSee('partnerCodeAvailability', false)
            ->assertSee('availabilityConfirmed', false);

        $old = $partner->fresh()->promoCodes()->where('is_primary', true)->first();
        $this->actingAs($partner, 'partner')->put(route('partner.code.update'), ['code' => 'DEMO2026'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('partner_promo_codes', [
            'partner_id' => $partner->id,
            'normalized_code' => 'DEMO2026',
            'status' => 'active',
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('partner_promo_codes', [
            'id' => $old->id,
            'status' => 'retired',
            'is_primary' => false,
        ]);
        $this->actingAs($partner, 'partner')->get(route('partner.code'))
            ->assertOk()
            ->assertSee('Historique des changements')
            ->assertSee($old->normalized_code)
            ->assertSee('DEMO2026');

        $this->actingAs($partner, 'partner')->put(route('partner.code.update'), ['code' => 'DEMO2027'])
            ->assertSessionHasErrors('code');
        $this->assertStringContainsString('pourra être modifié', session('errors')->first('code'));
    }

    public function test_reserved_code_is_rejected_and_public_validation_is_neutral(): void
    {
        $this->enablePortal();
        $partner = $this->activePartner(['username' => 'public-partner']);
        $this->actingAs($partner, 'partner')->get(route('partner.code'));

        $this->actingAs($partner, 'partner')->put(route('partner.code.update'), ['code' => 'ADMIN'])
            ->assertSessionHasErrors('code');

        $code = $partner->fresh()->promoCodes()->where('is_primary', true)->first()->normalized_code;
        $this->postJson(route('partner.code.validate'), ['code' => strtolower($code)])
            ->assertOk()
            ->assertExactJson([
                'valid' => true,
                'discount_percent' => 10,
                'message' => 'Code partenaire valide. La remise s’appliquera au premier abonnement éligible.',
            ]);

        $this->postJson(route('partner.code.validate'), ['code' => 'NOPE9999'])
            ->assertOk()
            ->assertExactJson([
                'valid' => false,
                'discount_percent' => 0,
                'message' => 'Ce code partenaire n’est pas disponible.',
            ]);
    }

    public function test_authenticated_availability_endpoint_distinguishes_current_available_and_taken_codes(): void
    {
        $this->enablePortal();
        $partner = $this->activePartner(['username' => 'availability-partner']);
        $this->actingAs($partner, 'partner')->get(route('partner.code'));
        $current = $partner->fresh()->promoCodes()->where('is_primary', true)->first()->normalized_code;
        PartnerPromoCode::create([
            'partner_id' => $partner->id,
            'code' => 'TAKEN2026',
            'normalized_code' => 'TAKEN2026',
            'status' => 'retired',
            'is_primary' => false,
            'disabled_at' => now(),
        ]);

        $this->actingAs($partner, 'partner')->getJson(route('partner.code.availability', ['code' => $current]))
            ->assertOk()->assertExactJson(['available' => true, 'state' => 'current', 'message' => 'Votre code actuel est disponible.']);
        $this->actingAs($partner, 'partner')->getJson(route('partner.code.availability', ['code' => 'newcode2026']))
            ->assertOk()->assertExactJson(['available' => true, 'state' => 'available', 'message' => 'Ce code est disponible.']);
        $this->actingAs($partner, 'partner')->getJson(route('partner.code.availability', ['code' => 'taken2026']))
            ->assertOk()->assertExactJson(['available' => false, 'state' => 'taken', 'message' => 'Ce code partenaire est déjà utilisé ou indisponible.']);
        $this->actingAs($partner, 'partner')->getJson(route('partner.code.availability', ['code' => 'ADMIN']))
            ->assertOk()->assertExactJson(['available' => false, 'state' => 'reserved', 'message' => 'Ce code est réservé par la plateforme.']);
    }

    public function test_public_validation_is_disabled_neutrally_when_portal_is_closed(): void
    {
        $this->postJson(route('partner.code.validate'), ['code' => 'DEMO2026'])
            ->assertOk()
            ->assertExactJson([
                'valid' => false,
                'discount_percent' => 0,
                'message' => 'Ce code partenaire n’est pas disponible.',
            ]);
    }
}
