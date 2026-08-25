# Audit non destructif de l’isolation des données

Date : 24 août 2026  
Périmètre : schéma MySQL local, données locales, modèles, contrôleurs, routes, exports et jobs  
Mode : lecture seule — aucune migration, aucun backfill, aucune suppression et aucune donnée métier modifiée

## Verdict exécutif

La base locale auditée contient **2 compagnies** et ne présente actuellement **aucune relation inter-compagnies détectée** sur les 19 relations contrôlées. Les deux compagnies possèdent un propriétaire actif, un réglage comptable, une caisse principale et une caisse de taxe cohérents.

Depuis l’audit initial, le schéma a été durci : les `company_id` actifs sont obligatoires et les relations métier/administratives critiques disposent de clés étrangères composites incluant le tenant. Les failles auditées sur les gestionnaires E-commerce, le Profil et la création de commande publique ont également été corrigées.

Conclusion actualisée : **données locales saines et isolation physique pratiquement complète**. Les tables actives n’ont plus aucun tenant nul. Les 12 actions et 3 rôles historiques ont été archivés avec leur contenu et permissions dans `legacy_tenant_records`, sans attribution arbitraire. Les relations centrales, adhésions, invitations, destinataires et managers E-commerce sont protégés par clés composites.

## Résultats quantifiés

| Contrôle | Résultat |
|---|---:|
| Compagnies | 2 |
| Relations inter-compagnies détectées | 0 sur 19 contrôles |
| Adhésions actives sans rôle | 0 |
| Compagnies sans propriétaire actif | 0 |
| Compagnies sans réglages | 0 |
| Compagnies sans caisse principale | 0 |
| Compagnies sans caisse de taxe | 0 |
| Caisses à la fois principale et taxe | 0 |
| Compagnies avec plusieurs caisses principales ou de taxe | 0 |
| Entreprises sans slug ou identifiant public | 0 |
| Slugs ou identifiants publics dupliqués dans les données actuelles | 0 |
| Actions avec `company_id` nul | 11 sur 55 |
| Rôles avec `company_id` nul | 3 sur 10 |

Les 12 actions et trois rôles historiques identifiés ont été déplacés dans l’archive réversible `legacy_tenant_records`. Les permissions des rôles ont été conservées dans le payload d’archive.

## Relations vérifiées sans anomalie actuelle

- Produit → catégorie et fournisseur
- Vente → client
- Ligne de vente → vente et produit
- Inventaire → produit et fournisseur
- Composition de menu → menu et produit
- Transaction → caisse source et caisse destination
- Réglage comptable → caisse principale et caisse de taxe
- Ligne de commande → commande et produit
- Adhésion → rôle
- Invitation → rôle
- Destinataire de notification → adhésion active
- Manager E-commerce → adhésion active

## Risques P0 — à corriger avant le pilote SaaS

### 1. Gestionnaires E-commerce modifiables avec un identifiant de compagnie fourni par le navigateur

**Statut au 24 août 2026 : corrigé et couvert par un test IDOR.** Le contrôleur prend désormais exclusivement la compagnie de `CompanyContext`, le formulaire n’envoie plus de `company_id`, seuls les membres actifs de la compagnie sont sélectionnables et la liste/suppression sont filtrées sur le tenant actif. Le test `EcommerceManagerTenantSecurityTest` couvre la falsification de compagnie, l’ajout d’un utilisateur étranger, la lecture et la suppression inter-compagnies.

`Ecommerce\SettingController::addManager()` valide un `company_id` global reçu du formulaire et l’enregistre directement. `managersList($companyId)` accepte aussi une compagnie arbitraire, tandis que `removeManager($id)` charge le manager uniquement par son ID global. Un utilisateur autorisé dans la compagnie A pourrait donc tenter de lire, ajouter ou retirer un manager de la compagnie B.

Emplacements :

- `app/Http/Controllers/Ecommerce/SettingController.php:61-116`
- `routes/web.php` — routes `ecommerce.managers.*`

Correction recommandée : ignorer tout `company_id` reçu, prendre exclusivement `CompanyContext::getCompanyId()`, filtrer les utilisateurs sur les adhésions actives de cette compagnie et charger/supprimer les managers avec le couple `(company_id, id)`.

### 2. Modification d’un compte utilisateur global depuis le profil

**Statut au 24 août 2026 : corrigé et couvert par quatre tests de sécurité.** Les endpoints ignorent désormais tout `user_id` reçu, utilisent exclusivement l’utilisateur authentifié, exigent son mot de passe actuel et valident l’unicité de l’adresse e-mail ainsi que les confirmations. Les routes sensibles sont limitées à 10 tentatives par minute. Les nouveaux utilisateurs invités définissent déjà leur propre mot de passe lors de l’acceptation et suivent ensuite ce même parcours.

`updateEmail()` et `updatePassword()` utilisent `user_id` reçu dans la requête. Pour l’e-mail, connaître l’ancienne adresse suffit ; la condition de vérification d’existence utilise une collection et n’empêche pas correctement l’opération. Dans un SaaS où un compte appartient à plusieurs entreprises, ces endpoints doivent toujours viser `Auth::id()`.

Emplacements :

- `app/Http/Controllers/User/UserController.php:400-520`
- routes `updateEmail` et `updatePassword`

Correction recommandée : supprimer `user_id` du contrat HTTP, charger uniquement `Auth::user()`, exiger le mot de passe courant pour les deux changements et appliquer une validation `unique` correcte.

### 3. Cohérence tenant non garantie par les clés étrangères

Les clés étrangères actuelles empêchent un ID inexistant, mais autorisent techniquement une vente de la compagnie A liée à un client de B, ou une ligne de vente A liée à un produit B. La protection repose donc sur les contrôleurs, Policies et scopes Eloquent.

Origine : `database/migrations/2026_08_18_100006_add_company_id_to_business_tables.php:19-35`.

Correction recommandée après nettoyage : rendre les `company_id` métier obligatoires, créer les index uniques parents `(id, company_id)` puis des clés étrangères composées enfant `(relation_id, company_id)`.

### 4. Création de commande publique sans arrêt explicite si aucune compagnie n’est résolue

**Statut au 24 août 2026 : corrigé et couvert par sept scénarios de sécurité et de cycle de vie.** La route historique ambiguë `/shop/order/place` ne peut plus créer de commande. Les produits sont chargés exclusivement depuis la compagnie du slug, les prix proviennent du serveur et commande/lignes sont enregistrées atomiquement sans modifier le stock. La notification est placée en file après commit et cible uniquement les managers E-commerce actifs de la bonne compagnie avec un e-mail HTML utilisant le header/footer communs. La migration `2026_08_24_151000_require_company_on_ecommerce_orders.php` rend `orders.company_id` et `order_items.company_id` obligatoires. La conversion ultérieure en vente verrouille alors les produits et réutilise le moteur transactionnel du POS ; elle diminue le stock et alimente les caisses une seule fois. L’annulation conserve auteur/date/motif sans mouvement de stock. La migration `2026_08_24_152000_add_sale_conversion_to_orders.php` lie la vente de façon unique à la commande.

`FrontController::placeOrder()` continue après `getCompany()` même si aucune boutique active n’est trouvée. Sans contexte, les scopes tenant deviennent ouverts et la création peut produire une commande sans compagnie ou charger un produit global.

Emplacements : `app/Http/Controllers/Ecommerce/FrontController.php:120-190`.

Correction appliquée : refuser immédiatement la requête si la compagnie est absente, valider chaque produit dans la compagnie résolue, créer commande/lignes sans mouvement de stock, puis verrouiller et décrémenter uniquement lors de la conversion confirmée en vente.

## Risques P1 — durcissement du schéma et conservation de l’historique

### 5. Colonnes `company_id` initialement nullables — corrigé

Les tables métier auditées imposent maintenant `company_id NOT NULL`. Le durcissement a été réalisé par lots avec précontrôle avant modification.

Les 12 actions historiques sont conservées dans `legacy_tenant_records`. Le défaut est corrigé pour les nouvelles connexions : une connexion mono-entreprise écrit explicitement le tenant ; une connexion multi-entreprises attend la sélection vérifiée avant de journaliser.

Décision nécessaire : soit journaliser la connexion après sélection de la compagnie, soit créer un journal plateforme distinct. Ne pas rattacher arbitrairement ces actions à la première compagnie.

### 6. Trois rôles système historiques sans compagnie

Les rôles nuls ne sont plus utilisés par une adhésion ou une invitation, mais ont encore respectivement 11, 11 et 3 permissions attachées. Ils empêchent le passage immédiat de `roles.company_id` en `NOT NULL`.

Correction recommandée : confirmer qu’ils sont bien obsolètes, détacher leurs lignes `permission_role`, puis les supprimer dans une migration dédiée et réversible.

### 7. Règles de suppression dangereuses pour l’historique

- supprimer le créateur d’une compagnie peut supprimer la compagnie entière (`company_settings.created_by` en cascade) ;
- supprimer physiquement un client peut supprimer ses ventes (`sales.client_id` en cascade) ;
- supprimer un produit peut supprimer ses lignes de vente ou inventaires ;
- supprimer une catégorie peut supprimer ses produits ;
- la suppression d’une compagnie est désormais bloquée si elle possède des commandes ; les autres règles historiques ci-dessus restent à traiter ;
- supprimer un utilisateur efface ses actions d’audit.

Correction recommandée : `SET NULL` ou `RESTRICT` selon la relation, anonymisation plutôt que suppression des historiques financiers, et politique explicite de rétention d’une compagnie.

### 8. Slug et identifiant public de compagnie non garantis uniques

**Statut au 24 août 2026 : corrigé.** La migration réversible `2026_08_24_150000_enforce_unique_company_public_identifiers.php` normalise les anciennes valeurs, complète les valeurs absentes, résout d’éventuels doublons, rend `slug` et `public_id` obligatoires puis ajoute deux index uniques. Le modèle génère `matrix`, `matrix-2`, etc. et ne change pas le slug lors d’un renommage. La configuration E-commerce permet aussi de personnaliser le slug avec normalisation, vérification de disponibilité, mots réservés et confirmation obligatoire avant de casser l’ancien lien. Les tests vérifient les compagnies homonymes, la résolution précise, les collisions et la falsification de compagnie.

Les données actuelles n’ont aucun doublon, mais le schéma ne possède aucun index unique sur `company_settings.slug` ou `public_id`. Deux entreprises portant le même nom peuvent obtenir le même slug, rendant la route publique de boutique ambiguë.

Origine : `database/migrations/2026_08_18_100000_enhance_company_settings_to_companies.php:13-14`.

Correction recommandée : stratégie de slug unique lors de la création, backfill contrôlé, puis index uniques sur `slug` et `public_id`.

### 9. Invariants comptables uniquement applicatifs

La base n’impose pas une seule ligne `settings` par compagnie ni une seule caisse principale/de taxe. Les données actuelles sont cohérentes, mais une concurrence ou un futur endpoint pourrait créer des doublons.

Correction recommandée : index unique sur `settings.company_id`; pour les caisses, utiliser une stratégie compatible MySQL (colonnes générées/index uniques ou table de référence explicite) et conserver la validation transactionnelle.

### 10. Policies encore absentes sur les ressources centrales

Les routes ont des permissions et les modèles ont un scope tenant, mais seules les ressources Produit, Catégorie, Client et Fournisseur disposent de Policies. Ventes, Inventaires, Caisses, Transactions et Commandes restent protégées principalement par middleware + scope.

Correction recommandée : ajouter les Policies et tests IDOR pour les accès `show`, mises à jour, exports et changements de statut.

### 11. Le journal d’activité est effacé globalement chaque semaine

**Statut : corrigé sans suppression historique.** L’ancienne commande globale a été remplacée par une rétention paramétrable de 365 jours, appliquée explicitement compagnie par compagnie. Elle offre un mode `--pretend`, un filtre `--company` et conserve toujours les anciennes lignes sans tenant. Le planificateur empêche les exécutions simultanées.

Emplacements : `app/Console/Commands/CleanActions.php` et `app/Console/Kernel.php`.

Correction recommandée : suspendre cette purge avant le pilote, définir une durée de rétention explicite, archiver par compagnie et ne jamais effacer les événements de sécurité sans politique validée.

## Risques P2 — performance et industrialisation

- ajouter des index composés adaptés aux requêtes fréquentes : `(company_id, created_at)`, `(company_id, status)`, `(company_id, product_id)` et `(company_id, type)` selon les tables ;
- décider si `cash_accounts.code` et `orders.code` doivent rester uniques globalement ou seulement dans une compagnie ;
- remplacer progressivement les `CompanySetting::first()` par `CompanyContext::getCompany()` pour rendre l’intention explicite ; le scope actuel évite une fuite lorsque le contexte est résolu ;
- faire effacer le contexte dans un bloc `finally` pour les commandes/services parcourant plusieurs compagnies.

## Points positifs confirmés

- tous les modèles métier portant `company_id` utilisent `BelongsToCompany` ;
- les routes métier principales résolvent une compagnie et vérifient une permission ;
- les requêtes SQL brutes de statistiques inspectées filtrent explicitement `company_id` ;
- les jobs de vente/inventaire restaurent le contexte avant de charger les modèles ;
- le rapport hebdomadaire traite les compagnies séparément ;
- les validations Client/Fournisseur/Produit récemment ajoutées empêchent les relations croisées dans les principaux flux ;
- aucun mélange de données n’a été trouvé dans la base locale actuelle.

## Plan de correction proposé — étapes 1 à 3 du Lot A appliquées

### Lot A — corrections applicatives sans migration risquée

1. **Terminé :** sécuriser les managers E-commerce avec `CompanyContext` et un test IDOR.
2. **Terminé :** limiter les mises à jour Profil à l’utilisateur authentifié et exiger son mot de passe actuel.
3. **Terminé :** refuser une commande publique sans compagnie et transactionnaliser la commande avec verrouillage du stock.
4. Définir le traitement correct des actions de connexion sans compagnie.
5. Remplacer la purge globale des actions par une politique de rétention sûre.
6. Ajouter les tests IDOR correspondants.

### Lot B — nettoyage contrôlé après validation

1. **Terminé :** archivage réversible des 12 actions et 3 rôles historiques, sans attribution arbitraire.
2. Retirer les trois rôles historiques après vérification finale.
3. Vérifier à nouveau que tous les compteurs d’anomalies valent zéro.

### Lot C — migrations réversibles

1. **Partiellement terminé :** `slug` et `public_id` sont obligatoires et uniques ; rendre encore `settings.company_id` unique.
2. Rendre progressivement les `company_id` métier non nuls.
3. Ajouter les contraintes composites garantissant la même compagnie.
4. Corriger les règles de suppression historiques.
5. Ajouter les index composés de performance.

### Lot D — défense par ressource

1. Policies et tests IDOR Ventes/Inventaires.
2. Policies et tests IDOR Caisses/Transactions.
3. Policies et tests IDOR Commandes ; gestionnaires E-commerce déjà couverts.

## Reproduire l’audit

Le script `scripts/audit_tenant_isolation.php` n’exécute que des `SELECT` et peut être relancé avec :

```powershell
php scripts/audit_tenant_isolation.php
```

Avant chaque migration future, sauvegarder la base, relancer ce script et conserver le résultat daté.
