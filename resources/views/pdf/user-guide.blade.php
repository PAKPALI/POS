<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Guide d’utilisation {{ config('app.name') }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #19243a; background: #ffffff; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.48; }
        .page { position: relative; padding: 19mm 18mm 20mm; page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .cover { height: 297mm; padding: 0; color: #ffffff; background: #0d1b35; }
        .cover-top { height: 13mm; background: #2f75ed; }
        .cover-body { padding: 44mm 25mm 0; }
        .brand { display: inline-block; padding: 7px 11px; color: #ffffff; background: #2366d5; border-radius: 8px; font-size: 13px; font-weight: bold; letter-spacing: 1.2px; }
        .cover-kicker { margin-top: 28mm; color: #8db6ff; font-size: 10px; font-weight: bold; letter-spacing: 1.6px; text-transform: uppercase; }
        .cover h1 { max-width: 150mm; margin: 8mm 0 5mm; color: #ffffff; font-size: 31px; line-height: 1.13; }
        .cover-intro { max-width: 142mm; color: #d7e3fa; font-size: 13px; line-height: 1.65; }
        .cover-rule { width: 32mm; height: 2mm; margin: 17mm 0 9mm; background: #47d5b6; }
        .cover-meta { color: #a9b9d5; font-size: 10px; }
        .cover-bottom { position: absolute; right: 25mm; bottom: 22mm; left: 25mm; padding-top: 5mm; border-top: 1px solid #294267; color: #a9b9d5; font-size: 9px; }
        .eyebrow { margin: 0 0 3mm; color: #2f75ed; font-size: 9px; font-weight: bold; letter-spacing: 1.1px; text-transform: uppercase; }
        h2 { margin: 0 0 5mm; color: #142a4e; font-size: 22px; line-height: 1.2; }
        h3 { margin: 0 0 2mm; color: #142a4e; font-size: 12px; line-height: 1.3; }
        h4 { margin: 0 0 1.5mm; color: #2f75ed; font-size: 10px; }
        p { margin: 0 0 4mm; }
        .lead { max-width: 155mm; color: #52627b; font-size: 11px; }
        .topline { height: 1mm; margin: -19mm -18mm 15mm; background: #2f75ed; }
        .section-tag { display: inline-block; min-width: 10mm; margin-bottom: 5mm; padding: 2mm 3mm; color: #ffffff; background: #2f75ed; border-radius: 4px; font-size: 9px; font-weight: bold; text-align: center; }
        .toc { margin: 9mm 0 0; padding: 0; list-style: none; }
        .toc li { margin-bottom: 4mm; padding: 4mm 5mm; border: 1px solid #dce5f2; border-radius: 6px; }
        .toc strong { display: inline-block; width: 12mm; color: #2f75ed; }
        .toc span { color: #52627b; }
        .summary-grid { display: table; width: 100%; table-layout: fixed; border-spacing: 3mm; margin: 7mm -3mm 0; }
        .summary-card { display: table-cell; width: 33.33%; padding: 5mm; vertical-align: top; background: #f1f6ff; border: 1px solid #dce8fb; border-radius: 7px; }
        .summary-card + .summary-card { margin-left: 3mm; }
        .summary-card .number { color: #2f75ed; font-size: 18px; font-weight: bold; }
        .summary-card strong { display: block; margin: 2mm 0 1mm; color: #142a4e; }
        .summary-card span { color: #52627b; font-size: 9px; }
        .block { margin: 6mm 0; padding: 5mm 6mm; border: 1px solid #dce5f2; border-left: 3px solid #2f75ed; border-radius: 5px; page-break-inside: avoid; }
        .block p:last-child, .block ul:last-child { margin-bottom: 0; }
        .block.green { border-left-color: #22aa8b; background: #f1fbf8; }
        .block.warning { border-left-color: #e8983d; background: #fff9ef; }
        .block.dark { color: #dce7f9; background: #142a4e; border-color: #142a4e; }
        .block.dark h3, .block.dark h4 { color: #ffffff; }
        .block.dark p, .block.dark li { color: #dce7f9; }
        .two-col { display: table; width: 100%; table-layout: fixed; border-spacing: 4mm; margin: 0 -4mm; }
        .two-col > div { display: table-cell; width: 50%; padding: 4mm 5mm; vertical-align: top; }
        .two-col > div:first-child { border-right: 1px solid #dce5f2; }
        ul { margin: 2mm 0 4mm; padding-left: 5mm; }
        li { margin-bottom: 2mm; color: #52627b; }
        li strong { color: #19243a; }
        .step { display: table; width: 100%; margin: 0 0 4mm; padding: 4mm; background: #f7f9fc; border: 1px solid #e2e8f2; border-radius: 5px; page-break-inside: avoid; }
        .step-no { display: table-cell; width: 10mm; color: #2f75ed; font-size: 16px; font-weight: bold; vertical-align: top; }
        .step-copy { display: table-cell; vertical-align: top; }
        .step-copy strong { display: block; margin-bottom: 1mm; color: #142a4e; }
        .step-copy span { color: #52627b; }
        .matrix { width: 100%; margin: 4mm 0; border-collapse: collapse; font-size: 9px; }
        .matrix th { padding: 3mm; color: #ffffff; background: #142a4e; text-align: left; }
        .matrix td { padding: 3mm; border-bottom: 1px solid #dce5f2; color: #52627b; vertical-align: top; }
        .matrix tr:nth-child(even) td { background: #f7f9fc; }
        .callout-title { margin-bottom: 2mm; color: #2f75ed; font-size: 9px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
        .footer-note { margin-top: 13mm; padding-top: 3mm; border-top: 1px solid #e2e8f2; color: #7a889c; font-size: 8px; }
        .closing { display: table; width: 100%; margin-top: 13mm; padding: 7mm; color: #ffffff; background: #142a4e; border-radius: 7px; }
        .closing strong { display: block; margin-bottom: 2mm; color: #ffffff; font-size: 14px; }
        .closing span { color: #dce7f9; }
        .security-page { font-size: 9px; }
        .security-page .lead { font-size: 10px; }
        .security-page .summary-grid { margin-top: 5mm; }
        .security-page .summary-card { padding: 4mm; }
        .security-page .summary-card span { font-size: 8px; }
        .security-page .two-col { margin-top: -1mm; }
        .security-page .two-col > div { padding: 3mm 5mm; }
        .security-page li { margin-bottom: 1.5mm; }
        .security-page .block { margin: 4mm 0; padding: 4mm 5mm; }
        .security-page .closing { margin-top: 7mm; padding: 5mm 6mm; }
        .security-page .closing strong { font-size: 12px; }
        .security-page .footer-note { margin-top: 7mm; }
    </style>
</head>
<body>
    <section class="page cover">
        <div class="cover-top"></div>
        <div class="cover-body">
            <div class="brand">MAXANOU</div>
            <p class="cover-kicker">Guide officiel de la plateforme</p>
            <h1>Bien utiliser MAXANOU</h1>
            <p class="cover-intro">Un guide complet pour comprendre les parcours de gestion, de vente, d’abonnement et de partenariat de la plateforme SaaS.</p>
            <div class="cover-rule"></div>
            <p class="cover-meta">Version 1.0 · 21 septembre 2026<br>Document de présentation et d’accompagnement</p>
        </div>
        <div class="cover-bottom">MAXANOU · Gestion commerciale, ventes et développement de réseau</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">À propos de ce document</p>
        <h2>Un accompagnement clair pour chaque rôle</h2>
        <p class="lead">Ce guide présente les usages principaux de MAXANOU afin d’aider les entreprises, leurs équipes et les partenaires à adopter la plateforme rapidement, avec une compréhension claire des droits, des données et des règles de sécurité.</p>
        <div class="summary-grid">
            <div class="summary-card"><div class="number">01</div><strong>Entreprise</strong><span>Organiser l’activité, vendre, suivre le stock, piloter l’équipe et l’abonnement.</span></div>
            <div class="summary-card"><div class="number">02</div><strong>Partenaire</strong><span>Recommander MAXANOU, suivre les entreprises attribuées et gérer ses commissions.</span></div>
            <div class="summary-card"><div class="number">03</div><strong>Confiance</strong><span>Préserver l’isolation des données, les paiements vérifiés et les accès sécurisés.</span></div>
        </div>
        <p class="eyebrow" style="margin-top:13mm">Sommaire</p>
        <ol class="toc">
            <li><strong>A</strong> <b>Guide entreprise</b><br><span>Prise en main, catalogue, ventes, stock, équipe, communication, e-commerce et abonnement.</span></li>
            <li><strong>B</strong> <b>Guide partenaire</b><br><span>Activation, code partenaire, entreprises attribuées, commissions, retraits et sécurité.</span></li>
            <li><strong>C</strong> <b>Principes de sécurité et support</b><br><span>Bonnes pratiques communes et conduite à tenir en cas de difficulté.</span></li>
        </ol>
        <div class="block green"><div class="callout-title">Repère important</div><p>Les fonctionnalités visibles dépendent du rôle de l’utilisateur, de la compagnie active et des options incluses dans le plan d’abonnement. Une donnée créée ou consultée reste rattachée à la compagnie concernée.</p></div>
        <div class="footer-note">MAXANOU · Guide officiel · Version 1.0</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <span class="section-tag">A · ENTREPRISE</span>
        <p class="eyebrow">Partie 1</p>
        <h2>Prendre en main l’espace entreprise</h2>
        <p class="lead">L’espace entreprise centralise l’activité commerciale. Le gestionnaire commence par préparer sa compagnie, son catalogue et ses équipes avant d’exécuter les ventes.</p>
        <div class="step"><div class="step-no">1</div><div class="step-copy"><strong>Vérifier la compagnie active</strong><span>Le nom de la compagnie affiché dans l’en-tête indique le contexte courant. Lorsqu’un utilisateur appartient à plusieurs compagnies, il doit sélectionner la bonne compagnie avant de consulter ou modifier une donnée.</span></div></div>
        <div class="step"><div class="step-no">2</div><div class="step-copy"><strong>Préparer les paramètres</strong><span>Compléter les coordonnées, la devise, l’identité visuelle et les préférences de communication. Ces informations servent aux documents, aux messages et à l’expérience client.</span></div></div>
        <div class="step"><div class="step-no">3</div><div class="step-copy"><strong>Organiser l’équipe</strong><span>Inviter les collaborateurs, attribuer un rôle adapté et retirer les accès devenus inutiles. Chaque personne doit utiliser son propre compte.</span></div></div>
        <div class="block dark"><h3>Rôles et permissions</h3><p>Les permissions déterminent les écrans accessibles et les actions autorisées. Un collaborateur peut consulter certaines informations sans pouvoir créer, modifier ou supprimer des données. Le gestionnaire conserve la responsabilité de vérifier les droits accordés.</p></div>
        <div class="two-col">
            <div><h3>Tableau de bord</h3><p>Le tableau de bord rassemble les indicateurs disponibles : ventes, activité récente, stock et alertes. Il sert à repérer rapidement les tendances et les points à contrôler.</p></div>
            <div><h3>Navigation</h3><p>Les menus sont regroupés par activité : catalogue, ventes, inventaires, clients, comptabilité, e-commerce, équipe, communications et abonnement.</p></div>
        </div>
        <div class="footer-note">A · Espace entreprise · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">Catalogue et stock</p>
        <h2>Construire un catalogue fiable</h2>
        <p class="lead">Un catalogue bien structuré accélère les ventes et améliore la qualité des indicateurs. Les données sont propres à la compagnie active.</p>
        <div class="two-col">
            <div><h3>Catégories et produits</h3><ul><li>Créer les catégories avant les produits.</li><li>Renseigner le nom, le prix de vente et la quantité initiale.</li><li>Le prix d’achat reste facultatif ; sans lui, le bénéfice normal n’est pas calculé.</li><li>La marge de sécurité reste facultative ; sans elle, aucune alerte de seuil ne peut être déclenchée.</li></ul></div>
            <div><h3>Fournisseurs et inventaires</h3><ul><li>Lorsque le plan l’inclut, enregistrer les fournisseurs pour les relier aux approvisionnements.</li><li>Utiliser les inventaires pour tracer les entrées, sorties et corrections.</li><li>Ajouter un motif compréhensible à chaque mouvement.</li><li>Contrôler régulièrement les quantités avant une période de forte vente.</li></ul></div>
        </div>
        <div class="block warning"><div class="callout-title">Alertes de stock</div><p>La marge de sécurité doit rester inférieure à la quantité présente. Si elle n’est pas configurée, le produit peut continuer à être vendu, mais l’utilisateur ne recevra pas l’alerte de fin de stock associée.</p></div>
        <h3 style="margin-top:9mm">Codes promo clients</h3>
        <p>Lorsque le plan inclut cette option, le gestionnaire crée un code rattaché à sa compagnie, définit un pourcentage et une date d’expiration. À la vente, MAXANOU vérifie la compagnie active, l’existence, l’état et la validité du code avant d’appliquer la remise.</p>
        <div class="block green"><div class="callout-title">Bon réflexe</div><p>Utiliser des noms de produits explicites et des codes promo faciles à communiquer, puis désactiver ou laisser expirer les campagnes terminées.</p></div>
        <div class="footer-note">A · Catalogue, stock et promotion · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">Vente et relation client</p>
        <h2>De la préparation à la facture</h2>
        <p class="lead">Le point de vente accompagne le caissier tout au long du parcours : recherche, panier, remise, paiement, stock et facture.</p>
        <div class="step"><div class="step-no">1</div><div class="step-copy"><strong>Ajouter les produits au panier</strong><span>Rechercher un produit, choisir la quantité et vérifier le prix. Associer un client lorsque l’historique ou une facture personnalisée est nécessaire.</span></div></div>
        <div class="step"><div class="step-no">2</div><div class="step-copy"><strong>Appliquer une remise ou un code promo</strong><span>Ouvrir « Remise et code promo ». Une remise ponctuelle peut être saisie selon les droits. Un code est contrôlé dans la compagnie active avant le calcul du total.</span></div></div>
        <div class="step"><div class="step-no">3</div><div class="step-copy"><strong>Confirmer la vente</strong><span>Vérifier le panier, le montant reçu et le reste éventuel. La confirmation actualise automatiquement l’historique, le stock et les informations de la compagnie.</span></div></div>
        <div class="step"><div class="step-no">4</div><div class="step-copy"><strong>Envoyer la facture</strong><span>Après la vente, les canaux SMS ou WhatsApp sont disponibles uniquement s’ils ont été activés dans la configuration de communication et si le quota nécessaire est disponible.</span></div></div>
        <div class="block"><h3>Historique des ventes</h3><p>L’historique permet de retrouver une opération, son montant et ses détails. Les exports suivent les filtres appliqués et restent soumis aux permissions de l’utilisateur.</p></div>
        <div class="footer-note">A · Point de vente, facturation et clients · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">Pilotage de l’entreprise</p>
        <h2>Communications, caisse et e-commerce</h2>
        <div class="two-col">
            <div><h3>Caisses et opérations</h3><p>La comptabilité rassemble les caisses et les opérations. Les corrections doivent rester explicites et justifiées afin de conserver une lecture fiable de l’activité.</p><h3 style="margin-top:8mm">Communications</h3><p>Le gestionnaire choisit les notifications, les destinataires et les canaux disponibles. La consommation SMS/WhatsApp est suivie séparément et peut nécessiter l’achat d’un quota.</p></div>
            <div><h3>Boutique e-commerce</h3><p>Lorsque le plan l’inclut, l’entreprise dispose d’un site dédié avec ses produits et ses clients. Le gestionnaire configure la boutique, publie le catalogue et suit les commandes.</p><h3 style="margin-top:8mm">Commandes et ventes</h3><p>Une commande e-commerce suit un cycle contrôlé avant sa conversion en vente. Le stock est vérifié afin d’éviter les incohérences et les doubles opérations.</p></div>
        </div>
        <table class="matrix"><thead><tr><th>Besoin</th><th>Point de contrôle</th></tr></thead><tbody><tr><td>Envoyer une facture</td><td>Canal autorisé, coordonnées du client et quota disponible.</td></tr><tr><td>Publier un produit</td><td>Produit actif, prix correct et stock cohérent.</td></tr><tr><td>Suivre la trésorerie</td><td>Caisse active et opérations correctement libellées.</td></tr></tbody></table>
        <div class="block green"><div class="callout-title">Pilotage responsable</div><p>Les indicateurs n’ont de valeur que si les prix d’achat, stocks, ventes et opérations sont régulièrement contrôlés. Une information absente peut réduire la précision des statistiques sans bloquer la vente.</p></div>
        <div class="footer-note">A · Pilotage, communications et e-commerce · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">Abonnement et continuité</p>
        <h2>Comprendre les plans et l’expiration</h2>
        <p class="lead">L’abonnement définit les limites et les fonctionnalités disponibles pour la compagnie : utilisateurs, entreprises, produits, messages, fournisseurs, e-commerce et codes promo.</p>
        <div class="two-col">
            <div><h3>Choisir ou renouveler</h3><ul><li>Comparer le plan et les fonctionnalités incluses.</li><li>Choisir une durée de 1 à 12 mois.</li><li>Vérifier le plan, la durée, le montant et les conditions avant paiement.</li><li>L’abonnement est activé après confirmation du paiement par notre prestataire sécurisé.</li></ul></div>
            <div><h3>Contrôle après expiration</h3><ul><li>Les données existantes restent consultables.</li><li>Lorsque le contrôle de l’abonnement est activé, les actions de gestion sont temporairement limitées.</li><li>Les ventes, ajouts, modifications et achats peuvent alors être refusés.</li><li>Le renouvellement rétablit les actions concernées selon le plan.</li></ul></div>
        </div>
        <div class="block dark"><h3>Paiements fiables</h3><p>Les paiements sont confirmés avant l’activation de l’abonnement ou d’un quota. En cas de doute, consultez le statut affiché dans votre espace et évitez de refaire un paiement immédiatement.</p></div>
        <div class="footer-note">A · Abonnement, paiements et continuité · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <span class="section-tag">B · PARTENAIRE</span>
        <p class="eyebrow">Partie 2</p>
        <h2>Développer son réseau avec le portail partenaire</h2>
        <p class="lead">Le portail partenaire permet de recommander MAXANOU, de suivre les entreprises attribuées et de consulter les commissions. Il ne donne pas accès aux données privées ou aux comptes des entreprises.</p>
        <div class="step"><div class="step-no">1</div><div class="step-copy"><strong>Activer le compte</strong><span>Vérifier l’adresse e-mail et compléter le profil. Une double vérification optionnelle peut renforcer la protection du compte.</span></div></div>
        <div class="step"><div class="step-no">2</div><div class="step-copy"><strong>Partager le code partenaire</strong><span>Copier le code depuis « Mon code partenaire » et le transmettre à une entreprise intéressée, avec une présentation exacte du service.</span></div></div>
        <div class="step"><div class="step-no">3</div><div class="step-copy"><strong>Suivre l’attribution</strong><span>Une entreprise apparaît après l’association correcte du code et la confirmation de l’opération éligible. Un simple clic ou une promesse ne suffit pas.</span></div></div>
        <div class="block warning"><div class="callout-title">Règle de confiance</div><p>Le partenaire ne demande jamais le mot de passe, un code de connexion, un code de double authentification ou les informations de paiement d’une entreprise.</p></div>
        <div class="footer-note">B · Portail partenaire · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">Code et entreprises attribuées</p>
        <h2>Recommander avec transparence</h2>
        <div class="two-col">
            <div><h3>Code partenaire</h3><p>Le code est consultable et partageable depuis le portail. Il doit être utilisé pour l’entreprise concernée, sans modification ni auto-recommandation.</p><h3 style="margin-top:8mm">Réduction de bienvenue</h3><p>Lorsqu’elle est prévue par le programme, la réduction est calculée lors du premier abonnement éligible après vérification du code et confirmation du paiement.</p></div>
            <div><h3>Entreprises attribuées</h3><p>La liste présente les entreprises correctement rattachées au partenaire. Elle ne révèle pas les mots de passe, les moyens de paiement, les contacts inutiles ou le contenu privé des entreprises.</p><h3 style="margin-top:8mm">Patience après paiement</h3><p>Une attribution peut rester en attente pendant la confirmation du paiement ou le traitement du système. Consulter le statut avant de contacter le support.</p></div>
        </div>
        <table class="matrix"><thead><tr><th>Situation</th><th>Réponse attendue</th></tr></thead><tbody><tr><td>Code refusé</td><td>Vérifier la saisie et les conditions affichées, sans créer de compte à la place de l’entreprise.</td></tr><tr><td>Entreprise absente</td><td>Attendre la confirmation de l’opération, puis utiliser le support officiel si le statut reste inchangé.</td></tr><tr><td>Demande d’informations sensibles</td><td>Refuser le partage et signaler la situation via le canal officiel.</td></tr></tbody></table>
        <div class="block green"><div class="callout-title">Bonne recommandation</div><p>Présenter les avantages avec exactitude, expliquer que les règles affichées dans MAXANOU font foi et laisser l’entreprise créer elle-même son compte et confirmer son paiement.</p></div>
        <div class="footer-note">B · Code partenaire et confidentialité · MAXANOU</div>
    </section>

    <section class="page">
        <div class="topline"></div>
        <p class="eyebrow">Commissions et retraits</p>
        <h2>Lire ses revenus et demander un paiement</h2>
        <div class="block"><h3>Comment naît une commission ?</h3><p>Une commission est créée après confirmation d’une opération éligible liée à une entreprise attribuée. Selon les règles du programme, elle peut concerner une acquisition, un renouvellement ou une amélioration de plan.</p></div>
        <div class="two-col">
            <div><h3>Statuts à comprendre</h3><ul><li><strong>En attente :</strong> l’opération attend une confirmation ou un traitement.</li><li><strong>Disponible :</strong> le montant peut être demandé selon les conditions.</li><li><strong>Annulée ou inversée :</strong> l’opération de référence a été annulée, remboursée ou invalidée.</li></ul></div>
            <div><h3>Montant et historique</h3><ul><li>Le taux applicable est conservé au moment de la création de la commission.</li><li>Consulter le détail pour retrouver l’entreprise et l’opération concernée.</li><li>Les exports servent au suivi et ne remplacent pas le statut affiché dans le portail.</li></ul></div>
        </div>
        <h3 style="margin-top:8mm">Retrait Mobile Money</h3>
        <div class="step"><div class="step-no">1</div><div class="step-copy"><strong>Enregistrer et vérifier le compte</strong><span>Lorsque l’option est disponible, renseigner le numéro Mobile Money puis confirmer le code reçu par e-mail.</span></div></div>
        <div class="step"><div class="step-no">2</div><div class="step-copy"><strong>Demander le montant disponible</strong><span>Choisir un montant dans la limite du solde disponible et confirmer la demande lorsque toutes les informations sont exactes.</span></div></div>
        <div class="step"><div class="step-no">3</div><div class="step-copy"><strong>Suivre le traitement</strong><span>Le retrait peut être en attente, en cours, effectué ou nécessiter une action. Ne jamais modifier manuellement un statut ni transmettre ses identifiants.</span></div></div>
        <div class="footer-note">B · Commissions, portefeuille et retraits · MAXANOU</div>
    </section>

    <section class="page security-page">
        <div class="topline"></div>
        <span class="section-tag">C · CONFIANCE</span>
        <p class="eyebrow">Sécurité et support</p>
        <h2>Une confiance construite à chaque étape</h2>
        <p class="lead">MAXANOU aide chaque entreprise et chaque partenaire à travailler dans un cadre clair : les informations restent séparées, les accès sont maîtrisés et les opérations sensibles sont confirmées avant d’être prises en compte.</p>
        <div class="summary-grid">
            <div class="summary-card"><div class="number">01</div><strong>Données séparées</strong><span>Chaque entreprise consulte uniquement les informations liées à sa compagnie active.</span></div>
            <div class="summary-card"><div class="number">02</div><strong>Accès maîtrisés</strong><span>Chaque collaborateur dispose d’un compte et d’un rôle adaptés à ses responsabilités.</span></div>
            <div class="summary-card"><div class="number">03</div><strong>Opérations contrôlées</strong><span>Les paiements, activations et retraits sont pris en compte après vérification.</span></div>
        </div>
        <div class="two-col">
            <div><h3>Pour les entreprises</h3><ul><li>Un compte par collaborateur, avec le rôle adapté.</li><li>Les accès peuvent être ajustés lorsque l’équipe évolue.</li><li>Les changements de compte renforcent la protection des sessions.</li><li>En cas d’abonnement arrivé à échéance, les informations restent consultables selon les règles de la plateforme, tandis que les nouvelles opérations peuvent être suspendues.</li></ul></div>
            <div><h3>Pour les partenaires</h3><ul><li>L’espace partenaire reste séparé des données confidentielles des entreprises.</li><li>Les codes, attributions et commissions sont rattachés aux bonnes entreprises.</li><li>Un retrait nécessite une vérification des informations du compte.</li><li>Une protection supplémentaire en deux étapes peut être activée pour le compte partenaire.</li></ul></div>
        </div>
        <div class="block green"><h3>Les bons réflexes</h3><p>Choisir un mot de passe unique, ne jamais partager son code reçu par e-mail et utiliser uniquement les liens officiels de MAXANOU. Une demande inhabituelle ou un message suspect doit être signalé avant toute action.</p></div>
        <div class="block dark"><h3>En cas de difficulté</h3><p>Noter l’écran concerné, l’heure, le rôle utilisé et le message affiché. Ne pas multiplier les paiements ou les tentatives si un statut est encore en cours. Contacter le support avec les informations strictement nécessaires, sans mot de passe ni code de sécurité.</p></div>
        <div class="footer-note">C · Sécurité, support et conclusion · MAXANOU</div>
    </section>
</body>
</html>
