# Reprise du chantier SaaS multi-entreprises

Dernière mise à jour : 25 août 2026 — après le troisième lot d’optimisation SQL multi-compagnies.

Ce fichier est le point de reprise commun pour Codex, Freebuff et tout autre intervenant. Le lire intégralement avant toute modification. Ne pas refaire les fonctions indiquées comme terminées et ne pas faire travailler deux assistants simultanément sur les mêmes fichiers.

Rapport actuel au 27 août 2026 : `docs/RAPPORT_AVANCEMENT_SAAS_2026-08-27.md`. Référence automatique après correction PWA : **143 tests, 824 assertions, 0 échec**. Trois migrations récentes concernant les factures, indicatifs pays et journaux de communications sont encore en attente dans la base locale ; les appliquer seulement après sauvegarde et contrôler séparément le staging.

Correctif connexion PWA staging du 27 août : le manifeste démarre désormais sur `/home` au lieu de `/`, le cache passe à `pro-seller-pwa-v4`, le POST de connexion est relatif à l’origine installée et la redirection reçue est ramenée au même hôte. L’e-mail mobile est normalisé (espaces/majuscules) et les erreurs 419/429 sont explicites. Après déploiement, supprimer l’ancien raccourci PWA, ouvrir le domaine HTTPS canonique dans Safari/Chrome, recharger puis réinstaller l’application.

Installation Android : `public/pwa-register.js` intercepte désormais `beforeinstallprompt` et affiche une bannière interne avec logo, « Plus tard » et « Installer ». Le refus est mémorisé 7 jours, l’installation masque la bannière et le bouton présente un loader pendant l’invite système. La bannière n’apparaît que si Chrome juge la PWA installable ; HTTPS, manifeste, service worker et absence d’installation existante restent obligatoires.

Compatibilité des autres navigateurs mobiles : si `beforeinstallprompt` n’est pas disponible, une bannière de secours apparaît après 4 secondes et explique comment utiliser le menu du navigateur pour installer ou ajouter la PWA à l’écran d’accueil. iPhone/iPad conservent leur guide Safari dédié. Aucun navigateur ne permet au site de forcer une installation lorsque son API native n’existe pas.

Organisation du menu Communications : un parent unique `SMS & WhatsApp` regroupe désormais `Configuration` (ancienne page Notifications), `Quota` et `Consommation`. Chaque sous-menu reste masqué ou affiché selon sa permission propre. Le lien Notifications a été retiré de Paramètres, qui redevient centré sur la compagnie.

Rapport consolidé destiné au propriétaire : `docs/RAPPORT_AVANCEMENT_SAAS_2026-08-25.md`. Il résume les acquis, les pourcentages, les risques résiduels et l’ordre recommandé avant le pilote.

Rapport de charge reproductible : `docs/RAPPORT_TEST_VOLUME_SAAS_2026-08-25.md`. Le benchmark MySQL isolé utilise uniquement une base `*_testing`, charge jusqu’à 100 000 lignes de vente détaillées et ne doit pas être ajouté à la suite quotidienne.

Rapport de concurrence : `docs/RAPPORT_CONCURRENCE_SAAS_2026-08-25.md`. Dix ventes simultanées et huit conversions simultanées d’une même commande sont validées sans survente, doublon ni écart de caisse. Le benchmark est également exclu de la suite quotidienne.

Rapport de charge des notifications : `docs/RAPPORT_CHARGE_NOTIFICATIONS_2026-08-25.md`. Le benchmark à 4 workers et 1 000 livraisons a révélé puis permis de corriger une double revendication concurrente. Après correction : 0 doublon, 0 échec et environ 108 livraisons locales par seconde.

Rapport des exports PDF : `docs/RAPPORT_EXPORTS_PDF_VOLUME_2026-08-26.md`. DomPDF dépassait 512 Mo sur plusieurs milliers de lignes. Les exports sont désormais bornés avant rendu à 300 produits, 500 mouvements ou 100 ventes ; les volumes supérieurs demandent un filtre plus précis.

Rapport CSV/Excel : `docs/RAPPORT_EXPORTS_CSV_EXCEL_2026-08-26.md`. Produits, Inventaire et Historique disposent d’exports exhaustifs générés en flux, filtrés par compagnie et protégés contre l’injection de formules. Les boutons utilisent le loader serveur global.

- Correctif téléchargement CSV/Excel : la PWA servait encore l’ancien `server-button-loader.js`, rendant `ServerButtonLoader.download()` indisponible. Le cache est maintenant `pro-seller-pwa-v3` et le script porte `?v=20260826-1` dans tous les layouts. Conserver le versionnement lors des prochaines modifications d’assets mis en cache.

## Niveau d’avancement

- Migration fonctionnelle SaaS : **environ 94 %**.
- Préparation à une production SaaS : **environ 70 %**.
- Monétisation et abonnements : **0 % volontairement**, les plans seront définis ultérieurement.
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
- Rapport complet : `docs/RAPPORT_TEST_VOLUME_SAAS_2026-08-25.md`. La performance locale passe raisonnablement de 88 % à **90 %**. Prochaine étape : concurrence multi-utilisateurs et données réparties sur plusieurs mois.

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
- Documentation : `docs/INTEGRATION_KPRIMEPAY_QUOTAS.md`. Après ajout du scénario webhook V1 et la non-régression complète : **138 tests, 785 assertions, 0 échec**.
- Expérience PWA KPrimePay : le checkout s’ouvre désormais dans une fenêtre séparée créée directement par le clic utilisateur. La page Quotas reste ouverte, garde le bouton bloqué avec son loader, surveille le statut local toutes les 3 secondes, ferme la fenêtre et se recharge après confirmation `paid`. Une redirection complète reste disponible si les pop-ups sont bloquées. Ne jamais remplacer ce contrôle local par une confiance dans la seule URL de retour.
- Identité des e-mails : ventes, stock, inventaire hebdomadaire, invitations, accès utilisateur et commandes e-commerce présentent désormais la compagnie concernée comme nom d’expéditeur visible et dans le footer. L’adresse SMTP authentifiée reste celle de la plateforme pour préserver la délivrabilité. Le nom de l’application apparaît uniquement dans le copyright dynamique du footer partagé ; l’ancienne année fixe et la signature personnelle ont été supprimées.

