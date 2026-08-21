# Reprise du chantier SaaS multi-entreprises

Dernière mise à jour : 21 août 2026 — après navigation réciproque Connexion/Inscription.

Ce fichier est le point de reprise commun pour Codex, Freebuff et tout autre intervenant. Le lire intégralement avant toute modification. Ne pas refaire les fonctions indiquées comme terminées et ne pas faire travailler deux assistants simultanément sur les mêmes fichiers.

## Niveau d’avancement

- Migration fonctionnelle SaaS : **environ 74 %**.
- Préparation à une production SaaS : **environ 55 %**.
- Monétisation et abonnements : **0 % volontairement**, les plans seront définis ultérieurement.
- Suite complète validée : **72 tests, 360 assertions, tous réussis**.

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
- La gestion des clients dépend désormais réellement de `clients.manage` dans la Policy, les routes et le menu. Un caissier peut donc gérer les clients sans recevoir `catalog.manage`; un rôle Clients ne peut pas administrer les fournisseurs.
- Les ventes refusent un client d’une autre compagnie ou un client archivé. Les entrées de stock refusent un produit ou un fournisseur d’une autre compagnie ainsi que les enregistrements archivés. Les filtres d’inventaire étrangers sont rejetés et son export PDF utilise `CompanyContext` au lieu de `CompanySetting::first()`.
- Tests associés : `PartnerTenantSecurityTest`, soit 4 scénarios et 46 assertions couvrant les IDOR Clients/Fournisseurs, les permissions dédiées, la création dans le tenant actif, les relations Vente→Client et Inventaire→Fournisseur ainsi que les filtres d’inventaire.
- Les interfaces Clients et Fournisseurs respectent maintenant la convention d’attente serveur : loaders sur ajout/modification/consultation et loader SweetAlert bloquant avec nouvelle tentative sur archivage/restauration.
- Les agrégations SQL brutes identifiées sur `sale_details` sont filtrées par `company_id`.
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
- Les réglages de notification ont été déplacés hors de la fiche compagnie vers le sous-menu `Paramètres > Notifications`. La page gère les destinataires actifs par catégorie (`sale`, `inventory`) et canal (`email`, `whatsapp`, `sms`). Les managers e-commerce restent inchangés et continuent de recevoir les commandes. Migration : `2026_08_19_130000_create_notification_recipients_table.php`.
- L’accès à `Paramètres > Notifications` dépend de la permission de rôle `notifications.manage` (`Gestion des notifications`). Tous les membres actifs peuvent devenir destinataires ; propriétaires et administrateurs sont classés en tête et E-mail/WhatsApp sont précochés par défaut. Les tableaux deviennent défilants au-delà de trois utilisateurs. Migration : `2026_08_19_140000_add_notification_management_permission.php`.
- Route dédiée : `POST /user/attach-existing`. Elle refuse les rôles d’une autre compagnie et le rôle propriétaire.
- Flux complet d’invitation sécurisé : création depuis la compagnie active, rôle local, e-mail portant le nom de l’entreprise, jeton aléatoire stocké uniquement sous forme SHA-256, expiration à 48 heures, acceptation, refus, renvoi avec rotation du jeton et révocation. Pour un compte existant, le lien sert de connexion sécurisée à usage unique : aucun mot de passe temporaire n’est envoyé et le mot de passe permanent n’est pas modifié. Si une autre adresse est connectée, l’interface prévient puis bascule vers le compte réellement invité après confirmation.
- La migration `2026_08_21_110000_limit_pending_company_invitations_to_48_hours.php` plafonne également à 48 heures les invitations encore en attente créées avant cette règle.
- Aucun `company_user` n’est créé avant acceptation. Un compte existant doit prouver la possession de son compte ; une nouvelle adresse crée son compte depuis le lien. L’acceptation conserve toutes les autres adhésions et applique le rôle seulement dans la compagnie invitante.
- La page Utilisateurs affiche l’historique et les états des invitations. Routes publiques `invitations.*`, routes administratives `user.invitations.*`. Migration : `2026_08_21_100000_complete_company_invitations_lifecycle.php`. Tests : `CompanyInvitationFlowTest`.
- La création, le renvoi et la révocation exigent une confirmation UI mentionnant l’adresse cible (et le rôle lors de la création). L’invitation utilise désormais exactement le même appel HTML `Mail::send` que les notifications de vente. `last_sent_at` n’est mis à jour qu’après acceptation par SMTP, journalisée sous `Invitation email accepted by SMTP` avec le `message_id` disponible. Les échecs sont journalisés sous `Invitation email sending failed`.
- Pendant un renvoi ou une révocation confirmée, la fenêtre SweetAlert reste affichée avec un loader, bloque la fermeture jusqu’à la réponse et présente l’erreur dans la même fenêtre si l’opération échoue.
- Convention globale obligatoire : toute attente serveur déclenchée par un clic affiche un loader dans le bouton et bloque les doubles clics. Le composant commun est `public/hub/assets/js/server-button-loader.js`, les règles d’usage sont dans `docs/UI_CONVENTIONS.md` et `AGENTS.md` impose cette règle aux prochains intervenants/agents.
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
- Navigation tenantée : accueil, produits, catégories, produit, panier, commande et succès.
- La configuration e-commerce affiche le lien public, le statut, « Ouvrir la boutique » et « Copier le lien ».
- Le storefront restaure le contexte public avant de lire catalogue, stock et commandes.
- Conserver les anciennes routes `/shop` seulement comme compatibilité temporaire ; tout nouveau lien doit utiliser les routes `storefront.*`.

## Fichiers structurants

- `app/Services/CompanyContext.php`
- `app/Services/CompanyProvisioner.php`
- `app/Services/CompanyOnboardingService.php`
- `app/Traits/BelongsToCompany.php`
- `app/Http/Middleware/ResolveCompany.php`
- `app/Http/Middleware/EnsureCompanySelected.php`
- `app/Http/Middleware/EnsurePermission.php`
- `app/Policies/ProductPolicy.php`
- `app/Policies/CategoryPolicy.php`
- `app/Policies/ClientPolicy.php`
- `app/Policies/SupplierPolicy.php`
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
- Référence de non-régression : **72 tests réussis, 360 assertions**.
- Prochaine phase convenue avec le propriétaire : **audit non destructif puis durcissement de l’isolation en base de données**.
- Commencer par produire l’inventaire des `company_id` nuls, relations inter-compagnies possibles, clés uniques globales et règles de suppression. Ne lancer aucune migration destructive et ne modifier aucune donnée réelle avant validation du rapport d’audit.
- Après validation : backfill contrôlé, contraintes `NOT NULL`, index composés par compagnie, stratégie des clés étrangères et nouveaux tests de cohérence tenant.

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

1. Lancer la suite de tests et confirmer la référence `72 tests / 360 assertions`.
2. Effectuer uniquement un audit en lecture de la base et du schéma : aucune migration ni correction de données dans le premier passage.
3. Présenter les anomalies de `company_id`, relations croisées, contraintes et suppressions avec leur niveau de risque avant toute modification.
4. Après validation du rapport, préparer des migrations réversibles et testées pour le backfill, les `NOT NULL` et les index composés.
5. Ne pas refaire les Policies ni tests IDOR déjà terminés pour Produits, Catégories, Clients et Fournisseurs ; reprendre ensuite ce modèle sur Ventes, Inventaires, Caisses et Commandes.
6. Mettre à jour ce fichier et la documentation PDF après chaque lot validé.

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

