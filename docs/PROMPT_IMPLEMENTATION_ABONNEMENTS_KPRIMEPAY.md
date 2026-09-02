# Prompt d'implementation - abonnements POS SaaS avec KPrimePay

## Mission

Tu travailles dans le projet Laravel local `C:\POS`. Tu dois concevoir, implementer, securiser, tester et documenter le moteur d'abonnements du POS SaaS en reutilisant l'integration KPrimePay deja en production pour l'achat de quotas SMS et WhatsApp.

Ce chantier touche a l'argent et au controle d'acces. Une erreur de prix, une activation sans paiement, un double traitement de webhook, une mauvaise limite ou une fuite inter-entreprises est un incident critique. Travaille par phases courtes, teste chaque phase immediatement, et ne passe a la phase suivante que lorsque les tests de la phase courante sont verts.

La consigne du proprietaire prime sur toute contradiction documentaire : un client peut renouveler son plan actuel ou passer a un plan superieur, mais il ne peut jamais passer a un plan inferieur. Cette interdiction doit etre appliquee dans l'interface, dans la validation serveur et dans le service de reglement. Une requete HTTP falsifiee ne doit jamais permettre un downgrade.

## Lecture obligatoire avant toute modification

Lis integralement, dans cet ordre :

1. `AGENTS.md` ;
2. `docs/FREEBUFF_HANDOFF.md` ;
3. `docs/STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.pdf` (15 pages, version 2.1 du 31 aout 2026) ;
4. `docs/GUIDE_KPRIMEPAY.md` ;
5. `docs/CAHIER_DES_CHARGES_ADMINISTRATION_SAAS.md` ;
6. `docs/RAPPORT_ADMINISTRATION_SAAS.md` ;
7. `docs/RAPPORT_GLOBAL_SAAS.md` ;
8. `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` ;
9. les migrations, modeles, services, middlewares, routes, controleurs, vues et tests cites dans ce prompt.

Avant de toucher un fichier, execute `git status --short`, examine le diff des fichiers vises et preserve toutes les modifications existantes. Le depot est sale et les changements deja presents appartiennent a leurs auteurs. Aucun reset destructif, aucun `migrate:fresh` hors base de test, aucune modification de secret ou de `.env` reel.

## Architecture existante a respecter

- Application Laravel multi-entreprises. La compagnie active est resolue par `CompanyContext`, `ResolveCompany` et `EnsureCompanySelected`.
- L'isolation metier repose sur `company_id`, le trait `BelongsToCompany`, les scopes, les contraintes physiques et les tests IDOR. Ne jamais accepter un `company_id` fourni par le navigateur comme source d'autorite.
- Les roles sont locaux a chaque compagnie via `company_user.role_id`. Les roles systeme sont `owner`, `admin` et `cashier`. Ne pas utiliser l'ancien `users.user_type`.
- Les permissions de role et les capacites de plan sont deux couches differentes. Une action n'est autorisee que si la permission ET l'entitlement du plan l'autorisent.
- L'achat de quotas utilise deja `KprimePayService`, `QuotaPayment`, `QuotaPaymentSettlementService`, `SmsQuotaController`, `KprimePayWebhookController`, la reconciliation planifiee et les tests `QuotaPaymentTest`/`PlatformPaymentPricingTest`.
- Le navigateur ne credite jamais rien. L'URL de retour n'est jamais une preuve. Un succes doit etre reconfirme par `/v2/transactions/debit-status`, puis montant, devise, transaction et statut doivent etre compares avant un reglement SQL atomique.
- KPrimePay est configurable par la plateforme. La cle reste uniquement dans `.env`, n'est jamais affichee ni journalisee et doit posseder `payments:write` et `read`.
- Toute action serveur affiche `window.ServerButtonLoader`, bloque les doubles clics, attend la vraie reponse serveur et restaure le bouton avec un message exploitable en cas d'erreur. Les SweetAlert utilisent `showLoaderOnConfirm`, `preConfirm` et restent bloquees pendant `Swal.isLoading()`.
- Le frontend actif utilise `layouts.saas`, les tokens `--ds-*`, les composants `x-ui.*`, le contraste clair/sombre, la navigation clavier, le focus restaure, le responsive et `prefers-reduced-motion`.
- La console plateforme utilise un garde separe `platform`. Ne fusionne jamais les administrateurs plateforme avec les roles owner/admin des compagnies.

## Offre commerciale normative

Tous les prix sont en XOF/FCFA et hors taxes. Ne code jamais un prix fiable uniquement dans Blade ou JavaScript. Les calculs ci-dessous sont recalcules cote serveur a partir du catalogue versionne en base.

| Rang | Cle stable | Plan | Prix mensuel HT | Prix annuel HT | Acces annuel | Compagnies | Utilisateurs distincts | Produits actifs par compagnie | SMS inclus/mois | WhatsApp inclus/mois | Fonctions |
|---:|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---|
| 0 | `trial` | Essai | 0 | 0 | 14 jours | 1 | 1 | 10 | 3 une fois | 3 une fois | Toutes |
| 1 | `basic` | Basic | 2 500 | 27 500 | 12 mois | 1 | 2 | 50 | 10 | 10 | Toutes sauf Fournisseurs et E-commerce |
| 2 | `bronze` | Bronze | 5 000 | 55 000 | 12 mois | 1 | 3 | 150 | 20 | 20 | Toutes |
| 3 | `silver` | Argent | 10 000 | 110 000 | 12 mois | 2 | 5 | 500 | 50 | 50 | Toutes |
| 4 | `gold` | Gold | 20 000 | 220 000 | 12 mois | 5 | 15 | 1 000 | 100 | 100 | Toutes |

Regles :

- Mensuel : une mensualite payee donne un mois d'acces et credite une fois le quota mensuel.
- Annuel : onze mensualites sont facturees, douze mois d'acces sont accordes, et douze quotas mensuels sont credites immediatement.
- Les quotas SMS et WhatsApp restent separes, cumulables et sans expiration. Les recharges payantes existantes restent possibles.
- La limite de produits s'applique separement a chaque compagnie incluse dans le compte d'abonnement.
- La limite d'utilisateurs correspond au nombre total d'identifiants utilisateur distincts ayant une adhesion active dans au moins une compagnie du compte d'abonnement ; un meme utilisateur present dans deux compagnies ne compte qu'une fois.
- Une compagnie inactive, une adhesion inactive et un produit archive/inactif ne consomment pas la limite active. Documente et teste cette definition.
- Bronze porte le badge `Recommande` ; le plan courant porte `Plan actif`.
- L'essai donne toutes les fonctions pendant 14 jours, une seule fois, avec 3 SMS et 3 WhatsApp credites une seule fois.
- Aucun delai de grace. Rappels J-3, J-2, J-1 ; a J0 lecture seule immediate.
- Atteindre une limite ou expirer ne supprime, ne deplace et n'archive jamais une donnee.

## Decision de modelisation obligatoire

Les limites de compagnies et d'utilisateurs portent sur un ensemble de compagnies, pas seulement sur la compagnie active. N'attache donc pas naivement l'abonnement a une seule ligne `company_settings`.

Introduis un compte de facturation explicite, par exemple `subscription_accounts`, auquel sont rattachees les compagnies couvertes. Le compte est cree lors de l'inscription initiale, lie au proprietaire fondateur, et contient une compagnie principale/de facturation. Une compagnie supplementaire creee par ce proprietaire rejoint ce meme compte dans la limite du plan. Une simple invitation d'un utilisateur dans une compagnie ne transfere jamais cette compagnie dans son propre compte d'abonnement.

Avant la migration, audite les compagnies existantes et prepare un backfill deterministe base sur `created_by` et le role `owner`. Si un cas est ambigu (plusieurs proprietaires fondateurs possibles, `created_by` absent, groupes qui se chevauchent), ne l'invente pas : produis un rapport, prevois une table/mecanique d'affectation explicite, et bloque seulement le backfill ambigu. La migration doit etre reversible et ne doit supprimer aucune donnee.

Pour les quotas inclus, credite la compagnie principale/de facturation du compte d'abonnement, car les compteurs existants sont portes par `company_settings`. Affiche clairement cette destination avant paiement et dans le recu. Ne repartis pas arbitrairement les credits entre compagnies. Conserve les achats de quotas existants par compagnie.

## Schema de donnees attendu

Adapte les noms aux conventions du depot, mais couvre au minimum :

- `subscription_accounts` : proprietaire fondateur, compagnie principale, statut, dates d'essai, marqueur d'essai deja utilise, timestamps ;
- rattachement explicite et contraint des compagnies au compte de facturation ;
- `subscription_plans` : cle stable, rang immutable, libelle, actif/commercialisable, prix mensuel HT, prix annuel HT, devise, limites, quotas, duree d'essai, version et dates d'effet ;
- `plan_features` ou structure equivalente : cles stables de capacites, notamment `suppliers` et `ecommerce` ;
- `subscriptions` : compte, plan, statut, periode, `starts_at`, `ends_at`, periodicite, renouvellement manuel, source et plan/prix/limites instantanes ;
- `subscription_payments` : compte, souscription cible, utilisateur initiateur, transaction interne, idempotency key, reference KPrimePay, event id, montant HT, taxe, montant total, devise, plan/rang/periodicite/duree/quotas instantanes, statut, URL checkout, expiration, paiement/echec ;
- `subscription_events` ou journal equivalent : essai, activation, renouvellement, upgrade, expiration, rappels, reconciliation, refus ;
- compteurs d'usage uniquement si necessaires ; les valeurs canoniques doivent rester calculables depuis les tables metier afin d'eviter une divergence silencieuse.

Contraintes minimales : references de transaction, idempotency key et event id uniques ; index sur compte, statut et dates ; cles etrangeres explicites ; montants en entiers, jamais en flottants ; devise `XOF` ; snapshots non retroactifs ; aucun prix historique modifie lors d'une edition du catalogue.

Les migrations doivent etre testees en `up` et `down` sur la base de test. Les seeders doivent etre idempotents. Ne fige pas la source tarifaire dans `config/marketing.php` : apres implementation, le site marketing, l'espace Abonnement et la console plateforme doivent lire une source canonique partagee, avec un fallback controle uniquement pendant migration.

## Services metier attendus

Cree des services petits et testables :

1. `SubscriptionCatalogService` : catalogue actif, version et snapshots de prix/limites.
2. `SubscriptionAccountService` : resolution du compte depuis l'utilisateur et la compagnie active, sans `company_id` navigateur.
3. `SubscriptionPricingService` : calcul mensuel/annuel cote serveur, taxes configurables, total, periode et quotas. Ignore tout montant, remise, taxe, date ou quota recu du client.
4. `EntitlementService` : plan effectif, fonctionnalites, limites, usage actuel, statut, lecture seule et raisons explicables.
5. `SubscriptionCheckoutService` : cree une tentative unique puis appelle KPrimePay.
6. `SubscriptionSettlementService` : reconfirme KPrimePay et regle atomiquement avec `lockForUpdate`.
7. `SubscriptionLifecycleService` : essai, renouvellement, upgrade, expiration, rappels et reactivation.

Refactorise `KprimePayService` seulement autant que necessaire pour supporter deux types de paiements. Ne duplique pas le client HTTP, les timeouts ou la validation de reponse. En revanche, ne reutilise pas `quota_payments` pour un abonnement. Preserve strictement le comportement et les tests des quotas.

Le webhook peut etre generalise ou dispatcher par prefixe/type de transaction. Il doit retrouver la transaction uniquement cote serveur, ignorer les metadonnees non fiables, accepter les formats V1 et V2 deja geres, verifier les en-tetes V2, puis appeler le bon settlement. Un event d'abonnement ne doit jamais crediter un quota achete et inversement.

## Regles financieres et temporelles

- Le serveur genere `transaction_id` et `idempotency_key`.
- Le checkout stocke un snapshot complet avant l'appel externe.
- Le paiement reste `created`/`pending` jusqu'a confirmation.
- Le retour navigateur affiche seulement un etat d'attente et lance le polling local ; il n'active rien.
- Un webhook re joue, deux webhooks concurrents, un polling simultane ou une reconciliation manuelle ne produisent qu'une activation et qu'un credit de quotas.
- Refuse tout ecart de transaction, montant, devise ou statut. Journalise une cause synthetique sans secret ni payload personnel complet.
- Paiement echoue : aucune activation, aucun quota.
- Paiement expire : aucune activation ; reconciliation automatique toutes les dix minutes, comme pour les quotas.
- Renouvellement du meme plan actif : nouvelle periode a partir de `max(now, ends_at)`.
- Plan expire : nouvelle periode a partir de la confirmation.
- Paiement pendant l'essai : activation payante immediate et fin de l'essai.
- Upgrade : activation immediate du plan superieur pour une periode complete a partir de la confirmation. Il n'y a ni prorata, ni avoir, ni remboursement automatique du reliquat. Cette consequence doit etre affichee avant confirmation. Place cette politique derriere un test metier et signale-la comme decision commerciale a valider avant activation en production.
- Downgrade : toujours refuse, meme apres expiration si le dernier plan payant connu a un rang superieur. Seul un administrateur plateforme disposant d'une future procedure exceptionnelle, auditee et non demandee dans ce chantier pourrait y deroger ; n'implemente pas cette derogation maintenant.
- Utilise des dates UTC en base et le fuseau de la compagnie pour l'affichage. Definis et teste precisement les bornes inclusives/exclusives afin d'eviter un jour offert ou perdu.

## Essai gratuit et prevention des abus

L'essai commence une seule fois lors de l'inscription initiale reussie et dure 14 jours calendaires. Il est lie au compte de facturation, au proprietaire fondateur et a la compagnie d'origine. Une invitation, une nouvelle compagnie, un changement d'e-mail ou une nouvelle adhesion ne cree pas d'essai.

Le provisioning et l'inscription doivent creer atomiquement le compte de facturation, l'essai, le rattachement de la compagnie et les 3+3 credits. Une reprise apres erreur ou un double POST ne doit pas doubler l'essai ni les credits. Pour les donnees existantes, ne credite pas retroactivement un essai sans regle de migration explicitement validee.

## Autorisation du menu Abonnement

Ajoute un menu principal `Abonnement` dans `resources/views/partials/saas-sidebar.blade.php`.

- Il est visible et accessible uniquement si le role systeme de l'adhesion active est `owner` ou `admin`.
- Ajoute une permission `subscription.manage` pour la defense en profondeur, attribuee aux roles systeme owner/admin lors du provisioning et du backfill.
- Un role personnalise, un caissier ou un utilisateur sans cette autorisation recoit 403 sur toutes les routes de consultation detaillee, checkout, renouvellement et upgrade.
- Les autres utilisateurs peuvent eventuellement recevoir dans le shell un petit libelle non sensible du plan et de son etat, mais ne voient ni historique financier, ni reference de transaction, ni bouton de paiement.
- La route de checkout doit revalider le role systeme, la permission, l'adhesion active et l'appartenance au compte de facturation.

## Page Abonnement cote utilisateur

La page responsive doit afficher :

- plan effectif, statut (`trial`, `active`, `expiring`, `expired`), periodicite, prix HT, dates et jours restants ;
- usage `compagnies / limite`, `utilisateurs distincts / limite`, `produits actifs / limite` pour chaque compagnie ;
- soldes actuels SMS/WhatsApp et quotas qui seront credites par le prochain paiement ;
- fonctions incluses et exclusions ;
- historique pagine des souscriptions et paiements, sans secret ;
- cartes des cinq offres, Bronze recommande, plan courant identifie ;
- aucun bouton sur un plan de rang inferieur ; afficher `Plan inferieur indisponible` avec explication ;
- choix 1 mois ou 12 mois et recapitulatif detaille : prix mensuel, calcul annuel x11, mois offert, HT, taxe, total, periode, quotas et compagnie destinataire ;
- confirmation explicite avant upgrade, notamment l'absence de prorata ;
- fenetre KPrimePay ouverte directement par le clic utilisateur, polling toutes les trois secondes, fermeture/rechargement seulement apres statut local `paid`, et repli par redirection si la pop-up est bloquee.

Le navigateur peut afficher une estimation mais le serveur renvoie le recapitulatif canonique signe par l'identifiant de tentative. Ne fais jamais confiance a `plan_id`, rang, prix, duree, taxe, quotas ou dates sans rechargement serveur du catalogue.

## Application des fonctions et limites

Ne te contente jamais de masquer des menus.

- Cree un middleware/couche serveur d'entitlement qui s'execute apres resolution de compagnie et permission.
- Basic bloque cote serveur les routes Fournisseurs et E-commerce, y compris endpoints AJAX, exports, managers et commandes. Les donnees existantes restent lisibles si elles ont ete creees sous un ancien plan plus haut ; aucune creation/modification/suppression n'est permise pour une fonction exclue.
- Le storefront public d'une compagnie sans entitlement E-commerce actif reste consultable au besoin sous une page d'indisponibilite, mais n'accepte aucune nouvelle commande. Le POST checkout public est bloque cote serveur.
- La limite de compagnies est verifiee avant `CompanyController::store` et dans tout autre chemin de creation/provisioning.
- La limite d'utilisateurs distincts est verifiee avant invitation, acceptation, rattachement, restauration et transfert vers une compagnie du compte. Revalide dans la transaction finale pour eviter les courses.
- La limite de produits actifs par compagnie est verifiee avant creation, duplication eventuelle et restauration/reactivation d'un produit. Un produit archive ne consomme pas la limite ; sa restauration peut etre refusee sans suppression.
- Utilise transaction et verrou/advisory lock approprie pour que deux requetes concurrentes ne depassent pas une limite.
- A l'atteinte d'une limite, renvoie HTTP 422/403 coherent avec un message indiquant usage, limite et lien d'upgrade. Ne supprime jamais de donnees.

Fais un inventaire complet des routes d'ecriture. Ne suppose pas que POST/PUT/PATCH/DELETE sont les seules mutations : recherche les GET historiques ayant un effet de bord et corrige/protege leur contrat avant de declarer la lecture seule fiable.

## Expiration et lecture seule

Sans abonnement actif et lorsque l'application des plans est active :

- autorise consultation, recherche, filtres, tableaux de bord, historiques, rapports, exports et telechargements existants selon les permissions ;
- bloque creation, modification, suppression, archivage, restauration, vente, stock, caisse, transaction, conversion/annulation de commande, nouvelle communication et invitation ;
- laisse accessibles connexion/deconnexion, profil, changement de compagnie, page Abonnement, checkout, statut de paiement et routes necessaires au renouvellement ;
- n'envoie pas de nouvelle commande E-commerce publique ;
- ne modifie pas definitivement `ecommerce_active` a l'expiration. Calcule une disponibilite effective `configuration active ET entitlement actif`, afin de retrouver automatiquement la boutique apres renouvellement.

Message standard : `Votre abonnement n'est plus actif. Vous pouvez consulter et exporter vos donnees, mais les nouvelles operations et votre boutique en ligne sont suspendues. Renouvelez votre abonnement pour reprendre votre activite.`

Les commandes planifiees doivent envoyer les rappels J-3/J-2/J-1 une seule fois par jour et par abonnement, marquer l'expiration a J0 de facon idempotente et journaliser le resultat. Les jobs transportent l'identifiant de compte/compagnie necessaire et restaurent explicitement le contexte.

## Interrupteur plateforme d'application des abonnements

Dans `Administration SaaS > Parametres generaux`, ajoute un reglage persistant `subscriptions.enforcement_enabled` libelle par exemple `Verifier les abonnements avant les acces metier`.

- Valeur initiale/fallback : `false`, pour permettre le travail local et un deploiement progressif sans verrouillage accidentel.
- `false` desactive seulement les restrictions de fonctions, limites et lecture seule. Il ne fabrique pas un abonnement actif, ne credite aucun quota, ne masque pas les vrais statuts et ne contourne jamais authentification, permissions, isolation tenant, statut actif de compagnie, maintenance, CSRF, rate limits ou verification KPrimePay.
- Le menu Abonnement, le catalogue, l'historique et les paiements restent consultables/testables lorsque le controle est desactive.
- Le frontend peut afficher un badge plateforme `Controle des abonnements desactive` aux administrateurs autorises ; ne l'affiche pas comme un abonnement paye aux clients.
- Le changement exige permission plateforme adaptee, mot de passe courant, motif obligatoire, confirmation SweetAlert avec loader, transaction, invalidation du cache, historique et `PlatformAuditLog` avec ancienne/nouvelle valeur.
- L'activation globale doit effectuer un preflight en lecture seule : nombre de comptes sans plan/essai valide, comptes ambigus, abonnements expires et boutiques qui deviendront indisponibles. Affiche cet impact avant confirmation.
- Teste explicitement que le mode OFF permet toutes les fonctions autorisees par role en local et que le mode ON applique immediatement les entitlements.

## Administration plateforme des abonnements

Ajoute un espace plateforme separe, protege par des permissions explicites telles que `platform.subscriptions.view`, `platform.subscriptions.manage_catalog` et `platform.subscriptions.reconcile`.

Il doit fournir : catalogue/version des plans, etat commercialisable, prix et limites ; liste des comptes et abonnements ; paiements et statuts ; filtres ; detail et timeline ; preflight d'activation ; reconciliation KPrimePay motivee et auditee. Aucun bouton `marquer paye`, aucun credit manuel, aucune modification directe d'un abonnement actif sans flux financier verifie.

Toute modification future d'un prix/limite cree une nouvelle version effective pour les prochains checkouts. Elle ne reecrit ni les paiements, ni les snapshots, ni les periodes historiques. Les changements sensibles exigent mot de passe plateforme et raison.

Mets a jour `docs/RAPPORT_ADMINISTRATION_SAAS.md` apres chaque phase touchant la plateforme, comme l'impose le handoff.

## Phases obligatoires et tests immediats

### Phase 0 - audit et baseline

- Inventorie routes, actions d'ecriture, creations de compagnies/utilisateurs/produits, E-commerce, scheduler et integration KPrimePay.
- Execute la suite ciblee puis la suite complete existante, sequentiellement.
- Documente la baseline exacte sans attribuer au chantier les modifications deja presentes.
- Mets a jour `docs/FREEBUFF_HANDOFF.md` : audit fait, fichiers vises, risques, tests, reste a faire.

### Phase 1 - schema, catalogue et backfill

- Ajoute migrations/modeles/relations/seeders et snapshots.
- Tests : migration up/down, seed idempotent, prix exacts, annuel x11/acces x12, limites, contraintes uniques, backfill clair, cas ambigu bloque, aucune retroactivite.
- Lance les tests de phase puis `git diff --check`.
- Mets a jour Freebuff avant de continuer.

### Phase 2 - compte de facturation et essai

- Integre inscription et provisioning atomiques.
- Tests : essai 14 jours, 3+3 une seule fois, double requete, nouvelle compagnie/invitation/changement e-mail sans nouvel essai, rattachement correct, isolation de deux fondateurs.
- Mets a jour Freebuff.

### Phase 3 - entitlements et limites, sans blocage global

- Cree `EntitlementService`, compteurs et reponses explicables. Garde `subscriptions.enforcement_enabled=false`.
- Tests unitaires matrice plans/fonctions, limites exactes et depassements concurrents ; tests Feature sur creation/restauration de compagnie, utilisateur et produit ; tests IDOR inter-comptes.
- Mets a jour Freebuff.

### Phase 4 - checkout et reglement KPrimePay

- Generalise prudemment KPrimePay, ajoute paiement d'abonnement, webhook/dispatcher, polling et reconciliation.
- Tests HTTP fakes : payload checkout, bearer/idempotency, montant recalcule, V1/V2, succes, refus, abandon, confirmation tardive, mismatch montant/devise/transaction, double webhook, concurrence, mauvais type de paiement, aucun double quota, retour navigateur sans activation.
- Relance integralement `QuotaPaymentTest` et `PlatformPaymentPricingTest` pour prouver l'absence de regression.
- Mets a jour Freebuff et `GUIDE_KPRIMEPAY.md`.

### Phase 5 - cycle abonnement

- Active renouvellement, upgrade, expiration, rappels et quotas inclus.
- Tests avec horloge figee : bornes des 14 jours, fin de mois, annee bissextile, J-3/J-2/J-1/J0, renouvellement depuis `ends_at`, upgrade immediat, absence de prorata, downgrade refuse dans tous les etats, quotas mensuels/annuels exacts et idempotents.
- Mets a jour Freebuff.

### Phase 6 - enforcement et lecture seule

- Branche les middlewares sur toutes les routes et le storefront. Ajoute le toggle plateforme et son preflight.
- Tests matrice `OFF/ON x trial/active/expiring/expired x Basic/Bronze/Argent/Gold x owner/admin/cashier/custom`.
- Tests de lecture seule pour chaque module : GET/export autorises selon permission, ecritures bloquees, paiement/profil/logout accessibles, storefront sans nouvelle commande.
- Tests Basic : Fournisseurs et E-commerce bloques cote serveur ; Bronze+ autorises.
- Mets a jour Freebuff et le rapport administration.

### Phase 7 - interfaces

- Ajoute menu/page Abonnement et ecrans plateforme en reutilisant le design system.
- Tests Blade/Feature : visibilite owner/admin seulement, 403 autres roles, boutons downgrade absents/desactives, donnees echappees, aucune confiance aux valeurs JS, loaders et double-clic.
- Recette navigateur non destructive a 1440, 1024, 768 et 390 px, themes clair/sombre, clavier, focus, zoom 200 %, contenu long, succes simule et erreurs simulees. Aucun vrai paiement pendant une recette visuelle.
- Mets a jour Freebuff.

### Phase 8 - validation finale et activation controlee

- `php artisan view:cache` ;
- tests abonnement cibles ;
- tests KPrimePay/quotas ;
- tests permissions/isolation/E-commerce/ventes ;
- `php artisan test --stop-on-failure` sequentiellement ;
- `git diff --check` ;
- verifier routes et scheduler ;
- verifier qu'aucun secret, token, payload sensible ou prix flottant n'est introduit ;
- laisser l'enforcement OFF tant qu'une validation explicite de production n'a pas ete donnee ;
- mettre a jour `FREEBUFF_HANDOFF.md`, `RAPPORT_ADMINISTRATION_SAAS.md`, `RAPPORT_GLOBAL_SAAS.md`, `GUIDE_KPRIMEPAY.md` et la mention previsionnelle de `config/marketing.php`/site Tarifs.

## Cas de test financiers minimums

- Basic mensuel = 2 500 XOF, +10 SMS, +10 WhatsApp, 1 mois.
- Basic annuel = 27 500 XOF, +120/+120, 12 mois.
- Bronze annuel = 55 000 XOF, +240/+240.
- Argent annuel = 110 000 XOF, +600/+600.
- Gold annuel = 220 000 XOF, +1 200/+1 200.
- Toute valeur navigateur modifiee est ignoree ou rejetee.
- Deux confirmations paralleles d'un Gold annuel produisent une seule periode et un seul credit +1 200/+1 200.
- Un webhook de montant 219 999 ou de devise differente n'active rien.
- Un plan inactive/non commercialisable ne peut pas etre achete depuis une ancienne page ouverte.
- Une modification de prix apres creation du checkout ne modifie pas le paiement en attente.
- Un paiement Basic apres Gold est refuse avant checkout et au settlement si une ancienne tentative est rejouee.

## Criteres de fin

Ne declare pas le chantier termine si l'un de ces points manque :

- source tarifaire canonique et versionnee ;
- snapshots financiers non retroactifs ;
- essai unique anti-abus ;
- renewal/upgrade fiables et downgrade impossible ;
- reglement KPrimePay reconfirme, atomique et idempotent ;
- quotas inclus credites une seule fois ;
- limites appliquees cote serveur avec protection contre la concurrence ;
- Basic bloque Fournisseurs/E-commerce cote serveur ;
- expiration en lecture seule sans suppression ;
- toggle plateforme OFF/ON audite et sans contournement de securite ;
- menu/paiement reserves a owner/admin ;
- tests de chaque phase et suite complete verts ;
- recette responsive/accessibilite faite ;
- documentation et Freebuff mis a jour apres chaque progression.

Dans le compte rendu final, distingue clairement : implemente, valide automatiquement, valide manuellement, non teste, risques/decisions commerciales restantes et commandes de deploiement. N'ecris jamais `termine` pour un paiement ou un controle d'acces non teste.
