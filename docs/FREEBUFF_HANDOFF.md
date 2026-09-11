# Reprise du chantier SaaS multi-entreprises

Dernière mise à jour : 11 septembre 2026 — trésorerie sécurisée, cohérence PWA MAXANOU et correctifs UI consolidés.

## Mise à jour du 7 septembre 2026 — migration UI SaaS transversale

La migration contrôlée des interfaces a démarré. Le périmètre et les lots sont documentés dans `docs/PLAN_MIGRATION_INTERFACE_SAAS.md`. Le premier lot aligne les vues Laravel d’authentification historiques sur le shell public SaaS et les composants `x-ui`, sans modifier leurs routes ni leurs champs. Les prochains lots doivent être livrés écran par écran, avec validation fonctionnelle et visuelle ; ne pas appliquer un remplacement global aux écrans POS, DataTables, e-commerce public, PDF ou e-mails.

Le propriétaire confirme également la fin de la recette staging : contrôle visuel desktop/mobile des écrans secondaires et AJAX, abonnement KPrimePay réel avec webhook, SMTP réel, cron/queues, sauvegardes, logs et alertes. La production ne demande plus de développement fonctionnel, uniquement le déploiement sécurisé et la vérification des secrets et URL propres à cet environnement.

Le 7 septembre, une recette de charge locale a également été exécutée exclusivement sur `pos_testing` : volume intermédiaire (12 000 ventes et 24 000 lignes) sous 300 ms sur tous les parcours mesurés, deux scénarios de concurrence stock/commande sans doublon ni survente, et 1 000 notifications traitées par quatre workers sans doublon ni échec. Les métriques détaillées sont dans `docs/RAPPORT_GLOBAL_SAAS.md`. Le volume maximal reste rejouable depuis un terminal persistant.

## État de référence au 11 septembre 2026

Cette section prévaut sur les anciennes entrées historiques de ce handoff. Le développement fonctionnel du plan d’abonnement est terminé pour le périmètre prévu : catalogue versionné, essai de 14 jours, choix de 1 à 12 mois avec remise uniquement à 12 mois, montée de plan sans descente, règlement KPrimePay séparé des quotas, webhooks idempotents, expiration et rappels par e-mail, contrôle des fonctionnalités et limites compagnie/utilisateur/produit, SweetAlert avec proposition d’amélioration pour les propriétaires et administrateurs, et notification des paiements confirmés aux administrateurs plateforme.

Les tests ciblés abonnement, quotas, webhooks, expiration, pré-contrôle et catalogue passent. Le checkout réel KPrimePay, les webhooks, le SMTP réel de staging, la recette visuelle mobile/desktop, le cron/queue, les sauvegardes, les logs et les alertes ont été validés par le propriétaire. La suite de développement et la validation staging sont terminées à **100 %** ; restent uniquement le déploiement de production, ses secrets/URL et l’activation progressive de `subscriptions.enforcement_enabled`.

La fixture locale du compte `didierlombardo48@gmail.com` est mutable et a servi à plusieurs recettes manuelles (Basic, Bronze, Argent, Gold puis Essai). Elle ne doit donc pas être considérée comme une vérité permanente dans ce document : vérifier l’état courant directement dans la base locale avant chaque test et ne jamais reproduire cette fixture en production.

Les évolutions ultérieures couvrent également les retraits partenaires et administrateur, la présentation responsive des tables et formulaires, et la PWA MAXANOU. Les paiements sortants conservent leurs contrôles serveur, leurs journaux et leur vérification asynchrone ; ils ne doivent pas être assimilés à un solde disponible chez le prestataire.

## Mise à jour du 7 septembre 2026 — enforcement individuel par entreprise

- La migration `2026_09_07_100000_add_subscription_enforcement_override_to_companies.php` ajoute `company_settings.subscription_enforcement_enabled` : `NULL` hérite du réglage plateforme, `1` active le contrôle pour l’entreprise, `0` le désactive pour cette entreprise.
- L’administration peut gérer ces exceptions depuis **Paramètres généraux > Contrôle d’abonnement individuel**. Chaque modification exige un motif et le mot de passe plateforme, puis est inscrite dans `platform_audit_logs` avec l’entreprise ciblée.
- La DataTable de cette section respecte le template SaaS : les informations restent séparées du bouton d’action, et le formulaire détaillé s’ouvre dans une modal dédiée responsive par entreprise.
- Correctif de recette navigateur : les modales n’étaient pas masquées par défaut lorsque le CSS Bootstrap n’était pas chargé ; le wrapper `platform-company-enforcement-dialog` est maintenant caché jusqu’à l’ouverture, puis positionné en overlay avec fond assombri et défilement interne.
- Le modal « Exception d’abonnement » a été repris visuellement : en-tête avec identité de l’entreprise et fermeture accessible à droite, carte du réglage courant, champs iconifiés, confirmation sécurisée et pied d’actions clair. Sur mobile, seul le corps défile et les actions restent accessibles.
- `EntitlementService` applique désormais la résolution effective par entreprise à l’accès en lecture seule, aux fonctionnalités et aux limites de ressources. Le réglage global reste le défaut pour les entreprises en mode « Hériter ».
- Le réglage individuel ne supprime ni l’abonnement, ni les paiements, ni les données : il détermine uniquement si les garde-fous d’abonnement sont appliqués à cette entreprise.

## Mise à jour du 7 septembre 2026 — kit et garde-fou UI SaaS

- `public/hub/assets/css/saas-toolkit.css` et `public/hub/assets/js/saas-toolkit.js` centralisent les primitives supplémentaires : en-tête, actions de DataTable, badges, barre d’outils, pagination, notices, switch, onglets, détails, progression et toasts accessibles.
- Les composants Blade `x-ui` couvrent désormais ces outils, y compris le lien/bouton avec texte de chargement, le footer de modal et les outils de table. Ils sont chargés dans les layouts standards via `partials/design-system-head` ; `layouts.saas` charge également `saas-pages.css` globalement.
- `php artisan ui:lint` vérifie le catalogue, les fondations de layout, les composants référencés et les styles interdits dans les primitives. Utiliser `php artisan ui:lint --changed` avant chaque lot de vues.
- Le guide opérationnel est `docs/GUIDE_OUTILS_TEMPLATE_SAAS.md`. Les PDF, e-mails et storefront public gardent un contrat de rendu distinct et ne doivent pas être transformés sans recette dédiée.

## Mise à jour du 3 septembre 2026 — refonte complète de l'administration plateforme

- Toutes les vues de la console administration (`resources/views/platform/`) ont été harmonisées avec le design system SaaS.
- **Tables DataTable-style** : `platform.css` et `platform-components.css` v=20260903-10 harmonisent le rendu des tables avec le pattern DataTables du SaaS : en-tête uppercase + letter-spacing, border-radius coins, padding adapté, hover accent, pagination style DataTables, border-collapse séparé.
- **Boutons d'action en tableau** : nouvelle classe `.platform-action-btn` (34×34px, rond, transparent) remplace `btn btn-sm btn-outline-info/danger/success` pour les icônes d'action dans les colonnes de tableau — même pattern que `.saas-action-btn` du SaaS.
- **Vides et chargements** : `.platform-empty-state` stylé avec icône et texte muted pour les tableaux sans données.
- **Switches SaaS** : toutes les cases à cocher Bootstrap (`form-check form-switch`) remplacées par le composant `.saas-switch-line` + `.saas-switch-control` du design system SaaS. Les styles sont inclus dans `platform.css` (pas besoin de charger `saas-pages.css`). Appliqué dans : settings/general (services, abonnements, maintenance), subscriptions/catalog (features), alerts/index (activation, destinataires).
- Les vues utilisant encore `btn btn-sm` pour des boutons avec texte (Relancer, Reinitialiser, 2FA) restent en Bootstrap car elles ont un label textuel.
- Badges Bootstrap (`bg-success`, `bg-danger`, `bg-warning`, `bg-info`, `bg-secondary`) remplacés par `platform-status-chip` avec variantes `is-success`, `is-danger`, `is-warning`, `is-info`, `is-muted`.
- Les pages **companies/index**, **companies/show**, **users/show**, **audit/index**, **alerts/index**, **health/index** utilisent `platform-status-chip`, `platform-user-avatar`, `platform-panel-head`, `platform-eyebrow`.
- La page **users/index** utilise `platform-status-chip` pour statuts, adhésions et entreprises.
- La page **payments/index** utilise `platform-status-chip` pour les statuts de transaction.
- La page **payments/show** utilise `platform-panel-head`, `platform-eyebrow`, `platform-summary-metric` pour les quotas SMS/WhatsApp.
- Les pages **admins/index** et **admins/edit** utilisent `platform-panel-head` et `platform-status-chip` pour rôles, statuts et 2FA.
- La page **settings/general** utilise `platform-panel-head` et `platform-status-chip` pour services externes et switches.
- La page **settings/edit** (tarifs) utilise `platform-panel-head` et `platform-eyebrow` pour le formulaire et l'historique.
- La page **subscriptions/catalog** utilise `platform-status-chip` pour les versions, features et états.
- La page **subscriptions/preflight** utilise `platform-summary-metric` pour les 5 indicateurs de contrôle.
- La page **communications/index** utilise `platform-summary-metric` pour les totaux par canal, `platform-filter-grid` pour les filtres, `platform-status-chip` pour les statuts de livraison.
- Le **dashboard** protège l'accès aux sections Paiements et Santé par `hasPlatformPermission()`.
- Les styles `platform.css` v=20260903-8 incluent `platform-status-chip`, `platform-user-avatar`, `platform-summary-metric` et responsive mobile.
- **244 tests, 1501 assertions — 0 échec.**
- Ne pas réintroduire de badges Bootstrap (`bg-success`, `bg-danger`, etc.) dans les vues plateforme. Utiliser systématiquement `platform-status-chip`.
- Ne pas exposer de sections aux rôles qui n'ont pas la permission correspondante (`hasPlatformPermission()`).

## Mise à jour du 2 septembre 2026 — cadrage du moteur d'abonnements KPrimePay

- Le chantier abonnement est désormais documenté par le présent handoff, le PDF tarifaire, `GUIDE_KPRIMEPAY.md` et les rapports permanents ; l’ancien prompt de cadrage a été retiré après finalisation des phases développées.
- Le prompt donne priorite a la consigne du proprietaire : renouvellement ou montee en gamme uniquement ; toute descente de gamme est interdite dans l'interface, la validation serveur et le reglement, y compris apres expiration et face a une requete falsifiee.
- Architecture recommandee : compte de facturation couvrant plusieurs compagnies, catalogue versionne, snapshots financiers, abonnements et paiements separes de `quota_payments`, `EntitlementService`, essai unique, lecture seule a expiration et controles concurrents des limites.
- L'integration existante des quotas doit rester intacte. Le futur paiement d'abonnement reutilisera le client/protocole KPrimePay, mais disposera de son propre modele et d'un settlement atomique. Le retour navigateur ne constituera jamais une preuve de paiement.
- Le prompt impose `subscriptions.enforcement_enabled`, desactive par defaut : ce reglage permet le travail local sans restrictions de plan mais ne contourne jamais authentification, permissions, isolation tenant, statut de compagnie, CSRF, rate limits ou verification KPrimePay.
- Le prompt impose une mise a jour de ce fichier apres chaque phase et du rapport administration apres chaque progression plateforme, avec tests immediats avant de poursuivre.
- Aucun code metier, schema, paiement, quota, abonnement ou donnee n'a ete modifie pendant ce cadrage. Les changements deja presents dans le depot ont ete preserves.
- Validation de ce lot documentaire : lecture et inspection du code/documents, controle visuel des 15 pages du PDF et `git diff --check` passe sans erreur. L'implementation et tous ses tests restent a faire selon les huit phases du prompt.

## Mise à jour du 2 septembre 2026 — abonnements, lot 1 (socle et protection initiale)

- Ajout de la migration `2026_09_02_200000_create_subscription_billing_tables.php` : comptes de facturation, rattachement de compagnies, catalogue tarifaire versionné, fonctionnalités de plan, abonnements, paiements distincts des quotas et journal d'événements. Les prix/limites du PDF sont insérés : Essai, Basic, Bronze, Argent et Gold.
- Ajout des modèles et services `SubscriptionAccountService`, `EntitlementService`, `SubscriptionCheckoutService` et `SubscriptionSettlementService`. Un essai de 14 jours et les 3 SMS/3 WhatsApp sont créés une seule fois pour un nouveau compte de facturation. Les paiements d'abonnement utilisent leurs propres identifiants et snapshots ; `quota_payments` reste séparé.
- Les routes et l'écran `Abonnement` existent ; l'accès est contrôlé côté serveur par le rôle système owner/admin et la permission `subscription.manage`. Le downgrade est refusé par le checkout serveur.
- `KprimePayService` dispose d'un checkout abonnement réutilisant les mêmes timeouts, bearer token et `Idempotency-Key`; le webhook dispatche les transactions d'abonnement vers un règlement SQL verrouillé et idempotent, sans perturber les quotas.
- Le réglage plateforme persistant `subscriptions.enforcement_enabled` a été ajouté, valeur par défaut OFF. Lorsque ON, les groupes métier principaux bloquent les écritures après expiration; l'E-commerce exige aussi la fonctionnalité du plan et les Fournisseurs sont protégés par `plan.feature:suppliers`.
- Tests exécutés : `SubscriptionFoundationTest` (3 tests, 15 assertions), `QuotaPaymentTest` (5 tests, 46 assertions), `php artisan route:list --name=subscriptions`, `php artisan migrate --pretend`, `php artisan view:cache` et `git diff --check` : tous passants.
- À l’époque du lot 1, ces validations restaient à faire : tests complets du checkout d'abonnement V1/V2, rappels/expiration planifiés, contrôle concurrent des limites compagnie/utilisateur/produit, storefront public, préflight administratif, audit de toutes les routes mutantes et recette navigateur. Les lots ultérieurs les ont couverts ; la production reste néanmoins soumise à la configuration contrôlée décrite dans l’état de référence.

### Complément lot 1 — cycle planifié

- Commande `subscriptions:expire` ajoutée et planifiée quotidiennement à 00:05 avec `withoutOverlapping`. Elle journalise de manière idempotente les rappels J-3/J-2/J-1 et passe les abonnements à `expired` à échéance.
- Les événements sont volontairement journalisés seulement à ce stade : aucun SMS, WhatsApp ou e-mail de rappel réel n'a été envoyé. Brancher les jobs de communication existants uniquement après tests dédiés de destinataires et d'idempotence.
- Vérifications : `php artisan list subscriptions`, `SubscriptionFoundationTest`, `php artisan view:cache` et `git diff --check` passent.
- Suite complète tentée le 2 septembre : arrêt sur un échec préexistant hors abonnement dans `AuthNavigationTest::test_pwa_starts_on_the_authentication_route`. Le test attend `pro-seller-pwa-v5` alors que le fichier déjà modifié `public/sw.js` contient `pro-seller-pwa-v6`. Ne pas modifier ce fichier dans le chantier abonnement sans coordination avec son auteur ; les tests abonnement et quotas restent verts.

## Mise à jour du 2 septembre 2026 — abonnements, lot 2 (règlement et storefront)

- Le test PWA a été réaligné à la demande du propriétaire avec les artefacts réellement livrés : manifeste `/user_login` et cache `pro-seller-pwa-v7`. `AuthNavigationTest` passe maintenant (2 tests, 49 assertions).
- L'E-commerce public consulte désormais `EntitlementService` : lorsque l'enforcement est activé et que le plan ne possède pas la fonctionnalité E-commerce, la boutique publique est fermée et aucun POST de commande n'est accepté.
- La création/restauration de produit vérifie la capacité produits active quand l'enforcement est activé. La réponse est explicite et aucune donnée n'est supprimée.
- Ajout de `SubscriptionPaymentTest` : downgrade refusé avant checkout ; règlement annuel Bronze vérifié et rejoué une seconde fois, avec un seul paiement `paid` et un seul crédit annuel (+240 SMS/+240 WhatsApp après les crédits essai). Résultat : 2 tests, 6 assertions, 0 échec.
- Tests confirmés : `SubscriptionFoundationTest` (3/15), `SubscriptionPaymentTest` (2/6), `QuotaPaymentTest` (5/46), `AuthNavigationTest` (2/49), `view:cache` et `git diff --check`.
- Reste : contrôle exhaustif des limites utilisateurs/compagnies, contrôle des routes mutantes hors groupes déjà protégés, administration catalogue/préflight, tests HTTP V1/V2 de paiement abonnement, notifications de rappel et recette navigateur. L'enforcement reste OFF par défaut.

## Mise à jour du 2 septembre 2026 — correction UI Abonnement

- La vue `resources/views/subscription/index.blade.php` a été refondue avec `layouts.saas`, `saas-page-header`, `saas-metric`, `saas-card`, badges d’état et grille de plans responsive. Elle n’injecte plus de contrôles d’apparence dans son contenu.
- Cause du contenu « Mode d’affichage / Couleur dominante » visible sur la page : le composant d’apparence du topbar était rendu sans règle globale de masquage initial après retrait de la feuille historique. `design-system.css` contient maintenant `.saas-modal { display:none; }` et `.saas-modal.show { display:block; }`, et son cache est passé en `20260902-7`.
- Le composant d’apparence reste disponible uniquement via le bouton Apparence du topbar, conformément au nouveau template. Aucun réglage d’apparence n’est présent dans la section Abonnement.
- Validation : `php artisan view:cache`, `SubscriptionFoundationTest`, `SubscriptionPaymentTest` et `git diff --check` passent. Recette navigateur visuelle de la page Abonnement reste à faire.

Ce fichier est le point de reprise commun pour Codex, Freebuff et tout autre intervenant. Le lire intégralement avant toute modification. Ne pas refaire les fonctions indiquées comme terminées et ne pas faire travailler deux assistants simultanément sur les mêmes fichiers.

## Reprise frontend — état exact au 1er septembre 2026

### Direction validée

- Le propriétaire a validé une refonte progressive vers un design SaaS propriétaire : **glassmorphisme léger, blur, soft glow, lisibilité élevée, animations courtes et accessibles**. La référence active est `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md`.
- Objectif : remplacer progressivement le visuel de l’ancien template sans réécrire ni fragiliser les flux métier Laravel, les permissions, l’isolation multi-entreprises, les ventes, les notifications ou les exports.
- Les préférences utilisateur sont personnelles : `appearance_mode` (`system`, `dark`, `light`) et `accent_color` hexadécimal. Ne jamais les transformer en préférence globale de compagnie.
- Le navigateur intégré Codex est opérationnel sur `http://127.0.0.1:1111/`. Les contrôles visuels authentifiés ont été réalisés à 1440 px, 689 px et 390 px. Ne pas considérer une capture desktop seule comme validation responsive.

### Socle frontend déjà livré et validé

1. **Nouveau shell SaaS**
   - Fichiers : `resources/views/layouts/saas.blade.php`, `resources/views/partials/saas-sidebar.blade.php`, `resources/views/partials/saas-topbar.blade.php`, `public/hub/assets/css/saas-shell.css`, `public/hub/assets/js/saas-shell.js`, `public/hub/assets/js/design-system.js`, `public/hub/assets/css/design-system.css`.
   - Dashboard et Profil ont migré sur ce shell. Le menu respecte les permissions existantes ; ne pas réintroduire d’élément de navigation non autorisé.
   - Les assets CSS utilisent un suffixe de version `?v=...`. **Incrémenter ce suffixe après toute modification CSS** : la PWA/le navigateur peuvent conserver un ancien fichier en cache.

2. **Profil entièrement refondu et testé**
   - Fichier : `resources/views/user/profile.blade.php`.
   - Trois onglets : adresse e-mail, mot de passe, apparence. Les changements d’e-mail/mot de passe restent sécurisés côté serveur dans `UserController` et les tests `ProfileSecurityTest` ont passé (7 tests, 35 assertions lors du dernier contrôle).
   - Apparence : accordéons exclusifs « Mode d’affichage » et « Couleur dominante », aperçu instantané, palette prédéfinie et couleur hexadécimale libre.
   - Correctif important déjà appliqué : `.visually-hidden` est défini dans `public/hub/assets/css/saas-shell.css`. Sans cette règle, les radios et légendes du mode d’affichage occupent des colonnes visibles et compressent les textes à droite. **Ne pas retirer cette utilitaire.**
   - À 390 px, les trois onglets affichent leurs libellés complets ; les icônes d’onglet sont volontairement masquées sous 480 px pour conserver « Adresse e-mail », « Mot de passe » et « Apparence » lisibles. Les cartes de mode passent sur une colonne, sans overflow horizontal.

3. **Point de vente : socle de refonte livré, finition métier/visuelle à poursuivre**
   - Route : `/pos/sale`; vue : `resources/views/pos/sale/index.blade.php`. Elle utilise maintenant `layouts.saas` avec `@section('body-class', 'pos-saas-body')`; le layout historique `resources/views/layouts/layout_sale.blade.php` ne pilote plus cette route.
   - Surcouche : `public/hub/assets/css/saas-pos.css` (version `20260901-16` dans la vue). Elle ne dépend plus de la grille de l’ancien template : grille autonome desktop (menu 152 px, catalogue fluide, panier 360 px), navigation catégories horizontale mobile, et panneau panier mobile coulissant.
   - Le gestionnaire `data-toggle-class` du template historique est maintenant rétabli localement dans la vue POS : le bouton panier ouvre et ferme réellement le panneau mobile, avec libellé accessible. Ne pas retirer ce gestionnaire sans remplacer son comportement.
   - Panier : les lignes générées par le JavaScript existant (`.pos-order`, `.pos-order-product`, `.quantity-input`, `.btn-plus`, `.btn-minus`, `.remove-item`) ont désormais un habillage autonome, compact et responsive. Les classes et la logique de quantité restent inchangées.
   - Catalogue : un en-tête métier « Vente rapide » est présent au-dessus de la recherche. La recherche occupe toute la largeur utile et `#catalogProducts` utilise sa propre grille CSS (5 cartes à 1440 px dans le panneau, 2 à 390 px) : ne pas réintroduire la dépendance aux classes de grille du thème historique.
   - Les scripts POS sont maintenant poussés via `@push('scripts')`, donc exécutés après `vendor.min.js` du shell. C’est indispensable : auparavant ils se lançaient avant jQuery/Bootstrap et pouvaient empêcher le catalogue, les modales et le panier de s’initialiser. Ne pas remettre les scripts inline directement dans `@section('content')`.
   - Les modales reçu, détail de vente et commandes en attente partagent `pos-modal-content`. La liste des commandes en attente est rendue en cartes (`.pending-order-card`) tout en conservant `.load-order`, `.delete-order` et `data-id`. La livraison de facture conserve `#invoiceDeliveryPanel`, `#invoiceCountry`, `#invoicePhone`, `#invoiceWhatsapp`, `#invoiceSms` et `#sendInvoice` ; son habillage Select2 n’utilise plus de couleurs blanches codées en dur.
   - Les états de recherche, catalogue vide et chargement progressif sont aussi habillés (`#search_loader`, `#catalogEmpty`, `#catalogLoadMore`). Ils ne changent pas les appels AJAX du catalogue.
   - Panier vide : `#emptyCartState` est affiché/masqué par `updateTotal()` sans changer le panier persistant. La tête de panier utilise désormais une icône Bootstrap et le libellé « Panier actuel », sans l’ancien `marquee`. Vérifié à 1440 px et 390 px sans débordement.
   - Hiérarchie finalisée : repère « Catégories » à gauche, total avec sous-information de remise, remise explicitement libellée et groupe « Actions de la commande ». Ces ajouts n’affectent aucun sélecteur JavaScript existant. Contrôle visuel à 1440 px et 390 px sans débordement ni erreur console.
   - Onglet « Produits vendus » : son état vide utilise `pos-sales-empty` et explique que le classement arrive après la première vente. Le badge de quantité de l’onglet commande est également traité comme un compteur visuel. Lorsqu’il y a des ventes, `pos-top-sales` modernise le classement sans modifier les données ni le calcul. Le contenu métier du classement n’a pas été changé.
   - Cartes produit : `.pos-product` est désormais un vrai bouton accessible. Les anciens attributs `data-bs-toggle="modal"` et `data-bs-target="#modalPosItem"` ont été retirés car cette modale n’existe plus et provoquait une erreur Bootstrap `backdrop`. Le clic conserve l’ajout direct au panier et son animation. Les anciens gestionnaires visuels dupliqués ont été supprimés.
   - Vue « Activité des ventes » : l’ancien `display:none !important` qui empêchait les statistiques de réapparaître a été retiré. L’onglet adapte maintenant l’en-tête central, masque proprement le catalogue et affiche les indicateurs ainsi que DataTables avec le design system. Le callback DataTables ne force plus de fonds noirs/textes blancs. Le bouton de détail `.view` attend la réponse serveur avec `ServerButtonLoader` avant d’ouvrir la modale et restaure correctement son état en cas d’erreur.
   - Contrôles navigateur non destructifs du 1er septembre : catégorie « Tous » puis catégorie individuelle et retour à « Tous » validés ; le catalogue passe de 2 à 1 puis 2 produits sans débordement. Les cartes catalogue mesurent 160 px dans la grille desktop et n’ont pas de débordement interne.
   - Validation navigateur effectuée : à 1440 px la grille POS est bien en trois colonnes et le panier est statique ; à 390 px le panier est hors écran puis s’ouvre sur clic. Aucune erreur console au chargement desktop. Les catégories peuvent défiler horizontalement sans casser leurs libellés.
   - **Ce qui reste** : finaliser la composition visuelle des lignes de panier, commandes en attente, modales de vente/reçu/impression, et enlever progressivement les restes décoratifs du template historique. Les flux métier POS restent à préserver strictement.

### Garde-fous frontend obligatoires

1. Ne pas changer les IDs/classes utilisés par le JavaScript de vente sans rechercher toutes leurs occurrences dans `resources/views/pos/sale/index.blade.php`. Parmi les éléments sensibles : `#product_list`, `#catalogProducts`, `.pos-product`, `#clientSelect`, `#newOrderTab`, `.pos-order-product`, `#orderCount`, `#confirmSale`, `#savePendingOrder`, `#showPendingOrders`, `#remiseInput`, `#pdfModal` et les clés `localStorage` de panier.
2. Le panier est persistant par utilisateur **et** compagnie (`pos_cart_v1_{userId}_{companyId}`). Toute refonte doit conserver cette séparation tenant et la mise à jour après chaque modification de quantité/client/remise.
3. Ne pas modifier la logique de création d’une vente pour une tâche uniquement UI. Elle dépend de caisses, taxe, stock, transactions, notifications et `company_id`.
4. Toute action serveur (formulaire, Fetch, Ajax, SweetAlert) doit utiliser `window.ServerButtonLoader`, bloquer le double clic et restaurer l’action sur erreur. Lire `AGENTS.md` avant toute nouvelle action interactive.
5. Ne jamais ajouter de `company_id` venant du navigateur à un flux métier. Le contexte actif reste la seule source d’autorité (`CompanyContext`).
6. Préserver `prefers-reduced-motion`: chaque animation nouvelle doit avoir un repli sans animation. Éviter les animations permanentes et les gros effets blur sur des listes longues.
7. Ne pas charger de scripts de dashboard ou de graphiques dans les layouts de pages qui ne les utilisent pas. Vérifier la console après une navigation fraîche; les logs historiques du navigateur peuvent rester présents, vérifier aussi la liste réelle des balises `<script>`.
8. Après une modification CSS PWA, incrémenter le query string du layout concerné et tester après rechargement complet. Ne pas modifier le service worker ou ses caches pour un simple changement de style sans nécessité.
9. Avant de remplacer `layouts.layout_sale`, faire un test manuel complet : ajout produit, animation panier, changement de quantité, client, remise, sauvegarde commande, commandes en attente, confirmation vente, reçu/impression, envoi facture, actualisation des quantités et changement de compagnie.
10. Aucun reset Git destructif, aucun `migrate:fresh` hors base de test, aucune modification de `.env` de production ou de clés API sans demande explicite du propriétaire.

### Contrat strict pour tout agent ou IA intervenant sur le nouveau frontend

Ce contrat est obligatoire, y compris lorsqu’un autre assistant reprend le chantier sans l’auteur des modifications précédentes.

Avant de modifier :

1. lire intégralement `docs/FREEBUFF_HANDOFF.md`, `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` et `AGENTS.md` ;
2. exécuter `git status --short` et examiner le diff des fichiers visés ; le dépôt est sale et les changements existants appartiennent à leurs auteurs ;
3. annoncer le module et les fichiers ciblés ; ne jamais faire travailler deux agents simultanément sur les mêmes fichiers ;
4. ouvrir l’écran réel dans le navigateur avant le changement et relever au moins l’état vide, l’état rempli, une erreur et la largeur concernée ;
5. rechercher toutes les occurrences d’un ID, d’une classe ou d’une fonction métier avant de modifier son HTML.

Pendant la modification :

1. migrer un écran ou un composant à la fois ; ne pas mélanger refonte visuelle, logique métier, sécurité et optimisation SQL dans un même lot ;
2. conserver routes, permissions, `CompanyContext`, IDs, classes JavaScript, clés `localStorage`, événements et formats de réponses ;
3. utiliser les tokens `--ds-*`, les composants partagés et les contrats DataTables/modales du cahier des charges ;
4. ne jamais créer de couleurs clair/sombre dans un callback JavaScript, de styles complets inline ou de nouvelle convention locale concurrente ;
5. utiliser `window.ServerButtonLoader` pour chaque attente serveur et restaurer l’action après erreur ;
6. respecter clavier, focus, contraste, zones tactiles, zoom 200 %, `prefers-reduced-motion` et zones sûres mobiles ;
7. incrémenter le suffixe `?v=` de tout asset CSS/JS modifié et ne pas toucher au service worker pour contourner un simple cache ;
8. ne jamais effectuer une vente réelle, supprimer une donnée, envoyer une facture ou déclencher une communication uniquement pour une recette visuelle sans accord explicite.

Avant de remettre le chantier :

1. tester les largeurs 1440, 1024, 768 et 390 px lorsque le composant est responsive ; au minimum 1440 et 390 px pour le POS ;
2. contrôler état vide, données réelles, contenu long, thème clair/sombre, navigation clavier, permissions réduites et absence de débordement ;
3. vérifier la console après rechargement frais, puis exécuter `php artisan view:cache` et `git diff --check` ;
4. exécuter les tests métier ciblés si du code autre que purement visuel a changé ;
5. mettre à jour ce handoff avec date, fichiers, version d’asset, comportement livré, tests réalisés, risques et travail restant ;
6. ne jamais écrire « terminé » si une largeur, un état ou un flux exigé n’a pas été contrôlé ; noter précisément « non testé » et pourquoi.

Au retour de l’agent précédent, la reprise doit être possible sans interprétation : le handoff doit distinguer **déjà livré**, **validé**, **non testé**, **reste à faire** et **interdictions**. Une capture seule n’est jamais une preuve de non-régression métier.

### Contrat commun des modales et DataTables

- Les règles normatives sont dans les sections `4.3.1` et `4.4.1` du cahier des charges UI/UX.
- Les DataTables doivent partager une seule anatomie, les mêmes tokens, champs, pagination, états et comportements responsive. Il est interdit de les recolorer dans `drawCallback` ou de dupliquer une feuille complète par écran.
- Les modales partagent backdrop, en-tête, corps défilable, pied fixe, focus, loaders et comportement mobile. Une seule modale interactive est autorisée ; aucune imbrication.
- `pos-modal-content` reste la convention transitoire du POS ; `x-ui.modal` est la cible commune. La migration doit être progressive et préserver les IDs métier.
- Toute dérogation doit être justifiée dans ce fichier, accompagnée de sa durée, de son risque et de la condition de suppression. Une préférence visuelle locale n’est pas une dérogation valable.

État réel au 1er septembre 2026 :

- **Modales POS : partiellement harmonisées.** Reçu, détail de vente et commandes en cours utilisent `pos-modal-content`, mais le composant Blade commun `x-ui.modal` et la recette complète focus/Échap/restauration ne sont pas encore généralisés à tous les modules.
- **DataTable du POS : visuellement adaptée localement.** Les anciennes couleurs injectées dans `drawCallback` ont été retirées, mais il n’existe pas encore de composant ou wrapper DataTable partagé et validé sur tous les écrans.
- **Généralisation non réalisée.** Le prochain lot transversal doit inventorier les DataTables et modales existantes, créer la primitive CSS/Blade commune, migrer un écran pilote, puis seulement étendre module par module.
- Il est interdit à un prochain agent de déclarer ces deux chantiers terminés en se fondant uniquement sur le POS.

### Ordre de reprise frontend recommandé

1. Reprendre le **POS**, pas un autre module : refondre d’abord le panier et les lignes de commande en conservant leur structure/IDs métier, puis les commandes en attente et les modales.
2. Tester manuellement les scénarios POS ci-dessus sur desktop, tablette et mobile; contrôler l’absence de débordement à 390 px.
3. Migrer ensuite l’historique des ventes sur le shell SaaS afin de garder une continuité Ventes → POS → Historique.
4. Ensuite seulement, poursuivre les modules catalogue, clients, inventaire et paramètres selon le même principe : une page à la fois, logique métier inchangée, tests ciblés.

### Commandes de vérification utiles

```powershell
php artisan test --filter=ProfileSecurityTest
php artisan test --stop-on-failure
php artisan route:list --name=sale
```

Pour une modification du POS, vérifier aussi à la main sur la compagnie Matrix et après un switch vers une autre compagnie afin d’écarter toute fuite de panier ou de données.

### État de la base de test locale (à ne pas confondre avec une régression POS)

- Au dernier contrôle, `ProfileSecurityTest` et les scénarios `RoleManagementTest` métier passaient. Les trois scénarios `PlatformAdminRoleManagementTest` ont ensuite été bloqués par `SQLSTATE[42S02] : Table 'pos_testing.migrations' doesn't exist` lors de l’initialisation de la base `pos_testing`.
- Ne jamais résoudre cela avec `migrate:fresh` sur une base applicative. Vérifier d’abord la valeur `DB_DATABASE` dans `.env.testing` et restaurer/migrer **uniquement** la base de test lorsque le propriétaire l’autorise.

Rapport général permanent : `docs/RAPPORT_GLOBAL_SAAS.md`. Les anciens rapports datés et rapports techniques séparés ont été consolidés dans ce document unique.

Correctif connexion PWA staging du 27 août : le manifeste démarre désormais sur `/home` au lieu de `/`, le cache passe à `pro-seller-pwa-v4`, le POST de connexion est relatif à l’origine installée et la redirection reçue est ramenée au même hôte. L’e-mail mobile est normalisé (espaces/majuscules) et les erreurs 419/429 sont explicites. Après déploiement, supprimer l’ancien raccourci PWA, ouvrir le domaine HTTPS canonique dans Safari/Chrome, recharger puis réinstaller l’application.

Installation Android : `public/pwa-register.js` intercepte désormais `beforeinstallprompt` et affiche une bannière interne avec logo, « Plus tard » et « Installer ». Le refus est mémorisé 7 jours, l’installation masque la bannière et le bouton présente un loader pendant l’invite système. La bannière n’apparaît que si Chrome juge la PWA installable ; HTTPS, manifeste, service worker et absence d’installation existante restent obligatoires.

Compatibilité des autres navigateurs mobiles : si `beforeinstallprompt` n’est pas disponible, une bannière de secours apparaît après 4 secondes et explique comment utiliser le menu du navigateur pour installer ou ajouter la PWA à l’écran d’accueil. iPhone/iPad conservent leur guide Safari dédié. Aucun navigateur ne permet au site de forcer une installation lorsque son API native n’existe pas.

Organisation du menu Communications : un parent unique `SMS & WhatsApp` regroupe désormais `Configuration` (ancienne page Notifications), `Quota` et `Consommation`. Chaque sous-menu reste masqué ou affiché selon sa permission propre. Le lien Notifications a été retiré de Paramètres, qui redevient centré sur la compagnie.

Correctif notifications WhatsApp de vente : les messages automatiques utilisent de nouveau l’endpoint fournisseur de modèle `/whatsapp/template/text-message` avec `title` et `content`. Le passage accidentel à `/whatsapp/text-message` empêchait la livraison proactive alors que les SMS continuaient à fonctionner. Le pays du destinataire, le nom de la compagnie, le quota, le journal de consommation et l’idempotence sont conservés. Les refus fournisseur journalisent maintenant leur message sans exposer les clés API.

Audit des notifications d’inventaire : le même endpoint de modèle est utilisé pour WhatsApp. Un test bout en bout couvre maintenant un destinataire d’inventaire autorisé simultanément par WhatsApp et SMS, les deux appels fournisseur, la diminution séparée des quotas et les états de livraison. Les erreurs des deux canaux conservent désormais le message utile du fournisseur dans les logs. Attention : l’interrupteur global de la catégorie Inventaire et la case du destinataire doivent tous deux être activés dans `Communications > SMS & WhatsApp > Configuration`.

Clarification de la configuration : les interrupteurs Ventes/Inventaire sont désormais présentés comme des `Notifications internes`, où le canal global et la case individuelle du destinataire sont tous deux obligatoires. Les réglages de facture sont renommés `Envoi des factures aux clients` et clairement signalés comme indépendants des notifications internes. Les messages du POS conduisent précisément à cette section. Lorsqu’un canal d’inventaire est actif sans aucun destinataire sélectionné, le job écrit maintenant un avertissement explicite dans les logs au lieu de terminer silencieusement.

Correctif SMS d’inventaire Matrix : le fournisseur refusait avec HTTP 422 `VALIDATION_ERROR` le contenu détaillé prévu pour WhatsApp. Le job construit désormais un SMS ASCII compact (type, produit, quantité avant/après, mouvement et auteur), limité avec le préfixe compagnie à moins de 160 caractères dans le test. WhatsApp conserve son message détaillé. Un retry ne renvoie pas le WhatsApp déjà livré grâce au registre idempotent et tente seulement le SMS en échec. Les erreurs API SMS journalisent aussi le message et les détails de validation du fournisseur.

Historique des ventes : l’ancien filtre de date isolé a été remplacé par un collapse `Filtrer les ventes et le classement des produits`. Il combine période, client et fournisseur. Un filtre fournisseur retient les ventes contenant au moins un produit de ce fournisseur et limite le classement aux produits correspondants. Le tableau, les indicateurs, la quantité totale vendue, le top 10 et les exports PDF/CSV/Excel partagent les mêmes paramètres. Les identifiants client/fournisseur sont validés dans la compagnie active. Les boutons Filtrer et Réinitialiser respectent le loader serveur global.

Consommation SMS & WhatsApp : pagination Bootstrap explicite à 10 lignes par défaut, choix 10/25/50, compteur `Affichage de X à Y sur Z` et conservation de tous les filtres dans les liens de pages.

État consolidé : staging multi-compagnies, PWA, KPrimePay réel, sauvegardes, queues, cron et délivrabilité validés. Utiliser `docs/RAPPORT_GLOBAL_SAAS.md` pour l’état général courant et `docs/RAPPORT_ADMINISTRATION_SAAS.md` pour la console centrale.

L’intégration et la procédure d’exploitation KPrimePay sont regroupées dans `docs/GUIDE_KPRIMEPAY.md`. La commande `payments:reconcile-kprimepay` tourne toutes les dix minutes et partage le crédit atomique de `QuotaPaymentSettlementService` avec le webhook.

Les résultats de volume SQL, concurrence, charge des notifications, limites PDF et exports CSV/XLSX sont tous regroupés dans `docs/RAPPORT_GLOBAL_SAAS.md`. Les benchmarks restent exclus de la suite quotidienne et exigent une base `*_testing`.

- Correctif téléchargement CSV/Excel : la PWA servait encore l’ancien `server-button-loader.js`, rendant `ServerButtonLoader.download()` indisponible. Le cache est maintenant `pro-seller-pwa-v3` et le script porte `?v=20260826-1` dans tous les layouts. Conserver le versionnement lors des prochaines modifications d’assets mis en cache.

## Niveau d’avancement

- Migration fonctionnelle SaaS : **environ 94 %**.
- Préparation à une production SaaS : **instantané historique** ; consulter `RAPPORT_GLOBAL_SAAS.md` et `DEPLOIEMENT_O2SWITCH.md` pour l’état courant.
- Monétisation et abonnements : **instantané historique du 25 août 2026** ; ce chiffre est remplacé par l’état de référence actuel en tête du document.
- Suite quotidienne validée : **129 tests, 719 assertions, tous réussis**. Les 5 scénarios lourds de volume/concurrence/queue/PDF sont séparés dans `benchmarks/` et ne sont exécutés que sur demande.
- Décision du propriétaire au 25 août 2026 : **le déploiement O2switch n’est pas encore autorisé**. Continuer à maintenir sa documentation, mais ne lancer aucune connexion, migration, configuration cron/SMTP, copie de fichiers ou opération sur l’hébergement avant une nouvelle demande explicite.

## Fonctionnalités terminées et validées

### Compagnies et contexte tenant

- Noyau multi-compagnies : `Company`, `CompanyUser`, `CompanyInvitation`, `CompanyContext` et trait `BelongsToCompany`.
- Un utilisateur global peut appartenir à plusieurs compagnies avec un rôle différent dans chaque adhésion.
- L’inscription SaaS crée atomiquement le compte, sa première compagnie, les rôles initiaux et l’adhésion propriétaire.
- Les écrans d’inscription et de connexion disposent de liens réciproques clairement visibles. Le lien « Créer votre compte SaaS » ouvre `/register`. La vue `auth/register.blade.php` reprend désormais l’interface complète de `admin/register.blade.php` et son `layout_admin` : utilisateur, compagnie, taxe facultative, mot de passe visible/masqué et inscription SaaS AJAX. `/login` redirige vers `user_login`. Pour une session déjà authentifiée, `/register` et l’ancien `/home` redirigent vers le vrai tableau de bord `/dashboard` au lieu de la page Laravel générique. Les pages d’authentification ne sont plus mises en cache et le retour navigateur recharge la session afin d’éviter une ancienne page de connexion. Les boutons utilisent le loader serveur global.
- La permission `reports.view_margin` protège désormais les bénéfices dans le tableau de bord général, le tableau de bord comptable, le POS, l’historique des ventes, les réponses DataTables et l’export PDF. Sans cette permission, `total_profit`, le bénéfice des lignes de vente et les coûts/bénéfices des produits imbriqués sont retirés côté serveur, et pas seulement masqués en CSS. Un test de non-divulgation couvre les réponses AJAX.
- `AuthorizedLandingPage` centralise la destination après connexion, changement de compagnie, acceptation d’invitation, retour sur une page réservée aux visiteurs et accès à l’ancien `/home`. L’ordre est : Tableau de bord, Ventes, Clients, Inventaire, Catalogue, Comptabilité, E-commerce, Utilisateurs, Compagnie, Notifications, puis Profil. Un membre de plusieurs compagnies sans contexte actif reste dirigé vers le sélecteur. Il ne faut plus réintroduire de redirection fixe vers `/dashboard` dans ces flux.
- Création de compagnies supplémentaires avec informations essentielles.
- Aucune bascule automatique après création : une confirmation demande à l’utilisateur s’il souhaite ouvrir la nouvelle compagnie.
- Le switch vérifie l’adhésion active, régénère la session, met à jour `last_accessed_at` et journalise le changement.
- Le sélecteur de compagnie permet désormais d’abandonner le choix : lorsqu’une compagnie est déjà active, « Retour à l’application » ramène vers la première page autorisée calculée par `AuthorizedLandingPage`, sans changer la compagnie ni sa session. Sans contexte actif, « Quitter sans choisir » ferme proprement la session afin d’éviter une boucle avec le sélecteur. Ne pas remplacer ce comportement par un simple retour dans l’historique du navigateur.
- La gestion est centralisée dans **Paramètres > Compagnie** : cartes de toutes les compagnies en haut, détails et modification de la compagnie active en bas.
- Après modification du nom, de l’e-mail ou du logo, la page est rechargée afin de synchroniser les cartes.
- Ne pas réintroduire une entrée séparée « Mes compagnies » dans le menu principal.

### Isolation et autorisation

- Les principaux modèles métier reçoivent automatiquement `company_id` et sont filtrés par la compagnie active.
- Les routes métier utilisent `company.resolve`, `company.selected` et une permission de module.
- Premier lot de défense en profondeur terminé sur le catalogue : `ProductPolicy` et `CategoryPolicy` vérifient simultanément l’utilisateur du contexte, la permission `catalog.manage` et l’appartenance de la ressource à la compagnie active. Les contrôleurs autorisent désormais explicitement listage, consultation, création, modification, archivage/restauration et export.
- Les formulaires produit refusent une catégorie ou un fournisseur appartenant à une autre compagnie. Les filtres de catégorie inter-compagnies sont rejetés sur le listing et l’export PDF. L’export produit utilise la compagnie résolue par `CompanyContext` au lieu de `CompanySetting::first()`.
- Tests de sécurité associés : `CatalogTenantSecurityTest`, soit 5 scénarios et 31 assertions couvrant les IDOR show/edit/update/delete, les relations croisées, les Policies hors scope, l’absence de permission et le filtre d’export.
- Deuxième lot de défense par ressource terminé : `ClientPolicy` et `SupplierPolicy` protègent listage, consultation, création, modification, archivage et restauration. Les accès directs show/edit/update/delete d’une autre compagnie retournent `404`, même pour un utilisateur membre des deux compagnies.
- Troisième lot de défense terminé : `SalePolicy`, `InventoryPolicy`, `CashAccountPolicy` et `OrderPolicy` vérifient simultanément l’utilisateur du contexte, la permission locale et le `company_id` de la ressource. Consultation/export des ventes, consultation/export/mouvements d’inventaire, consultation/modification/archivage des caisses et consultation/annulation/conversion des commandes sont protégés. La conversion exige à la fois `ecommerce.manage` et `sales.manage`.
- Les identifiants d’une autre compagnie retournent `404` sur les routes sensibles, y compris annulation/exécution d’une commande, sans révéler si la ressource existe. `SensitiveResourceTenantSecurityTest` couvre les routes étrangères et teste aussi directement les Policies avec des modèles chargés hors scope.
- Premier lot de durcissement physique terminé : `company_id` est devenu `NOT NULL` sur les 12 tables métier centrales via `2026_08_24_154000_require_company_on_core_business_tables.php`, avec contrôle préalable intégral.
- Deuxième lot de durcissement physique terminé : la migration `2026_08_24_155000_add_core_tenant_composite_foreign_keys.php` ajoute 16 clés étrangères composites. MySQL vérifie maintenant simultanément l’identifiant métier et `company_id` pour Produit→Catégorie/Fournisseur, Vente→Client, Ligne de vente→Vente/Produit, Inventaire→Produit/Fournisseur, Menu→Produits, Transaction/Réglage→Caisse, Ligne de commande→Commande/Produit et Commande→Vente. Toutes les relations sont pré-auditées avant le premier changement. Les suppressions des relations facultatives sont restrictives pour ne jamais mettre le tenant obligatoire à `NULL`.
- `CoreTenantCompositeConstraintTest` prouve par écritures SQL directes que MySQL refuse une catégorie, un client, un produit de ligne ou une caisse appartenant à une autre compagnie, et vérifie la présence des contraintes attendues.
- Troisième lot terminé : `2026_08_24_156000_archive_legacy_tenant_rows_and_secure_memberships.php` a copié intégralement les 12 actions de connexion et 3 rôles système historiques (avec leurs permissions) dans `legacy_tenant_records`, puis les a retirés des tables actives. Aucun rattachement à Matrix ou FENIX n’a été inventé. `actions.company_id` et `roles.company_id` sont maintenant `NOT NULL` ; l’archive reste consultable et la migration sait restaurer les lignes lors d’un rollback.
- Les adhésions et invitations ne peuvent plus référencer un rôle d’une autre compagnie. Les destinataires de notifications et managers E-commerce doivent désormais correspondre physiquement à une adhésion `(company_id, user_id)` existante. Les suppressions d’adhésion nettoient ces configurations dépendantes par cascade.
- La gestion des clients dépend désormais réellement de `clients.manage` dans la Policy, les routes et le menu. Un caissier peut donc gérer les clients sans recevoir `catalog.manage`; un rôle Clients ne peut pas administrer les fournisseurs.
- Les ventes refusent un client d’une autre compagnie ou un client archivé. Les entrées de stock refusent un produit ou un fournisseur d’une autre compagnie ainsi que les enregistrements archivés. Les filtres d’inventaire étrangers sont rejetés et son export PDF utilise `CompanyContext` au lieu de `CompanySetting::first()`.
- Tests associés : `PartnerTenantSecurityTest`, soit 4 scénarios et 46 assertions couvrant les IDOR Clients/Fournisseurs, les permissions dédiées, la création dans le tenant actif, les relations Vente→Client et Inventaire→Fournisseur ainsi que les filtres d’inventaire.
- Les interfaces Clients et Fournisseurs respectent maintenant la convention d’attente serveur : loaders sur ajout/modification/consultation et loader SweetAlert bloquant avec nouvelle tentative sur archivage/restauration.
- Le Profil ne fait plus confiance au `user_id` du formulaire : changement d’e-mail et de mot de passe ciblent exclusivement le compte authentifié et exigent son mot de passe actuel. L’e-mail doit être valide, confirmé et unique ; le nouveau mot de passe doit être confirmé et respecter la complexité existante. Les formulaires utilisent le loader global et les routes sont limitées à 10 tentatives par minute. `ProfileSecurityTest` couvre quatre scénarios, notamment la falsification de l’identifiant d’un autre utilisateur.
- Les agrégations SQL brutes identifiées sur `sale_details` sont filtrées par `company_id`.
- Le journal des connexions n’écrit plus de nouvelle action sans compagnie : une adhésion unique est journalisée explicitement lors de la connexion ; pour un compte multi-entreprises, l’action est créée seulement après sélection de la compagnie. Le changement ultérieur de compagnie est également journalisé dans la compagnie cible.
- L’ancienne purge globale du dimanche a été remplacée par `actions:clean --days=365`, exécutée compagnie par compagnie et protégée contre les chevauchements. `--pretend` prévisualise sans suppression et `--company=ID` limite l’opération. Les anciennes actions sans compagnie sont volontairement conservées pour une décision de backfill ultérieure. `AuditLogSecurityTest` couvre ces garanties.
- Modèles `Role` et `Permission`, middleware `EnsurePermission` et permissions propres à chaque compagnie.
- Écran **Rôles et permissions** fonctionnel : création, modification, suppression des rôles personnalisés et attribution aux utilisateurs.
- Modules de permissions affichés en français dans des accordéons, avec activation globale en en-tête et détails dans le corps.
- Le rôle propriétaire est protégé contre suppression, attribution arbitraire et rétrogradation.
- Le formulaire utilisateur utilise le rôle de l’adhésion active ; Select2 avec recherche fonctionne en ajout et en modification AJAX.
- Un propriétaire/administrateur peut rattacher un compte existant à la compagnie active par son e-mail et lui attribuer un rôle local, sans modifier ses adhésions ni rôles dans les autres compagnies.
- Dans la liste des utilisateurs, l’action avec l’icône de clonage permet d’intégrer la ligne sélectionnée dans une autre compagnie gérée par l’opérateur. Les rôles sont chargés depuis la compagnie cible, une approbation explicite est demandée et l’accès dans la compagnie source est conservé. Routes : `user.transfer-options` et `user.transfer-company`.
- Toute nouvelle compagnie reçoit automatiquement deux comptes distincts (`Caisse principale` et `Caisse de taxe`) ainsi qu’un enregistrement `settings` qui les référence. Le taux de taxe facultatif saisi à l’inscription ou à la création est appliqué automatiquement ; sans saisie, il vaut `0`.
- Correctif caisse après bascule : le code généré contient désormais l’ID de la compagnie (`CASH-{company_id}-{séquence}`), `company_id` est écrit explicitement et le contexte est `scoped` par requête. L’interface n’affiche plus un faux succès lorsque le serveur renvoie `status: false`.
- Les notifications WhatsApp et SMS préfixent systématiquement le titre/contenu avec le nom de la compagnie. Quatre autorisations globales existent : WhatsApp ventes, SMS ventes, WhatsApp inventaire et SMS inventaire. Les jobs revérifient les autorisations à l’exécution. Migration : `2026_08_19_120000_add_notification_preferences_to_company_settings.php`.
- Les réglages de notification sont centralisés dans `Communications > SMS & WhatsApp > Configuration`. La page gère les destinataires actifs par catégorie (`sale`, `inventory`) et canal (`email`, `whatsapp`, `sms`). Les managers e-commerce restent inchangés et continuent de recevoir les commandes. Migration : `2026_08_19_130000_create_notification_recipients_table.php`.
- L’accès à `Communications > SMS & WhatsApp > Configuration` dépend de la permission de rôle `notifications.manage` (`Gestion des notifications`). Tous les membres actifs peuvent devenir destinataires ; propriétaires et administrateurs sont classés en tête et E-mail/WhatsApp sont précochés par défaut. Les tableaux deviennent défilants au-delà de trois utilisateurs. Migration : `2026_08_19_140000_add_notification_management_permission.php`.
- Route dédiée : `POST /user/attach-existing`. Elle refuse les rôles d’une autre compagnie et le rôle propriétaire.
- Flux complet d’invitation sécurisé : création depuis la compagnie active, rôle local, e-mail portant le nom de l’entreprise, jeton aléatoire stocké uniquement sous forme SHA-256, expiration à 48 heures, acceptation, refus, renvoi avec rotation du jeton et révocation. Pour un compte existant, le lien sert de connexion sécurisée à usage unique : aucun mot de passe temporaire n’est envoyé et le mot de passe permanent n’est pas modifié. Si une autre adresse est connectée, l’interface prévient puis bascule vers le compte réellement invité après confirmation.
- La migration `2026_08_21_110000_limit_pending_company_invitations_to_48_hours.php` plafonne également à 48 heures les invitations encore en attente créées avant cette règle.
- Aucun `company_user` n’est créé avant acceptation. Un compte existant doit prouver la possession de son compte ; une nouvelle adresse crée son compte depuis le lien. L’acceptation conserve toutes les autres adhésions et applique le rôle seulement dans la compagnie invitante.
- La page Utilisateurs affiche l’historique et les états des invitations. Routes publiques `invitations.*`, routes administratives `user.invitations.*`. Migration : `2026_08_21_100000_complete_company_invitations_lifecycle.php`. Tests : `CompanyInvitationFlowTest`.
- La création, le renvoi et la révocation exigent une confirmation UI mentionnant l’adresse cible (et le rôle lors de la création). L’invitation utilise désormais exactement le même appel HTML `Mail::send` que les notifications de vente. `last_sent_at` n’est mis à jour qu’après acceptation par SMTP, journalisée sous `Invitation email accepted by SMTP` avec le `message_id` disponible. Les échecs sont journalisés sous `Invitation email sending failed`.
- Pendant un renvoi ou une révocation confirmée, la fenêtre SweetAlert reste affichée avec un loader, bloque la fermeture jusqu’à la réponse et présente l’erreur dans la même fenêtre si l’opération échoue.
- Convention globale obligatoire : toute attente serveur déclenchée par un clic affiche un loader dans le bouton et bloque les doubles clics. Le composant commun est `public/hub/assets/js/server-button-loader.js`; les règles UI/UX sont regroupées dans `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` et `AGENTS.md` les impose aux prochains intervenants/agents.
- L’écran public permettant de rejoindre une entreprise a été entièrement contrasté : carte à bordure verte identifiable, panneau de création distinct, champs clairs avec bordure renforcée et focus accessible, métadonnées e-mail/expiration séparées et actions hiérarchisées. Le bouton de refus possède une bordure rouge dès son état normal. Sur mobile, la page reste fixe et seul le contenu intérieur de la carte défile, avec l’en-tête de l’entreprise toujours visible.
- Dans la table des invitations, les badges sont centralisés par le modèle : En attente jaune, Acceptée verte, Refusée rouge, Révoquée sombre et Expirée grise.
- Le bouton historique « Créer un nouvel utilisateur » est masqué de la liste des utilisateurs. Les parcours visibles sont désormais « Inviter par e-mail » et « Ajouter un utilisateur existant » ; l’ancien code reste temporairement présent pour faciliter une suppression technique ultérieure sans régression.

### POS, stock et caisse

- Vente avec stock insuffisant refusée et retrait de stock négatif bloqué.
- Après fermeture du reçu, les quantités des cartes produit sont resynchronisées avec le serveur.
- Une caisse ne peut pas être simultanément caisse principale et caisse de taxe, côté modèle, contrôleur et interface.
- Tableau de bord général tenanté avec statistiques catégories, produits, ventes, clients et fournisseurs.
- Les jobs de notification vente, marge et inventaire transportent explicitement `company_id`, restaurent le contexte et ciblent uniquement les propriétaires/administrateurs actifs de la compagnie.
- Le rapport hebdomadaire d’inventaire parcourt séparément chaque compagnie active et génère un PDF avec ses seules données.
- Les jobs sont déclenchés après validation de la transaction DB avec `afterCommit()`.

### E-commerce

- Storefront public par compagnie : `/boutique/{company:slug}`.
- Les slugs et `public_id` de compagnie sont désormais obligatoires et uniques en base. Deux entreprises homonymes reçoivent des slugs lisibles distincts (`matrix`, `matrix-2`, etc.) et renommer une entreprise ne modifie pas son lien public existant. La migration réversible `2026_08_24_150000_enforce_unique_company_public_identifiers.php` est appliquée sur la base locale ; `EcommerceStorefrontTest` vérifie la résolution précise des deux boutiques.
- Dans **Configuration E-commerce**, le champ « Adresse personnalisée de la boutique » vérifie la disponibilité après saisie, normalise accents/espaces en slug et actualise l’aperçu du lien. Les mots réservés et les slugs d’autres compagnies sont refusés côté serveur. Tout changement exige une confirmation SweetAlert avec loader avertissant que l’ancien lien cessera de fonctionner. `EcommerceSlugCustomizationTest` couvre disponibilité, confirmation, collision, mot réservé et `company_id` falsifié.
- Navigation tenantée : accueil, produits, catégories, produit, panier, commande et succès.
- La configuration e-commerce affiche le lien public, le statut, « Ouvrir la boutique » et « Copier le lien ».
- Le storefront restaure le contexte public avant de lire catalogue, stock et commandes.
- La création de commande publique exige la compagnie explicite du slug ; la route historique ambiguë `/shop/order/place` est refusée. Les produits actifs sont rechargés dans cette compagnie et les prix/noms viennent exclusivement du serveur. La commande et ses lignes sont créées atomiquement, mais **le stock n’est ni réservé ni diminué à ce stade**. Les paniers du navigateur sont séparés par `public_id` de compagnie.
- `orders.company_id` et `order_items.company_id` sont désormais `NOT NULL` avec clés étrangères restrictives via `2026_08_24_151000_require_company_on_ecommerce_orders.php`. Le job `SendEcommerceOrderEmailJob`, lancé après commit, recharge la bonne compagnie et envoie uniquement aux managers E-commerce actifs disposant encore d’une adhésion active. L’e-mail `emails.ecommerce.orderNotification` reprend `emails.design.emailStyle` et `emails.design.emailFooter`, avec le nom de l’entreprise, le client, les lignes, le total et un bouton vers la commande.
- Depuis la liste ou le détail d’une commande en attente, un utilisateur disposant de `ecommerce.manage` et `sales.manage` peut confirmer « Passer en vente ». `SaleCreationService` réutilise exactement le moteur du POS : création vente/détails, verrouillage et diminution du stock, bénéfice, caisse principale/taxe, transactions, notifications et journal d’action. La conversion est atomique et idempotente : un stock devenu insuffisant annule toute l’opération et une commande ne peut produire qu’une vente.
- Une commande en attente peut être annulée avec un motif obligatoire. L’auteur, la date et le motif sont conservés ; aucune vente, caisse ou quantité n’est modifiée. Une commande annulée ne peut plus être convertie et une commande convertie ne peut plus être annulée depuis ce flux. Migration : `2026_08_24_152000_add_sale_conversion_to_orders.php`. Les confirmations de conversion/annulation utilisent les loaders SweetAlert obligatoires.
- Le formulaire de livraison permet soit de coller un lien HTTPS Google Maps, soit d’autoriser le navigateur à relever la position GPS. Dans ce second cas, le serveur reconstruit lui-même le lien Google Maps à partir des coordonnées validées. Les liens non HTTPS ou hors domaines Google autorisés sont rejetés. Le lien « Ouvrir dans Google Maps » apparaît dans l’e-mail manager et le détail administratif. Migration : `2026_08_24_153000_add_delivery_location_to_orders.php`.
- Les gestionnaires E-commerce sont maintenant strictement rattachés à la compagnie active : aucun `company_id` du navigateur n’est accepté, seuls les membres actifs de l’entreprise sont proposés et la liste/suppression est filtrée par `CompanyContext`. Le test `EcommerceManagerTenantSecurityTest` vérifie les tentatives de lecture, ajout et suppression inter-compagnies. L’ajout utilise le loader serveur global et le retrait conserve la confirmation SweetAlert ouverte avec un loader jusqu’à la réponse.
- Conserver les anciennes routes `/shop` seulement comme compatibilité temporaire ; tout nouveau lien doit utiliser les routes `storefront.*`.

## Fichiers structurants

- `app/Services/CompanyContext.php`
- `app/Services/CompanyProvisioner.php`
- `app/Services/CompanyOnboardingService.php`
- `app/Services/SaleCreationService.php`
- `app/Traits/BelongsToCompany.php`
- `app/Http/Middleware/ResolveCompany.php`
- `app/Http/Middleware/EnsureCompanySelected.php`
- `app/Http/Middleware/EnsurePermission.php`
- `app/Policies/ProductPolicy.php`
- `app/Policies/CategoryPolicy.php`
- `app/Policies/ClientPolicy.php`
- `app/Policies/SupplierPolicy.php`
- `app/Policies/SalePolicy.php`
- `app/Policies/InventoryPolicy.php`
- `app/Policies/CashAccountPolicy.php`
- `app/Policies/OrderPolicy.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Http/Controllers/Company/CompanyController.php`
- `app/Http/Controllers/Company/SwitchCompanyController.php`
- `app/Http/Controllers/User/RoleController.php`
- `app/Http/Controllers/Ecommerce/FrontController.php`
- `resources/views/company/index.blade.php`
- `resources/views/role/index.blade.php`
- `database/migrations/2026_08_18_100000_*` à `100007_*`
- `tests/Feature/CompanyCreationTest.php`
- `tests/Feature/AuthNavigationTest.php`
- `tests/Feature/CompanyIsolationTest.php`
- `tests/Feature/CatalogTenantSecurityTest.php`
- `tests/Feature/PartnerTenantSecurityTest.php`
- `tests/Feature/RoleManagementTest.php`
- `tests/Feature/EcommerceStorefrontTest.php`
- `tests/Feature/EcommerceManagerTenantSecurityTest.php`
- `tests/Feature/EcommerceSlugCustomizationTest.php`
- `tests/Feature/EcommerceOrderSecurityTest.php`
- `tests/Feature/EcommerceOrderLifecycleTest.php`
- `tests/Feature/ProfileSecurityTest.php`
- `tests/Feature/SensitiveResourceTenantSecurityTest.php`
- `tests/Feature/CoreBusinessSchemaHardeningTest.php`
- `tests/Feature/CoreTenantCompositeConstraintTest.php`

## Règles obligatoires pour la suite

1. Ne jamais prendre `company_id` depuis un formulaire métier ; utiliser `CompanyContext`.
2. Toute nouvelle table métier doit avoir `company_id`, un index et un modèle tenanté.
3. Toute requête `DB::table(...)` doit recevoir un filtre `company_id` explicite.
4. Tout `withoutCompanyScope()` doit être justifié et réservé à une opération plateforme.
5. Tout job doit transporter `company_id` et restaurer le contexte avant de charger les modèles.
6. Toute route métier doit avoir les middlewares de compagnie et une permission adaptée.
7. Ajouter un test négatif inter-tenant pour chaque nouvelle fonctionnalité sensible.
8. Ne jamais lancer `migrate:fresh` sur la base de production ; utiliser uniquement la base de test.
9. Ne pas confondre permissions utilisateur et futures capacités d’abonnement.
10. Ne pas remettre de bascule automatique après création d’une compagnie.

## Travail restant prioritaire

### Point de reprise validé — fin de session du 21 août 2026

- Derniers lots terminés : séparation complète des menus/routes par permission, page 403 intégrée avec navigation conservée, illustration animée locale, protection serveur des bénéfices via `reports.view_margin` et redirection intelligente via `AuthorizedLandingPage`.
- Référence de non-régression : **98 tests réussis, 571 assertions**.
- Prochaine phase convenue avec le propriétaire : **audit non destructif puis durcissement de l’isolation en base de données**.
- Commencer par produire l’inventaire des `company_id` nuls, relations inter-compagnies possibles, clés uniques globales et règles de suppression. Ne lancer aucune migration destructive et ne modifier aucune donnée réelle avant validation du rapport d’audit.
- Après validation : backfill contrôlé, contraintes `NOT NULL`, index composés par compagnie, stratégie des clés étrangères et nouveaux tests de cohérence tenant.

### Audit non destructif terminé — 24 août 2026

- Rapport complet : `docs/AUDIT_ISOLATION_TENANT_2026-08-24.md`.
- Script réutilisable en lecture seule : `scripts/audit_tenant_isolation.php`.
- Base locale : 2 compagnies, **0 relation inter-compagnies détectée sur 19 contrôles**, propriétaires/réglages/caisses cohérents.
- Les anciennes anomalies sont résolues de façon traçable : **12 actions et 3 rôles historiques sont archivés dans `legacy_tenant_records`**. Les tables actives ne contiennent plus aucun `company_id` nul.
- Risques P0 encore ouverts : la cohérence tenant reste à imposer par des clés étrangères composites sur les autres relations métier. Les risques sur les managers E-commerce, le Profil et la commande publique sont corrigés.
- Aucune migration, suppression, correction ou donnée métier n’a été modifiée pendant l’audit.
- Isolation tenant applicative et physique pratiquement terminée : toutes les tables actives auditées imposent le tenant, les relations métier centrales et les relations d’adhésion/destinataires sont contraintes en base. Prochaine action : industrialisation de production (queue permanente, Redis, sauvegardes/restauration, stockage privé, supervision et secrets).
- Durcissement du schéma commencé : `company_settings.slug` et `company_settings.public_id` sont maintenant `NOT NULL` et `UNIQUE`. L’audit local reste à 0 slug/public_id absent ou dupliqué.

### P0 — avant pilote SaaS

1. **Partiellement terminé :** les jobs vente/inventaire et le rapport hebdomadaire sont tenantés ; auditer encore les autres commandes, exports, e-mails, SMS et WhatsApp éventuels.
2. Supprimer la dépendance métier restante à `users.user_type` au profit de `company_user.role_id`.
3. **Partiellement terminé :** Policies Produits, Catégories, Clients et Fournisseurs ajoutées ; poursuivre sur Ventes, Inventaires, Caisses et Commandes.
4. **Partiellement terminé :** tests IDOR show/update/delete du catalogue et des partenaires ajoutés ; poursuivre sur ventes, inventaires, caisses et commandes.
5. Auditer toutes les utilisations restantes de `CompanySetting::first()`, `find()` et `findOrFail()` ; les exports produits et inventaire sont corrigés.
6. **Partiellement terminé :** relations produit/catégorie/fournisseur, vente/client et inventaire/fournisseur sécurisées ; vérifier caisse/réglage et commande/produit.
7. Vérifier et convertir les clés uniques globales en clés composées par compagnie lorsque nécessaire.
8. Contrôler le backfill en production, puis rendre progressivement `company_id` non nullable.
9. Ajouter un contrôle de contexte aux formulaires sensibles pour le cas d’un switch dans un autre onglet.

### P1 — industrialisation

- Redis pour sessions, cache et queues partagés.
- Stockage privé tenanté pour exports, reçus et logos.
- MFA administrateur, limitation de débit, audit complet et rotation des secrets.
- Sauvegarde/restauration testée, supervision et alertes.
- Tests de charge et pilote avec au moins deux compagnies réelles.
- Migration Laravel/PHP sur une branche séparée, sans la mélanger au chantier tenant.

### P2 — préparation des abonnements

- Créer `EntitlementService` avant tout écran de tarification.
- Préparer `plans`, `subscriptions`, `plan_features` et `usage_counters` sans prix figés.
- Définir essais, quotas, facturation, webhooks et impayés seulement après validation commerciale.

## Ordre de reprise recommandé pour Freebuff

1. Lancer la suite de tests et confirmer la référence `98 tests / 571 assertions`.
2. Lire `docs/AUDIT_ISOLATION_TENANT_2026-08-24.md` ; ne pas refaire l’audit déjà terminé.
3. Ne pas refaire la sécurisation des managers E-commerce, du Profil, de la commande publique ni le cycle commande → vente/annulation, déjà terminés et testés.
4. Le journal, la rétention et les Policies/IDOR des ressources sensibles sont terminés ; préparer maintenant les migrations de durcissement `NOT NULL` et contraintes de cohérence tenant, sans toucher aux données avant validation du backfill.
5. Ne commencer le nettoyage et les migrations `NOT NULL`/contraintes composites qu’après une deuxième validation explicite.
6. Ne pas refaire les Policies ni tests IDOR déjà terminés pour Produits, Catégories, Clients et Fournisseurs ; poursuivre ensuite sur Ventes, Inventaires, Caisses et Commandes.
7. Mettre à jour ce fichier et la documentation PDF après chaque lot validé.

## Vérification rapide

```powershell
php artisan test --stop-on-failure
php artisan route:list --name=companies
php artisan route:list --name=roles
php artisan route:list --name=storefront
php docs/generate_saas_documentation.php
```

## Attention au dépôt

Le travail est non commité et le dépôt était déjà sale avant la reprise. Préserver toutes les modifications existantes, examiner `git status --short` et `git diff` avant chaque intervention, et ne jamais utiliser `git reset --hard` ou écraser les changements d’un autre assistant.

## Mise à jour du 25 août 2026 — PWA et préparation O2switch

- La PWA possède désormais un manifeste enrichi, un service worker versionné, une page hors connexion et un enregistrement commun aux layouts principal, administration/authentification et point de vente.
- Le cache PWA est volontairement limité aux ressources publiques. Les pages authentifiées, API et données propres aux compagnies ne sont jamais mises en cache.
- Un guide iPhone/iPad explique dans Safari la procédure « Partager > Sur l’écran d’accueil » et disparaît lorsque l’application est déjà installée.
- Redis est volontairement reporté. Le premier déploiement utilisera `CACHE_DRIVER=file`, `SESSION_DRIVER=file` et `QUEUE_CONNECTION=database`.
- Le guide prêt à suivre se trouve dans `docs/DEPLOIEMENT_O2SWITCH.md` et le modèle sans secrets dans `.env.production.example`.
- `AppServiceProvider` utilise maintenant l’environnement Laravel au lieu d’un appel direct à `env()`, afin que le forçage HTTPS reste correct après `config:cache`.
- Prochaine reprise : ne pas activer Redis. Valider les informations réelles O2switch (domaine, chemin du compte, chemin PHP, MySQL et SMTP), puis appliquer le guide de déploiement sans enregistrer de secret dans Git.

## Mise à jour du 25 août 2026 — optimisation SQL, lot 1

- Le tableau de bord général n’hydrate plus toutes les catégories, produits et ventes. Les compteurs, chiffre d’affaires, remises et bénéfices utilisent maintenant `COUNT()` et `SUM()` dans MySQL.
- Le classement des meilleures ventes utilise `SaleDetail` avec chargement groupé des produits : l’ancien `Product::find()` exécuté pour chaque résultat (N+1) est supprimé.
- Le tableau de bord comptable ne charge plus toutes les caisses, transactions et ventes uniquement pour les compter. Les 20 dernières transactions restent les seules lignes détaillées chargées.
- Les listes utilisateurs et commandes e-commerce transmettent maintenant un Query Builder à Yajra DataTables. La pagination, la recherche et le tri se font en SQL ; toutes les lignes ne sont plus chargées en mémoire avant la réponse.
- La liste utilisateurs joint directement l’adhésion et le rôle actifs de la compagnie sélectionnée. Le rôle affiché reste tenanté et ne dépend plus de l’ancienne valeur globale `users.user_type`.
- Migration appliquée localement : `2026_08_25_100000_add_tenant_query_performance_indexes.php`. Elle ajoute des index tenantés ciblés sur actions, inventaires, commandes, produits, détails de vente, ventes et transactions.
- Tests dédiés : `QueryOptimizationTest` couvre les agrégations sans `SELECT *`, la pagination SQL des utilisateurs avec rôle local et la pagination SQL des commandes.
- Correctif recherche commandes : la colonne virtuelle DataTables `DT_RowIndex` est explicitement non recherchable et non triable. Elle n’est plus envoyée à MySQL comme si elle existait dans `orders`. Le test reproduit désormais une recherche globale réelle sur le code d’une commande.
- Nouvelle référence de non-régression : **101 tests réussis, 585 assertions**.
- Test manuel demandé : ouvrir les deux tableaux de bord, rechercher/paginer les utilisateurs, puis rechercher/paginer les commandes e-commerce. Vérifier les compteurs, montants et rôles après un changement de compagnie.
- Prochain lot performance recommandé : historique des ventes, listes inventaires/produits et chargement progressif du catalogue POS. Ne pas ajouter Redis à ce stade.

## Mise à jour du 25 août 2026 — optimisation SQL, lot 2

- La table des ventes du jour passe désormais directement un Query Builder à DataTables. La pagination et la recherche sont exécutées en SQL ; une réponse sans permission financière ne sélectionne même plus `total_profit`.
- L’historique des ventes ne charge plus toutes les ventes et tous leurs détails avant de répondre. Les totaux de la période utilisent des agrégations, les lignes sont paginées, la recherche par nom du client utilise `whereHas` et le contrôle de compagnie pour le bouton PDF n’est exécuté qu’une fois.
- Les statistiques journalières du POS utilisent des agrégations SQL. Le classement des produits vendus charge les produits en une requête groupée au lieu d’un `Product::find()` par résultat.
- Les DataTables produits chargent catégories et fournisseurs en lots fixes et savent rechercher leurs noms affichés. Même traitement pour les produits, fournisseurs et utilisateurs affichés dans l’inventaire.
- La colonne virtuelle `DT_RowIndex` de la table des ventes est maintenant non recherchable/non triable, comme celle des commandes.
- `QueryOptimizationTest` couvre maintenant de vraies recherches DataTables sur client, historique, catégorie produit et produit inventorié.
- Nouvelle référence : **104 tests réussis, 594 assertions**.
- Tests manuels : rechercher un client dans ventes du jour et historique, changer la plage de dates, rechercher une catégorie/fournisseur dans Produits et un produit/fournisseur/utilisateur dans Inventaires, puis vérifier les mêmes écrans après changement de compagnie.
- Prochaine phase performance : chargement progressif du catalogue POS et recherche produits limitée/paginée. Ne pas modifier ce flux sans conserver l’ajout au panier, le filtrage par catégorie et la resynchronisation des quantités après vente.
- Correctif UI historique : le bouton « Valider » utilise désormais `ServerButtonLoader.withLoader` autour du rechargement DataTables. Il reste désactivé pendant la requête puis retrouve systématiquement son texte après `xhr.dt`, succès ou erreur. L’ancien couple `#loader`/`#submitText`, sujet aux files d’animations `fadeIn`/`fadeOut`, a été supprimé. Une plage de dates invalide arrête l’action avant tout appel serveur.

## Mise à jour du 25 août 2026 — catalogue POS progressif

- L’ouverture du point de vente ne charge plus tous les produits disponibles dans le HTML initial. Le catalogue est chargé par pages de 24 produits afin de garder une interface rapide lorsque chaque compagnie possède un catalogue important.
- L’API `products.search` est protégée par l’authentification, la compagnie active et la permission Produits. Elle applique automatiquement l’isolation tenant, accepte la recherche par nom et le filtre de catégorie, puis renvoie uniquement les champs utiles à l’interface.
- Les catégories utilisent maintenant des compteurs SQL des produits actifs et disponibles au lieu de charger leurs collections complètes.
- La recherche attend 300 ms après la frappe avant d’interroger le serveur. Le bouton « Charger plus » affiche le loader global et empêche les doubles clics pendant la requête.
- L’ajout au panier, les prix TTC, les images de remplacement et les filtres par catégorie sont conservés avec les cartes chargées dynamiquement.
- Après une vente et la fermeture du reçu, la page courante du catalogue est rechargée avec le filtre et la recherche actifs. Les quantités sont donc resynchronisées sans recharger toute la page ; un produit épuisé disparaît du catalogue.
- `QueryOptimizationTest` couvre la pagination 24/6, la recherche, le prix de vente calculé, l’absence de `company_id` dans la réponse et l’isolation entre compagnies.
- Nouvelle référence de non-régression : **105 tests réussis, 603 assertions**.
- Tests manuels : ouvrir le POS avec plus de 24 produits, utiliser « Charger plus », rechercher rapidement un produit, filtrer une catégorie, ajouter au panier depuis chaque vue, finaliser une vente puis fermer le reçu et contrôler la nouvelle quantité. Refaire enfin le contrôle après un changement de compagnie.
- Prochaine phase recommandée : rendre progressifs les sélecteurs volumineux du POS, notamment la recherche de clients, puis mesurer les requêtes lentes sur des volumes proches de la production. Redis reste reporté.

## Mise à jour du 25 août 2026 — interaction visuelle produit vers panier

- Un clic sur une carte produit crée désormais une copie visuelle légère de son image qui suit une trajectoire courbe vers le compteur du panier en 480 ms.
- Le compteur réagit brièvement à l’arrivée de l’image. L’animation ne bloque ni l’ajout au panier ni les clics rapides et chaque copie temporaire est supprimée à la fin de son trajet.
- L’animation est automatiquement désactivée lorsque l’utilisateur demande la réduction des mouvements dans son navigateur ou son système. Les navigateurs trop anciens sans Web Animations conservent simplement le comportement fonctionnel précédent.
- Ajustement visuel : la vignette ombrée passe au-dessus de toute l’interface, suit une trajectoire plus haute et plus lisible, puis vise l’onglet « Commande » sur ordinateur ou le bouton flottant du panier sur mobile.
- Le départ est matérialisé par une petite impulsion exactement aux coordonnées du clic ou du toucher. La vignette naît à cet endroit à une échelle presque nulle, se déploie, puis rejoint le panier ; le centre de l’image reste la solution de repli pour un déclenchement sans pointeur.
- Correctif mobile : les coordonnées sont capturées dès `pointerdown`/`touchstart`, car certains navigateurs mobiles fournissent un `click` final sans position exploitable. En mode « mouvements réduits », une transition courte de 320 ms reste visible au lieu de supprimer totalement le retour visuel.
- Le flux de vente reste validé par **13 tests réussis, 36 assertions**. Test manuel : cliquer rapidement sur plusieurs produits, vérifier que chaque image se dirige vers « Commande », que les quantités du panier restent exactes et que l’interface demeure fluide sur mobile.

## Mise à jour du 25 août 2026 — panier POS persistant

- Le panier courant est enregistré dans `localStorage` sous une clé versionnée et isolée par identifiant utilisateur **et** identifiant compagnie. Un changement d’entreprise ou de compte ne peut donc pas afficher le panier d’un autre contexte.
- Les produits, quantités, prix affichés, images, remise, code promo et client sélectionné sont restaurés après une actualisation de la page ou la réouverture du POS sur le même navigateur.
- Le stockage est synchronisé après chaque ajout, incrément, décrément, saisie de quantité, suppression, changement de remise, code promo ou client. Un panier devenu vide supprime sa clé locale.
- Une vente acceptée par le serveur efface immédiatement le panier persistant avant l’affichage du reçu. Une erreur serveur conserve au contraire le panier pour éviter toute perte de saisie.
- Les commandes mises en attente existantes utilisent désormais elles aussi une clé séparée par utilisateur et compagnie, corrigeant un risque d’affichage inter-compagnies dans le navigateur.
- La restauration contrôle les identifiants, prix et quantités et échappe les données textuelles avant de reconstruire l’interface. Le serveur reste l’autorité finale sur le stock, les prix et la validité de la vente.
- Référence complète conservée : **105 tests réussis, 603 assertions**.
- Tests manuels : créer un panier avec client/remise/code promo, modifier les quantités, actualiser la page, fermer/réouvrir le POS, changer de compagnie puis revenir, supprimer un article et enfin terminer une vente. Vérifier que le panier est restauré uniquement dans sa compagnie et qu’il reste vide après la vente réussie.

## Mise à jour du 25 août 2026 — recherche progressive des clients au POS

- La page du point de vente ne charge plus la totalité des clients dans son HTML initial. Le sélecteur Select2 appelle désormais `clients.search` avec un délai de 300 ms et récupère des pages de 20 clients.
- L’API est placée derrière l’authentification, la résolution de compagnie et la permission `sales.manage`. Elle autorise le flux de vente sans exiger séparément la permission de gestion des clients et applique le scope de la compagnie active.
- La réponse ne contient que `id` et `text`. La recherche accepte au maximum 100 caractères et la restauration ciblée d’un client mémorisé ne peut pas récupérer un client d’une autre entreprise.
- Le panier persistant et les commandes mises en attente enregistrent maintenant le nom du client en plus de son identifiant. La sélection peut ainsi être restaurée immédiatement avant même une nouvelle recherche réseau ; les anciennes sauvegardes sans nom utilisent une résolution ciblée sécurisée.
- L’ancien `Client::get()` exécuté à chaque ouverture du POS est supprimé du contrôleur et la boucle complète des options est supprimée de la vue.
- `QueryOptimizationTest` couvre les pages 20/5, la recherche, l’indicateur de page suivante et le refus silencieux d’un identifiant client étranger.
- Nouvelle référence complète : **106 tests réussis, 612 assertions**.
- Tests manuels : ouvrir le sélecteur, saisir quelques lettres, faire défiler plus de 20 résultats, sélectionner un client, actualiser la page et contrôler sa restauration. Changer ensuite de compagnie et vérifier que les clients de la première ne sont jamais proposés.
- Prochaine phase recommandée : instrumentation des requêtes lentes et scénario de charge réaliste, puis finalisation des opérations O2switch (queue, cron, sauvegarde/restauration, SMTP et supervision), sans Redis pour le premier déploiement.

## Mise à jour du 25 août 2026 — instrumentation des requêtes lentes

- `config/performance.php` permet d’activer un moniteur SQL léger avec un seuil configurable. Le modèle O2switch active un seuil initial de 300 ms sans Redis ni dépendance supplémentaire.
- Les requêtes dépassant le seuil vont dans le canal quotidien `performance`, fichier `storage/logs/slow-queries-AAAA-MM-JJ.log`, avec une rétention de 14 jours. Le journal principal n’est pas encombré.
- Chaque événement contient durée, connexion, méthode, route, chemin, compagnie et utilisateur disponibles. La structure SQL est limitée à 1 200 caractères et les bindings/valeurs saisies ne sont jamais enregistrés par le moniteur.
- Le fonctionnement a été vérifié localement en forçant temporairement le seuil à 0 ms sur `migrate:status` : les requêtes ont bien été écrites dans le journal séparé. La configuration locale `.env` n’a pas été modifiée.
- Le test de volume initial crée 60 clients puis ouvre le POS : aucune requête vers la table `clients` n’est exécutée tant que le sélecteur n’est pas utilisé. Les tests dédiés comptent désormais 9 scénarios et 45 assertions.
- Le guide `docs/DEPLOIEMENT_O2SWITCH.md` décrit l’activation, le fichier à surveiller et la règle d’analyse avec `EXPLAIN` avant tout nouvel index.
- Nouvelle référence complète : **107 tests réussis, 616 assertions**.
- Le guide O2switch peut continuer à être complété sur le plan documentaire, mais toute validation ou opération réelle sur l’hébergement est reportée jusqu’au signal explicite du propriétaire.
- Prochaine action locale recommandée : poursuivre les tests de volume et l’optimisation des écrans métier restants, puis auditer les notifications et tâches asynchrones sans effectuer de déploiement.

## Mise à jour du 25 août 2026 — fiabilité locale des notifications

- Les cinq jobs de notification (`SendSaleEmailJob`, vente WhatsApp/SMS, inventaire WhatsApp/SMS, alerte de marge et commande E-commerce) partagent maintenant une politique bornée : 3 tentatives, délai maximal de 120 secondes, échec sur dépassement et reprises après 60 puis 300 secondes.
- `SmsService` impose 5 secondes maximum pour la connexion, 20 secondes pour les envois SMS/WhatsApp et 30 secondes pour l’envoi d’un document. Un fournisseur lent ne peut plus bloquer indéfiniment un worker.
- Les journaux applicatifs ne contiennent plus le numéro, l’adresse e-mail, le contenu complet du message ni la réponse brute du fournisseur pour ces flux. Les diagnostics reposent sur compagnie, événement, utilisateur, statut HTTP et motif synthétique.
- L’alerte de stock n’est plus envoyée après chaque vente lorsque le produit est déjà sous sa marge de sécurité. Elle part uniquement lorsque la quantité passe de strictement au-dessus du seuil à une valeur égale ou inférieure au seuil.
- Un test de vente franchit le seuil puis effectue une seconde vente sous le seuil et confirme qu’un seul `SendMarginEmailJob` est produit. Cinq tests unitaires contrôlent la politique de queue de chaque job.
- Nouvelle référence complète : **113 tests réussis, 639 assertions**.
- Tests manuels : placer un produit juste au-dessus de sa marge, vendre jusqu’au seuil et vérifier une alerte, vendre encore et confirmer l’absence d’une seconde alerte. Contrôler ensuite que les notifications normales de vente et d’inventaire arrivent toujours aux destinataires configurés.
- Prochaine amélioration locale recommandée : ajouter un registre de livraison idempotent par événement/canal/destinataire afin qu’une reprise de job ne renvoie jamais une notification déjà délivrée, tout en permettant de relancer uniquement les échecs.

## Mise à jour du 25 août 2026 — activation globale des e-mails

- La page `Communications > SMS & WhatsApp > Configuration` propose « Autoriser les e-mails » dans les deux catégories Ventes et Inventaire, au même niveau que WhatsApp et SMS. La route reste protégée par `notifications.manage`.
- La migration `2026_08_25_110000_add_email_notification_channels_to_companies.php` ajoute `sale_email_enabled` et `inventory_email_enabled`. Les deux valeurs sont actives par défaut afin de préserver le comportement des compagnies existantes ; la migration a été appliquée localement.
- `SendSaleEmailJob` quitte immédiatement sans charger les destinataires lorsque les e-mails de vente sont désactivés.
- `SendMarginEmailJob` et la commande `inventory:weekly-report` respectent l’activation globale des e-mails d’inventaire. Les commandes E-commerce restent volontairement gérées par leurs managers et ne sont pas modifiées.
- L’activation globale et le choix individuel du destinataire sont cumulatifs : un e-mail part uniquement si le canal de la catégorie est autorisé **et** si l’utilisateur possède la case E-mail cochée dans cette catégorie.
- Tests ajoutés : blocage des destinataires vente/inventaire lorsque les canaux e-mail sont désactivés, et absence de rapport hebdomadaire lorsque le canal inventaire est coupé.
- Nouvelle référence complète : **115 tests réussis, 646 assertions**.
- Test manuel : dans Ventes, désactiver « Autoriser les e-mails » en laissant un destinataire coché, effectuer une vente et vérifier l’absence d’e-mail ; réactiver puis vérifier la réception. Refaire le principe pour Inventaire et une alerte de marge.

## Mise à jour du 25 août 2026 — finalisation locale : livraisons idempotentes et transactions

- La migration appliquée `2026_08_25_120000_create_notification_deliveries_table.php` ajoute un registre de livraison indexé par compagnie, type/clé d’événement, canal et utilisateur. Cette combinaison est unique en base.
- `NotificationDeliveryService` marque chaque tentative `processing`, `sent` ou `failed`, conserve le nombre d’essais et uniquement la classe d’erreur. Une livraison `sent` n’exécute plus jamais son expéditeur lors d’une reprise ; une livraison `failed` reste relançable.
- Le registre couvre les e-mails de vente, WhatsApp/SMS de vente, WhatsApp/SMS d’inventaire, alertes de marge, commandes E-commerce et rapports hebdomadaires. Tous les destinataires sont traités avant qu’un job contenant des échecs soit remis en file ; les succès ne seront pas doublés au prochain essai.
- Avant chaque livraison, le service revérifie l’adhésion active du destinataire. Les cinq jobs ignorent également une compagnie devenue inactive. Un même identifiant d’événement reste indépendant entre deux compagnies.
- La commande `notifications:clean-deliveries --days=180` supprime les anciens états après 180 jours. Elle est planifiée chaque dimanche à 23 h 30 avec protection contre le chevauchement et propose `--pretend` pour un contrôle sans suppression.
- Le tableau des transactions charge caisse source, caisse cible et utilisateur en lots, supprimant les N+1. Ses quatre compteurs, quatre montants et le solde net proviennent désormais d’une seule agrégation SQL au lieu de sept requêtes.
- Le scénario de volume crée 80 transactions et confirme une seule requête d’agrégation sans `SELECT *`. Les tests de livraison couvrent succès unique, échec puis reprise, isolation entre deux compagnies, membre révoqué et rétention.
- Audit local relancé après migrations : **2 compagnies, 0 relation inter-compagnies détectée, 0 `company_id` nul, invariants métiers à zéro anomalie**. Les migrations 15, 16 et 17 sont appliquées.
- Nouvelle référence complète : **121 tests réussis, 667 assertions**.
- Tests manuels : provoquer une notification réussie puis relancer le même job et vérifier l’absence de doublon ; simuler un échec fournisseur puis corriger et relancer ; vérifier les compteurs du tableau Transactions ; exécuter `php artisan notifications:clean-deliveries --days=180 --pretend`.
- Travaux locaux critiques terminés. Restent volontairement hors périmètre jusqu’au signal du propriétaire : déploiement O2switch, configuration réelle des fournisseurs et conception des abonnements.

## Mise à jour du 25 août 2026 — optimisation SQL, lot 3

- Les tableaux Clients, Fournisseurs, Catégories, Codes promotionnels et Caisses chargent désormais leur créateur en lot. Le nombre de requêtes ne grandit plus avec le nombre de lignes affichées (suppression des N+1 sur `user`).
- Le tableau de bord général et le tableau de bord comptable calculent chiffre d’affaires, remise et bénéfice dans une seule agrégation sur `sales`, au lieu d’exécuter une seconde somme pour le bénéfice.
- La page des caisses calcule les compteurs et soldes totaux, actifs et inactifs avec une seule requête conditionnelle. Une seconde requête récupère ensemble la caisse principale et la caisse de taxe : le résumé utilise donc exactement deux requêtes, indépendamment du volume.
- La migration `2026_08_25_130000_add_tenant_listing_performance_indexes.php` ajoute les index `(company_id, status, created_at)` aux tables `cash_accounts`, `categories`, `clients`, `code_promos` et `suppliers`. Ces index accélèrent l’isolation, le filtre de statut et le tri des listes récentes pour chaque compagnie.
- `QueryOptimizationTest` impose désormais une seule agrégation des ventes, vérifie l’absence de N+1 sur une page de clients et plafonne le résumé des caisses à deux requêtes.
- Nouvelle référence complète : **123 tests réussis, 674 assertions**.
- À la mise en production seulement, surveiller le journal des requêtes lentes avec des volumes réels et utiliser `EXPLAIN` dans phpMyAdmin avant tout index supplémentaire. Redis reste inutile pour ce lot.

## Mise à jour du 25 août 2026 — benchmark MySQL à gros volume

- Un benchmark séparé, explicitement exclu de la suite quotidienne, est disponible dans `benchmarks/SaasVolumeBenchmark.php`. Il refuse toute base dont le nom ne se termine pas par `_testing`.
- Volume validé : 5 compagnies, 50 utilisateurs, 10 000 produits, 5 000 clients, 50 000 ventes, 100 000 lignes de vente et 10 000 commandes. Pic PHP : 96 Mo.
- Les sept parcours mesurés restent sous 0,8 seconde lors du passage diagnostique final et sous 14 requêtes : tableau de bord 781 ms, POS 773 ms, produits 94 ms, clients 17 ms, utilisateurs 17 ms, commandes 14 ms et historique 696 ms. Un passage à froid peut atteindre environ 1,4 seconde sur les agrégations.
- Les filtres `DATE(created_at)` du POS ont été remplacés par des plages indexables. Les agrégations de bénéfice ont été fusionnées, réduisant le POS de 15 à 14 requêtes et l’historique de 11 à 10.
- La migration appliquée `2026_08_25_140000_add_sale_detail_period_performance_index.php` ajoute l’index `(company_id, created_at, product_id)` sur `sale_details`.
- Rapport consolidé : `docs/RAPPORT_GLOBAL_SAAS.md`.

## Mise à jour du 26 août 2026 — vrais exports Excel XLSX

- Laravel Excel `maatwebsite/excel` 3.1.70 est installé et l'extension PHP `zip` est active en local.
- `StreamingTabularExport` ne produit plus du XML Spreadsheet : Produits, Inventaire et Historique des ventes téléchargent maintenant de vrais fichiers `.xlsx`.
- Les requêtes restent parcourues avec `cursor()` et un export `FromGenerator`; la neutralisation des formules dangereuses est conservée.
- Le test `StreamingTabularExportTest` contrôle le nom `.xlsx` et la signature ZIP `PK` des trois classeurs : **1 test, 20 assertions, 0 échec**.
- À vérifier lors du futur déploiement O2switch : activer l'extension PHP `zip` avant `composer install`.
- Les commandes PDF/CSV/Excel des pages Produits, Inventaire et Historique des ventes sont maintenant rangées dans des accordéons responsives. Les boutons sont pleine largeur et empilés sur mobile, puis répartis en trois colonnes à partir de `sm`.

## Mise à jour du 26 août 2026 — fin de la dépendance métier à `users.user_type`

- La connexion vérifie maintenant uniquement le statut global du compte. Les accès et la destination après connexion proviennent de l’adhésion et du rôle de la compagnie active ; `users.user_type` n’intervient plus.
- Un compte désactivé est refusé quel que soit son ancien type. Un compte actif reste connectable même si sa valeur historique est inconnue.
- Les statistiques du POS adaptent leurs colonnes à la permission `reports.view_margin`, et non à l’ancien type d’utilisateur.
- La création par inscription, invitation ou ajout depuis la liste n’écrit plus de rôle global. Une modification d’utilisateur met à jour uniquement `company_user.role_id` pour la compagnie active.
- La colonne DataTables a été renommée `role_name` afin de ne plus présenter le rôle actif sous le faux nom `user_type`.
- Migration locale appliquée : `2026_08_26_120000_make_legacy_user_type_nullable.php`. La colonne reste temporairement en base pour compatibilité historique, mais devient facultative et n’est plus remplie par les nouveaux flux.
- Nouvelle référence complète : **131 tests, 729 assertions, 0 échec**. Avancement fonctionnel SaaS réévalué à **96 %** ; utilisateurs/rôles/permissions à **97 %**.

## Mise à jour du 26 août 2026 — sélecteurs Inventaire progressifs

- L’ouverture de la page Inventaire ne charge plus tous les produits et fournisseurs de la compagnie.
- Les sélecteurs Produit et Fournisseur du filtre et de la modale d’entrée utilisent désormais Select2 avec recherche serveur, temporisation de 250 ms et pagination de 20 résultats.
- Le sélecteur de la modale de sortie applique en plus `qte > 0`, afin de ne proposer que les produits réellement disponibles.
- Deux routes protégées par `inventory.manage` ont été ajoutées : `inventory.products.search` et `inventory.suppliers.search`. Les scopes de la compagnie active restent appliqués aux deux requêtes.
- Le test de volume confirme la pagination, le filtre de stock et l’absence des collections complètes dans le HTML initial.
- Nouvelle référence complète : **132 tests, 740 assertions, 0 échec**.

## Mise à jour du 26 août 2026 — catalogue E-commerce progressif

- Une catégorie publique n’envoie plus tous ses produits : elle est paginée à 12 articles avec conservation des paramètres d’URL.
- La page « Tous nos produits » propose une recherche responsive par nom et conserve cette recherche pendant la pagination.
- Les listes publiques Produits chargent leur catégorie en lot, supprimant le N+1 associé à l’affichage des cartes.
- Le checkout ne fait plus de `pluck()` de toutes les images du catalogue. Il utilise l’image déjà enregistrée dans le panier local et le placeholder pour les anciens paniers incomplets.
- Les menus de catégories sélectionnent uniquement `id` et `name`, triés par nom.
- Le nouveau test couvre 14 produits, la seconde page, la recherche et l’absence du catalogue dans le HTML du checkout.
- Nouvelle référence complète : **133 tests, 751 assertions, 0 échec**. E-commerce : **94 %** ; performance locale et qualité : **96 %**.

## Mise à jour du 26 août 2026 — composition des menus progressive et tenantée

- Les écrans de création et modification d’un menu ne chargent plus tous les produits simples dans leur HTML.
- Chaque ligne de composition utilise Select2 et la route paginée `menu.products.search`, avec 20 résultats, recherche temporisée et scope de la compagnie active.
- Les produits déjà associés restent préselectionnés lors de la modification sans reconstruire toute la liste.
- Les validations de création et modification imposent désormais que la catégorie et chaque produit composant soient actifs, du bon type et rattachés à la compagnie active. Une composition inter-compagnies est rejetée avant écriture.
- La liste des menus charge les catégories en lot afin d’éviter le N+1.
- Nouveau test : pagination sur 25 composants, HTML initial allégé et rejet d’un produit étranger.
- Nouvelle référence complète : **134 tests, 759 assertions, 0 échec**.

## Mise à jour du 26 août 2026 — KPrimePay et achat de quotas

- L’ancienne modification manuelle gratuite des compteurs a été supprimée de l’interface. La page permet maintenant d’acheter des SMS à 35 FCFA et des messages WhatsApp à 30 FCFA.
- Permission dédiée `quota.manage`, visible en français dans la configuration des rôles et attribuée automatiquement aux propriétaires/administrateurs existants par la migration `2026_08_26_130000_create_quota_payments_and_permission.php`.
- Le checkout KPrimePay v2 utilise bearer token serveur, `Idempotency-Key`, montant recalculé côté serveur et redirection vers l’URL hébergée.
- Webhook public : `POST /api/kprimepay/webhook`. Le contrôleur accepte le format V2 et le format V1 actuellement émis (`payment.web.checkout`). Un retour navigateur ne crédite rien. Tout succès est reconfirmé avec `/v2/transactions/debit-status`, puis montant/devise/statut sont comparés avant un crédit SQL atomique.
- `transaction_id`, `idempotency_key` et `event_id` empêchent les doublons. La V1 ne fournissant pas d'`event_id`, le contrôleur génère une empreinte SHA-256 stable à partir de la transaction et du paiement. Un même webhook rejoué ne crédite pas deux fois.
- La clé fournie dans la conversation est considérée exposée et n’a pas été enregistrée. Elle doit être régénérée avec `payments:write` et `read`.
- Documentation : `docs/GUIDE_KPRIMEPAY.md`.
- Expérience PWA KPrimePay : le checkout s’ouvre désormais dans une fenêtre séparée créée directement par le clic utilisateur. La page Quotas reste ouverte, garde le bouton bloqué avec son loader, surveille le statut local toutes les 3 secondes, ferme la fenêtre et se recharge après confirmation `paid`. Une redirection complète reste disponible si les pop-ups sont bloquées. Ne jamais remplacer ce contrôle local par une confiance dans la seule URL de retour.
- Identité des e-mails : ventes, stock, inventaire hebdomadaire, invitations, accès utilisateur et commandes e-commerce présentent désormais la compagnie concernée comme nom d’expéditeur visible et dans le footer. L’adresse SMTP authentifiée reste celle de la plateforme pour préserver la délivrabilité. Le nom de l’application apparaît uniquement dans le copyright dynamique du footer partagé ; l’ancienne année fixe et la signature personnelle ont été supprimées.

# Administration centrale SaaS

La référence active de la console plateforme est `docs/RAPPORT_ADMINISTRATION_SAAS.md`. Elle est séparée des rôles `owner` et `admin` des compagnies et ne doit jamais reposer uniquement sur le champ historique `users.user_type`.

Toute l’implémentation et son avancement sont désormais regroupés dans un seul document permanent : `docs/RAPPORT_ADMINISTRATION_SAAS.md`. Ne plus créer de rapport distinct par phase. Après chaque modification de la partie administrative, mettre à jour dans ce document la date, la fonctionnalité concernée, les contrôles de sécurité, les tests réalisés et, si nécessaire, les instructions de déploiement.

État de référence au 28 août 2026 : garde `platform`, connexion dédiée, tableau de bord global, gestion et consultation des entreprises et utilisateurs, paiements et quotas KPrimePay, tarification et rentabilité SMS/WhatsApp, journal d’audit, santé du système, gestion des administrateurs et rôles Super-administrateur, Support, Finance et Technique. Les permissions protègent les menus et les routes. Dernière suite complète connue : **185 tests, 1 109 assertions, 0 échec**.

Sécurité plateforme ajoutée le 28 août 2026 : double authentification par code e-mail hashé et expirant, renvoi limité, récupération du mot de passe par lien hashé à usage unique, invalidation des anciennes sessions, activation/désactivation individuelle et réinitialisation 2FA auditées depuis **Administrateurs**. Migration : `2026_08_28_200000_add_security_to_platform_admins.php`. Le suivi détaillé reste exclusivement dans `docs/RAPPORT_ADMINISTRATION_SAAS.md`.

Alertes d’exploitation ajoutées le 28 août 2026 : commande `platform:check-alerts` toutes les cinq minutes, seuils configurables, destinataires plateforme, e-mails anti-spam, historique, prise en charge et résolution. Migration : `2026_08_28_210000_create_platform_operational_alerts.php`. Le cron O2switch `schedule:run` reste indispensable ; prévoir une surveillance externe pour détecter immédiatement son arrêt total.

Communications globales ajoutées le 28 août 2026 : accès super-admin/technique, statistiques e-mail/SMS/WhatsApp, filtres SQL, pagination, destinataires masqués, consommation par entreprise, exports CSV/XLSX et relances atomiques/auditées uniquement pour les événements reconstruisibles sans risque avec les jobs existants.

Paramètres généraux ajoutés le 28 août 2026 : identité et logo, support, valeurs par défaut, état masqué des configurations externes, activation réelle e-mail/SMS/WhatsApp/KPrimePay, délais invitation/2FA/paiement et maintenance applicative excluant console/API. Les composants Blade manquants ont été restaurés et `php artisan view:cache` passe.

Accès : `/admin-saas` ou `/platform/login`. Le même e-mail peut ouvrir une entreprise via la connexion POS ; les deux gardes restent volontairement séparées.

## Mise à jour du 31 août 2026 — refonte frontend propriétaire

- Référence UI/UX unique : `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md`.
- Préférences personnelles ajoutées aux utilisateurs : `appearance_mode` (`system`, `dark`, `light`) et `accent_color`; migration `2026_08_31_140000_add_appearance_preferences_to_users_table.php`.
- L’onglet **Apparence** du profil enregistre la préférence côté serveur. Le socle est dans `design-system.css`, `design-system.js` et `partials/design-system-head.blade.php`.
- Nouveau frontend propriétaire commencé avec `layouts.saas`, `partials/saas-sidebar.blade.php`, `partials/saas-topbar.blade.php`, `saas-shell.css` et `saas-shell.js`.
- Seul `dashboard.blade.php` utilise actuellement le nouveau shell. Les autres modules restent volontairement sur l’ancien layout jusqu’à validation manuelle du pilote.
- Ne pas réintroduire `app.min.css` dans `layouts.saas`. `vendor.min.css` reste provisoirement chargé pour la grille, les utilitaires et les icônes, sans utiliser les composants visuels de l’ancien template.
- Le menu du nouveau shell doit continuer à filtrer chaque lien par permission de la compagnie active.
- Le tableau de bord pilote anime sobrement les cartes et icônes au survol et fournit un retour pressé tactile. Préserver `prefers-reduced-motion` et éviter toute animation bloquante ou permanente hors interaction.
- Contrôles ciblés : permissions/isolation **8 tests, 31 assertions** ; performance dashboard **1 test, 3 assertions** ; préférences d’apparence **2 tests, 12 assertions**.
- Le profil utilisateur est le deuxième écran migré vers `layouts.saas`. Il n’utilise plus jQuery pour ses trois opérations et conserve les contrats serveur existants. Référence ciblée : `ProfileSecurityTest`, **7 tests, 35 assertions**.
- Dans l’onglet Apparence, le mode et la couleur sont des accordéons exclusifs : une seule section est ouverte à la fois et chaque résumé indique la préférence courante.

## Mise à jour du 1er septembre 2026 — refonte du point de vente

- `resources/views/pos/sale/index.blade.php` utilise désormais `layouts.saas` avec une interface POS plein écran dédiée. Le catalogue, les catégories, le panier, les commandes en attente, les statistiques du jour, les modales et les états vides ont été adaptés au design system.
- La feuille dédiée est `public/hub/assets/css/saas-pos.css` (version de cache actuelle `20260901-33`). Conserver le glassmorphisme léger, le contraste clair/sombre, `prefers-reduced-motion` et les grilles responsives : cinq produits sur grand écran et deux sur mobile.
- Le panier mobile fonctionne comme un tiroir et l’animation d’ajout part du point exact du clic/toucher vers le panier visible. Le brouillon de commande et le client sélectionné restent gérés par le stockage local existant.
- Le catalogue progressif, les filtres par catégorie, la recherche, les détails de ventes, le reçu PDF, les envois de facture et toute la logique métier existante doivent rester intacts. Ne pas réintroduire de modal produit inexistant ni de gestionnaires de clic dupliqués.
- Les scripts propres au POS sont placés dans `@push('scripts')` afin d’être exécutés après les dépendances. Le chargement du détail d’une vente utilise `window.ServerButtonLoader.withLoader` et restaure le bouton en cas d’erreur.
- L’onglet **Produits vendus** masque le catalogue, affiche les statistiques et le tableau ; le retour à **Commande** restaure l’en-tête, la recherche et les produits.
- Le panier desktop utilise une colonne élargie, un corps produit seul défilable et un récapitulatif fixe. Les boutons segmentés **Commande** et **Produits vendus** sont côte à côte. `#clientSelect` est hors de la zone défilante, reste fixe sous ces boutons dans Commande et disparaît dans Produits vendus. Le tiroir mobile masque son bouton flottant pendant l’ouverture afin de ne jamais recouvrir **Vendre**. Ne pas replacer le client dans `#newOrderTab`.
- Les derniers artefacts de l’ancien template (`card-arrow`, graphiques Apex vides et conteneur de démonstration `hljs`) ont été retirés le 1er septembre. Ils ne doivent pas être recréés.
- Le POS possède maintenant ses composants autonomes pour les modales Bootstrap, SweetAlert et DataTables : fond occultant, contenu structuré, header/footer fixes, corps défilable, états de chargement/vides, pagination et adaptation plein écran sur mobile. Ne pas réintroduire les styles visuels du template historique dans ces composants.
- Les fenêtres **Commandes en cours**, **Détail de la vente** et **Aperçu du reçu** suivent le même contrat visuel. Le détail ancien reçu par AJAX est normalisé par le CSS du POS ; le reçu utilise une grille dédiée pour les destinataires et les canaux. À l’ouverture, le focus va sur le premier contrôle de fermeture ; à la fermeture, il revient au déclencheur. Préserver ce comportement clavier.
- Le journal DataTables possède une enveloppe locale `.pos-datatable-shell` : la barre de recherche, le nombre de lignes, le tableau, l’état de traitement et la pagination doivent conserver ce même langage visuel. Sur petit écran, seul le tableau défile horizontalement ; la page ne doit jamais créer de débordement horizontal global.
- Les quatre indicateurs de l’activité utilisent une grille 4 colonnes sur bureau et 2 × 2 sur tablette/mobile. Ne pas les recompacter sur une seule ligne à 390 px.
- Le tiroir du panier impose explicitement ses transformations fermé/ouvert/mobile et `none` sur bureau. Ces priorités corrigent les conflits du CSS historique lors des changements de largeur ; ne pas les retirer sans tester réellement l’ouverture et la fermeture à 390 px.
- Contrôle final du 1er septembre : 1440, 1024, 768 et 390 px vérifiés sans débordement global ; catalogue, recherche avec état vide, filtres catégorie, ajout/quantités réversibles, tiroir, onglets, commandes en cours, détail, reçu, SweetAlert et retour de focus testés. Aucune vente, impression, suppression ou expédition de facture réelle n’a été déclenchée.
- Avant toute nouvelle modification, vérifier au minimum les deux largeurs 1440 px et 390 px dans le navigateur intégré, puis exécuter `php artisan view:cache`, `git diff --check` et le test ciblé `php artisan test --filter=CatalogSaasUiTest`. Ne jamais effectuer une vente réelle pendant un simple contrôle visuel sans accord explicite.
- La session navigateur peut expirer et rediriger vers `/user_login`. Ne jamais écrire les identifiants de test dans ce document ou dans le code ; demander une confirmation au moment de toute saisie sensible.

## Mise à jour du 1er septembre 2026 — finalisation du Catalogue SaaS

- Le périmètre Catalogue couvert est : **Produits, Catégories, Menus et Fournisseurs**, avec listes actives/archivées, filtres, exports existants, formulaires, détails AJAX, états de traitement/vides et responsive.
- La feuille commune `public/hub/assets/css/saas-pages.css` est en version de cache `20260901-9`. Elle porte le contrat unique des pages CRUD : en-têtes, cartes, boutons, formulaires, Select2, SweetAlert, DataTables, détails et fenêtres.
- Correction fonctionnelle impérative : les scripts DataTables et Select2 de Produits, Menus et Fournisseurs doivent rester dans `@push('scripts')`. Les replacer dans le contenu exécuterait leurs callbacks après le remplacement de jQuery par `vendor.min.js` et casserait les tableaux ou Select2.
- Les DataTables doivent conserver leur enveloppe locale : recherche et longueur au-dessus, tableau avec scroll horizontal interne sur mobile, pagination et information sous le tableau, traitement visible et états vides en français. Aucun tableau ne doit élargir la page entière.
- Le loader DataTables neutralise explicitement les positions inline de la bibliothèque (`top`, `left`, `width`, marges et transformation). Il reste centré dans la carte active et archivée à 390 px comme sur desktop, puis disparaît après la réponse AJAX. Ne pas retirer ces priorités sans observer le chargement réel avant la réponse serveur.
- Depuis la généralisation du 1er septembre, ce loader ne réside plus dans `saas-pages.css` : sa source unique est `public/hub/assets/css/datatable-loading.css`, chargée par `partials/design-system-head.blade.php`. Elle couvre DataTables 1 (`.dataTables_processing`) et DataTables 2 (`.dt-processing`) dans `layouts.saas`, `layouts.layout` et `layouts.layout_sale`. `design-system.js` francise les libellés anglais, ajoute `role=status`/`aria-live=polite` et conserve les libellés métier déjà traduits. Ne recréer aucun loader local dans un module.
- Les scripts DataTables/Select2 de **Clients** et **Inventaire** ont également été déplacés dans `@push('scripts')`, comme ceux du Catalogue, afin qu’ils s’attachent à la copie jQuery définitive du shell. Cette position est couverte par le test de régression et ne doit pas être inversée.
- Les fenêtres sont autonomes vis-à-vis de l’ancien template : overlay fixe, largeur `sm/md/lg/xl` contrôlée, centrage desktop, scroll du corps, fermeture visible, focus initial et restitution au déclencheur. Sous 768 px, toutes les tailles deviennent strictement plein écran et les formulaires passent en une colonne.
- Les détails Produit, Menu et Fournisseur utilisent `saas-detail-hero` et `saas-detail-list`. Les anciens tableaux rayés, `card-arrow` et `hljs-container` ont été supprimés et ne doivent pas être réintroduits.
- Contrôle navigateur final : les quatre listes s’initialisent sans erreur JavaScript ; Produits affiche 2 DataTables, Catégories 2, Menus 1 et Fournisseurs 2. Desktop 1440 px et mobile 390 × 844 px vérifiés, sans débordement global. Les fenêtres Produit et Menu, le détail Produit, les états vides, le scroll local et le retour de focus ont été inspectés sans créer, modifier, archiver ou restaurer de donnée.
- Contrôles techniques : `php artisan view:cache`, `git diff --check` et `php artisan test --filter=CatalogSaasUiTest`. Référence actuelle : **10 tests, 60 assertions, 0 échec**.

## Mise à jour du 1er septembre 2026 — correction du loader DataTables

- **Problème diagnostiqué** : le loader DataTables global restait affiché après la fin des requêtes AJAX. La cause racine était dans `public/hub/assets/css/datatable-loading.css` : `display: flex !important` sur les sélecteurs `.dataTables_processing` / `.dt-processing` empêchait DataTables de masquer le loader via son style inline `display: none`. L'at-rule `!important` du CSS surclassait toujours l'inline `style`, même après le chargement.
- **Correction appliquée** : tous les sélecteurs `.dataTables_processing` et `.dt-processing` utilisent désormais `:not([style*="display: none"])` avant `display: flex !important`. Le loader n'est en mode flex que tant que DataTables n'a pas explicitement masqué l'élément. Dès que la réponse AJAX arrive, DataTables écrit `style="display: none"` et le sélecteur `:not(...)` ne correspond plus : le loader disparaît proprement.
- **Fichiers modifiés** :
  - `public/hub/assets/css/datatable-loading.css` — version de cache `20260901-2`.
  - `resources/views/partials/design-system-head.blade.php` — le `?v=` du CSS passe de `20260901-1` à `20260901-2` pour bust le cache PWA.
- **Portée** : les deux sélecteurs couvrent DataTables 1 (`.dataTables_processing` dans `.dataTables_wrapper`) et DataTables 2 (`.dt-processing` dans `.dt-container`). Le correctif s'applique dans les trois layouts : `layouts.saas`, `layouts.layout` et `layouts.layout_sale`. Aucun changement dans `design-system.js` ni dans les vues métier.
- **Vérification** : les pages Contrôles, Catégories, Clients, Inventaire, Historique des ventes et Utilisateurs ont été contrôlées (desktop 1440 px, mobile 390 px) via le navigateur intégré. Le loader apparaît pendant le chargement AJAX puis disparaît proprement. Les états vides, la pagination et le tri restent fonctionnels.
- **Tests** : `php artisan view:cache` passé, `git diff --check` propre, `php artisan test --filter=CatalogSaasUiTest` — **10 tests, 60 assertions, 0 échec**.
- **Interdictions** : ne pas retirer le sélecteur `:not([style*="display: none"])` sans réintroduire une autre mécanique permettant à DataTables de masquer le loader. Ne pas ajouter de `display: none !important` sur les sélecteurs de traitement sans risquer de masquer le loader pendant le chargement. Conserver la priorité CSS pour les positions et dimensions sans conflictuer avec l'inline `display` de DataTables.

## Mise à jour du 1er septembre 2026 — refonte Inventaire et Clients

- Périmètre couvert : **Inventaire** (index, détail) et **Clients** (index, modification, détail), avec listes actives/archivées, filtres, exports, formulaires, détails AJAX, états de traitement/vides et responsive.
- **Fichiers modifiés** :
  - `resources/views/component/inventory/index.blade.php` — refonte complète du template.
  - `resources/views/component/inventory/show.blade.php` — remplacement des artefacts legacy par `saas-detail-list`.
  - `resources/views/component/client/index.blade.php` — refonte complète du template.
  - `resources/views/component/client/edit.blade.php` — refonte du formulaire avec `saas-form-group` et `saas-btn`.
  - `resources/views/component/client/show.blade.php` — remplacement des artefacts legacy par `saas-detail-list`.
- **Décisions UI/UX** :
  - Les modales Inventaire conservent leur structure raw (`modal-xl`) car `x-ui.modal` ne supporte pas la variante `xl` nécessaire pour les formulaires larges (Produit + Fournisseur + Quantité). Les classes `saas-modal-content` et `saas-modal-close` sont appliquées pour l'harmonisation visuelle.
  - Les modales Clients utilisent le même pattern raw avec `saas-modal-content` pour les formulaires de taille standard.
  - Les anciens `<div id="loader" class="spinner-grow">` et `<span id="submitText">` ont été remplacés par `data-loading-text` sur les boutons et `ServerButtonLoader.start/stop` dans les gestionnaires AJAX.
  - Les `<div class="card-arrow">` et `<div class="hljs-container">` des vues show ont été supprimés et remplacés par le composant `saas-detail-list` avec `dt`/`dd` structurés.
  - Les en-têtes de carte reçoivent une description courte (`saas-card-description`) conformément au contrat SaaS.
  - Les messages DataTables `processing`, `zeroRecords`, `emptyTable` et `info` sont entièrement en français.
  - Les SweetAlert d'archivage/restauration utilisent `buttonsStyling: false` et `customClass` avec les classes `saas-swal`, `saas-btn` pour l'harmonisation.
  - Le bouton de modification du client utilise `saas-btn-warning` au lieu de l'ancien `btn-warning` Bootstrap.
  - Les sélecteurs Select2 restent en CDN car le projet ne les embarque pas en local.
- **Comportements à préserver** :
  - Les sélecteurs Produit et Fournisseur de l'Inventaire continuent d'utiliser la recherche serveur paginée (`inventory.products.search`, `inventory.suppliers.search`).
  - Le sélecteur de la modale de sortie applique toujours le filtre `in_stock` pour ne proposer que les produits disponibles.
  - Les deux DataTables Clients (actifs/archivés) réagissent au `datatableUpdated` event pour recharger après modification depuis la modale.
  - Les exports CSV/Excel/PDF continuent d'utiliser `ServerButtonLoader.download` et `window.open`.
  - Les confirmations destructives utilisent le SweetAlert commun avec `showLoaderOnConfirm: true`.
  - Les boutons d'action dans les lignes DataTables (`.view`, `.editModal`, `.archive`, `.restore`) conservent leurs sélecteurs existants.
- **Largeurs testées** : 1440 × 900, 1024 × 768, 768 × 1024, 390 × 844 via le navigateur intégré. Aucun débordement horizontal global détecté.
- **Tests exécutés** :
  - `php artisan view:cache` — **passé**.
  - `php artisan test --filter=CatalogSaasUiTest` — **10 tests, 60 assertions, 0 échec**.
  - `php artisan test --stop-on-failure` — **199 tests, 1189 assertions, 0 échec** (suite complète).
  - `git diff --check` — **propre**.
- **Aucune donnée créée, modifiée, archivée, restaurée ou supprimée** pendant les contrôles visuels. Aucun export déclenché.
- **Travail restant** : les tests UI dédiés Inventaire et Clients n'existent pas encore. Les fenêtres d'édition/modification de l'Inventaire ne sont pas des composants Blade autonomes (le formulaire de modification est chargé par AJAX et le formulaire d'ajout est intégré dans l'index). La modale « Retirer du stock » pourrait bénéficier du composant `x-ui.modal` lorsque celui-ci supportera la variante `xl`.
- **Interdictions** : ne pas réintroduire `card-arrow`, `hljs-container`, les blocs de démonstration ou les anciens styles inline. Ne pas supprimer les sélecteurs `.view`, `.editModal`, `.archive`, `.restore` ni les routes AJAX existantes. Conserver la recherche serveur progressive des sélecteurs Inventaire.

## Mise à jour du 1er septembre 2026 — refonte Comptabilité

- Périmètre couvert : **tableau de bord comptable**, **caisses** (index, ajout, modification, détail), **transactions** (index, détail, ajout), **paramètres AMS** (caisse par défaut, taux de taxe, caisse de taxe).
- **Migration majeure** : les 7 vues du module Comptabilité migrent de `layouts.layout` (ancien template) vers `layouts.saas` (nouveau shell SaaS propriétaire). C'est la première fois que ces écrans quittent l'ancien layout.
- **Fichiers modifiés** :
  - `resources/views/ams/dashboard.blade.php` — refonte complète : `layouts.saas`, `saas-card`, cartes statiques ApexCharts adaptées aux tokens `--ds-*`, `saas-detail-list` pour les paramètres, liste des 20 dernières opérations sans `table-striped` ni `card-arrow`.
  - `resources/views/ams/cash/index.blade.php` — refonte complète : suppression de `drawCallback` qui forçait `background-color: black` et `color: white` sur chaque ligne (violait le contrat DataTables), suppression de `blink-badge`, `card-white-shadow`, `card-arrow`, `hljs-container`, `form-check-input` inline. Scripts DataTables migrés dans `@push('scripts')` avec les plugins responsive.
  - `resources/views/ams/cash/edit.blade.php` — `saas-form-group`, `saas-btn-warning`, `ServerButtonLoader`, suppression du spinner div.
  - `resources/views/ams/cash/show.blade.php` — `saas-detail-list` remplace `card-arrow` + `table-striped`.
  - `resources/views/ams/transaction/index.blade.php` — refonte complète : suppression de `drawCallback` colors, `blink-badge`, `card-white-shadow`. Scripts dans `@push('scripts')`. Balance nette avec `saas-status-badge`.
  - `resources/views/ams/transaction/show.blade.php` — `saas-detail-list` remplace `card-arrow` + `table-striped`.
  - `resources/views/ams/settings/index.blade.php` — `layouts.saas`, `saas-card`, `saas-form-group`, `saas-btn`, `ServerButtonLoader`, suppression de `card-arrow` et spinner.
- **Décisions UI/UX** :
  - Les `drawCallback` des DataTables Cash et Transactions utilisaient `jQuery.css()` pour forcer fond noir/texte blanc sur chaque ligne. Cela violait le contrat section 4.3.1 du cahier des charges (jamais de couleurs injectées via `drawCallback`). Supprimés, le design system统合 les DataTables via `saas-pages.css`.
  - Les `blink-badge` et `blink-btn` (animations CSS clignotantes) ont été supprimés. Les montants sont affichés en texte statique avec typographie `font-weight: 800` pour la lisibilité.
  - Le graphique ApexCharts utilise désormais les tokens `--ds-text-secondary`, `--ds-text-muted` et `--ds-accent` au lieu de codes couleur codés en dur.
  - Les cartes statistiques utilisent `saas-card` avec description et valeur en typographie forte.
  - Les modales utilisent `saas-modal-content`, `saas-modal-close` et `saas-modal-eyebrow`.
  - Les SweetAlert de confirmation utilisent `buttonsStyling: false` et `customClass` avec `saas-swal`.
  - Les scripts DataTables sont placés dans `@push('scripts')` conformément au contrat.
  - Les montants sont formatés avec `number_format()` et alignés à gauche dans les cartes pour la lisibilité.
- **Contraintes financières préservées** :
  - Aucun calcul financier modifié.
  - Aucune transaction réelle créée, validée ou annulée pendant les tests.
  - Les montants, soldes et écritures restent inchangés.
  - La logique de caisse principale/taxe (exclusivité toggle) est conservée.
  - Les routes AJAX (`cash-account.store`, `transaction.store`, `ams.settings.store`) et leurs formats de réponse ne sont pas modifiés.
- **Largeurs testées** : 1440 × 900, 1024 × 768, 768 × 1024, 390 × 844 via le navigateur intégré. Aucun débordement horizontal global détecté.
- **Tests exécutés** :
  - `php artisan view:cache` — **passé**.
  - `php artisan test --filter=CatalogSaasUiTest` — **10 tests, 60 assertions, 0 échec**.
  - `php artisan test --stop-on-failure` — **199 tests, 1189 assertions, 0 échec** (suite complète).
  - `git diff --check` — **propre**.
- **Aucune donnée créée, modifiée, archivée, restaurée ou supprimée** pendant les contrôles visuels.
- **Travail restant** : les tests UI dédiés Comptabilité n'existent pas encore. Le graphique ApexCharts pourrait benefit d'un rechargement thématique dynamique au changement de mode clair/sombre. Le sélecteur daterangepicker pourrait benefit de styles design system.
- **Interdictions** : ne pas réintroduire `card-arrow`, `hljs-container`, `drawCallback` avec `jQuery.css()`, `blink-badge`, `blink-btn`, `card-white-shadow` ou `form-check-input` inline. Ne pas forcer de `display` sur les sélecteurs DataTables qui empêcherait la disparition du loader. Conserver l'exclusivité des toggles caisse principale/taxe.

## Mise à jour du 1er septembre 2026 — refonte E-commerce (administration)

- Périmètre couvert : **paramètres boutique** (settings, slug, logo, managers), **commandes** (index DataTable, détail), **actions** (passer en vente, annuler avec motif).
- **Migration majeure** : les 3 vues admin E-commerce migrent de `layouts.layout` (ancien template) vers `layouts.saas`.
- **Fichiers modifiés** :
  - `resources/views/ecommerce/admin/settings.blade.php` — refonte complète : `layouts.saas`, `saas-card`, `saas-form-group`, `saas-btn`, suppression du `drawCallback` qui forçait fond noir/texte blanc, scripts dans `@push('scripts')`. Le slug check, la copie du lien et la gestion des managers conservent leur logique existante. SweetAlert de changement de slug avec `buttonsStyling: false` et `customClass`.
  - `resources/views/ecommerce/admin/orders.blade.php` — refonte complète : suppression du `drawCallback` colors, `saas-card`, scripts dans `@push('scripts')`. SweetAlert de conversion/annulation avec `saas-swal` et `saas-btn`.
  - `resources/views/ecommerce/admin/order-show.blade.php` — refonte complète : `saas-card`, `saas-detail-list`, `saas-status-badge` pour les statuts (En attente, Confirmée, Passée en vente, Annulée). Tableau des produits avec alignement numérique à droite. Actions « Passer en vente » et « Annuler » avec SweetAlert commun.
- **Boutique publique** : les 8 vues publiques (layout, index, products, product, category, checkout, success, closed) conservent leur propre système de design CSS indépendant (thème clair/sombre, product cards, panier localStorage, recherche live). Ce design est cohérent, conversion-oriented et n'utilise pas le shell SaaS admin. Il n'a pas été modifié.
- **Décisions UI/UX** :
  - Les statuts de commande utilisent `saas-status-badge` avec classes `is-active`/`is-inactive` pour la différenciation visuelle sans couleur unique.
  - Les totaux sont alignés à droite avec `font-weight: 700` pour la lisibilité.
  - Le lien boutique utilise `saas-status-badge` pour l'état En ligne/Hors ligne.
  - Les SweetAlert de confirmation utilisent `buttonsStyling: false` et `customClass` avec `saas-swal`.
  - Les scripts DataTables sont dans `@push('scripts')` avec les plugins responsive.
- **Aucune donnée créée, modifiée ou annulée** pendant les contrôles visuels. Aucune commande réelle passée.
- **Tests exécutés** :
  - `php artisan view:cache` — **passé**.
  - `php artisan test --filter=CatalogSaasUiTest` — **10 tests, 60 assertions, 0 échec**.
  - `php artisan test --stop-on-failure` — **199 tests, 1189 assertions, 0 échec**.
  - `git diff --check` — **propre**.
- **Travail restant** : les tests UI dédiés E-commerce n'existent pas encore. Le layout public pourrait benefit d'une synchronisation des tokens `--ds-*` avec le design system admin pour une cohérence maximale.
- **Interdictions** : ne pas réintroduire `drawCallback` avec `jQuery.css()`, `card-arrow`, `hljs-container` dans les vues admin. Ne pas migrer la boutique publique vers `layouts.saas` (elle est un flux consommateur séparé).

## Mise à jour du 1er septembre 2026 — refonte du module Équipe

- Périmètre couvert : **utilisateurs** (index, modification), **rôles et permissions** (index, création, modification, suppression), **invitations** (envoi, renvoi, révocation), **rattachement d'un utilisateur existant**, **intégration dans une autre compagnie**.
- **Migration majeure** : les 4 vues du module Équipe migrent de `layouts.layout` (ancien template) vers `layouts.saas` (nouveau shell SaaS propriétaire).
- **Fichiers modifiés** :
  - `resources/views/user/index.blade.php` — refonte complète : `layouts.saas`, `saas-card`, `saas-btn`, `saas-status-badge`, `saas-action-group`, `saas-action-btn`, `saas-empty-state`, `saas-modal-content`, `saas-modal-close`, `saas-modal-eyebrow`, `saas-swal`. Suppression du `drawCallback` qui forçait `background-color: black` et `color: white` sur chaque ligne DataTables. Suppression de `card-arrow`, `blink-badge`. Scripts DataTables migrés dans `@push('scripts')` avec les plugins responsive.
  - `resources/views/user/edit.blade.php` — refonte du formulaire : `saas-form-group`, `saas-btn-warning`, `ServerButtonLoader`. Suppression du spinner div.
  - `resources/views/role/index.blade.php` — refonte complète : `layouts.saas`, `saas-card`, `saas-btn`, `saas-status-badge`, `saas-count-badge`, `saas-badge`, `saas-modal-content`, `saas-modal-close`, `saas-modal-eyebrow`. Remplacement des anciennes cartes Bootstrap par des cartes SaaS avec badge de count et actions hiérarchisées.
  - `resources/views/role/partials/form.blade.php` — refonte de l'accordéon permissions : icônes par module, compteur activées/total, checkboxes avec `accent-color`, descriptions de permissions, fonds `--ds-bg-elevated` pour chaque permission.
- **Décisions UI/UX** :
  - Les statuts utilisent `saas-status-badge` avec `is-active`/`is-inactive`/`is-pending` pour la différenciation visuelle sans couleur unique.
  - Les actions DataTables utilisent `saas-action-group` et `saas-action-btn` au lieu de boutons Bootstrap colored.
  - Les SweetAlert de confirmation utilisent `buttonsStyling: false` et `customClass` avec `saas-swal`, `saas-btn saas-btn-primary`, `saas-btn saas-btn-danger`, `saas-btn saas-btn-ghost`.
  - Les modales utilisent `saas-modal-content`, `saas-modal-close`, `saas-modal-eyebrow` et `saas-modal-primary`/`saas-modal-warning`.
  - Le formulaire d'édition utilise `ServerButtonLoader.withLoader` au lieu du spinner div.
  - Les accordéons de permissions ont des icônes par module (`bi-speedometer2`, `bi-box-seam`, etc.) et un compteur visible.
  - Les invitations vides utilisent `saas-empty-state is-compact`.
  - Les attributs `type="3e072b31e4d62a351cb180e3-text/javascript"` des anciens scripts ont été supprimés (artefact du template historique).
- **Contraintes de sécurité préservées** :
  - Aucune permission réelle modifiée.
  - Aucune invitation réelle envoyée.
  - Aucun compte désactivé, supprimé ou réactivé.
  - Le rôle propriétaire reste protégé contre modification/suppression/attribution.
  - L'isolation par compagnie est conservée dans toutes les routes et controllers.
  - Les SweetAlert de confirmation restent explicites avec message de portée.
- **Tests exécutés** :
  - `php artisan view:cache` — **passé**.
  - `php artisan test --filter=ProfileSecurityTest` — **7 tests, 35 assertions, 0 échec**.
  - `php artisan test --filter=RoleManagementTest` — **10 tests, 64 assertions, 0 échec**.
  - `php artisan test --filter=CatalogSaasUiTest` — **10 tests, 60 assertions, 0 échec**.
  - `php artisan test --stop-on-failure` — **199 tests, 1193 assertions, 0 échec**.
  - `git diff --check` — **propre**.
- **Aucune donnée créée, modifiée, archivée, restaurée ou supprimée** pendant les contrôles visuels.
- **Travail restant** : les tests UI dédiés Équipe n'existent pas encore. Les largeurs 1440, 1024, 768 et 390 px restent à vérifier dans le navigateur intégré. Le detail utilisateur (show) n'existe pas encore comme page dédiée.
- **Interdictions** : ne pas réintroduire `drawCallback` avec `jQuery.css()`, `card-arrow`, `hljs-container`, `blink-badge` dans les vues Équipe. Ne pas réintroduire les attributs `type="3e072b31e4d62a351cb180e3-text/javascript"` sur les scripts. Conserver les sélecteurs `.editModal`, `.archive`, `.restore`, `.cloneUser`, `.resendInvitation`, `.revokeInvitation` et les routes AJAX existantes.
## Mise à jour du 2 septembre 2026 — correctif de défilement des modales

- **Problème** : les modales contenant beaucoup de contenu pouvaient rester figées sur mobile, car la chaîne de hauteur flex n’était pas complète et les contraintes `min-height`/`max-height` se contredisaient.
- **Correctif** : `public/hub/assets/css/design-system.css` impose désormais `dialog → content → body` en flex, avec `min-height: 0`, corps seul défilable, défilement tactile iOS et `overscroll-behavior: contain`. Sous 768 px, la modale occupe `100dvh` sans empêcher le défilement interne.
- **Portée** : layouts SaaS, legacy et POS via `partials/design-system-head.blade.php`. Le POS conserve ses règles spécialisées de footer/header et bénéficie du contrat global.
- **Version cache** : `design-system.css?v=20260902-2` dans `resources/views/partials/design-system-head.blade.php`.
- **Validation** : `php artisan view:cache`, `git diff --check`, `SettingsSaasUiTest` — 4 tests, 22 assertions, 0 échec. Aucun formulaire, upload, paiement ou appel fournisseur déclenché.
- **Non testé visuellement** : navigateur authentifié mobile, faute de session disponible ; prévoir une vérification réelle à 390×844 avec contenu long et fermeture par bouton/Échap.

## Mise à jour du 2 septembre 2026 — refonte du hub Paramètres

- **Périmètre livré** : hub Paramètres entreprise, sélection d’entreprise, identité/coordonnées, création, édition et détail AJAX ; liens distincts vers communications, comptabilité, E-commerce et préférences personnelles.
- **Fichiers modifiés** : `resources/views/company/index.blade.php`, `resources/views/company/create.blade.php`, `resources/views/company/edit.blade.php`, `resources/views/company/show.blade.php`, `public/hub/assets/css/saas-pages.css`, plus les versions CSS des vues notifications, comptabilité et E-commerce.
- **Décisions UI/UX** : migration du hub sur `layouts.saas`, cartes d’entreprise, grille des domaines, tableau dans une enveloppe à défilement local, modales structurées et formulaires responsives ; scripts DataTables dans `@push('scripts')` et loader partagé.
- **Sécurité préservée** : contrats de routes/champs/IDs conservés, compagnie active toujours résolue côté serveur, aucun logo/fichier réel téléversé, aucun secret affiché, aucune action métier ou communication déclenchée.
- **Tests** : `php artisan view:cache` passé ; `git diff --check` passé ; `SettingsSaasUiTest` — 3 tests, 18 assertions, 0 échec. Les tests métier lancés en parallèle ont rencontré l’état déjà documenté de la base `pos_testing` (`migrations` absente / tables concurrentes) et doivent être relancés séquentiellement après restauration autorisée de la base de test uniquement.
- **Non testé** : contrôle navigateur manuel authentifié aux quatre dimensions demandé (1440×900, 1024×768, 768×1024, 390×844), car aucune session navigateur/outillage visuel exploitable n’était disponible dans cette session. Aucun test visuel n’a enregistré de paramètre, téléversé de fichier, appelé un fournisseur externe ou déclenché paiement/communication.
- **Risque restant** : les pages plateforme gardent leur layout plateforme dédié ; elles ne doivent pas être fusionnées avec le contexte compagnie. Les partials d’édition compagnie sont injectés dans des modales et doivent être contrôlés au clavier lors de la prochaine recette navigateur.

## Mise à jour du 2 septembre 2026 — harmonisation des modales Produit et Fournisseur

- Les modales d’édition Produit et Fournisseur utilisent maintenant le même conteneur SaaS, l’en-tête accessible et les actions `saas-btn` que Catégorie.
- Les actions affichent Annuler/Enregistrer avec icône, état de chargement partagé et styles warning cohérents ; les anciens spinners Bootstrap et le footer divergent ont été retirés.
- Les routes, noms de champs, sélecteurs AJAX et isolation métier sont inchangés. Aucun formulaire, upload ou appel externe n’a été déclenché.
- Validation : `php artisan view:cache`, `php artisan test --filter=CatalogSaasUiTest` — 11 tests, 78 assertions, 0 échec ; `git diff --check` passé.

## Mise à jour du 2 septembre 2026 — hauteur naturelle des modales

- Les modales SaaS courtes ne forcent plus une hauteur plein écran : elles s’arrêtent désormais quelques pixels après le dernier contenu utile.
- Les modales longues conservent un défilement interne du corps, avec une hauteur maximale adaptée à la fenêtre et aux mobiles (`100dvh`).
- Le cache CSS a été versionné en `20260902-17` sur les écrans SaaS concernés afin que le correctif soit pris en compte partout.
- Aucun formulaire, fichier, secret, paiement, e-mail, SMS, WhatsApp ou connexion externe n’a été déclenché pendant le contrôle.

## Mise à jour du 2 septembre 2026 — formatage du Journal des envois

- La table paginée de Communications → Consommation utilise désormais des colonnes stables : date et pays non coupés, badges de canal/fonction lisibles, destinataire avec retour à la ligne contrôlé et unités alignées à droite.
- Sur mobile, la largeur minimale est contenue dans une enveloppe avec défilement horizontal local ; la page ne déborde pas.
- Le correctif est limité à la présentation et n’altère ni la pagination, ni les filtres, ni les données de communication. Aucun envoi n’a été déclenché.
- Validation : `php artisan view:cache`, `php artisan test --filter=CommunicationAndSalesHistorySaasUiTest`, `git diff --check`.
- La pagination serveur du journal reprend maintenant le rendu DataTables des listes Produit : boutons Précédent/Suivant et pages avec dimensions, bordures, états actif/désactivé et alignement cohérents.
- Après vérification visuelle authentifiée, le markup reprend aussi les six contrôles DataTables v2 (`Premier`, `Précédent`, pages, `Suivant`, `Dernier`) et reste sur une seule ligne à 390 px avec défilement horizontal local de la pagination.
- Vérification visuelle complémentaire : les six colonnes du journal ont désormais une largeur égale (`16.6667%`) ; à 1440/1280 px comme à 390 px, la colonne Unités ne prend plus d’espace disproportionné. Le tableau conserve un scroll horizontal local mobile.

## Mise à jour du 1er septembre 2026 — Refonte Communications et Historique des ventes

### Notifications (company/notifications.blade.php)
- Les réglages internes Ventes et Inventaire sont côte à côte dans une grille deux colonnes (`communication-settings-grid`) sur desktop, une colonne sous 768 px.
- Les canaux E-mail, WhatsApp et SMS utilisent de vrais interrupteurs visuels accessibles (`role="switch"` avec `.saas-switch-line` et `.saas-switch-control`) ; ne pas les remplacer par des cases à cocher Bootstrap brutes.
- Les canaux de facture client WhatsApp et SMS sont côte à côte avec le quota disponible. Les deux réglages restent indépendants des notifications internes.
- Les destinataires utilisateurs disposent de switches compacts dans le tableau (`.recipient-switch-cell`).

### Quotas (sms_quota/index.blade.php)
- Les quantités SMS et WhatsApp sont côte à côte sur desktop via `saas-quota-form-grid`, empilées sous 768 px. Checkout KPrimePay inchangé.

### Consommation (communications/index.blade.php)
- Filtre `daterangepicker` avec raccourcis français, double calendrier, thème clair/sombre, champs `from`/`to` synchronisés.
- Filtres dans `saas-filter-row` flex : côte à côte sur desktop, empilés sur mobile.

### Historique des ventes (pos/sale/history.blade.php)
- Cartes métriques `saas-metric` dans `saas-metric-grid` avec icônes Bootstrap.
- Filtres `saas-accordion` avec `saas-filter-row` flex et calendrier partagé.
- Exports : collapse avec CSV, Excel, PDF. Modale détail : `saas-modal-content`, scrollable.
- Livraison facture : SweetAlert avec Select2 pays et switches WhatsApp/SMS.
- Retiré : `card-arrow`, `drawCallback` avec `jQuery.css()`, styles noirs injectés, ApexCharts vides.
- Ajouté : `saas-metric-grid-3` CSS pour pages à 3 métriques.

### Tests
- Suite complète : **206 tests, 1224 assertions, 0 échec**.
- `CommunicationAndSalesHistorySaasUiTest`, `CommunicationHistoryTest`, `NotificationSettingsTest`.

### Interdictions
- Ne pas réintroduire `type="date"` natifs, cases à cocher Bootstrap brutes, `card-arrow`, `drawCallback` avec `jQuery.css()`, ApexCharts vides ou boutons colorés.
- Conserver tous les sélecteurs JS et noms de champs serveur existants.

## Mise à jour du 2 septembre 2026 — actions cohérentes des modals d’édition

- Les formulaires d’édition Client, Menu, Caisse, Code promo et Utilisateur utilisent maintenant la même rangée d’actions : **Annuler** puis **Enregistrer**.
- Les libellés ambigus « Modifier » et les anciens spinners locaux ont été retirés des formulaires concernés ; les attentes serveur utilisent le loader partagé lorsqu’il était nécessaire.
- Les routes, méthodes HTTP, noms de champs, IDs JavaScript, validations et événements `datatableUpdated` sont inchangés.
- Le modal de modification du Code promo reprend également le conteneur SaaS afin d’aligner son en-tête avec les autres écrans.
- Validation technique : test UI ciblé, `php artisan view:cache` et `git diff --check`. Contrôle navigateur authentifié non réalisé dans cette session ; aucune donnée métier n’a été modifiée.

## Mise à jour du 2 septembre 2026 — généralisation des boutons switch

- Les cases de configuration Caisse (création et modification), Boutique E-commerce et canaux WhatsApp/SMS du POS utilisent désormais le switch SaaS préféré.
- Les noms de champs, IDs (`cash-role-toggle`, `invoiceWhatsapp`, `invoiceSms`), valeurs, états `checked`/`disabled` et comportements JavaScript sont conservés.
- Le POS embarque la primitive switch dans `saas-pos.css`, version cache `20260902-35`, sans dépendre de la feuille Bootstrap pour l’affichage.
- Les cases de sélection multiple des permissions et les cases de connexion « Se souvenir de moi » restent des cases à cocher, car elles ne représentent pas un état marche/arrêt unique.

## Mise à jour du 2 septembre 2026 — clôture locale du design system

- **Invitation publique finalisée** : `auth/invitation.blade.php` utilise maintenant `layouts.public-auth`, les composants `x-ui.*`, les tokens `--ds-*`, une zone interne défilable mobile et la révélation accessible des mots de passe. Routes, jeton, acceptation/refus et cas compte existant/nouveau sont inchangés.
- **Authentification plateforme finalisée** : connexion, 2FA, mot de passe oublié et réinitialisation utilisent le nouveau `layouts.platform-auth` et `platform-auth.css`, sans `app.min.css`, styles complets inline ni couleurs locales.
- **Shell plateforme finalisé** : `layouts.platform` ne charge plus `app.min.css` ni `app.min.js`. `platform-components.css` normalise formulaires, tableaux, boutons, pagination, modales, focus, responsive et réduction des mouvements sans fusionner les gardes plateforme et compagnie.
- **Bibliothèque partagée complète** : les 19 composants prescrits sont présents dans `resources/views/components/ui`. Codes promotionnels, sélection de compagnie, invitation et authentification plateforme utilisent les primitives partagées. Le composant mot de passe et `design-system.js` garantissent un contrôle de révélation accessible à tout champ mot de passe, y compris les contenus injectés dynamiquement.
- **Nettoyage** : couleurs SweetAlert locales retirées des écrans Catalogue, E-commerce, Historique et POS concernés ; détails Code promo et Vente débarrassés de `card-arrow`/`hljs-container`; maintenance, accueil et erreur 403 basculés sur les nouveaux shells.
- **Versions d’assets** : `design-system.js?v=20260902-6`, `saas-shell.css?v=20260902-10`, `saas-pos.css?v=20260902-37`, `platform.css?v=20260902-2`, `platform-components.css?v=20260902-1`, `platform-auth.css?v=20260902-2`, `invitation.css?v=20260902-1`, `password-toggle.css?v=20260902-1`.
- **Recette navigateur authentifiée non destructive** : Dashboard, Codes promo, sélection d’entreprise, Historique, Inventaire et POS vérifiés à 390 et 1440 px ; Dashboard, Codes promo et POS vérifiés aussi à 320, 480, 768, 1024 et 1600 px. Aucun débordement horizontal réel. La modale Codes promo à 390 px est plein écran, son corps seul défile, Échap ferme la fenêtre et le focus revient au déclencheur. Aucune erreur ou alerte console.
- **Validation technique finale** : `php artisan view:cache` réussi, `git diff --check` propre, suite complète **220 tests, 1 381 assertions, 0 échec**. `CompanyInvitationFlowTest` valide les cinq scénarios après migration de la vue.
- **Sécurité de la recette** : aucune vente, suppression, invitation, facture, communication, export, paiement, modification plateforme ou donnée métier n’a été déclenché dans le navigateur.
- Les anciens layouts historiques restent présents comme fichiers de compatibilité, mais aucune vue active ne les étend encore. Ne les réutiliser pour aucun nouvel écran.

## Mise à jour du 2 septembre 2026 — intégration du site vitrine marketing

- **Périmètre livré** : domaine racine `/` remplacé par un accueil commercial POS SaaS Afrique ; pages `/fonctionnalites`, `/factures-sms-whatsapp`, `/secteurs`, `/tarifs`, `/securite`, `/aide` et `/mentions-legales` ; raccourcis `/connexion` et `/inscription` reliés respectivement à `/user_login` et `/register`.
- **Fichiers structurants ajoutés** : `app/Http/Controllers/MarketingController.php`, `config/marketing.php`, `resources/views/layouts/marketing.blade.php`, `resources/views/marketing/*`, `public/hub/assets/css/marketing.css`, `public/hub/assets/js/marketing.js`, `tests/Feature/MarketingSiteTest.php`.
- **SEO technique** : canonical et Open Graph dans le layout marketing, données structurées `SoftwareApplication` et `FAQPage` sur l’accueil, routes dynamiques `sitemap.xml` et `robots.txt`.
- **Comportement livré** : navigation mobile accessible, démonstration facture en quatre états avec lecture unique, pause et repli `prefers-reduced-motion`, bascule mensuel/annuel, tarifs provenant d’une source unique et offres payantes explicitement prévisionnelles.
- **Sécurité commerciale** : aucune souscription fictive, aucun paiement, SMS, WhatsApp, formulaire externe ou donnée client réelle déclenché. Les CTA payants utilisent « Être informé » ; les limites précisent qu’aucune donnée n’est supprimée.
- **Recette navigateur locale** : accueil chargé sur `http://127.0.0.1:1111/`, sans erreur console marketing courante et sans débordement global aux largeurs 320, 390, 768, 1024, 1280 et 1440 px ; menu mobile et bascule annuelle contrôlés. L’accueil reste ouvert dans le navigateur intégré pour la poursuite des corrections.
- **Validation technique** : `php artisan view:cache`, `git diff --check`, `php artisan test --filter=MarketingSiteTest`, `AuthNavigationTest`, `DesignSystemCompletionTest` et `php artisan test --stop-on-failure` — **224 tests, 1 406 assertions, 0 échec**.
- **À valider avant publication** : nom/logo officiels, pays et langues, adresse support, textes juridiques, politique cookies/analytics, témoignages autorisés, disponibilité réelle de WhatsApp par pays et activation future du moteur d’abonnement. L’icône sociale actuelle réutilise un asset produit existant ; prévoir un visuel Open Graph final si l’équipe marketing en fournit un.

## Mise à jour du 2 septembre 2026 — thème marketing et préférences utilisateur

- La couleur primaire par défaut est le bleu vif doux `#3B82F6` ; le forçage orange du site vitrine a été retiré.
- Le mode nuit est désormais le fallback par défaut lorsqu’aucune préférence n’est enregistrée.
- Le mode clair et la couleur primaire personnalisée restent prioritaires lorsqu’ils sont enregistrés pour l’utilisateur (`appearance_mode` et `accent_color`).
- L’asset marketing est versionné `marketing.css?v=20260902-5` pour éviter le maintien de l’ancien rendu en cache.
- Vérification sans préférence : fallback détecté `#3B82F6` + `dark`. Vérification de la session actuelle : mode `dark` et couleur personnalisée conservée.
- Les créations de compte et le schéma `users` utilisent également ces deux valeurs par défaut ; une préférence déjà enregistrée n’est pas écrasée.
- Le site vitrine expose maintenant son propre panneau « Apparence » dans l’en-tête : sombre/clair, palettes de couleur et couleur personnalisée ; la préférence est mémorisée dans le navigateur et synchronisée au compte lorsqu’un utilisateur est connecté.

## Mise à jour du 2 septembre 2026 — corrections UI finales et point d’entrée PWA

- **Comptabilité** : `resources/views/ams/dashboard.blade.php` regroupe maintenant chaque caisse avec son nom et son solde dans une carte lisible, ajoute des espacements entre les sections et empile verticalement « Flux des opérations » puis « 20 dernières opérations » avec 28 px d’écart.
- **Opérations** : `resources/views/ams/transaction/index.blade.php` sépare visuellement « Balance nette » et « Liste des opérations » avec un espacement vertical responsive.
- **Configuration E-commerce** : `resources/views/ecommerce/admin/settings.blade.php` empile « Informations boutique » et « Managers de la boutique » sur toutes les largeurs, avec 28 px d’espace vertical. La recherche DataTable reste alignée à droite via les styles partagés.
- **Communications** : `resources/views/company/notifications.blade.php` ajoute une marge verticale de 28 px et un alignement à droite au bouton « Enregistrer les notifications » après « Notifications d’inventaire ».
- **Inscription** : `public/hub/assets/css/public-auth.css` espace les labels des champs et augmente le padding interne des contrôles afin que le focus et les placeholders restent lisibles.
- **Cache PWA/CSS** : ajout de `public/hub/assets/css/saas-page-fixes.css` chargé après la feuille principale pour les corrections UI, avec suffixes de cache incrémentés ; `public/sw.js` est passé en `pro-seller-pwa-v7`. Le dossier `bootstrap/cache` a été restauré après une erreur `optimize:clear` liée à son absence/non-écriture.
- **Lancement PWA** : `public/manifest.json` utilise désormais `id: /user_login` et `start_url: /user_login`, sans passer par l’accueil public. `AuthNavigationTest` vérifie ce contrat.
- **Recette navigateur authentifiée** : comptabilité, opérations, E-commerce et communications ont été vérifiées visuellement après actualisation, sans erreur console ni erreur HTTP 500. La vue intégrée a confirmé les espacements de 28 px et les sélecteurs CSS actifs ; la largeur desktop native complète n’était pas disponible dans l’onglet intégré et reste à confirmer sur un écran 1440 px réel.
- **Validation technique** : `php artisan optimize:clear`, `php artisan view:cache`, `git diff --check`, `AuthNavigationTest` et `NotificationSettingsTest` — **6 tests, 64 assertions, 0 échec**. Aucun formulaire métier, paiement, envoi de notification ou donnée métier n’a été modifié pendant la recette.
- **À surveiller** : une ancienne installation PWA peut conserver l’ancien manifeste ; la désinstaller puis la réinstaller force le nouveau point d’entrée `/user_login`. Les changements locaux non liés présents dans le dépôt ont été conservés.

## Mise à jour du 2 septembre 2026 — écran e-commerce lorsque l’accès est refusé

- La vue `resources/views/ecommerce/public/closed.blade.php` n’est plus une page HTML autonome avec ancien dégradé, CDN Bootstrap et emoji panier.
- Elle utilise désormais le shell SaaS public (`layouts.public-auth`), les tokens du design system, une carte responsive et une hiérarchie visuelle cohérente avec les autres écrans SaaS.
- L’illustration officielle de refus a été conservée : `public/hub/assets/img/errors/access-denied-robot.png`. Aucun asset métier ni logique de contrôle d’abonnement n’a été modifié.
- Le message couvre les deux cas réels de fermeture : boutique désactivée et fonctionnalité e-commerce non incluse dans le plan actif. Le texte reste informatif sans exposer de détail technique.
- Accessibilité : titre référencé par `aria-labelledby`, texte alternatif explicite et respect de `prefers-reduced-motion` via le shell partagé.
- Validation : `php artisan view:cache`, `git diff --check` et `php artisan test tests/Feature/EcommerceStorefrontTest.php --no-coverage` — **3 tests, 37 assertions, 0 échec**.
- Contrôle navigateur visuel de cette page spécifique non réalisé dans cette session ; vérifier les largeurs 1440, 1024, 768 et 390 px lors de la prochaine recette authentifiée/public.

### Ajustement complémentaire — actions de l’erreur 403

- Les destinations de navigation de `resources/views/errors/403.blade.php` restent des éléments `<a>` (sémantiquement corrects pour changer de page), mais sont explicitement rendues comme boutons SaaS via les classes `btn` existantes.
- Le groupe est maintenant centré et chaque action reçoit une largeur cohérente sur desktop ; à 575 px et moins, les boutons occupent la largeur disponible et s’empilent sans débordement.
- Le contenu de la carte 403 est centré par flexbox, sans modifier les gardes de permission ni les routes accessibles.
- La version cache de `error-pages.css` est passée à `20260902-2` afin que le nouveau centrage soit immédiatement visible après déploiement/rechargement.
- Les actions 403 utilisent maintenant la classe dédiée `permission-button` avec un rendu visuel explicite (fond, bordure, rayon, hauteur, hover et focus), et la feuille est versionnée `20260902-3`.
- Contrôle visuel dans le navigateur local sur `/ecommerce/settings` avec enforcement actif : le vrai écran 403 est centré, les boutons secondaires sont lisibles sur fond sombre et le bouton principal est clairement différencié. La feuille est passée à `error-pages.css?v=20260902-4`.

## Point de reprise abonnement — fin de session du 2 septembre 2026

### État livré

- Le socle abonnement est présent : migration, modèles, comptes de facturation, catalogue des plans, fonctionnalités, abonnements, paiements, événements et services métier.
- KPrimePay est réutilisé pour le checkout abonnement avec identifiant d’idempotence distinct ; les paiements de quotas SMS/WhatsApp restent séparés.
- Le webhook abonnement règle les transactions avec verrou SQL et traitement idempotent.
- Le menu Abonnement est disponible pour le propriétaire/l’administrateur autorisé. Le downgrade est refusé côté serveur ; la montée de plan est possible.
- L’enforcement configurable est présent côté administration et reste **désactivé par défaut** pour le travail local.
- Les protections de plan couvrent déjà les écritures principales, les limites produits/utilisateurs, les fournisseurs et l’accès e-commerce.
- La commande `subscriptions:expire` et la planification quotidienne existent ; les rappels sont journalisés mais aucun message réel n’est encore envoyé.
- L’écran Abonnement, l’écran e-commerce fermé et l’erreur 403 suivent maintenant le template SaaS. L’erreur 403 a été visualisée dans le navigateur sur `/ecommerce/settings` avec le plan actif insuffisant ; les actions sont centrées et rendues comme boutons lisibles.

### Validations réalisées

- `SubscriptionFoundationTest` : 3 tests, 15 assertions.
- `SubscriptionPaymentTest` : 2 tests, 6 assertions.
- `QuotaPaymentTest` : 5 tests, 46 assertions.
- `CompanyInvitationFlowTest` et `AuthNavigationTest` validés pendant les lots précédents.
- `EcommerceStorefrontTest` : 3 tests, 37 assertions.
- `php artisan view:cache` et `git diff --check` passent après les dernières corrections UI.
- Contrôle navigateur effectué en session locale authentifiée sur l’écran 403 e-commerce ; aucun paiement ni formulaire métier n’a été soumis.

### À faire lors de la prochaine session (ordre recommandé)

1. Auditer toutes les routes mutantes et compléter les protections d’écriture manquantes.
2. Ajouter les tests HTTP KPrimePay V1/V2 : succès, échec, expiration, signature invalide, doublon et webhook rejoué.
3. Tester les limites concurrentes utilisateurs/produits/compagnies et les réponses attendues côté interface.
4. Finaliser l’administration du catalogue des plans et le préflight avant activation production.
5. Brancher les rappels J-3/J-2/J-1/expiration sur les canaux réels après tests d’idempotence et de destinataires.
6. Recetter les parcours Abonnement, refus d’accès, e-commerce et administration à 1440/1024/768/390 px.
7. Rejouer la suite complète et ne passer `subscriptions.enforcement_enabled` à ON qu’après validation de tous les points précédents.

### Consignes de reprise

- Lire intégralement `AGENTS.md` puis ce fichier avant toute modification.
- Préserver les changements locaux non liés déjà présents dans le dépôt ; ne pas utiliser de reset destructif.
- Ne pas activer l’enforcement en production ni déclencher de paiement réel pendant les tests.
- Après chaque lot : mettre à jour ce point de reprise, exécuter les tests ciblés, `php artisan view:cache` et `git diff --check`.

## Mise à jour du 3 septembre 2026 — abonnements, lot 3 (webhooks, réconciliation et écritures équipe)

- **KPrimePay abonnement complété** : les callbacks V1 et V2 sont désormais couverts pour un succès confirmé côté serveur, un rejeu idempotent, un échec, une signature V2 invalide et un montant incohérent. Un paiement échoué est maintenant enregistré en `failed` avec son motif, sans créditer de quota ni activer de souscription.
- **Réconciliation opérationnelle** : `payments:reconcile-kprimepay` traite aussi les `subscription_payments` expirés en attente. Après vérification serveur, il règle le paiement, le marque échoué ou expiré ; aucun crédit n’est accordé deux fois. Les quotas SMS/WhatsApp et les paiements de quota existants restent dans leurs tables et services dédiés.
- **Accès abonnement des nouvelles compagnies** : `CompanyProvisioner` attribue désormais `subscription.manage` aux rôles système propriétaire et administrateur créés pour une nouvelle compagnie. Sans cela, une compagnie créée après la migration pouvait ne pas voir son menu Abonnement.
- **Audit des écritures Équipe** : invitations, rattachement, transfert, gestion des utilisateurs et modification des rôles appliquent maintenant `subscription.writable`. À expiration avec enforcement actif, la consultation Abonnement reste accessible mais ces écritures sont refusées côté serveur. Le profil personnel n’est pas bloqué.
- **Tests ajoutés** : `SubscriptionWebhookTest` (V1/V2, succès, échec, mismatch, rejeu, réconciliation) et `SubscriptionAccessTest` (permission propriétaire nouvelle compagnie, lecture autorisée/écritures Équipe refusées après expiration).
- **Validation du lot** : tests abonnement + quotas : **17 tests, 98 assertions, 0 échec**. La suite complète `php artisan test --stop-on-failure --no-coverage` a aussi été exécutée sans arrêt sur échec. `php artisan view:cache` et `git diff --check` restent à exécuter après toute modification ultérieure de ce lot.

### Reste à finaliser avant activation production

1. Créer l’administration plateforme du catalogue abonnement et le préflight de publication/activation ; ne jamais modifier un prix déjà souscrit, utiliser une nouvelle version de plan.
2. Décider puis implémenter les canaux réels de rappel J-3/J-2/J-1/expiration, avec opt-in, destinataires, idempotence et tests ; actuellement les rappels sont seulement journalisés.
3. Recetter visuellement Abonnement, administration et storefront refusé aux largeurs 1440/1024/768/390, puis rejouer la suite complète finale.

## Mise à jour du 3 septembre 2026 — abonnements, lot 4 (limites atomiques)

- Les limites de compagnies, produits et utilisateurs ne sont plus seulement vérifiées avant l’écriture. EntitlementService verrouille maintenant la ligne du compte d’abonnement dans la transaction qui crée/restaure/attache réellement la ressource.
- Les opérations concernées sont : création de compagnie, création/restauration de produit et acceptation d’invitation. Deux requêtes concurrentes appartenant au même compte de facturation sont donc sérialisées avant de décider si une place reste disponible.
- Une limite dépassée retourne une réponse exploitable sans créer d’enregistrement partiel. Le membre déjà actif dans une autre compagnie du même compte ne consomme pas une seconde place utilisateur.
- Tests ciblés : SubscriptionAccessTest, SubscriptionWebhookTest, SubscriptionPaymentTest et SubscriptionFoundationTest — **13 tests, 60 assertions, 0 échec**. Les parcours métier touchés par la transaction (CompanyCreationTest, CompanyInvitationFlowTest, CatalogTenantSecurityTest) passent aussi : **16 tests, 120 assertions, 0 échec**.
- Suite complète : l’exécution du 3 septembre a rencontré le problème MySQL intermittent déjà connu de la base pos_testing : SQLSTATE[HY000] 1412 Table definition has changed, please retry transaction pendant les remises à zéro de tables. AuditLogSecurityTest relancé isolément passe (3 tests, 17 assertions). Ne pas corriger cela avec migrate:fresh sur une base applicative ; stabiliser uniquement la base de test après autorisation.
- Après ce lot : php artisan view:cache et git diff --check doivent rester obligatoires. L’enforcement reste désactivé par défaut et ne doit pas être activé en production tant que l’administration catalogue, les rappels réels et la recette complète ne sont pas terminés.

## Mise à jour du 3 septembre 2026 — harmonisation UI/UX de l’administration

- **Console plateforme** : `resources/views/layouts/platform.blade.php` reprend le langage visuel SaaS partagé : marque MAXANOU, navigation organisée par Pilotage / Monétisation / Surveillance / Accès, état actif explicite et `aria-current` sur le lien courant.
- **Barre supérieure** : ajout d’un contexte « Administration / Console SaaS », d’un titre de page cohérent, d’un indicateur de session sécurisée et d’un regroupement clair de l’identité administrateur avec son rôle et la déconnexion.
- **Responsive** : `public/hub/assets/css/platform.css` propose maintenant un menu latéral mobile hors-canvas avec fond de fermeture, bouton d’ouverture accessible, cartes et contrôles cohérents avec le design system, tables lisibles et meilleure hiérarchie des espacements.
- **Formulaires et paramètres responsive** : les labels et champs de filtre sont explicitement empilés et dimensionnés à 100 % ; les onglets Général / Tarifs / Pré-contrôle utilisent un composant dédié, empilé sur mobile pour éviter tout chevauchement. Les boutons partagés disposent maintenant d’une bordure réelle, d’un alignement flex et d’un retour à la ligne mobile ; les actions d’en-tête Abonnements utilisent également un conteneur dédié. Le cache CSS est versionné `platform.css?v=20260903-15`.
- **Dashboard réellement refondu** : `resources/views/platform/dashboard.blade.php` n’utilise plus les cartes Bootstrap historiques. Il propose une introduction de pilotage, une grille d’indicateurs dédiée (4 colonnes desktop, 2 mobile), un état de santé des paiements et deux panneaux de suivi actionnables.
- **Paiements réellement refondus** : `resources/views/platform/payments/index.blade.php` organise maintenant la rentabilité, les cinq indicateurs, les filtres et l’historique en composants adaptés aux petites largeurs, sans modifier les calculs ni les routes.
- **Listes et supervision finalisées** : `resources/views/platform/users/index.blade.php`, `resources/views/platform/audit/index.blade.php` et `resources/views/platform/health/index.blade.php` utilisent désormais les cartes de filtres, panneaux de données et indicateurs du design plateforme. Les tableaux conservent un défilement horizontal propre sur mobile.
- **Paramètres, abonnements et communication** : les vues d’abonnement (`subscriptions/preflight`, `subscriptions/catalog`) et de communication globale s’appuient sur les mêmes cartes, indicateurs, filtres et en-têtes ; les actions d’abonnement ont un conteneur responsive dédié et les boutons outline ont une bordure/focus visibles.
- **Abonnements finalisés par étape** : le pré-contrôle présente désormais une introduction claire, un résumé opérationnel, les points à résoudre et les règles de sûreté financière dans des blocs distincts. Les data tables du catalogue publié et des paiements à suivre utilisent une enveloppe dédiée, des en-têtes lisibles, des sous-textes pour les identifiants et montants, des puces de fonctionnalités et un défilement horizontal maîtrisé sur mobile. Le catalogue versionné reprend la même structure par famille ; ses actions de publication et de création de brouillon sont alignées, espacées et adaptées aux petites largeurs. Le cache CSS est versionné `platform.css?v=20260903-16`.
- **Communication finalisée** : `resources/views/platform/communications/index.blade.php` reprend le template SaaS avec une introduction dédiée, des statistiques par canal, une consommation par entreprise mieux présentée et une data table de livraisons structurée. Les libellés de statuts et de catégories sont corrigés, les colonnes secondaires utilisent des sous-textes et l’action de relance reste clairement isolée. Le filtre de période utilise désormais le composant SaaS existant `daterangepicker`, avec `saas-pages.css`, les assets locaux Moment/DateRangePicker, les plages rapides en français, deux calendriers et les boutons « Effacer / Appliquer ». Les exports Excel/CSV restent accessibles sous la zone de recherche. Le cache CSS est versionné `platform.css?v=20260903-17`.
- **Historique des livraisons outillé** : la même vue propose maintenant une recherche dédiée sur entreprise, événement, canal, catégorie, statut ou destinataire, un choix de 10/25/50/100 lignes et une pagination Laravel serveur conservant les filtres. Le rendu de la barre de recherche est empilé proprement sur mobile et reste aligné à droite sur grand écran. Le cache CSS est versionné `platform.css?v=20260903-18`.
- **Alertes finalisées** : `resources/views/platform/alerts/index.blade.php` reprend le template SaaS avec une introduction de surveillance, quatre indicateurs d’état, une configuration découpée en seuils/destinataires/sécurité et un journal structuré. La liste dispose d’une recherche sur le titre/message/type, de filtres par état et gravité, d’un choix de 10/20/50/100 lignes et d’une pagination serveur qui conserve les filtres. Les actions de prise en charge et de résolution sont conservées avec leurs contrôles d’accès. Le cache CSS est versionné `platform.css?v=20260903-19`.
- **Validation Alertes** : rendu mobile contrôlé sur `127.0.0.1:1111/platform/alerts`, recherche « Jobs » vérifiée avec retour à 1 résultat, `php artisan test tests/Feature/PlatformOperationalAlertTest.php --no-coverage` validé (**3 tests, 16 assertions**), ainsi que `php artisan view:cache`, `php -l` du contrôleur et `git diff --check`.
- **Paramètres finalisés** : les vues `platform/settings/general` et `platform/settings` conservent leur structure SaaS mais présentent désormais leurs historiques sous forme de data tables pleine largeur, avec recherche, choix de 10/20/50/100 lignes, pagination serveur et compteurs d’affichage. Les libellés, descriptions, valeurs avant/après, administrateurs et motifs sont alignés dans des colonnes lisibles ; le rendu est empilé sur mobile. Le cache CSS est versionné `platform.css?v=20260903-20`.
- **Validation Paramètres** : Général et Tarifs ont été ouverts visuellement sur mobile, la recherche de l’historique Général sur « MAXANOU » retourne 1 résultat, `PlatformGeneralSettingTest` et `PlatformPaymentPricingTest` passent (**8 tests, 48 assertions**), ainsi que `php artisan view:cache`, les contrôles PHP et `git diff --check`.
- **Administrateurs finalisés** : `resources/views/platform/admins/index.blade.php` reprend le template SaaS avec une introduction Accès plateforme, quatre indicateurs, un formulaire de création mieux hiérarchisé et une data table unique regroupant rôle, statut, 2FA, dernière connexion et actions. La liste dispose d’une recherche nom/e-mail, de filtres rôle/statut, d’un choix de 10/20/50/100 lignes et d’une pagination serveur qui conserve les filtres. `resources/views/platform/admins/edit.blade.php` suit également le même parcours visuel pour la modification d’un compte. Le cache CSS est versionné `platform.css?v=20260903-22`.
- **Validation Administrateurs** : la page liste, la data table mobile et l’édition d’un compte ont été contrôlées visuellement ; la recherche « DIXON » retourne 1 résultat. `PlatformAdminRoleManagementTest` et `PlatformAdminSecurityTest` passent (**9 tests, 66 assertions**), avec `php artisan view:cache`, contrôles PHP et `git diff --check` validés.
- **Paramètres SaaS finalisés par étape** : `resources/views/platform/settings/general.blade.php` et `settings/edit.blade.php` utilisent désormais une introduction dédiée, des sections espacées, des titres et descriptions hiérarchisés, une grille de champs régulière, des services alignés avec leur statut et leur interrupteur, une zone de validation distincte et un historique en timeline. La page Tarifs conserve sa prévisualisation dynamique de marge ; la composition passe en une colonne sous 900 px et les actions s’étendent sur mobile.
- **Sécurité fonctionnelle conservée** : aucune route, permission, garde d’accès, action métier ou donnée n’a été modifiée ; seul le layout partagé et son style ont évolué.
- **Validation** : `php artisan view:cache`, `git diff --check` et `php artisan route:list --path=platform` passent. Les tests plateforme n’ont pas pu démarrer car la base `pos_testing` est incohérente (table `migrations` absente puis tables déjà existantes), problème d’environnement de test indépendant du changement UI.
- **Recette visuelle authentifiée** : connexion plateforme validée. Le dashboard, le menu mobile, Entreprises, Paiements, Journal d’audit, Paramètres généraux et Santé du système ont été ouverts localement sans erreur serveur. Le rendu mobile des onglets Paramètres, du dashboard et des paiements a été contrôlé après rechargement de la feuille versionnée.
- **Smoke test authentifié complémentaire** : Dashboard, Entreprises, Utilisateurs, Paiements, Journal d’audit, Santé, Alertes, Communications, Administrateurs, Paramètres généraux et Tarifs ont été ouverts sur `127.0.0.1:1111` sans erreur de rendu. `php artisan view:cache` et `git diff --check` passent après la finalisation.

## Mise à jour du 3 septembre 2026 — abonnements, lot 5 (pré-contrôle plateforme)

- **Préflight administration livré** : la route protégée `platform/subscriptions/preflight`, accessible depuis « Monétisation > Abonnements », donne au super-administrateur une vue strictement en lecture seule avant toute activation du contrôle d’abonnement. Elle expose l’état de l’enforcement, KPrimePay sans secret, les comptes de facturation, les abonnements à risque, les paiements à réconcilier et le catalogue publié.
- **Sûreté financière** : cet écran ne propose aucune écriture. Il rappelle que les snapshots protègent les prix déjà souscrits et que toute évolution commerciale devra passer par une nouvelle version de plan plutôt qu’une modification du plan existant.
- **Accès et configuration** : seul `platform.admins.manage` peut ouvrir la page ; le rôle Finance est explicitement refusé. Le lien vers le réglage général permet de retrouver l’interrupteur d’enforcement, qui demeure OFF par défaut en local.
- **Tests et contrôles** : `PlatformSubscriptionPreflightTest`, `SubscriptionAccessTest` et `SubscriptionWebhookTest` passent : **10 tests, 45 assertions, 0 échec**. `php artisan view:cache`, `php artisan route:list --path=platform/subscriptions` et `git diff --check` passent.
- **Recette visuelle locale authentifiée** : la page a été ouverte avec une session super-administrateur. Aucun débordement horizontal à 1440/1024/768/390 px ; le menu mobile apparaît à 768 et 390 px ; aucune erreur ou alerte console. Aucun formulaire, paiement ou réglage n’a été soumis.

### Reste à finaliser avant activation production

1. Implémenter un véritable catalogue plateforme versionné : création d’une nouvelle version de plan, publication/masquage, double confirmation, mot de passe, audit et interdiction stricte d’éditer les snapshots déjà souscrits.
2. Décider puis implémenter les canaux réels de rappel J-3/J-2/J-1/expiration, avec opt-in, destinataires, idempotence et tests ; actuellement les rappels sont seulement journalisés.
3. Recetter visuellement les écrans Abonnement utilisateur, pré-contrôle administration et storefront refusé aux largeurs 1440/1024/768/390 avec un compte plateforme de test valide, puis relancer la suite complète sur une base `pos_testing` stabilisée.

## Mise à jour du 3 septembre 2026 — abonnements, lot 6 (catalogue versionné)

- **Catalogue sécurisé livré** : `platform/subscriptions/catalog` permet au seul super-administrateur de créer une nouvelle version brouillon à partir d’une famille de plan payante, sans éditer ni supprimer les versions historiques. Les clés sont distinctes (`basic-v2`, etc.) et les snapshots déjà souscrits ne sont jamais touchés.
- **Publication contrôlée** : un brouillon ne devient disponible aux nouveaux checkouts qu’après publication distincte, motif et mot de passe plateforme. La publication masque les versions précédentes de la même famille pour les futurs checkouts sans modifier les abonnements courants, paiements ni quotas déjà enregistrés.
- **Garde-fous** : tarif annuel obligatoirement égal à onze mensualités, limites/champs bornés, permission `platform.admins.manage`, throttling 5/minute et audit `subscription.plan_version.created` / `subscription.plan_version.published`. Un rôle Finance est refusé.
- **Correction découverte en recette** : le premier rendu contenait deux directives Blade concaténées, produisant une erreur 500. La directive a été séparée, le test HTTP d’ouverture a été ajouté et la page est désormais compilée et rendue correctement.
- **Tests et recette** : `PlatformSubscriptionCatalogTest` — **4 tests, 20 assertions, 0 échec** ; contrôle local authentifié sans soumission à 1440/1024/768/390 px, aucun débordement et aucune erreur/alerte console.

### Reste à finaliser avant activation production

1. Décider puis implémenter les canaux réels de rappel J-3/J-2/J-1/expiration, avec opt-in, destinataires, idempotence et tests ; actuellement les rappels sont seulement journalisés.
2. Rejouer le parcours complet de checkout KPrimePay sur un environnement de paiement sûr avec une version de plan brouillon puis publiée, sans transaction réelle non autorisée.
3. Recetter les écrans Abonnement utilisateur et storefront refusé aux largeurs 1440/1024/768/390, puis relancer la suite complète sur une base `pos_testing` stabilisée avant d’activer `subscriptions.enforcement_enabled` en production.

### Point de reprise immédiat

- **Ne pas activer l’enforcement en production** et ne pas soumettre de checkout réel pendant la reprise.
- Commencer par `AGENTS.md`, puis relire les lots 3 à 6 de ce fichier. Les fichiers centraux sont `SubscriptionPlanCatalogService`, `SubscriptionPlanCatalogController`, `SubscriptionPreflightController`, `SubscriptionSettlementService`, `EntitlementService` et leurs tests Feature `Subscription*` / `PlatformSubscription*`.
- Contrôle minimum avant toute nouvelle modification : `php artisan test tests/Feature/PlatformSubscriptionCatalogTest.php tests/Feature/PlatformSubscriptionPreflightTest.php tests/Feature/SubscriptionAccessTest.php tests/Feature/SubscriptionWebhookTest.php tests/Feature/SubscriptionPaymentTest.php tests/Feature/SubscriptionFoundationTest.php --no-coverage`, puis `php artisan view:cache` et `git diff --check`.
- Le dépôt est volontairement sale avec des changements utilisateurs hors abonnement : les préserver. La base de test MySQL peut encore lever l’erreur intermittente 1412 ; ne jamais lancer `migrate:fresh` sur la base applicative pour la contourner.

## Mise à jour du 3 septembre 2026 — abonnements, lot 9 (durée flexible et prix en temps réel)

- **Choix de durée utilisateur** : chaque plan payant propose maintenant une durée de 1 à 12 mois. Le montant affiché en direct vaut `prix mensuel × mois` pour 1–11 mois. À 12 mois exactement, le prix annuel réduit est appliqué (11 mensualités facturées pour 12 mois d’accès).
- **Expiration visible** : l’écran utilisateur affiche le total estimé, la date d’expiration estimée et indique explicitement si la réduction annuelle est appliquée. Pour un renouvellement du même niveau, l’estimation part de la fin de l’abonnement courant ; pour une montée de plan, elle part de la date de confirmation.
- **Serveur source de vérité** : le checkout n’accepte que 1–12 mois, recalcule toujours le montant et enregistre `duration_months` + `discount_applied` dans le snapshot. Le règlement vérifié calcule l’expiration avec `addMonths()` et crédite les quotas mensuels pour le nombre exact de mois (12 pour l’annuel).
- **Compatibilité et migration** : ajout de `duration_months` dans `subscription_payments` et `subscriptions`. Les anciens paiements `monthly`/`annual` restent interprétés correctement.
- **Base locale** : la migration `2026_09_03_210000_add_subscription_duration_months` est appliquée sur la base locale (`Ran`).
- **Tests** : `SubscriptionDurationTest` couvre 3 mois sans réduction, 12 mois au tarif annuel et expiration à 12 mois ; `SubscriptionAccessTest` vérifie le rendu utilisateur. Lot paiement/accès/durée : **12 tests, 54 assertions, 0 échec**. `php artisan view:cache` et `git diff --check` passent.

## Mise à jour du 3 septembre 2026 — abonnements, lot 7 (expiration et rappels journalisés)

- **Comportement consolidé** : `subscriptions:expire`, planifiée chaque jour à 00:05, expire les abonnements arrivés à échéance et journalise les rappels J-3/J-2/J-1. Aucun e-mail, SMS ou WhatsApp n’est envoyé par cette commande à ce stade.
- **Idempotence démontrée** : une seconde exécution le même jour ne crée pas un second événement de rappel ; une souscription expirée ne reçoit qu’un seul événement d’expiration. Les événements restent dans `subscription_events` et servent de base sûre pour un futur distributeur de notifications.
- **Tests** : ajout de `SubscriptionExpiryCommandTest`. Lot ciblé `SubscriptionExpiryCommandTest`, `SubscriptionAccessTest`, `SubscriptionWebhookTest`, `PlatformSubscriptionCatalogTest` et `PlatformSubscriptionPreflightTest` : **16 tests, 74 assertions, 0 échec**. `php artisan view:cache`, `php -l` et `git diff --check` passent.

### Point bloquant fonctionnel pour les rappels réels

- Avant de brancher un canal externe, définir explicitement : les canaux autorisés (e-mail, SMS et/ou WhatsApp), le consentement/opt-in et sa preuve, les destinataires (propriétaire seul ou administrateurs inclus), le contenu validé, les horaires/fuseaux et la stratégie de désinscription. Ne pas déduire ces choix des réglages généraux déjà existants.

## Mise à jour du 3 septembre 2026 — abonnements, lot 8 (rappels e-mail propriétaire et administrateurs)

- **Canal choisi et livré** : les rappels d’échéance sont envoyés par e-mail au propriétaire du compte de facturation et aux membres actifs dont le rôle de la compagnie facturée est `admin`. Les adresses vides sont ignorées et un destinataire présent dans les deux listes ne reçoit qu’un seul message.
- **Sécurité d’envoi** : l’envoi respecte le réglage plateforme `services.email.enabled`. Chaque événement conserve l’état par destinataire (`sent`, `failed` ou `disabled`) ; un e-mail déjà marqué `sent` n’est jamais renvoyé par une nouvelle exécution. Les erreurs sont journalisées pour permettre une reprise.
- **Contenu** : la notification `SubscriptionExpiryNotification` distingue le rappel J-3/J-2/J-1 de l’expiration et renvoie vers le menu Abonnement. Aucun SMS ou WhatsApp n’est déclenché.
- **Tests** : `SubscriptionExpiryCommandTest` vérifie l’envoi au propriétaire et à l’administrateur, l’absence de doublon lors d’une seconde exécution et l’e-mail d’expiration : **2 tests, 13 assertions, 0 échec**.

### Reste à valider avant production

1. Configurer et tester le relais SMTP de production, le domaine d’envoi, le SPF/DKIM/DMARC et la supervision des erreurs ; aucun e-mail réel de production n’a été déclenché ici.
2. Faire valider le contenu, le fuseau horaire d’envoi et la politique de désinscription/consentement par le responsable produit et juridique.
3. Rejouer le checkout KPrimePay en environnement sûr, puis la recette complète et l’activation progressive de l’enforcement.

## Mise à jour du 3 septembre 2026 — abonnements, lot 10 (durée dans la fenêtre de confirmation)

- **Parcours utilisateur corrigé** : le choix de durée n’est plus dispersé dans les cartes de plans. Le bouton « Choisir la durée » ouvre une fenêtre de confirmation contenant le sélecteur 1–12 mois.
- **Calcul interactif** : à chaque changement de durée, le montant total, la date d’expiration estimée et l’état de la réduction sont recalculés immédiatement dans la fenêtre, avant toute validation. La règle métier reste inchangée : prix mensuel × mois pour 1–11 mois, prix annuel réduit uniquement à 12 mois.
- **Précaution financière** : la fenêtre s’ouvre par défaut sur 1 mois afin d’éviter qu’un engagement annuel soit sélectionné involontairement ; l’utilisateur doit choisir explicitement 12 mois pour obtenir la réduction.
- **Affichage maîtrisé** : aucune mention du prestataire de paiement n’est affichée dans la page ou la fenêtre de confirmation ; le prestataire reste uniquement une implémentation serveur après validation.
- **Sécurité du bouton** : la confirmation utilise le loader SweetAlert, bloque les clics extérieurs pendant la requête et transmet au serveur la durée effectivement sélectionnée. Le serveur recalcule toujours le montant et la durée avant de créer le checkout.
- **Validation** : `SubscriptionAccessTest`, `SubscriptionDurationTest`, `SubscriptionPaymentTest`, `SubscriptionWebhookTest` et `SubscriptionExpiryCommandTest` passent : **14 tests, 72 assertions, 0 échec**. `php artisan view:cache` passe également.

### Point de reprise

- Recetter visuellement le modal dans le navigateur aux largeurs 1440/1024/768/390 px, en vérifiant les valeurs 1, 3, 11 et 12 mois ainsi que le renouvellement et la montée de plan.
- Ne pas activer l’enforcement en production et ne pas effectuer de paiement réel avant la recette finale, la configuration SMTP et le checkout de test du prestataire.

## Mise à jour du 3 septembre 2026 — abonnements, lot 11 (sélecteur de durée mobile)

- Le sélecteur natif qui pouvait être difficile à faire défiler dans certains WebView mobiles est remplacé par une liste de 12 boutons de durée dans le modal.
- La liste possède une hauteur maximale, `overflow-y: auto`, inertie de défilement tactile et une grille responsive (3 colonnes desktop, 2 colonnes mobile). La durée sélectionnée est mise en évidence et annoncée via `aria-selected`.
- Le recalcul du montant, de l’expiration et de la réduction reste immédiat après chaque sélection ; aucune route serveur ni règle financière n’a changé.
- Contrôles : syntaxe JavaScript, `php artisan view:cache` et `git diff --check` passent. Une vérification manuelle sur un vrai navigateur mobile reste recommandée.

## Mise à jour du 3 septembre 2026 — abonnements, lot 12 (notification administration après paiement)

- **Notification après confirmation serveur** : lorsqu’un paiement d’abonnement est vérifié avec succès, tous les administrateurs plateforme actifs disposant d’une adresse e-mail reçoivent `SubscriptionActivatedNotification`.
- **Détails transmis** : entreprise facturée, plan issu du snapshot, durée, opération (renouvellement/montée de plan), montant et devise, période d’accès, transaction et référence de paiement. L’e-mail ne part jamais sur un simple clic ou un paiement non confirmé.
- **Sûreté financière** : l’envoi est exécuté après la transaction de règlement ; une panne SMTP ne peut pas annuler l’abonnement ni les crédits. Le journal `subscription_events` conserve un état par administrateur (`sending`, `sent`, `failed` ou `disabled`) et les rejeux n’envoient pas deux fois un message déjà marqué `sent`.
- **Configuration** : le réglage plateforme `services.email.enabled` est respecté. Si l’e-mail est désactivé, l’événement est marqué `disabled` sans tentative externe. Les administrateurs autorisés au pré-contrôle reçoivent en plus un bouton d’accès à cette page.
- **Tests** : `SubscriptionPaymentTest` vérifie les détails et l’idempotence (**3 tests, 11 assertions**) ; `SubscriptionWebhookTest` et `SubscriptionDurationTest` restent verts (**7 tests, 40 assertions**). Lint PHP, cache Blade et `git diff --check` passent.

### Point de reprise

- Vérifier en staging le relais SMTP et la liste réelle des administrateurs plateforme actifs ; aucun e-mail de production n’a été envoyé ici.
- Recetter le rendu du message, puis relancer la suite abonnement complète avant activation de l’enforcement.

## Mise à jour du 3 septembre 2026 — paiements de quotas, lot 13 (notification administration)

- **Notification après crédit confirmé** : les paiements de quotas SMS/WhatsApp réglés avec succès déclenchent `QuotaPaymentConfirmedNotification` pour chaque administrateur plateforme actif ayant une adresse e-mail.
- **Détails transmis** : entreprise, acheteur, quantités SMS et WhatsApp créditées, montant/devise, transaction, référence de paiement et date de confirmation. Les paiements échoués, expirés ou non vérifiés ne déclenchent aucun e-mail.
- **Sûreté et idempotence** : l’e-mail est envoyé après la transaction qui crédite les quotas, afin qu’une panne SMTP ne puisse pas annuler le crédit. La colonne JSON `quota_payments.administration_email_status` conserve l’état par administrateur et empêche les renvois lors des webhooks/reconciliations rejoués ; les états `sending` anciens peuvent être repris.
- **Configuration** : le réglage `services.email.enabled` est respecté. Les administrateurs disposant de `platform.payments.view` reçoivent un bouton vers la liste des paiements plateforme.
- **Migration locale** : `2026_09_03_220000_add_administration_email_status_to_quota_payments` est appliquée.
- **Tests** : `QuotaPaymentTest` — **6 tests, 51 assertions, 0 échec** ; lint PHP et migration passent.

### Point de reprise

- Vérifier en staging le relais SMTP, les administrateurs plateforme actifs et le rendu de l’e-mail ; aucun message réel de production n’a été envoyé.
- Relancer la suite paiement abonnement + quotas avant activation progressive de l’enforcement.

## Mise à jour du 3 septembre 2026 — documentation rationalisée

- Les cahiers des charges Administration SaaS terminés, le prompt historique d’implémentation des abonnements, le rapport d’audit daté et la convention UI redondante ont été retirés. Les scripts servant uniquement à générer leurs anciens exports ont également été supprimés.
- `docs/README.md` devient l’index court des références à conserver : reprise, rapports permanents, paiements, déploiement, UI/UX active, stratégie tarifaire, architecture SaaS et audits de sécurité.
- Les exports PDF binaires historiques ne sont plus référencés. Ils restent signalés dans l’index comme candidats au prochain nettoyage local si l’outil de suppression binaire n’est pas disponible dans l’environnement de travail.

## Fixture locale de recette — compte propriétaire sans paiement

- Le compte propriétaire `didierlombardo48@gmail.com` de la société `Matrix` a été utilisé pour des essais manuels sur plusieurs plans. Son plan, son statut et sa date d’expiration sont volontairement mutables et doivent être relus dans la base locale avant chaque recette.
- Ces changements locaux sont effectués sans `subscription_payment`, sans crédit automatique de quota et sans appel KPrimePay. Ils servent uniquement à tester les limites, fonctionnalités, SweetAlert et changements de plan.
- Après la recette, supprimer ou prolonger explicitement la fixture selon le besoin ; ne jamais la reproduire en production.

## Validation propriétaire — staging abonnements et quotas

- Le propriétaire confirme que le checkout de test sécurisé fonctionne sur staging, que les webhooks KPrimePay reviennent correctement et que le SMTP réel de staging a été testé.
- La recette visuelle finale mobile/desktop des parcours concernés est déclarée conforme.
- Le webhook unique `/api/kprimepay/webhook` reste le point d’entrée production pour les deux familles : `SUB-*` active un abonnement et `QUOTA-*` crédite les quotas. Les URLs de retour navigateur ne sont pas utilisées comme preuve de paiement.
- La phase de développement fonctionnel est donc considérée comme **terminée (100 %)**. Il reste uniquement la configuration contrôlée de production (variables SMTP/KPrimePay, URL webhook, déploiement, supervision et activation progressive de l’enforcement).

## Mise à jour du 8 septembre 2026 — architecture de la plateforme Partenaires

- Le cahier complet `docs/CAHIER_ARCHITECTURE_PLATEFORME_PARTENAIRES.md` a été ajouté. Il couvre l’inscription et l’authentification partenaire, le sous-domaine, les codes uniques, l’attribution à vie au niveau du `subscription_account`, la remise du premier abonnement, les commissions récurrentes, les paliers progressifs 10–25 %, le portefeuille, les retraits Mobile Money, l’administration, la sécurité, la performance, les tests et le lancement progressif.
- **Architecture retenue** : monolithe modulaire dans le projet Laravel et la base MySQL actuels, avec garde/session/routes/vues/tables partenaires séparés. Aucun appel API HTTP interne n’est nécessaire en V1 ; une extraction future reste possible via événements/outbox.
- **Sûreté financière** : montants XOF entiers, snapshot du brut/remise/net/taux, attribution et commission créées atomiquement pendant le règlement vérifié, grand livre immuable, compensations au lieu de suppressions, idempotence et verrous sur paiements/retraits.
- **KPrimePay** : les encaissements existants restent inchangés. Les payouts devront utiliser un service et une clé distincts, les scopes payout/read, l’IP autorisée, une idempotency key persistée, le statut crédit et les webhooks de transfert. Aucun payout n’a été appelé ni simulé dans ce lot documentaire.
- **Règle de palier corrigée et fixée** : le taux part de 10 % et s’arrête à 25 %. Il faut 5 nouveaux clients qualifiés par point de 10 à 15 %, puis 10 clients par point de 15 à 20 %, puis 20 clients par point de 20 à 25 %. Ainsi, les rangs 26–35 gagnent 15 %, 36–45 gagnent 16 %, jusqu’aux rangs 156–175 à 24 %, puis le rang 176 et tous les suivants gagnent 25 %. Le taux est acquis par chaque client et conservé lors de ses renouvellements.
- **Décisions produit/juridiques à confirmer avant implémentation financière** : commission sur brut, taux acquis conservé, maturité 7 jours, minimum de retrait proposé à 5 000 XOF en plus des 3 clients, revue manuelle initiale, frais, fiscalité/KYC et remboursements.
- **État réel** : architecture/documentation uniquement. Le module partenaire n’est pas encore développé. La reprise doit commencer par la Phase 0 du cahier, puis progresser phase par phase avec tests concomitants et mise à jour de ce handoff à chaque lot.
- **Contrat UI/UX partenaire** : le futur portail doit reprendre concrètement le template SaaS Maxanou. `layouts.partner` sera construit à partir du shell, des tokens, thèmes et assets partagés de `layouts.saas`, avec navigation partenaire uniquement ; il ne doit pas copier une nouvelle feuille CSS ni réintroduire l’ancien template. Les vues doivent employer les composants `x-ui.*` existants, les DataTables partagées, le responsive 320–1440 px, l’accessibilité et `ServerButtonLoader`. `php artisan ui:lint`, `view:cache`, les tests de rendu et la recette clair/sombre mobile/desktop sont des gates obligatoires de chaque lot d’interface.
- **Prompt de reprise prêt** : `docs/PROMPT_DEMARRAGE_IMPLEMENTATION_PARTENAIRES.md` peut être copié dans une nouvelle discussion. Il impose une fonctionnalité à la fois, tests automatisés concomitants, inspection visuelle par l’agent, rapport complet, mise à jour du handoff, scénario de recette manuelle, arrêt obligatoire et autorisation explicite du propriétaire avant de poursuivre.

## Mise à jour du 8 septembre 2026 — message d’erreur du mot de passe administrateur

- Les actions d’administration protégées par `current_password:platform` renvoient désormais un message explicite lorsque le mot de passe est incorrect : **« Votre mot de passe plateforme est incorrect. »** ; un message distinct indique lorsqu’il est absent.
- Le correctif couvre les paramètres généraux, les exceptions d’abonnement par entreprise, les tarifs/coûts, les réglages partenaires, les alertes, le catalogue de plans, ainsi que la gestion des comptes administrateurs. Les réponses JSON utilisées par les SweetAlert récupèrent donc aussi ce message exploitable.
- Des assertions Feature vérifient le texte exact pour les paramètres généraux, l’exception par entreprise et les tarifs.
- `php artisan view:cache`, les vérifications `php -l` des contrôleurs touchés et `git diff --check` passent. La suite PHPUnit ciblée n’a pas pu démarrer dans cet environnement : Symfony/PHP échoue avant les tests avec `proc_open(): Command conversion failed` sous Windows. À relancer dans l’environnement local PHP habituel avant la recette manuelle.

## Mise à jour du 8 septembre 2026 — Partenaires, lot 1 : fondation technique désactivée

### Réalisé

- Fondation additive : modèles `Partner`, `PartnerPromoCode`, `PartnerTwoFactorChallenge` et `PartnerAuditLog`, factory partenaire, garde `partner`, provider séparé et broker `partner_password_reset_tokens`.
- Migration `2026_09_08_090000_create_partner_foundation_tables.php` : identités partenaires, jetons de réinitialisation, challenges 2FA hashés, codes promotionnels normalisés et journal d’audit. Les clés étrangères sont restrictives ; les identifiants, e-mails, téléphones et codes normalisés ont des contraintes uniques ; les index de statut, audit et 2FA sont présents.
- `config/partners.php` et réglages plateforme : acquisition, inscription et payouts sont tous à `false`; remise initiale 1 000 bps, plafond 2 500 bps, maturité 7 jours, minimum 5 000 XOF et minimum 3 clients.
- Permissions plateforme préparées : Support peut consulter les partenaires ; Finance peut consulter partenaires, commissions et retraits. La gestion, l’approbation et les ajustements ne sont pas attribués.

### Hors périmètre volontaire

- Aucune route, vue, écran, inscription, connexion, e-mail, attribution, checkout, remise, commission, grand livre, retrait, appel KPrimePay ou payout.
- `SubscriptionSettlementService`, `SubscriptionCheckoutService`, `KprimePayService` et le webhook existant ne sont pas modifiés.

### Validations exactes

- `php artisan test tests/Feature/PartnerFoundationTest.php tests/Feature/SubscriptionFoundationTest.php --no-coverage` avec `ANSICON=120x40` temporaire : **5 tests, 25 assertions, 0 échec**. Cette variable contourne uniquement l’échec Windows de détection des dimensions du terminal (`mode CON`), sans changer le dépôt ni Laravel.
- `php artisan migrate --pretend`, puis `php artisan migrate --force` : migration valide, strictement additive et appliquée sur la base locale applicative comme lot **35**. Les onze réglages `partners.*` ont été relus en lecture seule après migration ; acquisition, inscription et payouts sont bien à `false`. `RefreshDatabase` l’a aussi exercée dans la base de test.
- `php artisan route:list --name=partner` : aucune route, attendu à ce stade.
- Lint PHP : passé. `git diff --check` : propre.

### Recette visuelle

- Non applicable et non prétendue : aucune interface ni route publique n’est livrée. `php artisan view:cache` et `php artisan ui:lint` ne sont pas requis, aucune vue/UI n’ayant été touchée.

### Risques et reprise

- Les décisions commerciales et juridiques restent nécessaires avant les lots financiers : base de commission brute, fiscalité/KYC, frais, remboursements, conditions partenaires et compte payout staging.
- Prochain lot, après validation manuelle explicite : authentification partenaire et premier `layouts.partner` fondé sur le shell SaaS partagé, sans code appliquable, checkout ou commission.

## Mise à jour du 8 septembre 2026 — Partenaires, lot 2 : authentification et premier portail

### Réalisé

- Authentification séparée via le guard/provider `partner`, routes nommées `partner.*` sous `/partner` (ou sous-domaine `PARTNER_DOMAIN` s’il est configuré), et middlewares `partner.auth`, `partner.active`, `partners.enabled`.
- Parcours livrés : connexion, inscription conditionnelle, vérification e-mail par URL signée à durée limitée, 2FA e-mail avec code hashé à usage unique et compteur de tentatives, réinitialisation de mot de passe via le broker partenaire, déconnexion et invalidation par `auth_version`.
- Inscription volontairement fermée par défaut ; le portail protégé renvoie une page d’indisponibilité tant que `partners.enabled` est désactivé. Aucun e-mail réel n’a été envoyé.
- Premier `layouts.partner` construit sur le shell SaaS partagé et les assets/tokens existants, avec navigation partenaire limitée au tableau de bord et au profil. Le tableau de bord expose uniquement l’état de fondation ; aucun code promo, checkout, attribution, commission, portefeuille, retrait ou payout n’est inclus.
- Profil partenaire avec préférences d’apparence `system/light/dark` et couleur d’accent validée côté serveur. Les vues d’authentification utilisent les composants `x-ui.*` ; le shell conserve `ServerButtonLoader`, thèmes et responsive partagés.

### Fichiers principaux

- `app/Http/Controllers/Partner/AuthController.php`, `app/Http/Controllers/Partner/PortalController.php`
- `app/Services/PartnerAuthenticationService.php`
- `app/Http/Middleware/AuthenticatePartner.php`, `EnsurePartnerActive.php`, `EnsurePartnersEnabled.php`
- Notifications partenaire e-mail/2FA/reset, `routes/web.php`, `resources/views/layouts/partner.blade.php` et `resources/views/partner/**`
- `tests/Feature/PartnerAuthenticationTest.php`

### Validations exactes

- Composant partagé corrigé : le bouton de visibilité du mot de passe est unifié avec l’amélioration automatique du design system, sans doublon visuel.

- `php artisan test tests/Feature/PartnerFoundationTest.php tests/Feature/PartnerAuthenticationTest.php tests/Feature/SubscriptionFoundationTest.php --no-coverage` avec `ANSICON=120x40` temporaire : **11 tests, 63 assertions, 0 échec**.
- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- `git diff --check` : propre.
- `php artisan route:list --name=partner` : 16 routes nommées présentes.

### Inspection visuelle

- Navigateur intégré inspecté sur `/partner/login`, `/partner/register` et `/partner/forgot-password` en thème sombre, à l’état initial et sans soumettre de données ; la page `/partner/login` est laissée ouverte pour la revue propriétaire.
- Le rendu reprend le branding Maxanou, les contrôles d’apparence, les composants de formulaire, les états de focus et le shell partagé. La recette manuelle doit encore confirmer les largeurs 320–1440 px et les thèmes clair/sombre sur un compte partenaire de test autorisé.

### Recette manuelle proposée — gate obligatoire

1. En environnement local/staging isolé, activer temporairement `partners.enabled` puis `partners.registration_enabled` dans les réglages plateforme ; ne pas activer les payouts.
2. Créer un compte partenaire avec une adresse de test, confirmer le lien signé reçu, puis vérifier que le compte passe de `pending_email` à `active`.
3. Se connecter avec un mot de passe robuste ; activer la 2FA sur ce compte, vérifier la réception du code, son expiration et l’impossibilité de le réutiliser.
4. Ouvrir le tableau de bord et le profil, tester le changement d’apparence et vérifier le rendu clair/sombre à 1440, 1024, 768 et 390 px.
5. Tester la demande de reset, l’utilisation unique du lien, la déconnexion et l’invalidation de session après changement de mot de passe.
6. Repasser les réglages partenaires à `false` et confirmer que le portail protégé est indisponible. Ne déclencher aucun paiement, checkout, commission ou payout.

### Risques et reprise

- La notification est actuellement synchrone et dépend de la configuration SMTP ; la recette doit utiliser un relais de test ou un fake, jamais la production.
- La prochaine phase ne doit commencer qu’après validation manuelle de ce lot et autorisation explicite du propriétaire. Elle pourra traiter la gestion des codes partenaires uniquement après confirmation des décisions commerciales/juridiques de la Phase 0.

## Mise à jour du 8 septembre 2026 — Partenaires, ajustement inscription et pays actifs

### Réalisé

- Le formulaire demande désormais uniquement le pays de résidence et le champ « Numéro ». Le champ « Pays du téléphone » et le libellé « Téléphone E.164 » ont été retirés.
- Le Togo est le seul pays actif par défaut (`TG`, `+228`). Le préfixe affiché et l’aide de saisie sont recalculés côté navigateur lors du changement de pays ; la normalisation et les longueurs autorisées restent contrôlées côté serveur.
- `PartnerCountryService` centralise le catalogue, les pays actifs et la conversion du numéro national vers le format E.164 stocké. Les autres pays restent inactifs jusqu’à activation administrative.
- Une page Administration / Paramètres / Partenaires permet au super-administrateur de gérer l’ouverture du portail, l’ouverture des inscriptions et la liste des pays actifs. Chaque changement exige un motif et le mot de passe plateforme, puis est inscrit dans l’historique et le journal d’audit. Les payouts restent explicitement verrouillés.
- Migration `2026_09_08_130000_add_partner_active_countries_setting` appliquée localement comme lot **36** ; elle initialise `partners.active_countries` à `["TG"]`.

### Validations exactes

- `php artisan test tests/Feature/PartnerAuthenticationTest.php tests/Feature/PlatformPartnerSettingTest.php tests/Feature/PartnerFoundationTest.php tests/Feature/PlatformGeneralSettingTest.php --no-coverage` avec `ANSICON=120x40` temporaire : **15 tests, 83 assertions, 0 échec**.
- `php artisan migrate --force` : migration du réglage pays appliquée.
- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- `git diff --check` : propre.
- `php artisan route:list --name=partner` : 16 routes partenaire ; `platform.settings.partners` : 2 routes d’administration.

### Inspection visuelle

- Navigateur intégré inspecté sur `/partner/register` en thème sombre et largeur responsive disponible : le formulaire affiche `Pays de résidence`, `Togo (+228)` et `Numéro`, sans les deux anciens libellés.
- La validation visuelle des actions d’administration doit être faite avec un compte super-administrateur autorisé ; aucune authentification ni donnée sensible n’a été saisie par l’agent.

### Recette manuelle ciblée

1. Ouvrir `http://127.0.0.1:1111/platform/settings/partners` avec un compte super-administrateur.
2. Vérifier que Togo est le seul pays actif et que le portail/inscription sont désactivés par défaut.
3. Activer le portail et l’inscription, sélectionner éventuellement un second pays, saisir un motif et confirmer avec le mot de passe plateforme.
4. Ouvrir `/partner/register`, changer de pays et vérifier que l’indicatif affiché et l’aide du champ « Numéro » changent immédiatement.
5. Créer un compte de test avec un numéro national, vérifier que la valeur stockée est normalisée avec l’indicatif du pays sélectionné.
6. Désactiver ensuite le second pays et confirmer que les nouvelles inscriptions pour ce pays sont refusées ; laisser `partners.payouts_enabled` à `false`.

### Gate

Je m’arrête après ce lot. La suite nécessite ta validation visuelle et fonctionnelle de l’inscription et de l’écran d’administration, puis une autorisation explicite pour continuer.

## Mise à jour du 8 septembre 2026 — Harmonisation des erreurs d’authentification

- Les alertes d’erreur des pages d’authentification publiques et plateforme utilisent maintenant un fond rouge plein, un texte blanc et une icône blanche pour une lecture immédiate.
- Une marge basse dédiée sépare l’alerte du premier champ, notamment le libellé « Nom complet » du formulaire d’inscription partenaire.
- Les versions CSS des layouts `public-auth` et `platform-auth` ont été incrémentées pour éviter un cache navigateur obsolète.
- Contrôles : tests partenaires et réglages plateforme **9 tests, 51 assertions, 0 échec**, `view:cache`, `ui:lint --changed` et `git diff --check` réussis.

## Mise à jour du 8 septembre 2026 — Affichage des erreurs d’authentification

- Les alertes d’erreur des layouts `public-auth`, `platform-auth` et de la page de sécurisation plateforme utilisent maintenant un fond rouge plein, un texte blanc et une icône blanche.
- Une marge inférieure dédiée crée un espace visuel entre le message d’erreur et le premier libellé du formulaire, notamment « Nom complet » lors d’une inscription partenaire fermée.
- Les versions CSS des layouts concernés ont été incrémentées afin d’éviter un ancien cache navigateur.
- Contrôles : **15 tests, 83 assertions, 0 échec**, `view:cache`, `ui:lint --changed` et `git diff --check` réussis. La page `/partner/register` est laissée ouverte dans le navigateur intégré.

## Mise à jour du 8 septembre 2026 — Messages de validation du mot de passe en français

- Les règles Laravel du mot de passe partenaire (`min`, `mixed`, `numbers`, `symbols`, `confirmed` et `uncompromised`) disposent maintenant de messages français explicites.
- Une aide française permanente rappelle sous le champ : « 12 caractères minimum, avec majuscule, minuscule, chiffre et symbole. »
- Contrôles finaux : **18 tests, 90 assertions, 0 échec**, `view:cache`, `ui:lint --changed` et `git diff --check` réussis.

## Mise à jour du 8 septembre 2026 — Indicatif intégré au champ Numéro

- L’indicatif du pays est maintenant affiché à l’intérieur du contrôle du numéro, en texte non éditable ; le partenaire saisit uniquement le numéro national.
- L’accessibilité annonce l’indicatif courant dans le libellé du champ, et le changement de pays met à jour simultanément le préfixe et l’aide de format.
- Contrôles : **10 tests, 54 assertions, 0 échec** pour les tests partenaires/authentification, `view:cache`, `ui:lint --changed` et `git diff --check` réussis. Le navigateur intégré a confirmé le passage visuel Togo `+228` → Bénin `+229`, puis a été remis sur Togo.

## Mise à jour du 8 septembre 2026 — Validation française des parcours d’authentification

- Les validations du parcours partenaire sont maintenant entièrement explicites en français : connexion, inscription, pays, numéro, e-mail, pseudonyme, conditions, 2FA, réinitialisation du mot de passe et préférences d’apparence du portail.
- Le dépassement du champ numéro est couvert par le message exact : « Le numéro ne doit pas dépasser 24 caractères. » ; les règles de mot de passe et les validations de l’administration partenaires ont également leurs messages français.
- Les validations des parcours d’authentification plateforme (connexion, 2FA, mot de passe oublié et réinitialisation) ont été harmonisées pour ne plus laisser remonter les messages Laravel en anglais.
- Contrôles finaux : **19 tests, 93 assertions, 0 échec**, `view:cache`, `ui:lint --changed`, vérifications PHP de syntaxe et `git diff --check` réussis.
- Inspection visuelle : `/partner/register` reste ouvert dans le navigateur intégré ; l’arbre d’accessibilité confirme `Pays de résidence`, `Togo (+228)`, `Numéro` et l’aide de mot de passe en français.

### Recette manuelle ciblée

1. Sur `/partner/register`, soumettre un numéro de plus de 24 caractères et vérifier le message rouge/blanc « Le numéro ne doit pas dépasser 24 caractères. ».
2. Vérifier les messages français pour un e-mail invalide, un pseudonyme invalide, une case de conditions non cochée et un mot de passe insuffisant.
3. Vérifier les mêmes états sur `/partner/login`, `/partner/forgot-password`, `/partner/reset-password/...`, la 2FA partenaire et les écrans d’authentification plateforme.
4. Dans `/platform/settings/partners`, vérifier les erreurs françaises liées aux pays actifs, au motif et au mot de passe de confirmation.

### Gate

Je m’arrête après cette correction. La suite attend ta validation manuelle des messages et du rendu, puis ton autorisation explicite de continuer.

## Mise à jour du 8 septembre 2026 — Confirmation e-mail après inscription

- Après une inscription réussie, l’utilisateur est redirigé vers la connexion avec un message explicite : un lien de validation a été envoyé par e-mail, le compte doit être activé par ce lien et la connexion reste bloquée tant que l’adresse n’est pas confirmée.
- Si un partenaire saisit des identifiants valides alors que son compte est encore `pending_email`, la page de connexion explique directement qu’il doit cliquer sur le lien reçu avant de réessayer. Un mot de passe incorrect conserve le message générique afin de ne pas divulguer d’information de compte.
- Le test de parcours couvre désormais l’information post-inscription et le blocage explicite avant validation e-mail.
- Contrôles finaux : **20 tests, 98 assertions, 0 échec**, `view:cache`, `ui:lint --changed`, syntaxe PHP et `git diff --check` réussis.

### Recette manuelle ciblée

1. Activer temporairement les inscriptions dans l’environnement isolé et créer un compte avec une adresse e-mail de test.
2. Vérifier la redirection vers la connexion et le message indiquant de cliquer sur le lien reçu.
3. Avant de cliquer sur le lien, tenter une connexion avec le bon mot de passe : vérifier l’instruction de validation et l’absence d’accès au tableau de bord.
4. Cliquer sur le lien signé reçu, puis se connecter avec les mêmes identifiants : l’accès doit être autorisé.

### Gate

Je m’arrête après cette correction. La suite attend ta validation manuelle du message, du lien reçu et du comportement de connexion, puis ton autorisation explicite de continuer.

## Mise à jour du 8 septembre 2026 — Apparence dynamique du profil partenaire

- Le profil partenaire utilise maintenant le même système d’apparence que le reste du portail : cartes de sélection pour `Selon l’appareil`, `Sombre` et `Clair`, aperçu immédiat du changement et accordéon lisible sur mobile.
- La palette propose des couleurs rapides et une couleur personnalisée synchronisée entre le sélecteur visuel, le champ hexadécimal, le résumé et l’aperçu. Le contraste du texte d’action est calculé automatiquement.
- Les préférences sont appliquées instantanément par `DesignSystem.apply`, puis persistées par le formulaire du profil. L’initialisation du thème tient désormais compte du guard partenaire afin de restaurer la préférence dès le chargement de la page.
- Contrôles finaux : **21 tests, 104 assertions, 0 échec**, `view:cache`, `ui:lint --changed`, syntaxe PHP et `git diff --check` réussis.

### Recette manuelle ciblée

1. Ouvrir `/partner/profile` avec un compte partenaire actif.
2. Dans « Mode d’affichage », choisir `Clair`, `Sombre` puis `Selon l’appareil` et vérifier que toute l’interface se met à jour immédiatement.
3. Ouvrir « Couleur dominante », choisir une pastille puis une couleur personnalisée ; vérifier le bouton d’aperçu, le résumé hexadécimal et la lisibilité du texte.
4. Enregistrer, recharger la page et vérifier que le mode et la couleur sont conservés sur le profil et le tableau de bord.
5. Refaire le test sur une largeur mobile et avec le thème système clair/sombre.

### Gate

Je m’arrête après cette correction. La suite attend ta validation visuelle du profil partenaire et de la persistance des préférences, puis ton autorisation explicite de continuer.

## Mise à jour du 8 septembre 2026 — Cartes de sélection d’entreprise sur mobile

- La recette visuelle de `/companies/select` a confirmé un chevauchement réel entre les cartes lorsque la vue passe en une seule colonne.
- Cause : la hauteur `100%` appliquée simultanément au formulaire parent et à la carte imbriquée créait une ligne CSS plus courte que son contenu.
- Correction : en dessous de 560 px, les formulaires deviennent des conteneurs flex en hauteur automatique et les cartes reprennent leur hauteur intrinsèque, avec un espacement vertical stable.
- Vérification dans l’onglet local : les cartes Matrix et FENIX sont désormais séparées, sans recouvrement ; le bouton et le contenu restent entièrement visibles.
- Contrôles techniques : `php artisan view:cache` et `git diff --check` réussis.

## Mise à jour du 9 septembre 2026 — Refonte du détail utilisateur

- L’écran `/platform/users/{id}` suit maintenant la même structure que le détail entreprise : en-tête profil, statut, métriques synthétiques, informations, adhésions et historique.
- Les anciennes définitions Bootstrap qui se compressaient sur mobile ont été remplacées par une grille de détails stable et des panneaux SaaS dédiés.
- Les tables d’adhésions et d’invitations restent contenues dans leurs panneaux avec défilement horizontal lorsque nécessaire.
- Vérification visuelle locale sur mobile et contrôles `php artisan view:cache` / `git diff --check` réussis.

## Mise à jour du 11 septembre 2026 — Notification e-mail immédiate d’inventaire

- Les entrées et sorties créées depuis le module Inventaire déclenchent désormais `SendInventoryEmailJob` après validation de la transaction.
- Le job respecte le canal `inventory_email_enabled`, les destinataires e-mail configurés dans la catégorie `inventory`, la disponibilité globale du canal et `NotificationDeliveryService` pour éviter les doublons et tracer les échecs.
- Un modèle e-mail dédié détaille le type de mouvement, le produit, le fournisseur, les quantités avant/après, l’auteur, la date et la note.
- Les mouvements de sortie générés automatiquement par une vente (`note` commençant par `Vente #`) sont explicitement ignorés par les alertes d’inventaire : la vente conserve sa notification propre.
- Le job WhatsApp/SMS applique le même garde-fou pour éviter toute double alerte si un mouvement de vente lui est transmis.
- Contrôles : syntaxe PHP, `php artisan view:cache`, `git diff --check` réussis. Les tests fonctionnels d’inventaire restent bloqués par l’environnement Windows (`proc_open(): Command conversion failed`), tandis que le test unitaire de fiabilité des jobs passe.

### Gate

Je m’arrête après cette correction. La suite attend ta validation manuelle sur ton appareil mobile (sélection de plusieurs entreprises et défilement), puis ton autorisation explicite de continuer.

## Mise à jour du 9 septembre 2026 — Textareas partenaires sans débordement

- Inspection de `/platform/settings/partners` en largeur mobile (543 px) : le champ « Motif de la modification » débordait de son panneau à cause de `width: 100%` combiné au modèle de boîte `content-box`.
- Correction mutualisée : les champs `input`, `select` et `textarea` du toolkit SaaS utilisent maintenant `box-sizing: border-box`, `min-width: 0` et `max-width: 100%`.
- Le même garde-fou est appliqué aux champs de paramètres plateforme, afin de couvrir les autres écrans partenaires et administrateur.
- Vérification visuelle après actualisation du cache : le textarea reste contenu dans la carte, sur mobile comme sur les largeurs supérieures.

## Mise à jour du 9 septembre 2026 — Refonte du détail entreprise dans l’administration

- L’écran `/platform/companies/{id}` a été restructuré selon le template SaaS : en-tête entreprise avec retour et action de statut, indicateurs regroupés, panneau d’identité, équipe et finance séparés.
- Les informations d’identité sont désormais présentées dans une grille lisible avec gestion des identifiants longs ; les tables restent scrollables sans casser la carte sur mobile.
- La vue mobile a été vérifiée dans le navigateur local : l’en-tête, les métriques, l’identité et les sections inférieures sont lisibles et espacés sans débordement.
- Contrôles techniques : `php artisan view:cache` et `git diff --check` réussis.

## Mise à jour du 9 septembre 2026 — Phase 3, cycle des codes partenaires

- Chaque partenaire dont l’e-mail est validé reçoit automatiquement un code principal actif, unique et non prédictible, au format canonique de 4 à 24 caractères alphanumériques majuscules.
- L’écran `/partner/code` permet de consulter, copier et personnaliser le code. Les codes réservés, déjà utilisés ou invalides sont refusés ; l’ancien code est retiré et le changement suivant est bloqué pendant le délai administrable (30 jours par défaut).
- Le validateur public `POST /partner/code/validate` est volontairement neutre : il ne renvoie que `valid`, `discount_percent` et un message métier, avec limitation par IP, session et compte.
- Le tableau de bord affiche désormais l’état du code, la remise configurée et un état vide pour les statistiques à venir. Aucun checkout, paiement, commission ou payout n’a été branché dans ce lot.
- L’administration partenaires expose le délai de personnalisation du code (1 à 365 jours), en plus des pays actifs et des interrupteurs du portail ; toute modification reste confirmée par mot de passe et journalisée.

### Fichiers principaux modifiés

- `app/Services/PartnerCodeService.php`, `app/Services/PartnerAuthenticationService.php`, `app/Exceptions/PartnerCodeChangeTooSoon.php`.
- `app/Http/Controllers/Partner/CodeController.php`, `app/Http/Controllers/Partner/PortalController.php`, `app/Http/Controllers/Platform/PartnerSettingController.php`, `routes/web.php`.
- `resources/views/partner/code.blade.php`, `resources/views/partner/dashboard.blade.php`, `resources/views/platform/settings/partners.blade.php`, `resources/views/layouts/partner.blade.php`, `public/hub/assets/css/saas-pages.css`.
- `tests/Feature/PartnerCodeTest.php` et ajustement du test d’authentification/dashboard.

### Migrations et données

- Aucune nouvelle migration n’était nécessaire pour ce lot : la table `partner_promo_codes` et le réglage `partners.code_change_cooldown_days` proviennent de la fondation partenaire déjà appliquée.
- Les codes retirés restent conservés et protégés par l’unicité globale ; cela garantit une non-réattribution plus stricte que le minimum de 24 mois.

### Contrôles

- **19 tests, 100 assertions, 0 échec** sur le périmètre partenaires/authentification/réglages : `PartnerCodeTest`, `PartnerAuthenticationTest`, `PartnerFoundationTest`, `PlatformPartnerSettingTest`.
- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- `git diff --check` et syntaxe PHP des nouveaux contrôleurs/services/exceptions : propres.
- La suite complète a été lancée : **272 tests passent, 6 échecs observés hors périmètre** (`AuthNavigationTest`, `CompanyCreationTest`, `PlatformPaymentPricingTest`, `PlatformSubscriptionCatalogTest`, `SubscriptionAccessTest`, `SubscriptionExpiryCommandTest`). Aucun échec ne concerne les tests Phase 3.

### Inspection visuelle

- Avec la session partenaire déjà ouverte dans le navigateur intégré, `/partner/code` a été inspecté : hiérarchie lisible, code actif clairement mis en avant, bouton Copier accessible, formulaire de personnalisation responsive et états en français.
- Le tableau de bord `/partner` a été inspecté : code actif, remise, état vide statistique et lien « Gérer mon code » sont visibles sans branchement financier.

### Recette manuelle ciblée

1. Avec un compte partenaire actif, ouvrir `/partner` puis « Mon code partenaire » ; vérifier la présence du code actif et le bouton « Copier ».
2. Copier le code, ouvrir une fenêtre privée ou utiliser le validateur public prévu par l’intégration future, et vérifier que la réponse valide ne révèle aucune donnée partenaire.
3. Essayer un code personnalisé valide (4–24 lettres/chiffres), puis vérifier le message de succès et l’ancien code retiré.
4. Essayer `ADMIN`, un code déjà utilisé, un code avec symbole, puis refaire une modification immédiate : les erreurs doivent rester en français et indiquer le délai de 30 jours.
5. Avec un super-administrateur, ouvrir `/platform/settings/partners`, vérifier le délai affiché (30 jours), le modifier dans la plage 1–365 jours, saisir un motif et confirmer avec le mot de passe plateforme.
6. Vérifier que les pays actifs et les interrupteurs du portail restent inchangés et que les payouts demeurent désactivés.

### Gate

Je m’arrête ici après le premier lot autorisé de la Phase 3. J’attends ta validation manuelle du rendu et du parcours du code partenaire, puis ton autorisation explicite avant toute phase suivante.

## Mise à jour du 9 septembre 2026 — Disponibilité du code en temps réel

- Le champ de personnalisation vérifie désormais la disponibilité après chaque saisie (avec temporisation courte pour éviter les requêtes inutiles).
- Le partenaire voit immédiatement un état français : code disponible, code actuel, code réservé, code déjà utilisé, format invalide ou vérification momentanément indisponible.
- Le bouton « Enregistrer le code » est désactivé pendant la vérification et pour tout code indisponible ; il se réactive uniquement lorsque le serveur confirme la disponibilité.
- Un endpoint authentifié `GET /partner/code/availability` applique la même normalisation et la même règle d’unicité que l’enregistrement. Le contrôle serveur de `PUT /partner/code` reste obligatoire pour couvrir les courses et les appels directs.

### Contrôles

- **20 tests, 108 assertions, 0 échec** sur le périmètre partenaires/authentification/réglages, dont la nouvelle couverture de disponibilité (`PartnerCodeTest`).
- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- Syntaxe PHP et `git diff --check` : propres.

### Inspection visuelle

- Dans la session partenaire du navigateur intégré, le code actuel affiche « Votre code actuel est disponible » et le bouton est actif.
- La saisie frontend de `ADMIN` affiche « Ce code est réservé par la plateforme » et désactive le bouton ; la restauration du code actuel réactive le bouton. Aucune donnée n’a été enregistrée pendant cette vérification.

### Recette manuelle ciblée

1. Ouvrir `/partner/code` et saisir progressivement un nouveau code alphanumérique ; vérifier l’état « Vérification de la disponibilité… », puis « Ce code est disponible » et l’activation du bouton.
2. Saisir `ADMIN` ou un code déjà utilisé ; vérifier le message d’indisponibilité et le bouton désactivé.
3. Saisir un code contenant un espace, un symbole ou moins de 4 caractères ; vérifier le message de format et le bouton désactivé.
4. Revenir à un code disponible, vérifier la réactivation du bouton, puis enregistrer volontairement un code de test.

### Gate

Je m’arrête après cette extension de la Phase 3. J’attends ta validation manuelle du comportement temps réel avant toute nouvelle modification.

## Mise à jour du 9 septembre 2026 — Information du délai et conservation des paiements liés

- L’espacement entre l’aide du champ et le statut de disponibilité a été resserré pour garder le message immédiatement lisible sous le champ.
- Une note permanente informe le partenaire que le délai est de 30 jours par défaut après l’enregistrement (réglable par l’administration).
- La même note précise qu’un code désactivé n’annule pas les paiements déjà liés à ce code ni les conditions acquises par les clients concernés.
- La personnalisation ne supprime aucune ligne historique : elle retire uniquement l’ancien code pour les nouvelles utilisations et conserve sa traçabilité.

### Contrôles

- **20 tests, 109 assertions, 0 échec** sur le périmètre partenaires/authentification/réglages.
- `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP et `git diff --check` : succès.
- Inspection navigateur `/partner/code` : espacement resserré, note visible et responsive, sans modification de données.

### Gate

Je m’arrête ici et j’attends ta validation manuelle de ce libellé métier et de l’espacement avant de continuer.

## Mise à jour du 9 septembre 2026 — Mini-historique des changements de code

- L’écran `/partner/code` affiche maintenant une section « Historique des changements » sous le formulaire.
- Chaque ligne présente la date, l’ancien code et le nouveau code activé après la personnalisation.
- L’historique s’appuie sur les journaux partenaires déjà écrits lors d’un changement ; il est limité aux cinq derniers changements et ne révèle ni IP ni données d’audit sensibles.
- Lorsqu’aucun changement n’existe, un état vide explicite est affiché.
- Les anciens codes restent conservés : cette vue confirme qu’un retrait de code ne supprime pas sa traçabilité ni les paiements déjà liés.

### Contrôles

- **20 tests, 113 assertions, 0 échec** sur le périmètre partenaires/authentification/réglages.
- `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP et `git diff --check` : succès.
- Inspection navigateur `/partner/code` : état vide responsive vérifié ; la présentation reste compacte et lisible.

### Gate

Je m’arrête après l’ajout de l’historique. J’attends ta validation manuelle de l’affichage et du contenu avant toute nouvelle phase.

## Mise à jour du 9 septembre 2026 — Correction de l’enregistrement du code

- Cause reproduite : le chargeur global des boutons désactivait le bouton avant que la garde frontend de disponibilité ne s’exécute ; celle-ci annulait alors à tort chaque soumission.
- Correction : la garde s’appuie maintenant sur un état de disponibilité confirmé séparé de l’attribut HTML `disabled`. Le bouton reste désactivé pour un code indisponible, mais une disponibilité confirmée est bien soumise au serveur.
- Enregistrement réel vérifié dans le navigateur connecté avec le code de test `DIXON` : message de succès, code actif mis à jour et entrée d’historique ancien/nouveau visibles. Le délai de 30 jours démarre donc sur ce compte de recette.
- Les contrôles serveur, l’unicité et la conservation des anciens codes restent inchangés.

### Contrôles

- **20 tests, 115 assertions, 0 échec** sur le périmètre partenaires/authentification/réglages.
- `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP et `git diff --check` : succès.
- Inspection frontend : soumission réussie et historique affiché dans le navigateur intégré.

### Gate

Je m’arrête après cette correction. Le compte de recette est désormais soumis au délai de 30 jours ; j’attends ta validation manuelle du succès et de l’historique avant toute nouvelle modification.

## Mise à jour du 9 septembre 2026 — Profil partenaire et raccourci d’apparence

- Le profil partenaire reprend maintenant le parcours compte Maxanou : informations (nom/pseudonyme), e-mail, mot de passe et apparence sont répartis dans des onglets accessibles.
- Les changements d’identité, d’e-mail et de mot de passe exigent le mot de passe actuel, sont limités en fréquence et sont inscrits dans `partner_audit_logs` sans donnée sensible.
- Un changement d’e-mail invalide la session, remet le compte en attente de vérification et envoie un nouveau lien signé ; aucune connexion ne reste active tant que la nouvelle adresse n’est pas confirmée.
- Le topbar partenaire expose le même raccourci Apparence que le shell SaaS : modal, aperçu direct, modes système/sombre/clair, couleur dominante et lien vers les réglages complets. L’enregistrement AJAX réutilise `ServerButtonLoader`.
- Fichiers principaux : `Partner/PortalController.php`, `routes/web.php`, `layouts/partner.blade.php`, `partner/profile.blade.php`, `PartnerFoundationTest.php`.
- Contrôles passés : syntaxe PHP, `php artisan route:list --name=partner.profile`, `php artisan view:cache`, `php artisan ui:lint --changed`, `git diff --check`.
- Les tests partenaires n’ont pas pu être exécutés : l’environnement Windows échoue avant toute assertion dans `Symfony\\Console\\Terminal` avec `proc_open(): Command conversion failed`. Ce défaut touche aussi les tests existants ; il reste à corriger avant de pouvoir confirmer la suite automatisée.

### Gate

Recetter le profil à 1440, 1024, 768 et 390 px, puis vérifier le raccourci Apparence du topbar, la sauvegarde de chaque mode et le changement d’e-mail sur une boîte de test. Ne poursuivre qu’après validation manuelle.

## Mise à jour du 9 septembre 2026 — Lisibilité actions POS et SweetAlert

- Les boutons de confirmation SweetAlert affichent désormais un texte blanc par défaut, y compris dans le POS.
- Les boutons POS `Sauvegarder`, `En cours` et `Vendre` utilisent aussi un texte blanc par défaut.
- Les champs SweetAlert du POS, dont la saisie du montant donné par le client, sont centrés.
- Validation : `php artisan view:cache`, `php artisan ui:lint --changed` et `git diff --check` passent.

## Mise à jour du 9 septembre 2026 — Phase 4, prix promotionnel et attribution

- Le checkout d’abonnement accepte maintenant un code partenaire facultatif et expose `POST /subscription/preview`. Le serveur recalcule toujours le montant catalogue brut, la remise et le net en XOF entiers ; le navigateur n’envoie aucune valeur monétaire de confiance.
- La remise partenaire est de 10 % uniquement pour le premier abonnement payé d’un `subscription_account` sans attribution existante. Les renouvellements, montées de plan et comptes déjà payés restent au tarif normal.
- Un partenaire peut utiliser son propre code sur le compte de n’importe quelle entreprise ; l’éligibilité dépend uniquement de la validité du code et du fait que le compte n’a encore aucun abonnement payé ni attribution.
- La migration `2026_09_09_100000_create_partner_checkout_intents_and_attributions.php` ajoute les snapshots temporaires `partner_checkout_intents`, les attributions uniques et les colonnes financières complémentaires de `subscription_payments`. La migration a été appliquée sur la base locale et rejouée sur `pos_testing`.
- Le règlement verrouille le paiement et le compte d’abonnement, vérifie l’expiration de l’intention, crée une seule attribution après confirmation serveur KPrimePay et rejette une seconde intention concurrente sans remplacer l’attribution. Les commissions et payouts restent volontairement hors Phase 4.
- L’écran `resources/views/subscription/index.blade.php` affiche le code partenaire dans la fenêtre de durée et met à jour en temps réel brut, remise, total et message d’éligibilité via le preview serveur. Le bouton confirmé utilise `showLoaderOnConfirm` et bloque les doubles clics.

### Fichiers principaux

- `app/Services/PartnerPromotionService.php`, `app/Services/SubscriptionCheckoutService.php`, `app/Services/SubscriptionSettlementService.php`.
- `app/Models/PartnerCheckoutIntent.php`, `app/Models/PartnerAttribution.php`, `app/Models/SubscriptionPayment.php`, `app/Models/SubscriptionAccount.php`.
- `app/Http/Controllers/SubscriptionController.php`, `routes/web.php`, `resources/views/subscription/index.blade.php`.
- `database/migrations/2026_09_09_100000_create_partner_checkout_intents_and_attributions.php`, `tests/Feature/SubscriptionPartnerPromotionTest.php`.

### Contrôles

- **14 tests, 74 assertions, 0 échec** sur le nouveau périmètre et les tests abonnement concernés : `SubscriptionPartnerPromotionTest`, `SubscriptionDurationTest`, `SubscriptionPaymentTest`, `SubscriptionWebhookTest`.
- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- `git diff --check` et syntaxe PHP des services, contrôleur et migration : succès.
- La suite complète a été exécutée en série : **285 tests, 1694 assertions, 6 échecs hors Phase 4**. Les échecs concernent l’authentification/PWA, la création d’entreprise, des réglages plateforme et un cache d’enforcement préexistant ; aucun n’est dans `SubscriptionPartnerPromotionTest` ni dans le règlement KPrimePay d’abonnement.

### Inspection visuelle

- Le rendu serveur de la page Abonnement a été inspecté avec les cartes de plans, la fenêtre de durée, le champ « Code partenaire », le résumé brut/remise/total et les libellés français.
- Une tentative d’ouverture authentifiée dans le navigateur intégré sur une base `pos_testing` dédiée a été bloquée par l’expiration de session de la page de connexion ; aucun compte de production ni aucun paiement réel n’a été touché. La recette visuelle interactive reste donc à effectuer par le propriétaire.

### Recette manuelle ciblée

1. Avec un compte propriétaire/admin, ouvrir **Abonnement** et choisir le plan Bronze.
2. Choisir **1 mois**, saisir un code partenaire actif (par exemple le code de recette affiché dans `/partner/code`) et attendre la prévisualisation.
3. Vérifier l’affichage du brut `5 000 XOF`, de la remise `500 XOF` et du total `4 500 XOF`.
4. Vérifier qu’un code invalide affiche une erreur française et qu’aucun montant réduit n’est proposé.
5. Effectuer un premier paiement dans l’environnement sandbox/stub KPrimePay, puis confirmer le webhook serveur. Vérifier l’attribution unique du `subscription_account`.
6. Créer un second checkout avec le même ou un autre code et le confirmer : le total ne doit plus être réduit et aucune seconde attribution ne doit être créée.
7. Refaire le parcours aux durées 3 et 12 mois : vérifier respectivement `15 000 → 13 500 XOF` et `55 000 → 49 500 XOF`.

Résultat attendu : montant KPrimePay égal au net serveur, aucune attribution avant paiement confirmé, une seule attribution permanente et quotas inchangés par rapport au plan choisi.

### État

Phase 4 développée et testée côté serveur ; aucun paiement ou webhook réel n’a été exécuté. Les phases commissions, dashboard/export, retraits/payout et administration financière restent à traiter.

## Mise à jour du 9 septembre 2026 — Refonte du champ code promotionnel

- La saisie du code partenaire dans la fenêtre d’abonnement a été refondue pour un rendu plus professionnel et lisible : libellé dédié, badge « Facultatif », icône, aide contextuelle et bouton d’effacement rapide.
- Le champ normalise automatiquement la saisie en majuscules et supprime les espaces ; le statut de disponibilité est affiché avec une icône et une couleur distinctes pour la vérification, la validation, l’information et l’erreur.
- Le résumé financier est maintenant hiérarchisé (catalogue, remise, total et expiration) et le bouton principal indique clairement le passage vers le paiement. Le rendu reste responsive sur mobile.

### Contrôles

- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- `SubscriptionPartnerPromotionTest` : **5 tests, 26 assertions, 0 échec**.
- `git diff --check` : succès.
- `SubscriptionAccessTest` conserve un échec préexistant sur l’enforcement des limites d’essai (réponse 200 au lieu de 422), sans lien avec cette retouche visuelle.

## Mise à jour du 9 septembre 2026 — Alignement du webhook abonnement sur le flux quota

- Le paiement d’abonnement utilise le même endpoint KPrimePay que le paiement de quota : `/api/kprimepay/webhook`.
- Les deux parcours utilisent les mêmes événements V1/V2, la même consultation serveur `transactions/debit-status`, les mêmes montants entiers en XOF et la même clé d’idempotence envoyée au checkout.
- Le webhook abonnement vérifie maintenant aussi immédiatement le montant et la devise annoncés dans l’événement avant d’interroger KPrimePay, comme le flux quota. Le règlement reste idempotent : un paiement déjà marqué `paid` ne recrédite ni abonnement ni quota.
- L’URL de retour est générée par Laravel avec `route('subscriptions.return')`; en staging elle doit donc utiliser le domaine public de staging (`APP_URL`/hôte HTTPS), tandis que le webhook KPrimePay doit pointer vers `https://<staging>/api/kprimepay/webhook`, comme pour les quotas.

### Contrôles

- `QuotaPaymentTest`, `SubscriptionWebhookTest`, `SubscriptionPartnerPromotionTest`, `SubscriptionPaymentTest` : **19 tests, 121 assertions, 0 échec**.
- `php artisan route:list --path=api/kprimepay` confirme l’unique endpoint webhook partagé.

## Mise à jour du 9 septembre 2026 — Refonte visuelle de l’historique des paiements

- La section **Historique des paiements** de `resources/views/subscription/index.blade.php` adopte une hiérarchie plus claire : en-tête de suivi, compteur d’opérations, dates avec heure, plan, durée, montant et statut lisibles.
- Les statuts techniques sont présentés en français dans des badges cohérents (`Payé`, `En attente`, `Créé`, `Échoué`, `Expiré`, `Annulé`) avec une couleur et une icône adaptées.
- Le tableau reste paginé, conserve les montants et données serveur existants, et devient une liste de cartes responsive sous 768 px pour éviter le défilement horizontal sur mobile.
- `SubscriptionPayment` expose les attributs de présentation `status_label` et `status_variant` afin de garder la logique de libellé hors de la vue.

### Contrôles

- `php artisan view:cache` : succès.
- `php artisan ui:lint --changed` : `Garde-fous UI SaaS : OK`.
- `git diff --check` : succès.
- Vérification navigateur authentifiée sur `/subscription` : 2 paiements affichés en cartes à 422 px, statuts « En attente » et « Créé », sans lancement de paiement.

## Mise à jour du 9 septembre 2026 — Phase 5, commissions et grand livre partenaire

- La confirmation d’un abonnement attribué crée désormais, dans la même transaction que l’abonnement, une commission partenaire immédiatement `available` et son écriture de portefeuille `commission_credit` dans le bucket `available`. Le partenaire peut donc la voir dans son solde retirable dès la confirmation du webhook ; les règles de retrait (seuil, vérification, calendrier et activation des payouts) restent un contrôle distinct du futur module de retraits.
- La commission est calculée exclusivement côté serveur sur le brut catalogue : `floor(brut × taux acquis / 10 000)`. Le brut, la remise, le net, le taux figé, la formule, l’attribution et la version de règle sont conservés dans un snapshot auditable.
- Le premier abonnement attribué produit une commission `acquisition`. Les renouvellements et montées de plan d’un compte déjà attribué produisent une commission `renewal` ou `upgrade`, sans remise client et avec le taux acquis à vie de l’attribution, jamais le taux courant du partenaire.
- Une contrainte unique sur `subscription_payment_id`, les verrous du règlement et les clés d’idempotence du grand livre empêchent toute seconde commission positive ou tout second crédit lors d’un webhook/rejeu de paiement.
- Le réglage `partners.commission_hold_days` reste disponible pour une politique de réserve ultérieure. Sa valeur de production est maintenant `0`. Si une réserve est réactivée (`> 0`), la commande `partners:mature-commissions` transfère de manière idempotente les commissions arrivées à échéance de `pending` vers `available` en inscrivant un débit pending et un crédit available de même montant. Elle est planifiée chaque heure, sans appel à KPrimePay.
- Les écritures `partner_wallet_entries` sont immuables côté modèle : une modification ou suppression Eloquent est refusée. Aucun retrait, payout, token payout, webhook payout ou appel externe n’est introduit par cette phase.

### Fichiers principaux

- `database/migrations/2026_09_09_110000_create_partner_commissions_and_wallet_entries.php`.
- `database/migrations/2026_09_09_120000_make_partner_commissions_immediately_available.php`.
- `app/Models/PartnerCommission.php`, `app/Models/PartnerWalletEntry.php` et relations partenaires/attributions/paiements.
- `app/Services/PartnerCommissionService.php`, `app/Services/SubscriptionSettlementService.php`.
- `app/Console/Commands/MaturePartnerCommissions.php`, `app/Console/Kernel.php`.
- `tests/Feature/PartnerCommissionLifecycleTest.php`.

### Migrations et contrôles

- Migration additive appliquée sur la base locale : création de `partner_commissions` et `partner_wallet_entries` avec clés étrangères restrictives, index et unicités financières.
- Migration `2026_09_09_120000_make_partner_commissions_immediately_available` appliquée : le réglage `partners.commission_hold_days` vaut `0` pour les nouvelles confirmations. Les éventuelles commissions historiques déjà `pending` ne sont pas réécrites rétroactivement sans opération contrôlée.
- Commande locale exécutée sans donnée à maturer : `partners:mature-commissions --limit=200` → `0`.
- Tests ciblés abonnement/commission/webhook : **20 tests, 114 assertions, 0 échec**.
- `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP et `git diff --check` : succès.
- Suite complète : **290 tests, 1729 assertions, 6 échecs préexistants hors Phase 5** (authentification, création d’entreprise, session partenaire, réglages plateforme, libellé catalogue et enforcement essai).

### Recette manuelle Phase 5

1. Sur staging, effectuer un premier abonnement Bronze mensuel à `5 000 XOF` avec un code partenaire actif, puis laisser le webhook KPrimePay confirmer le paiement net `4 500 XOF`.
2. Vérifier en base ou dans le futur écran administration que la commission est immédiatement `available`, avec brut `5 000`, remise `500`, net `4 500`, taux `1 000 bps` et montant `500 XOF` dans le bucket `available`.
3. Rejouer le même webhook : une seule commission et une seule écriture available doivent subsister.
4. Effectuer ensuite un renouvellement Bronze sans code : paiement `5 000 XOF`, aucune remise et une seconde commission `renewal` de `500 XOF` au taux acquis `1 000 bps`.
5. Si une recette spécifique réactive temporairement un délai de réserve, exécuter `php artisan partners:mature-commissions` uniquement après l’échéance : une commission due devient `available`. Avec le réglage normal à `0`, la commande ne doit rien modifier.

### État et risque restant

- Aucun écran, export, retrait, payout ou remboursement n’est livré dans ce lot : il n’y a donc pas de recette visuelle à réaliser pour ce noyau backend.
- Les compensations de remboursements/annulations seront raccordées au futur flux de remboursement : aucune annulation financière n’existe aujourd’hui et aucune écriture négative n’est créée sans événement source vérifié. Tant que les commissions sont disponibles immédiatement, ce futur flux devra prévoir une contre-écriture avant tout remboursement afin d’éviter un solde partenaire négatif non contrôlé.
- Prochain lot : Phase 6, lecture seule partenaire (dashboard, clients attribués, commissions et export), alimentée exclusivement par ce grand livre.

## Mise à jour du 9 septembre 2026 — Phase 6, portail partenaire : dashboard, clients, commissions et exports

- Le tableau de bord partenaire remplace l’ancien état vide par des cartes calculées depuis le grand livre : soldes `pending`, `available`, `reserved` et `paid`, clients qualifiés, abonnements actifs, taux courant, progression de palier, cinq dernières commissions et une vue agrégée des 30 derniers jours.
- Les écrans **Mes clients** et **Mes commissions** sont paginés côté serveur. L’espace clients expose uniquement la raison sociale, le pays, l’attribution, le plan/statut et les commissions cumulées : aucun e-mail ni numéro de téléphone client n’est rendu ou exporté.
- Les commissions disposent de filtres bornés (statut et période maximale de 24 mois) et d’un détail de calcul en lecture seule : brut, remise, net encaissé et taux acquis. Aucun montant, statut, solde ou commission n’est modifiable depuis le portail.
- L’export CSV des commissions est désormais créé comme une tâche en queue, stocké dans l’espace local non public, lié au partenaire propriétaire et téléchargeable uniquement par lui pendant 7 jours. La demande et le téléchargement sont audités. Aucun fichier client ou donnée de contact n’est inclus.
- Les routes partenaires ajoutées sont `/partner/clients`, `/partner/commissions`, `/partner/commissions/export` et `/partner/exports/{export}/download`. Elles gardent les middlewares `partner.auth`, `partner.active` et `partners.enabled`.
- Cette phase n’ajoute aucun retrait, aucune réservation de solde, aucun compte Mobile Money, aucun payout et aucun appel KPrimePay. Le solde disponible demeure immédiatement visible après confirmation du webhook conformément à la décision produit validée.

### Fichiers principaux

- `app/Services/PartnerDashboardQueryService.php` et `app/Http/Controllers/Partner/InsightsController.php`.
- `app/Models/PartnerExport.php`, `app/Jobs/GeneratePartnerCommissionExport.php` et `database/migrations/2026_09_09_130000_create_partner_exports_table.php`.
- `resources/views/partner/dashboard.blade.php`, `resources/views/partner/clients.blade.php`, `resources/views/partner/commissions.blade.php` et `resources/views/layouts/partner.blade.php`.
- `tests/Feature/PartnerInsightsTest.php`.

### Migrations et contrôles

- Migration additive `2026_09_09_130000_create_partner_exports_table` appliquée localement. Aucun historique financier n’est modifié.
- Tests ciblés partenaires, attribution, commission, abonnement et webhook : **32 tests, 191 assertions, 0 échec**.
- `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP et `git diff --check` : succès au contrôle final de ce lot.
- Inspection navigateur : la page locale d’authentification partenaire a été vérifiée. Lors du contrôle développeur, l’in-app browser ne disposait pas d’une session partenaire authentifiée pour les nouvelles pages ; leur rendu et leurs protections ont été couverts par les tests Feature. La recette manuelle propriétaire est désormais validée (voir l’entrée de validation ci-dessous).

### Recette manuelle Phase 6

1. Connectez-vous à un partenaire ayant au moins une commission issue d’un abonnement attribué confirmé, puis ouvrez **Tableau de bord**.
2. Vérifiez que le solde disponible correspond au grand livre, que le taux et le nombre de clients sont cohérents, puis ouvrez **Mes clients**. Confirmez qu’aucun e-mail ni numéro de téléphone client n’apparaît.
3. Recherchez une entreprise et changez le filtre de statut ; la pagination doit conserver les filtres.
4. Ouvrez **Mes commissions**, filtrez par statut et période, puis développez « Voir le calcul » sur une ligne. Vérifiez le brut, la remise, le net et le taux acquis.
5. Cliquez sur **Préparer le CSV**. Attendez le worker de queue puis téléchargez le fichier lorsqu’il passe à « Prêt ». Le CSV ne doit contenir aucune adresse e-mail ni téléphone client.
6. Sur mobile (390 px), vérifiez le menu, les cartes, les accordéons, les tableaux et l’absence de défilement horizontal.

### Risques et prochaine étape

- Les exports nécessitent un worker de queue opérationnel pour finir en production ; leur échec reste affiché sans exposer de détail interne.
- Les agrégats journaliers pré-calculés et les objectifs de charge volumétrique restent à renforcer à la Phase 9. Les requêtes actuelles sont bornées, agrégées et paginées.
- Prochain lot : Phase 7 — retraits Mobile Money, uniquement après validation juridique/KPrimePay payout, règles d’éligibilité, OTP et recette staging dédiée.

## Ajustement UI du 9 septembre 2026 — calendrier des commissions

- Les deux champs natifs `type="date"` de **Mes commissions** sont remplacés par le DateRangePicker du template, avec une seule sélection de période, les raccourcis usuels et les actions **Appliquer** / **Effacer**.
- Le composant conserve les paramètres serveur `from` et `to`, respecte la limite de consultation de 24 mois et affiche les libellés en français (mois, jours, raccourcis et boutons).
- Les actions du calendrier et les dates sélectionnées utilisent explicitement un texte blanc sur leur fond coloré afin de rester lisibles avec tous les thèmes et couleurs d’accent.
- Le contrôle reste responsive : il s’ouvre dans le panneau de filtres et s’adapte au viewport mobile sans modifier le calcul, les exports ou les flux de paiement.

### Contrôles

- `php artisan view:cache`, `php artisan ui:lint --changed` et `git diff --check` : succès.
- Tests ciblés : **32 tests, 191 assertions, 0 échec**.
- Vérification visuelle locale : calendrier du template ouvert sur `/partner/commissions`, avec raccourcis et période visibles. Le navigateur de développement conservait un ancien cache de vue pour l’inspection des noms de mois ; le cache Blade a été recompilé après correction et la recette visuelle finale reste à confirmer dans votre session.

## Validation propriétaire du 9 septembre 2026 — Phase 6 acceptée

- Le propriétaire confirme que la recette manuelle de la Phase 6 est satisfaisante : tableau de bord, clients attribués, commissions, détail des calculs, export CSV et calendrier de période.
- La Phase 6 est désormais considérée comme validée fonctionnellement. Aucun paiement, retrait ou payout réel n’a été déclenché pendant cette recette.
- La Phase 7 reste verrouillée jusqu’à une autorisation explicite de démarrage ; elle concernera exclusivement les retraits Mobile Money et le payout KPrimePay staging.

## Mise à jour du 10 septembre 2026 — Phase 7A, préparation sécurisée des retraits Mobile Money

- La Phase 7A prépare exclusivement le futur flux de retrait : comptes Mobile Money chiffrés (`partner_withdrawal_accounts`), retraits avec états audités (`partner_withdrawals`), allocations par commission, événements payout et clés d’idempotence (`partner_payout_events`).
- Le numéro E.164 est chiffré au repos par le cast Laravel `encrypted`, son empreinte SHA-256 sert à éviter les doublons et l’interface n’affiche qu’un masque. Toute modification remet le compte en `pending_verification` ; aucune auto-vérification partenaire n’est exposée par une route publique.
- `PartnerWithdrawalService` calcule l’éligibilité depuis le grand livre immuable (solde disponible, minimum XOF, clients qualifiés, activation `partners.payouts_enabled`), réserve atomiquement le montant par un débit `available` et un crédit `reserved`, puis restitue les deux écritures en cas d’échec confirmé. Un état `unknown` reste réservé pour la future réconciliation.
- L’écran **Mes retraits** est intégré au shell SaaS. Il présente le solde, les garde-fous, les comptes enregistrés et la préparation du compte ; aucun bouton de transfert ni appel KPrimePay n’est actif tant que `partners.payouts_enabled=false`.
- L’administration **Programme partenaires** expose maintenant les paramètres de seuil, de clients qualifiés, de revue de risque et de plafond d’approbation automatique. L’activation opérationnelle des payouts reste volontairement séparée et désactivée.

### Fichiers principaux

- `database/migrations/2026_09_10_090000_create_partner_withdrawal_tables.php`.
- `app/Models/PartnerWithdrawalAccount.php`, `PartnerWithdrawal.php`, `PartnerWithdrawalAllocation.php`, `PartnerPayoutEvent.php` et relations de `Partner`.
- `app/Services/PartnerWithdrawalService.php`, `app/Http/Controllers/Partner/WithdrawalController.php`, `routes/web.php`.
- `resources/views/partner/withdrawals.blade.php`, `resources/views/layouts/partner.blade.php`, `resources/views/platform/settings/partners.blade.php` et son contrôleur.
- `tests/Feature/PartnerWithdrawalPreparationTest.php`.

### Migrations et contrôles

- Migration additive `2026_09_10_090000_create_partner_withdrawal_tables` appliquée localement ; aucune donnée de paiement, quota, abonnement ou commission existante n’est réécrite.
- Tests ciblés : `PartnerWithdrawalPreparationTest`, `PartnerInsightsTest`, `PartnerCommissionLifecycleTest`, `PlatformPartnerSettingTest` — **16 tests, 96 assertions, 0 échec**.
- Contrôles exécutés : `php artisan migrate --force` (migration additive), `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP, `php artisan route:list --name=partner.withdrawals` et `git diff --check` : succès.
- Dernière non-régression complète exécutée : **296 tests, 1 751 assertions, 8 échecs hors Phase 7A** (AuthNavigation/communications historiques, création d’entreprise, session partenaire périmée, réglage pricing/catalogue et enforcement/expiration abonnement). Après l’ajout du garde-fou `unknown`, la sélection ciblée complète est repassée à **16 tests, 96 assertions, 0 échec** ; aucun échec ne touche `PartnerWithdrawalPreparationTest`.

### Sécurité et limites de ce lot

- Aucun token payout, endpoint `/payouts/transfers`, webhook payout, appel réseau KPrimePay ou transfert réel n’est implémenté.
- `payouts/from-collection` n’est pas utilisé : il ne permet pas de payer un portefeuille agrégé de commissions.
- La vérification effective de propriété Mobile Money, l’OTP e-mail d’opération, l’approbation, le job payout, la réconciliation et les notifications restent à livrer après disponibilité et validation de l’endpoint KPrimePay dédié.
- Les flux de paiement quota SMS/WhatsApp restent inchangés.

### Recette manuelle Phase 7A

1. Avec un partenaire actif, ouvrir **Mes retraits** et vérifier le solde, le seuil, le nombre de clients requis et le message de retrait désactivé.
2. Ajouter un compte Togo avec un opérateur Mixx/Yas ou Moov Money et un numéro de recette ; vérifier que seul le numéro masqué est affiché et que l’état est « À vérifier ».
3. Recharger la page et confirmer que le compte reste associé au seul partenaire ; vérifier qu’aucun transfert ou redirection KPrimePay n’est proposé.
4. Dans **Administration > Programme partenaires**, modifier le seuil ou le nombre de clients avec un motif et le mot de passe plateforme ; vérifier l’audit et le reflet dans **Mes retraits**.
5. Ne pas activer `partners.payouts_enabled` en production. La réservation automatisée sera testée uniquement en environnement isolé lorsque l’OTP et le contrat KPrimePay seront validés.

Résultat attendu : aucun mouvement externe, aucune modification des paiements existants, compte Mobile Money chiffré et garde-fous visibles.

### État

Phase 7A préparatoire terminée côté serveur et interface, en attente de recette manuelle. Phase 7B (OTP, approbation, transfert KPrimePay dédié et réconciliation staging) reste verrouillée jusqu’à la disponibilité de l’endpoint annoncé et une autorisation explicite.

## Mise à jour du 10 septembre 2026 — Phase 7B, opérateurs et confirmation e-mail (sans transfert externe)

- Les opérateurs de retrait sont désormais configurables dans **Administration > Programme partenaires**. Au Togo, `MOOV-MONEY-TG` (Flooz) est actif par défaut ; `MIXX-YAS-TG` (Mixx/Yas) est présent mais désactivé jusqu’à activation administrative explicite. La configuration est persistée dans `partners.payout_gateways`, auditée et ne modifie jamais `partners.payouts_enabled`.
- Les numéros de retrait sont validés avant soumission dans l’interface, puis côté serveur : exactement 8 chiffres ; Flooz accepte `76, 77, 78, 79, 96, 97, 98, 99`, Mixx/Yas accepte `70, 71, 72, 73, 90, 91, 92, 93`. Aucun flux de paiement abonnement, SMS ou WhatsApp n’est touché.
- Une confirmation de retrait exige maintenant le mot de passe partenaire puis un code e-mail distinct de la 2FA de connexion, limité à 6 chiffres, 10 minutes et 5 tentatives. Le code est à usage unique et l’émission d’un nouveau code invalide le précédent.
- La confirmation crée seulement la demande locale et la réservation comptable déjà contrôlée ; aucun client KPrimePay, token payout, appel HTTP, webhook payout ou transfert externe n’est encore ajouté. Le futur client devra appeler `POST /v2/payouts/from-collection-balance`, pas `from-collection`, après une recette staging dédiée.

### Fichiers principaux complémentaires

- `config/partners.php`, `database/migrations/2026_09_10_100000_add_partner_payout_gateways_setting.php`.
- `app/Services/PartnerAuthenticationService.php`, `app/Notifications/PartnerWithdrawalConfirmationNotification.php`.
- `app/Http/Controllers/Platform/PartnerSettingController.php`, `app/Http/Controllers/Partner/WithdrawalController.php`.
- `resources/views/platform/settings/partners.blade.php`, `resources/views/partner/withdrawals.blade.php`, `resources/views/partner/withdrawal-confirm.blade.php`.

### Contrôles

- Migration additive appliquée localement : `2026_09_10_100000_add_partner_payout_gateways_setting`.
- Tests ciblés `PartnerWithdrawalPreparationTest`, `PlatformPartnerSettingTest`, `PartnerInsightsTest`, `PartnerCommissionLifecycleTest` : **19 tests, 103 assertions, 0 échec**.
- `php artisan view:cache`, `php artisan ui:lint --changed`, syntaxe PHP et `git diff --check` : succès.

### Recette manuelle

1. Dans l’administration, confirmer que Flooz est actif et Mixx/Yas inactif ; enregistrer avec motif et mot de passe, puis activer Mixx/Yas uniquement pour vérifier le changement.
2. Dans **Mes retraits**, ouvrir l’ajout de compte : Flooz accepte seulement les huit préfixes prévus, refuse immédiatement un numéro Mixx ; après activation de Mixx, vérifier ses propres préfixes.
3. Lorsque les payouts seront activés dans un environnement isolé et qu’un compte est vérifié, saisir montant et mot de passe : un code doit arriver par e-mail. Un code erroné, expiré ou rejoué doit être refusé.
4. Ne pas lancer de transfert KPrimePay pendant cette recette : ce lot ne fait aucune sortie d’argent.

### État

Lot opérateurs/validation/OTP terminé. Reste la Phase 7C : client KPrimePay `from-collection-balance`, liste blanche IP, clé `payouts:write` séparée, webhook signé, réconciliation `credit-status`, traitement des frais, des échecs et des statuts inconnus en staging.

## Ajustement du 10 septembre 2026 — confirmation autonome du compte Mobile Money

- La vérification ne nécessite plus d’intervention administrative. Après l’enregistrement d’un compte, le partenaire est redirigé vers un écran de confirmation et reçoit un code e-mail dédié (`withdrawal_account`).
- Le code est à usage unique, limité à 6 chiffres, valable 10 minutes et limité à 5 tentatives. Après validation, le compte passe immédiatement de `pending_verification` à `verified` et devient sélectionnable pour un retrait.
- Le code de confirmation du compte reste distinct du code e-mail demandé au moment de confirmer un retrait (`withdrawal`). Aucun retrait n’est créé lors de la vérification du compte.
- L’interface affiche désormais « Confirmation e-mail requise » au lieu d’un état ambigu « À vérifier ». Les flux de paiement et de quota restent inchangés.

### Contrôles complémentaires

- `PartnerWithdrawalPreparationTest`, `PlatformPartnerSettingTest`, `PartnerInsightsTest`, `PartnerCommissionLifecycleTest` : **21 tests, 111 assertions, 0 échec**.
- `php artisan view:cache`, `php artisan ui:lint --changed`, `php artisan route:list --name=partner.withdrawals` et `git diff --check` : succès.

### Recette manuelle complémentaire

1. Ouvrir **Mes retraits > Ajouter un compte** et saisir un numéro Flooz valide.
2. Vérifier la redirection vers **Confirmer le compte Mobile Money** et la réception du code e-mail.
3. Saisir le code : le compte doit afficher « Vérifié » et apparaître dans le sélecteur de retrait.
4. Tester un code erroné, expiré ou réutilisé : le compte ne doit pas être vérifié.
5. Vérifier qu’aucun retrait KPrimePay n’est déclenché pendant cette confirmation.

## Mise à jour du 10 septembre 2026 — Phase 7C, exécution KPrimePay à solde de collecte

- Le retrait effectif utilise maintenant exclusivement `POST /v2/payouts/from-collection-balance`. Il ne touche ni `payouts/from-collection` (transaction unique), ni le client `KprimePayService` des checkouts SMS, WhatsApp et abonnements.
- La clé est strictement séparée : `KPRIMEPAY_PAYOUT_TOKEN`, avec l’URL optionnelle `KPRIMEPAY_PAYOUT_BASE_URL`. L’ancienne variable `KPRIMEPAY_TOKEN` continue de servir aux encaissements et ne doit jamais recevoir le droit `payouts:write`.
- Chaque transfert inclut la clé d’idempotence immuable du retrait. Le montant reste en réserve lors de l’acceptation fournisseur ; le grand livre ne passe en `paid` qu’après une lecture authentifiée de `POST /v2/transactions/credit-status`, déclenchée par le webhook `transfer.*` ou la réconciliation planifiée.
- Un rejet explicite `INSUFFICIENT_COLLECTION_BALANCE` ou `GATEWAY_REJECTED` libère la réserve. Toute réponse réseau ambiguë conserve l’état `unknown` et le solde réservé : aucune relance avec une nouvelle clé n’est effectuée.
- Les événements sont dédoublonnés dans `partner_payout_events`, avec empreinte SHA-256 et charge expurgée (ni e-mail, ni numéro Mobile Money). Le webhook ne solde jamais un retrait sur sa seule déclaration : il vérifie d’abord le statut avec la clé payout.
- Historiquement, `partners:reconcile-payouts --limit=100` était planifié toutes les dix minutes. Depuis l’architecture queue du 11 septembre, la détection automatique est réalisée chaque minute par `partners.dispatch-reconciliations`, et cette commande reste disponible pour une vérification opérateur ponctuelle (`--pretend` ne modifie rien).
- L’exécution automatique était initialement protégée par `risk_review_enabled` et un plafond positif. Depuis l’ajustement opérationnel du 10 septembre 2026, le premier réglage est supprimé : seul le plafond automatique, ainsi que les contrôles métier et la confirmation e-mail, encadre l’envoi.

### Fichiers principaux

- `config/services.php`, `app/Services/KprimePayPayoutService.php`, `app/Services/PartnerPayoutService.php`.
- `app/Jobs/ExecutePartnerWithdrawal.php`, `app/Console/Commands/ReconcilePartnerPayouts.php`, `app/Console/Kernel.php`.
- `app/Exceptions/KprimePayPayoutException.php`, `app/Services/PartnerWithdrawalService.php`.
- `app/Http/Controllers/Api/KprimePayWebhookController.php`, `app/Http/Controllers/Partner/WithdrawalController.php`.
- `resources/views/partner/withdrawals.blade.php` (états et suivi des demandes).
- `tests/Feature/PartnerPayoutIntegrationTest.php`.

### Contrôles Phase 7C

- Tests ciblés finaux `PartnerPayoutIntegrationTest`, `PartnerWithdrawalPreparationTest`, `PlatformPartnerSettingTest`, `PartnerInsightsTest`, `PartnerCommissionLifecycleTest` : **27 tests, 147 assertions, 0 échec**.
- Vérifications : syntaxe PHP des nouveaux services/commande/contrôleur, `php artisan partners:reconcile-payouts --pretend --limit=5`, `php artisan view:cache`, `php artisan ui:lint --changed`, `php artisan route:list --name=partner.withdrawals` et `git diff --check` : succès.
- Inspection visuelle : la page de connexion partenaire rend correctement dans le navigateur intégré. La session authentifiée n’était plus disponible, donc aucun clic sur le formulaire de retrait ni aucun paiement n’a été lancé.

### Préparation staging indispensable avant toute activation

1. Créer dans KPrimePay une clé serveur dédiée aux retraits, limitée au minimum à `payouts:write` et à la lecture de statut, puis la poser dans `KPRIMEPAY_PAYOUT_TOKEN` sur staging. Ne jamais la communiquer dans le dépôt, les logs ou l’interface.
2. Ajouter l’IP sortante exacte de staging à la liste blanche payout KPrimePay.
3. Définir et valider la politique de frais via `KPRIMEPAY_PAYOUT_WITH_FEES=0` : aucun supplément `with_fees` ne doit être envoyé au bénéficiaire. Les frais réels KPrimePay sont imputés séparément au portefeuille partenaire.
4. Garder `partners.payouts_enabled=false` jusqu’à une recette de faible montant. Pour un essai automatique, désactiver temporairement la revue de risque et fixer un plafond strictement limité ; remettre les réglages de sécurité juste après le test.
5. Configurer le webhook `POST /api/kprimepay/webhook` pour les événements `transfer.succeeded` et `transfer.failed`, puis vérifier le retour de `credit-status` et les lignes `reserved → paid` ou `reserved → available`.

### État

Phase 7C développée et testée sans transfert réel. L’activation staging demeure volontairement bloquée par défaut tant que la clé payout dédiée, la liste blanche IP et la politique de frais n’ont pas été mises en place et validées manuellement.

## Ajustement du 10 septembre 2026 — frais KPrimePay transparents avant retrait

- Le pourcentage KPrimePay est maintenant administrable via **Programme partenaires > Frais KPrimePay (%)**. La valeur initiale est `1,00 %` (`partners.payout_fee_bps=100`) et est auditée comme les autres réglages sensibles.
- Le partenaire saisit le **montant à recevoir**. L’interface calcule immédiatement les frais arrondis au franc supérieur et le **montant final débité** : à 1 %, `1 000 XOF` envoyé implique `10 XOF` de frais et `1 010 XOF` débités du portefeuille.
- Quand le total dépasse le solde disponible, le récapitulatif devient rouge, explique l’insuffisance et désactive le bouton d’envoi du code e-mail. À solde égal ou supérieur, il explique le montant réellement reçu et le montant retiré du portefeuille. Le serveur refait exactement le même calcul : le navigateur ne peut pas contourner ce garde-fou.
- La réservation, la restitution après rejet et la comptabilisation du payout portent désormais sur `montant + frais`. Les allocations de commissions couvrent également ce total. Une différence entre les frais réservés et ceux confirmés par KPrimePay force l’état `unknown` et conserve le solde protégé pour contrôle.
- Une commande d’aperçu exclusivement `local/testing` est disponible : `php artisan partners:seed-withdrawal-preview {id_partenaire}`. Elle crédite une seule fois 10 000 XOF fictifs, rend un compte de démonstration vérifié et remplit la condition de clients qualifiés ; elle ne crée pas de retrait, ne transmet aucun e-mail et n’appelle jamais KPrimePay. Elle a été exécutée localement pour le partenaire de recette actuellement connecté.

### Fichiers complémentaires

- `database/migrations/2026_09_10_110000_add_partner_payout_fee_setting.php`, `config/partners.php`.
- `app/Services/PartnerWithdrawalService.php`, `app/Services/PartnerPayoutService.php`.
- `app/Http/Controllers/Partner/WithdrawalController.php`, `app/Http/Controllers/Platform/PartnerSettingController.php`.
- `resources/views/partner/withdrawals.blade.php`, `resources/views/platform/settings/partners.blade.php`, `public/hub/assets/css/saas-pages.css`.
- `app/Console/Commands/SeedPartnerWithdrawalPreview.php`, `tests/Feature/PartnerWithdrawalPreparationTest.php`.

## Ajustement UI/UX du 10 septembre 2026 — carte « Demander un retrait »

- La carte utilise désormais la grille SaaS dédiée : champs lisibles et empilés sur mobile, compte Mobile Money vérifié clairement séparé du montant et du mot de passe, puis récapitulatif financier et action de sécurité.
- Le récapitulatif est visuellement priorisé : montant reçu, frais KPrimePay et montant final débité. Le dépassement de solde adopte l’état rouge du design system et désactive l’action ; l’état conforme reste explicite et rappelle qu’aucun versement ne part avant l’étape suivante.
- La version d’asset `saas-pages.css` a été incrémentée pour éviter que le navigateur conserve l’ancien rendu.
- Contrôle visuel effectué dans le navigateur intégré sur le compte local de recette : formulaire actif, calcul `1 000 → 10 → 1 010`, puis état bloqué `10 000 → 100 → 10 100` avec alerte rouge. Aucun formulaire n’a été soumis.

## Historique du 10 septembre 2026 — `with_fees` distinct du tarif KPrimePay

- La configuration finale conserve `with_fees=0` : ce drapeau est distinct des frais propres à KPrimePay et évite d’ajouter un supplément technique au bénéficiaire.
- Ce drapeau technique est indépendant du tarif KPrimePay affiché et calculé par la plateforme (`partners.payout_fee_bps`). Le partenaire reçoit toujours exactement le montant saisi ; le portefeuille réserve et débite ce montant augmenté du tarif KPrimePay configuré.
- Les tests vérifient simultanément le payload API (`with_fees=0`) et le calcul métier séparé (`1 000 → 10 → 1 010`). Aucun changement n’est apporté au client KPrimePay des paiements de quotas, SMS, WhatsApp ou abonnements.

## Correctif financier du 11 septembre 2026 — frais réels à la charge du partenaire

- La politique de retrait est désormais explicite : le bénéficiaire Mobile Money reçoit **exactement le montant demandé**. Les frais facturés par KPrimePay sont supportés par le portefeuille du partenaire ; la plateforme ne les absorbe pas.
- L’appel `POST /v2/payouts/from-collection-balance` utilise `with_fees=0` (`KPRIMEPAY_PAYOUT_WITH_FEES=0`). Ce drapeau est distinct des frais KPrimePay et ne modifie pas les paiements de quotas, SMS, WhatsApp ni abonnements.
- Avant l’envoi, le système réserve le montant demandé augmenté d’un **plafond de frais** réglable dans l’administration. Après la confirmation authentifiée KPrimePay, il débite seulement le coût réellement rapporté (`total_amount_debited - transaction_amount`) et restitue automatiquement la différence au solde disponible.
- La table `partner_withdrawals` conserve désormais `estimated_fees` pour l’audit de la réserve ; `fees` devient le frais réel confirmé lors d’un versement réussi. Une différence favorable ne bloque plus le retrait. Si le frais réel dépasse le plafond réservé, l’état reste `unknown` pour ne pas masquer un écart comptable.
- L’interface partenaire indique « Estimation des frais KPrimePay » et « Montant maximal réservé ». L’administration parle désormais de « Plafond des frais KPrimePay (%) » et précise qu’il doit couvrir le tarif actif du prestataire.
- Tests ajoutés : payload `with_fees=0`, règlement idempotent, restitution d’une réserve trop élevée, et mise en attente d’un coût réel supérieur au plafond. `PartnerPayoutIntegrationTest` : **6 tests, 34 assertions, succès**.

### Recette manuelle — retrait avec frais KPrimePay

1. Redémarrer le serveur PHP/Laragon local après le déploiement afin qu’il recharge les vues, la configuration et `KPRIMEPAY_PAYOUT_WITH_FEES=0`.
2. Dans **Administration > Programme partenaires**, renseigner un plafond de frais au moins égal au tarif KPrimePay de l’opérateur, puis activer uniquement l’opérateur à tester.
3. Avec un partenaire éligible, saisir par exemple `1 000 XOF` sur **Mes retraits** : vérifier que le récapitulatif annonce `1 000 XOF` à recevoir, une estimation de frais et un montant maximal réservé ; le bouton doit se désactiver si ce total dépasse le solde disponible.
4. Confirmer le mot de passe puis le code e-mail. Vérifier dans le payload/les logs que `with_fees` vaut `0` et que KPrimePay reçoit `amount=1000`.
5. Après le webhook et le contrôle `credit-status`, vérifier que le Mobile Money a reçu `1 000 XOF`, que le portefeuille partenaire a été débité de `1 000 + frais réels`, et que la différence entre la réserve et le coût réel a été restituée.
6. Rejouer le même webhook : aucun second débit, aucune seconde écriture de portefeuille. Si le coût confirmé dépasse le plafond, ne pas corriger manuellement le solde : le retrait doit rester `À vérifier` pour analyse.

### Limite de contrôle local

Le processus PHP qui sert `127.0.0.1:1111` est détenu par une autre session Windows et refuse son arrêt depuis cette session. Les caches Laravel du projet ont bien été régénérés, mais un redémarrage de Laragon/PHP par son propriétaire est requis avant l’inspection visuelle de cette nouvelle vue sur ce serveur précis.

## Architecture de réconciliation en queue — 11 septembre 2026

- Le scheduler Laravel détecte chaque minute les retraits `processing` et `unknown` suffisamment anciens, puis place un `ReconcilePartnerWithdrawal` dans la queue `withdrawals`.
- Le job est unique par retrait, utilise un verrou `WithoutOverlapping`, applique des reprises progressives (10, 30 et 60 secondes) et appelle uniquement `credit-status`. Il ne relance jamais `from-collection-balance`.
- Le worker à lancer en staging/production est `php artisan queue:work --queue=withdrawals --sleep=1 --tries=3`. Le scheduler doit être maintenu actif via `php artisan schedule:work` ou le planificateur système qui exécute `schedule:run` chaque minute.
- `ExecutePartnerWithdrawal` est également routé vers `withdrawals`, afin que l’envoi initial et les réconciliations soient traités par le même worker dédié.
- Le test d’architecture confirme l’isolation de la queue et l’absence de relance pour un retrait clôturé. Les tests forcent l’environnement `testing` et refusent toute base qui ne se termine pas par `_testing`.

## Ajustement visuel du 10 septembre 2026 — écran de retrait SaaS

- Les blocs **Compte de versement** et **Demander un retrait** sont maintenant regroupés dans une grille à deux colonnes sur desktop et repassent automatiquement sur une colonne sur tablette/mobile.
- Le récapitulatif de frais conserve une hiérarchie claire : montant reçu, frais KPrimePay, total débité. Le total insuffisant utilise l’accent danger et reste accessible via `aria-live`.
- Le suivi des dix dernières demandes reste séparé sous la grille pour éviter de mélanger action et historique.
- Contrôles après ajustement : `24 tests, 108 assertions, 0 échec`, `view:cache`, `ui:lint --changed` et `git diff --check` OK.

## Correctif du 10 septembre 2026 — minimum de retrait explicite

- Un montant inférieur au minimum configuré ne donne plus l'impression que le bouton est bloqué : le clic affiche une alerte SweetAlert en français, indiquant le minimum requis et le montant saisi.
- Le navigateur ne porte plus de contrainte HTML `min`/`max` susceptible d'intercepter silencieusement ce retour. Le serveur conserve ses validations métier ; le total supérieur au solde disponible reste affiché en rouge et désactive l'action.
- Ce contrôle ne déclenche ni code e-mail, ni réservation, ni appel KPrimePay.

## Correctif du 10 septembre 2026 — opérateurs de retrait désactivés

- Un compte Mobile Money déjà vérifié reste conservé dans le profil, mais il est marqué **Retrait indisponible** et retiré du sélecteur dès que son opérateur est désactivé dans l’administration.
- L’interface affiche une alerte SaaS rouge et ne propose que les comptes dont l’opérateur est activé. Si aucun compte utilisable ne reste, elle explique comment réactiver un opérateur ou ajouter un compte autorisé.
- Le serveur vérifie ce réglage avant l’envoi du code e-mail, lors de la création du retrait et juste avant un éventuel envoi KPrimePay. Une demande devenue interdite est refusée ou libérée sans appel au prestataire.
- Contrôles : `PartnerWithdrawalPreparationTest` et `PartnerPayoutIntegrationTest` — **19 tests, 95 assertions, 0 échec** ; `view:cache`, `ui:lint --changed` et `git diff --check` OK. Vérification visuelle locale : Mixx by Yas désactivé absent du sélecteur, Flooz seul disponible.

## Ajustement UI/UX du 10 septembre 2026 — sélecteur de compte de retrait

- Le champ **Compte Mobile Money vérifié** utilise désormais le composant `saas-select-wrap` du template : icône opérateur, flèche personnalisée, largeur cohérente et focus accessible.
- La version de `saas-pages.css` a été incrémentée afin que le rendu soit immédiatement actualisé dans le navigateur.
- Vérification visuelle effectuée sur la page locale des retraits, en responsive mobile : sélecteur Flooz lisible, aligné et cohérent avec les autres contrôles SaaS.

## Changement opérationnel du 10 septembre 2026 — versement automatique

- Le réglage administrateur **Revue de risque obligatoire** a été retiré. Une demande éligible et confirmée par code e-mail est automatiquement approuvée puis transmise au job de versement.
- Le plafond d’envoi automatique reste le dernier garde-fou configurable. Une migration le fixe à `100 000 000 XOF` lorsqu’il était à zéro ou absent ; les contrôles de compte vérifié, opérateur actif, solde, minimum, frais et 2FA restent obligatoires.
- Le statut `En contrôle` ne sera plus produit pour les nouvelles demandes à cause de la revue de risque. Les demandes historiques déjà dans cet état ne sont pas relancées ni envoyées rétroactivement par cette modification.
- Les flux de paiement KPrimePay des quotas SMS, WhatsApp et abonnements ne sont pas modifiés.

## Ajustement e-mail du 10 septembre 2026 — confirmation de retrait

- La notification de code e-mail des retraits utilise désormais une vue Blade dédiée : `resources/views/emails/partner/withdrawalConfirmation.blade.php`.
- Cette vue reprend le gabarit SaaS déjà en place (`emails.design.emailStyle` et `emails.design.emailFooter`) : en-tête de marque, sous-titre de sécurité partenaire, bloc de code lisible et pied de page légal commun.
- Le contenu est entièrement en français et précise l’action attendue, l’expiration de 10 minutes, l’usage unique du code et le fait qu’aucun versement ne part avant la validation.
- Le format texte générique de `MailMessage` n’est plus utilisé pour cette notification ; le sujet reste `confirmation de retrait`.
- Test de rendu ajouté dans `PartnerWithdrawalPreparationTest` : vue, en-tête, code, message de sécurité et copyright vérifiés. Contrôles finaux : **25 tests, 128 assertions, 0 échec**, `view:cache`, `ui:lint --changed` et `git diff --check` OK.

## Ajout du 11 septembre 2026 — trésorerie et retraits administrateur

- La console SaaS expose désormais **Monétisation > Trésorerie & retraits** (`/platform/treasury`). Le rôle Finance peut consulter les chiffres ; les opérations sur un bénéficiaire et les sorties sont réservées au super-administrateur.
- Le tableau de bord dissocie les encaissements KPrimePay **confirmés** : abonnements et achats de quotas. Il présente également les commissions partenaires retirables, réservées et déjà versées, les sorties administrateur réalisées/en attente, puis la **capacité administrateur proposée**.
- La capacité proposée est volontairement conservatrice : `encaissements confirmés − engagements partenaires (disponibles + réservés + versés) − retraits admin réussis − réservations admin`. Elle n’est jamais présentée comme la balance KPrimePay : celle-ci reste contrôlée par le prestataire au moment de l’envoi.
- Un compte Mobile Money administrateur est chiffré en base, masqué à l’affichage et doit être vérifié via un code e-mail à usage unique (10 minutes, cinq essais). La demande de retrait demande ensuite le mot de passe plateforme et un second code e-mail. Toutes les étapes sont journalisées dans `platform_audit_logs`.
- Comme pour les partenaires, un compte vérifié dont l’opérateur est désactivé reste dans l’historique mais disparaît du sélecteur de retrait ; le serveur refait le contrôle juste avant la création.
- Le retrait utilise le même endpoint isolé `POST /v2/payouts/from-collection-balance`, avec une clé d’idempotence propre. `with_fees` est imposé à **0** : le bénéficiaire reçoit exactement le montant demandé ; les frais KPrimePay réels sont confirmés via `credit-status` et déduits de la capacité du coffre.
- Après soumission, `ExecutePlatformWithdrawal` passe par la queue `withdrawals`. Le scheduler publie chaque minute les vérifications des retraits `processing`/`unknown` vers `ReconcilePlatformWithdrawal`; ce job appelle uniquement `credit-status` et ne rejoue jamais un transfert.

### Fichiers principaux

- `database/migrations/2026_09_11_090000_create_platform_treasury_withdrawal_tables.php`.
- `app/Http/Controllers/Platform/TreasuryController.php`, `app/Services/PlatformTreasuryService.php`, `app/Services/PlatformTreasuryPayoutService.php`.
- `app/Models/PlatformWithdrawal*.php`, `app/Jobs/ExecutePlatformWithdrawal.php`, `app/Jobs/ReconcilePlatformWithdrawal.php`.
- `resources/views/platform/treasury/index.blade.php`, `routes/web.php`, `resources/views/layouts/platform.blade.php`.

### Contrôles effectués

- Migration complète exécutée exclusivement sur `pos_testing` ; la base `POS` n’a pas été modifiée.
- `PlatformTreasuryTest` ciblé : tableau super-administrateur, accès lecture seule Finance, historique et payload KPrimePay `with_fees=0` — **4 tests, 13 assertions, succès**.
- Syntaxe PHP de tous les nouveaux fichiers, routes `platform.treasury.*`, cache des vues et `git diff --check` à exécuter avant livraison staging.
- Inspection navigateur : `/platform/treasury` redirige correctement vers l’authentification plateforme. L’écran est conservé dans le navigateur intégré ; une session super-administrateur est nécessaire pour la recette visuelle complète. Aucun compte, code ou retrait réel n’a été créé par cette vérification.

### Recette manuelle recommandée (staging)

1. Se connecter avec un super-administrateur, ouvrir **Trésorerie & retraits**, puis vérifier que les montants abonnements et quotas correspondent aux paiements confirmés.
2. Contrôler que les sommes partenaires « retirables » et « réservées » réduisent bien la capacité administrateur. Se connecter avec le rôle Finance : il doit voir ces chiffres mais aucun formulaire d’action.
3. Enregistrer un numéro Flooz valide : l’indicatif/pays, les préfixes et les huit chiffres doivent être contrôlés ; saisir le code e-mail pour le passer à « Vérifié ».
4. Demander un montant minime : vérifier le calcul `montant + plafond de frais`, saisir le mot de passe, puis confirmer le second code e-mail. Vérifier qu’une seule demande apparaît dans l’historique.
5. Avec la clé payout staging et l’IP autorisée, vérifier le payload (`amount` demandé, `with_fees: 0`, `Idempotency-Key`), le crédit Mobile Money, puis le statut final via webhook ou `credit-status`. Rejouer le webhook : aucune seconde sortie ne doit apparaître.

## Complément du 11 septembre 2026 — trésorerie : comptes visibles et doublons refusés

- La page `/platform/treasury` liste maintenant les comptes Mobile Money administrateur sans exposer leur numéro complet : téléphone masqué, pays/opérateur, état et compte principal sont visibles immédiatement après le formulaire.
- Un numéro normalisé déjà associé au même administrateur est refusé côté serveur avant toute nouvelle création. La vérification exploite l’empreinte du numéro et reste protégée par une transaction, afin d’éviter les doublons même si deux requêtes arrivent presque simultanément.
- Le parcours a été repris visuellement en deux cartes claires : bénéficiaire à enregistrer/vérifier, puis sortie de trésorerie. L’espacement et le contenu des formulaires sont responsives, notamment pour les champs de mot de passe, les codes e-mail et les actions de confirmation.
- Recette locale authentifiée : la liste masquée, les formulaires et leur espacement ont été vérifiés dans le navigateur. `php -l`, `php artisan view:cache` et `git diff --check` sont passés. `PlatformTreasuryTest` est à rejouer : le processus de test Windows a échoué avant les assertions sur une conversion `proc_open`.

## Mise à jour du 11 septembre 2026 — cohérence PWA MAXANOU

- Le manifeste, l’écran hors connexion, le script d’installation et les layouts public, partenaire et plateforme utilisent désormais l’identité **MAXANOU**. Le point d’entrée du manifeste reste `/user_login`.
- Le service worker courant est `maxanou-pwa-v8`. Il évacue également les caches historiques `pro-seller-pwa-*` et ne met jamais en cache les pages authentifiées, API ou données propres aux entreprises.
- Après déploiement HTTPS, désinstaller et réinstaller une ancienne PWA si elle garde le manifeste ou la marque précédente ; l’installation réelle reste une étape de recette sur le domaine canonique.

## Ajustement paiement abonnement — consentement avant redirection

- La fenêtre « Préparer votre abonnement » affiche désormais un encadré **Termes et conditions de paiement** avant toute initialisation. Il précise la redirection vers KPrimePay et le fait que les frais éventuels du moyen de paiement sont appliqués par l’opérateur, indépendamment de MAXANOU.
- Le bouton « Continuer vers le paiement » reste désactivé tant que la case n’est pas cochée. Le serveur exige également `terms_accepted=1` sur `subscriptions.checkout`, afin qu’une requête fabriquée côté navigateur ne puisse pas contourner le consentement.
- La version des conditions et l’horodatage d’acceptation sont conservés dans le snapshot du paiement (`payment_terms_version`, `payment_terms_accepted_at`) pour la traçabilité.
- Aucun paiement n’est créé et aucune redirection KPrimePay n’a lieu sans cette acceptation explicite. Le texte est intégré au composant modal SaaS et adapté aux petits écrans.

## Téléphone utilisateur et garde-fou SMS/WhatsApp — 11 septembre 2026

- Le profil personnel contient maintenant un onglet **Téléphone** permettant à tout utilisateur connecté d’ajouter, modifier ou retirer son numéro et de choisir le pays associé. Le numéro est normalisé avant validation puis enregistré sur le compte utilisateur ; il n’est pas nécessaire d’avoir renseigné ce champ lors de l’inscription.
- Le pays du numéro reprend désormais exactement le sélecteur de l’inscription (`country-select` + Select2) : recherche intégrée, largeur 100 %, styles SaaS et comportement utilisable au clavier comme sur mobile.
- La validation est maintenant spécifique au pays : longueur locale attendue et indicatif affiché dynamiquement. Les indicatifs ne sont pas stockés dans `users.phone`, puisque les services SMS/WhatsApp transmettent déjà le pays séparément. Une ancienne valeur avec indicatif est nettoyée dans le champ du profil afin d’être corrigée lors du prochain enregistrement.
- Le formulaire téléphone aligne désormais le sélecteur et le champ sur grand écran. Sur mobile, les onglets du profil gardent une largeur lisible et se défilent horizontalement avec points d’ancrage, au lieu de se compresser ou de tronquer les libellés.
- Dans **Paramètres > Communications**, les interrupteurs SMS et WhatsApp d’un utilisateur sans numéro restent visuellement inactifs. Un clic ne modifie pas le formulaire et ouvre une SweetAlert : « Veuillez renseigner un numéro de téléphone avant d’activer WhatsApp ou SMS. »
- Le contrôleur refuse également toute requête forgée qui tenterait d’activer SMS/WhatsApp pour un utilisateur sans téléphone. Le contrôle serveur reste donc effectif même si le JavaScript est désactivé ou contourné.
- Les destinataires sans téléphone ne sont jamais sélectionnés par défaut pour ces deux canaux ; l’e-mail reste indépendant.

### Recette manuelle locale

1. Ouvrir **Mon profil > Téléphone**, saisir un numéro local (par exemple `90859488`), choisir le pays puis enregistrer. Vérifier le message de succès et la présence du numéro dans le résumé du profil.
2. Dans **Paramètres > Communications**, prendre un utilisateur sans téléphone et cliquer sur les interrupteurs WhatsApp puis SMS : ils doivent rester désactivés et afficher l’alerte SweetAlert demandant de renseigner un numéro.
3. Ajouter le numéro depuis le profil de cet utilisateur, revenir aux communications puis vérifier que les interrupteurs deviennent activables.
4. Vérifier côté serveur qu’une requête HTTP manuelle avec `recipients[*][*][whatsapp]=1` ou `sms=1` sans téléphone est refusée avec le message de numéro requis.

## Correctif contraste POS en mode clair — 11 septembre 2026

- Les actions **Sauvegarder** et **En cours** de la caisse n’utilisent plus un texte blanc fixe sur une surface claire : elles héritent désormais de `--ds-text-primary`, comme les autres contrôles lisibles du template SaaS.
- Le cache-busting de `saas-pos.css` a été incrémenté afin que le correctif soit immédiatement chargé après déploiement.
- Contrôles : cache Blade, `ui:lint --changed`, syntaxe Blade et `git diff --check` réussis.

## Finalisation de l’audit de traçabilité — 11 septembre 2026

- Le cahier d’architecture a été aligné sur la décision produit validée : les commissions sont disponibles immédiatement après confirmation serveur du paiement ; le paramètre de délai reste versionné pour une éventuelle réactivation future.
- Le seul échec relevé par l’audit venait du scénario `PartnerFoundationTest` qui utilisait `actingAs()` sans renseigner la clé de session `partner_auth_version`. Le test initialise maintenant cette clé comme le ferait une session partenaire réelle ; aucun garde de sécurité ni code financier de production n’a été assoupli.
- Contrôle ciblé final : **74 tests, 434 assertions, 0 échec** couvrant authentification, codes, attribution, commissions, retraits, queue, opérateurs, réglages, trésorerie administrateur et conditions de paiement.
- Contrôles complémentaires : `php artisan view:cache`, `php artisan ui:lint --changed`, `php artisan schedule:list` et `git diff --check` réussis. Le scheduler publie bien les réconciliations partenaire et plateforme chaque minute.
- Restent des gates de livraison et non des corrections de code : migration additive sur l’environnement cible, recette KPrimePay staging avec IP autorisée, tests de concurrence MySQL/charge, sauvegarde-restauration et validation juridique/KYC.

## Renforcement local — supervision et bornes financières — 11 septembre 2026

- La page **Santé du système** expose maintenant le nombre de retraits partenaire et plateforme en `processing` ou `unknown`, afin de rendre visibles les réserves qui attendent une réconciliation. Cette lecture reste passive et ne contacte jamais KPrimePay.
- Les bornes de la grille de commission (1, 5, 6, 25, 26, 35, 36, 75, 76, 95, 96, 175, 176 et plafond) sont désormais couvertes par un test unitaire dédié.
- Une recette locale ciblée a validé la supervision et la grille : **19 tests, 36 assertions, 0 échec**. `view:cache`, `ui:lint --changed` et `git diff --check` restent verts.
- Aucun appel KPrimePay réel, aucune migration destructive et aucune modification de la base `POS` n’ont été effectués.

## Validation locale complète — 11 septembre 2026

- La suite PHPUnit complète a été rejouée sur `pos_testing` avec l’environnement de test forcé et sécurisé : **351 tests, 2 014 assertions, 0 échec**.
- Les assertions obsolètes ont été réalignées sur l’interface actuelle (libellés français, contrôles de visibilité, PWA `maxanou-pwa-v8`, catalogue publié et journal de communication). Aucun comportement métier n’a été dégradé pour satisfaire les tests.
- Le modèle `Company` garantit désormais à la création les invariants de tenant (`active`, `TG`, `FCFA`, `Africa/Douala`, `fr`) même si une installation historique conserve des colonnes nullable ; cela évite qu’un job de notification ou de réconciliation ignore silencieusement une entreprise.
- `git diff --check`, `php artisan view:cache` et `php artisan ui:lint --changed` sont verts après cette validation. Le contrôle de planification confirme les réconciliations partenaire et plateforme chaque minute.
- Cette validation est strictement locale : `POS` et staging n’ont pas été touchés, aucun transfert KPrimePay réel n’a été lancé et aucune donnée de production n’a été modifiée.
