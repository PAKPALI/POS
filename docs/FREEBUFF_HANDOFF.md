# Reprise du chantier SaaS multi-entreprises

Dernière mise à jour : 3 septembre 2026 — état consolidé après validation des abonnements, quotas, limites et notifications.

## État de référence au 3 septembre 2026

Cette section prévaut sur les anciennes entrées historiques de ce handoff. Le développement fonctionnel du plan d’abonnement est terminé pour le périmètre prévu : catalogue versionné, essai de 14 jours, choix de 1 à 12 mois avec remise uniquement à 12 mois, montée de plan sans descente, règlement KPrimePay séparé des quotas, webhooks idempotents, expiration et rappels par e-mail, contrôle des fonctionnalités et limites compagnie/utilisateur/produit, SweetAlert avec proposition d’amélioration pour les propriétaires et administrateurs, et notification des paiements confirmés aux administrateurs plateforme.

Les tests ciblés abonnement, quotas, webhooks, expiration, pré-contrôle et catalogue passent. Le checkout de test KPrimePay, les webhooks, le SMTP réel de staging et la recette visuelle mobile/desktop ont été validés par le propriétaire. La suite de développement reste terminée à **100 %** ; seules la configuration des secrets/URL de production, la supervision et l’activation progressive de `subscriptions.enforcement_enabled` restent à réaliser sur l’hébergement.

La fixture locale du compte `didierlombardo48@gmail.com` est mutable et a servi à plusieurs recettes manuelles (Basic, Bronze, Argent, Gold puis Essai). Elle ne doit donc pas être considérée comme une vérité permanente dans ce document : vérifier l’état courant directement dans la base locale avant chaque test et ne jamais reproduire cette fixture en production.

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
