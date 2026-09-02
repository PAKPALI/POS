<footer class="marketing-footer">
    <div class="marketing-container marketing-footer-grid">
        <div>
            <a class="marketing-brand" href="{{ route('marketing.home') }}"><span class="marketing-brand-mark">P</span><span><strong>POS</strong><small>SaaS Afrique</small></span></a>
            <p>Une manière plus simple et plus professionnelle de gérer votre commerce.</p>
        </div>
        <div><strong>Produit</strong><a href="{{ route('marketing.features') }}">Fonctionnalités</a><a href="{{ route('marketing.invoices') }}">Factures SMS & WhatsApp</a><a href="{{ route('marketing.pricing') }}">Tarifs</a></div>
        <div><strong>Confiance</strong><a href="{{ route('marketing.security') }}">Sécurité</a><a href="{{ route('marketing.help') }}">Aide</a><a href="{{ route('marketing.legal') }}">Mentions légales</a></div>
        <div><strong>Accès</strong><a href="{{ route('marketing.login') }}">Se connecter</a><a href="{{ route('marketing.register') }}">Créer un espace</a><span class="marketing-footer-note">Français · autres langues à venir</span></div>
    </div>
    <div class="marketing-container marketing-footer-bottom"><span>© {{ date('Y') }} POS SaaS Afrique</span><span>Les offres affichées sont prévisionnelles.</span></div>
</footer>
