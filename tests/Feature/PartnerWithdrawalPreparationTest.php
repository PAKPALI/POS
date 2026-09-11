<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerWalletEntry;
use App\Services\PartnerWithdrawalService;
use App\Services\PartnerAuthenticationService;
use App\Models\PartnerTwoFactorChallenge;
use App\Notifications\PartnerWithdrawalConfirmationNotification;
use App\Notifications\PartnerWithdrawalAccountConfirmationNotification;
use App\Services\PlatformConfigurationService;
use App\Models\PlatformSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PartnerWithdrawalPreparationTest extends TestCase
{
    use RefreshDatabase;

    private function request(): Request
    {
        return Request::create('/partner/withdrawals', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_USER_AGENT' => 'phpunit']);
    }

    public function test_account_is_encrypted_masked_and_pending_by_default(): void
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $account = app(PartnerWithdrawalService::class)->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96 00 00 00', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $this->assertSame('pending_verification', $account->status);
        $this->assertSame('+228••••00', $account->maskedPhone());
        $this->assertNotSame('+2289000000', (string) $account->getRawOriginal('phone_e164'));
        $this->assertDatabaseHas('partner_audit_logs', ['action' => 'partner.withdrawal_account.registered']);
    }

    public function test_withdrawal_page_exposes_preparation_state_without_transfer_action(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $this->actingAs($partner, 'partner')->get(route('partner.withdrawals'))
            ->assertOk()
            ->assertSee('Retraits Mobile Money')
            ->assertSee('Les retraits sont temporairement désactivés.')
            ->assertSee('Configurez le compte Mobile Money de vos futurs versements.')
            ->assertSee('Ce compte est réservé à vos versements.')
            ->assertSee('Suivi de vos retraits')
            ->assertDontSee('payouts/transfers');
    }

    public function test_withdrawal_page_exposes_remaining_eligibility_blockers_when_payouts_are_enabled(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payouts_enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payout_min_xof')->update(['value' => '100']);
        PlatformSetting::where('key', 'partners.payout_min_qualified_clients')->update(['value' => '1']);
        app(PlatformConfigurationService::class)->forget([
            'partners.enabled',
            'partners.payouts_enabled',
            'partners.payout_min_xof',
            'partners.payout_min_qualified_clients',
        ]);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 0]);

        $this->actingAs($partner, 'partner')->get(route('partner.withdrawals'))
            ->assertOk()
            ->assertSee('Votre demande est encore indisponible')
            ->assertSee('Il faut au moins 1 client(s) qualifié(s).')
            ->assertSee('Le solde disponible doit atteindre 100 XOF.')
            ->assertDontSee('Les retraits sont temporairement désactivés.');
    }

    public function test_pending_account_can_be_confirmed_from_a_saas_modal_after_cancelling_the_first_step(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $account = app(PartnerWithdrawalService::class)->registerAccount($partner, [
            'country_code' => 'TG',
            'gateway' => 'MOOV-MONEY-TG',
            'phone_number' => '96 00 00 04',
            'beneficiary_name' => 'Partenaire Test',
        ], $this->request());

        $this->actingAs($partner, 'partner')
            ->withSession(['partner_withdrawal_account_pending_id' => $account->id])
            ->get(route('partner.withdrawals'))
            ->assertOk()
            ->assertSee('Confirmation e-mail requise')
            ->assertSee('partnerAccountConfirmModal')
            ->assertSee('Code reçu par e-mail')
            ->assertSee(route('partner.withdrawals.accounts.confirm.submit'));
    }

    public function test_pending_account_confirmation_code_can_be_resent_from_the_modal(): void
    {
        Notification::fake();
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $account = app(PartnerWithdrawalService::class)->registerAccount($partner, [
            'country_code' => 'TG',
            'gateway' => 'MOOV-MONEY-TG',
            'phone_number' => '96 00 00 05',
            'beneficiary_name' => 'Partenaire Test',
        ], $this->request());

        $this->actingAs($partner, 'partner')
            ->withSession(['partner_withdrawal_account_pending_id' => $account->id])
            ->post(route('partner.withdrawals.accounts.confirm.resend'), ['account_id' => $account->id])
            ->assertRedirect(route('partner.withdrawals'))
            ->assertSessionHas('open_partner_account_confirmation', true)
            ->assertSessionHas('status', 'Un nouveau code de confirmation vient d’être envoyé à votre adresse e-mail.');

        Notification::assertSentTo($partner, PartnerWithdrawalAccountConfirmationNotification::class);
    }

    public function test_request_is_blocked_while_payout_feature_is_disabled(): void
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 1, 'idempotency_key' => 'test-withdrawal-disabled', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96 00 00 01', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());
        $this->expectExceptionMessage('PAYOUTS_DISABLED');
        $service->requestWithdrawal($partner, $account, 5000, $this->request(), true);
    }

    public function test_reservation_moves_balance_and_failure_releases_it_atomically(): void
    {
        $setting = \App\Models\PlatformSetting::where('key', 'partners.payouts_enabled')->firstOrFail();
        $setting->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.payouts_enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 2, 'idempotency_key' => 'test-withdrawal-enabled', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96 00 00 02', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());
        $withdrawal = $service->requestWithdrawal($partner, $account, 5000, $this->request(), true);
        $this->assertSame('otp_verified', $withdrawal->status);
        $this->assertSame(2950, $service->balances($partner)['available']);
        $this->assertSame(5050, $service->balances($partner)['reserved']);
        $service->failAndRelease($withdrawal, 'Échec simulé de recette', $this->request());
        $this->assertSame(8000, $service->balances($partner)['available']);
        $this->assertSame(0, $service->balances($partner)['reserved']);
        $this->assertSame('failed', $withdrawal->fresh()->status);
    }

    public function test_unknown_provider_result_keeps_the_reservation(): void
    {
        $setting = \App\Models\PlatformSetting::where('key', 'partners.payouts_enabled')->firstOrFail();
        $setting->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.payouts_enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 3]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 8000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 3, 'idempotency_key' => 'test-withdrawal-unknown', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96 00 00 03', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());
        $withdrawal = $service->requestWithdrawal($partner, $account, 5000, $this->request(), true);
        $withdrawal->update(['status' => 'unknown']);
        $service->failAndRelease($withdrawal, 'Réponse fournisseur inconnue', $this->request());
        $this->assertSame('unknown', $withdrawal->fresh()->status);
        $this->assertSame(2950, $service->balances($partner)['available']);
        $this->assertSame(5050, $service->balances($partner)['reserved']);
    }

    public function test_gateway_prefix_is_checked_before_a_mobile_money_account_is_stored(): void
    {
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        $this->expectExceptionMessage('PAYOUT_PHONE_PREFIX_INVALID');
        app(PartnerWithdrawalService::class)->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '90000000', 'beneficiary_name' => 'Partenaire Test'], $this->request());
    }

    public function test_disabled_gateway_is_hidden_from_the_withdrawal_selector_and_rejected_by_the_server(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payouts_enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payout_min_xof')->update(['value' => '100']);
        PlatformSetting::where('key', 'partners.payout_min_qualified_clients')->update(['value' => '1']);
        PlatformSetting::where('key', 'partners.payout_gateways')->update(['value' => '{"TG":["MOOV-MONEY-TG","MIXX-YAS-TG"]}']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled', 'partners.payouts_enabled', 'partners.payout_min_xof', 'partners.payout_min_qualified_clients', 'partners.payout_gateways']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 1, 'password' => Hash::make('password')]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 1000, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 90, 'idempotency_key' => 'disabled-gateway-credit', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $mixx = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MIXX-YAS-TG', 'phone_number' => '90000090', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($mixx, $this->request());

        PlatformSetting::where('key', 'partners.payout_gateways')->update(['value' => '{"TG":["MOOV-MONEY-TG"]}']);
        app(PlatformConfigurationService::class)->forget(['partners.payout_gateways']);

        $this->actingAs($partner, 'partner')->get(route('partner.withdrawals'))
            ->assertOk()
            ->assertSee('Retrait indisponible')
            ->assertSee('Aucun moyen de retrait autorisé.')
            ->assertDontSee('<option value="'.$mixx->id.'">', false);

        Notification::fake();
        $this->actingAs($partner, 'partner')
            ->from(route('partner.withdrawals'))
            ->post(route('partner.withdrawals.request'), ['account_id' => $mixx->id, 'amount' => 100, 'current_password' => 'password'])
            ->assertRedirect(route('partner.withdrawals'))
            ->assertSessionHasErrors(['account_id' => 'Ce moyen de retrait est temporairement indisponible. Sélectionnez un opérateur activé par la plateforme.']);
        Notification::assertNothingSent();

        $this->expectExceptionMessage('PAYOUT_GATEWAY_DISABLED');
        $service->requestWithdrawal($partner, $mixx, 100, $this->request(), true);
    }

    public function test_withdrawal_reserves_the_amount_sent_plus_the_kprimepay_tariff(): void
    {
        PlatformSetting::where('key', 'partners.payouts_enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payout_min_xof')->update(['value' => '100']);
        PlatformSetting::where('key', 'partners.payout_min_qualified_clients')->update(['value' => '1']);
        PlatformSetting::where('key', 'partners.payout_fee_bps')->update(['value' => '100']);
        app(PlatformConfigurationService::class)->forget(['partners.payouts_enabled', 'partners.payout_min_xof', 'partners.payout_min_qualified_clients', 'partners.payout_fee_bps']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 1]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 1010, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 88, 'idempotency_key' => 'fee-preview-credit', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96000088', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());

        $this->assertSame(10, $service->feesForAmount(1000));
        $this->assertSame(1010, $service->totalForAmount(1000));
        $withdrawal = $service->requestWithdrawal($partner, $account, 1000, $this->request(), true);
        $this->assertSame(10, $withdrawal->fees);
        $this->assertSame(0, $service->balances($partner)['available']);
        $this->assertSame(1010, $service->balances($partner)['reserved']);
    }

    public function test_withdrawal_fee_preview_is_rendered_from_isolated_test_wallet_data_without_sending_a_confirmation(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payouts_enabled')->update(['value' => 'true']);
        PlatformSetting::where('key', 'partners.payout_min_xof')->update(['value' => '100']);
        PlatformSetting::where('key', 'partners.payout_min_qualified_clients')->update(['value' => '1']);
        PlatformSetting::where('key', 'partners.payout_fee_bps')->update(['value' => '100']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled', 'partners.payouts_enabled', 'partners.payout_min_xof', 'partners.payout_min_qualified_clients', 'partners.payout_fee_bps']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now(), 'qualified_clients_count' => 1]);
        PartnerWalletEntry::create(['partner_id' => $partner->id, 'entry_type' => 'commission_credit', 'bucket' => 'available', 'direction' => 'credit', 'amount' => 1010, 'currency' => 'XOF', 'source_type' => 'test', 'source_id' => 89, 'idempotency_key' => 'fee-preview-ui-credit', 'occurred_at' => now()]);
        $service = app(PartnerWithdrawalService::class);
        $account = $service->registerAccount($partner, ['country_code' => 'TG', 'gateway' => 'MOOV-MONEY-TG', 'phone_number' => '96000089', 'beneficiary_name' => 'Partenaire Test'], $this->request());
        $service->verifyAccount($account, $this->request());

        $this->actingAs($partner, 'partner')->get(route('partner.withdrawals'))
            ->assertOk()
            ->assertSee('Estimation des frais KPrimePay (jusqu’à 1,00 %)')
            ->assertSee('Montant maximal réservé')
            ->assertSee('data-fee-bps="100"', false)
            ->assertSee('withdrawalRequestButton')
            ->assertSee('data-minimum="100"', false)
            ->assertSee('Montant minimum non atteint')
            ->assertSee('Swal.fire')
            ->assertDontSee('Un code de confirmation vient d’être envoyé');
    }

    public function test_invalid_prefix_error_explains_the_accepted_prefixes(): void
    {
        PlatformSetting::where('key', 'partners.enabled')->update(['value' => 'true']);
        app(PlatformConfigurationService::class)->forget(['partners.enabled']);
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);

        $this->actingAs($partner, 'partner')
            ->from(route('partner.withdrawals'))
            ->post(route('partner.withdrawals.accounts.store'), [
                'country_code' => 'TG',
                'gateway' => 'MOOV-MONEY-TG',
                'phone_number' => '90859488',
                'beneficiary_name' => 'Partenaire Test',
            ])
            ->assertRedirect(route('partner.withdrawals'))
            ->assertSessionHasErrors(['phone_number' => 'Les deux premiers chiffres (90) ne correspondent pas aux préfixes acceptés : 76, 77, 78, 79, 96, 97, 98, 99.']);
    }

    public function test_withdrawal_confirmation_uses_a_separate_email_challenge(): void
    {
        Notification::fake();
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        app(PartnerAuthenticationService::class)->issueWithdrawalConfirmation($partner, $this->request());
        $this->assertDatabaseHas('partner_two_factor_challenges', ['partner_id' => $partner->id, 'purpose' => 'withdrawal']);
        Notification::assertSentTo($partner, PartnerWithdrawalConfirmationNotification::class);

        $mail = (new PartnerWithdrawalConfirmationNotification('654321'))->toMail($partner);
        $this->assertSame('emails.partner.withdrawalConfirmation', $mail->view);
        $this->view($mail->view, $mail->viewData)
            ->assertSee('Confirmation de votre demande de retrait')
            ->assertSee('Sécurité du compte partenaire')
            ->assertSee('654321')
            ->assertSee('Aucun versement ne sera lancé avant cette validation.')
            ->assertSee('Copyright');
    }

    public function test_account_confirmation_uses_a_dedicated_email_challenge(): void
    {
        Notification::fake();
        $partner = Partner::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
        app(PartnerAuthenticationService::class)->issueWithdrawalAccountConfirmation($partner, $this->request());
        $this->assertDatabaseHas('partner_two_factor_challenges', ['partner_id' => $partner->id, 'purpose' => 'withdrawal_account']);
        Notification::assertSentTo($partner, PartnerWithdrawalAccountConfirmationNotification::class);

        $mail = (new PartnerWithdrawalAccountConfirmationNotification('123456'))->toMail($partner);
        $this->assertSame('emails.partner.withdrawalAccountConfirmation', $mail->view);
        $this->view($mail->view, $mail->viewData)
            ->assertSee('Sécurité du compte partenaire')
            ->assertSee('123456')
            ->assertSee('Copyright');
    }
}
