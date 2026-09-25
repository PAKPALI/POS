@extends('layouts.partner')

@section('title', 'Guide partenaire')
@section('page-title', 'Guide partenaire')

@push('styles')
    <link rel="stylesheet" href="{{ asset('hub/assets/css/saas-guides.css') }}?v=20260918-1">
@endpush

@section('content')
    <div class="saas-guide">
        <x-ui.page-header
            eyebrow="Guide partenaire"
            title="Développez votre réseau avec sérénité"
            description="Retrouvez les repères essentiels pour partager votre code, suivre vos commissions et utiliser votre espace partenaire en toute sécurité."
            icon="bi-journal-richtext"
        >
            <x-slot:actions>
                <x-ui.button :href="route('partner.guide.pdf')" variant="primary"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Télécharger le guide PDF</x-ui.button>
                <x-ui.button :href="route('partner.code')" variant="secondary" icon="bi-ticket-perforated">Mon code</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <section class="saas-guide-hero" aria-labelledby="partner-guide-intro">
            <div>
                <span class="saas-guide-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Espace partenaire</span>
                <h2 id="partner-guide-intro">Votre rôle : recommander, informer, suivre.</h2>
                <p>Votre espace vous aide à accompagner les entreprises que vous recommandez, sans jamais vous demander de gérer leurs données ni leurs paiements.</p>
            </div>
            <a class="saas-guide-hero-link" href="#commissions">Comprendre mes commissions <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
        </section>

        <nav class="saas-guide-nav" aria-label="Sommaire du guide partenaire">
            <a href="#demarrer"><i class="bi bi-person-check" aria-hidden="true"></i> Démarrer</a>
            <a href="#code"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Mon code</a>
            <a href="#clients"><i class="bi bi-buildings" aria-hidden="true"></i> Entreprises</a>
            <a href="#commissions"><i class="bi bi-wallet2" aria-hidden="true"></i> Commissions</a>
            <a href="#retraits"><i class="bi bi-cash-stack" aria-hidden="true"></i> Retraits</a>
            <a href="#securite"><i class="bi bi-shield-check" aria-hidden="true"></i> Sécurité</a>
        </nav>

        <x-ui.notice variant="info" icon="bi-info-circle">
            Les montants, liens entre entreprises et commissions sont confirmés par le système après le paiement concerné. Ne promettez donc jamais une réduction ou une commission avant cette confirmation.
        </x-ui.notice>

        <section id="demarrer" class="saas-guide-section" aria-labelledby="demarrer-title">
            <div class="saas-guide-section-heading">
                <span class="saas-guide-number">01</span>
                <div>
                    <span class="saas-guide-eyebrow">Premiers repères</span>
                    <h2 id="demarrer-title">Configurer votre espace</h2>
                </div>
            </div>
            <div class="saas-guide-start-grid">
                <article>
                    <i class="bi bi-envelope-check" aria-hidden="true"></i>
                    <h3>Vérifiez votre e-mail</h3>
                    <p>Utilisez une adresse à laquelle vous avez réellement accès : elle permet de recevoir les confirmations et les codes de sécurité.</p>
                </article>
                <article>
                    <i class="bi bi-person-gear" aria-hidden="true"></i>
                    <h3>Complétez votre profil</h3>
                    <p>Gardez vos coordonnées à jour afin que les échanges et, lorsque proposé, les retraits puissent être traités correctement.</p>
                </article>
                <article>
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <h3>Activez la double vérification</h3>
                    <p>Elle est optionnelle, mais fortement recommandée : un code supplémentaire sera demandé lors de la connexion.</p>
                </article>
            </div>
        </section>

        <section id="code" class="saas-guide-section" aria-labelledby="code-title">
            <div class="saas-guide-section-heading">
                <span class="saas-guide-number">02</span>
                <div>
                    <span class="saas-guide-eyebrow">Recommandation</span>
                    <h2 id="code-title">Partager votre code partenaire</h2>
                </div>
            </div>
            <div class="saas-guide-accordion">
                <details open>
                    <summary><span><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Où trouver et utiliser mon code ?</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="saas-guide-accordion-body">
                        <p>Ouvrez <strong>Mon code partenaire</strong> pour copier votre code ou le partager. La personne recommandée le saisit lors de son abonnement ; le code doit être associé à la bonne entreprise avant le paiement.</p>
                    </div>
                </details>
                <details>
                    <summary><span><i class="bi bi-percent" aria-hidden="true"></i> Quelle réduction reçoit l’entreprise ?</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="saas-guide-accordion-body">
                        <p>Lorsqu’un code valide est utilisé pour un premier abonnement éligible, la réduction prévue par le programme est calculée par la plateforme. Les conditions affichées dans l’application font foi.</p>
                    </div>
                </details>
                <details>
                    <summary><span><i class="bi bi-hand-thumbs-up" aria-hidden="true"></i> Les bonnes pratiques de recommandation</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="saas-guide-accordion-body">
                        <p>Présentez le produit avec exactitude, partagez uniquement votre propre code et évitez toute auto-recommandation. Ne demandez jamais le mot de passe, un code de connexion ou des informations de paiement d’une entreprise.</p>
                    </div>
                </details>
            </div>
        </section>

        <section id="clients" class="saas-guide-section" aria-labelledby="clients-title">
            <div class="saas-guide-section-heading">
                <span class="saas-guide-number">03</span>
                <div>
                    <span class="saas-guide-eyebrow">Suivi</span>
                    <h2 id="clients-title">Suivre les entreprises attribuées</h2>
                </div>
            </div>
            <div class="saas-guide-callout-grid">
                <article class="saas-guide-callout-card">
                    <i class="bi bi-buildings" aria-hidden="true"></i>
                    <h3>Liste des entreprises</h3>
                    <p>La rubrique dédiée affiche les entreprises qui ont été correctement rattachées à votre code après une opération éligible.</p>
                </article>
                <article class="saas-guide-callout-card">
                    <i class="bi bi-incognito" aria-hidden="true"></i>
                    <h3>Données protégées</h3>
                    <p>Vous ne voyez pas les mots de passe, les moyens de paiement ni les données privées inutiles au suivi du partenariat.</p>
                </article>
                <article class="saas-guide-callout-card">
                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                    <h3>Patientez après un paiement</h3>
                    <p>Une attribution ou une commission peut nécessiter la confirmation du paiement. Rafraîchissez plus tard si l’opération est encore en cours.</p>
                </article>
            </div>
        </section>

        <section id="commissions" class="saas-guide-section" aria-labelledby="commissions-title">
            <div class="saas-guide-section-heading">
                <span class="saas-guide-number">04</span>
                <div>
                    <span class="saas-guide-eyebrow">Revenus</span>
                    <h2 id="commissions-title">Lire vos commissions</h2>
                </div>
            </div>
            <div class="saas-guide-accordion">
                <details open>
                    <summary><span><i class="bi bi-receipt" aria-hidden="true"></i> Quand une commission apparaît-elle ?</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="saas-guide-accordion-body">
                        <p>Elle est créée après la confirmation d’une opération éligible liée à une entreprise attribuée. Selon les règles du programme, cela peut concerner une acquisition, un renouvellement ou une amélioration de plan.</p>
                    </div>
                </details>
                <details>
                    <summary><span><i class="bi bi-list-check" aria-hidden="true"></i> Comprendre les statuts</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="saas-guide-accordion-body">
                        <p>Une commission peut être en attente de validation, disponible au retrait, ou annulée si l’opération de référence est annulée ou remboursée. Consultez le détail pour connaître l’origine et le statut exact.</p>
                    </div>
                </details>
                <details>
                    <summary><span><i class="bi bi-calculator" aria-hidden="true"></i> Comment le montant est-il calculé ?</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="saas-guide-accordion-body">
                        <p>Le taux applicable est enregistré avec la commission au moment où elle est créée. Vos historiques restent ainsi cohérents même si les règles évoluent par la suite.</p>
                    </div>
                </details>
            </div>
        </section>

        <section id="retraits" class="saas-guide-section" aria-labelledby="retraits-title">
            <div class="saas-guide-section-heading">
                <span class="saas-guide-number">05</span>
                <div>
                    <span class="saas-guide-eyebrow">Paiement partenaire</span>
                    <h2 id="retraits-title">Demander un retrait</h2>
                </div>
            </div>
            <div class="saas-guide-start-grid">
                <article>
                    <i class="bi bi-phone" aria-hidden="true"></i>
                    <h3>Enregistrez le bon numéro</h3>
                    <p>Lorsque cette option est disponible, ajoutez votre numéro Mobile Money et vérifiez-le avec le code reçu par e-mail.</p>
                </article>
                <article>
                    <i class="bi bi-send-check" aria-hidden="true"></i>
                    <h3>Demandez uniquement le solde disponible</h3>
                    <p>Choisissez un montant dans la limite de votre solde disponible. La demande conserve son statut jusqu’à son traitement.</p>
                </article>
                <article>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <h3>Suivez le statut</h3>
                    <p>Consultez vos retraits pour savoir s’ils sont en attente, en cours, effectués ou nécessitent une action de votre part.</p>
                </article>
            </div>
        </section>

        <section id="securite" class="saas-guide-section" aria-labelledby="securite-title">
            <div class="saas-guide-section-heading">
                <span class="saas-guide-number">06</span>
                <div>
                    <span class="saas-guide-eyebrow">Compte protégé</span>
                    <h2 id="securite-title">Conserver le contrôle de votre compte</h2>
                </div>
            </div>
            <div class="saas-guide-callout-grid">
                <article class="saas-guide-callout-card">
                    <i class="bi bi-key" aria-hidden="true"></i>
                    <h3>Mot de passe unique</h3>
                    <p>Utilisez un mot de passe long et réservé à MAXANOU. Ne le partagez avec personne, y compris une personne qui prétend appartenir au support.</p>
                </article>
                <article class="saas-guide-callout-card">
                    <i class="bi bi-123" aria-hidden="true"></i>
                    <h3>Codes confidentiels</h3>
                    <p>Un code reçu par e-mail sert seulement à confirmer une action. Le support ne vous le demandera jamais.</p>
                </article>
                <article class="saas-guide-callout-card">
                    <i class="bi bi-headset" aria-hidden="true"></i>
                    <h3>Besoin d’aide ?</h3>
                    <p>Utilisez les canaux officiels indiqués dans l’application pour signaler une anomalie sur un code, une entreprise ou un retrait. Vous pouvez aussi écrire à <a href="mailto:{{ config('marketing.contact_email') }}">{{ config('marketing.contact_email') }}</a>.</p>
                </article>
            </div>
        </section>
    </div>
@endsection
