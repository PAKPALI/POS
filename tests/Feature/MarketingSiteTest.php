<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingSiteTest extends TestCase
{
    public function test_public_marketing_pages_render_with_real_auth_links(): void
    {
        $this->get('/')->assertOk()->assertSee('Vendez simplement. Gardez le contrôle.')->assertSee('Testez Maxanou dans les conditions réelles de votre commerce.')->assertSee(route('marketing.register'), false)->assertSee(route('marketing.login'), false)->assertSee('data-marketing-appearance', false)->assertSee('data-marketing-mode="light"', false)->assertSee('data-marketing-accent="#3B82F6"', false);
        foreach (['fonctionnalites', 'factures-sms-whatsapp', 'secteurs', 'securite', 'aide', 'mentions-legales'] as $page) {
            $this->get('/'.$page)->assertOk();
        }
    }

    public function test_pricing_data_is_centralized_and_future_offers_are_not_subscriptions(): void
    {
        $response = $this->get('/tarifs')->assertOk();
        $response->assertSee('Offres prévisionnelles', false)->assertSee('Être informé', false)->assertSee('data-price-annual="55000"', false)->assertDontSee('Souscrire', false);
    }

    public function test_marketing_shortcuts_redirect_to_existing_authentication_routes(): void
    {
        $this->get('/connexion')->assertRedirect(route('user_login'));
        $this->get('/inscription')->assertRedirect(route('register'));
    }

    public function test_public_seo_files_are_coherent(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSee('/tarifs', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('marketing.sitemap'), false);
    }
}
