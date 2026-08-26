# Rapport complet d’avancement — migration SaaS du POS

Date de référence : 25 août 2026  
État vérifié : base locale, migrations, code applicatif, documentation et suite automatique.

## Synthèse exécutive

Le POS est désormais un socle SaaS multi-compagnies fonctionnel. Un même utilisateur peut appartenir à plusieurs entreprises, disposer d’un rôle différent dans chacune, changer de contexte et ne consulter que les données et fonctions autorisées dans la compagnie active.

| Axe | Avancement estimé | État |
|---|---:|---|
| Migration fonctionnelle SaaS | **96 %** | Socle opérationnel, héritage global des rôles neutralisé |
| Isolation et sécurité multi-tenant | **95 %** | Défense applicative et contraintes SQL en place |
| POS, caisse, stock et tableaux de bord | **95 %** | Fonctionnel et optimisé |
| E-commerce multi-boutiques | **94 %** | Commande, livraison, conversion et catalogue paginé opérationnels |
| Utilisateurs, rôles et permissions | **97 %** | Rôles déterminés exclusivement par l’adhésion active |
| Notifications | **92 %** | Canaux, destinataires, reprises et anti-doublon en place |
| Performance locale et qualité | **96 %** | Gros volumes, concurrence, queue, PDF et catalogues progressifs validés |
| Préparation production SaaS | **70 %** | Documentation prête, validation réelle O2switch reportée |
| Abonnements et facturation | **0 %** | Volontairement différé jusqu’à la définition des plans |

Référence automatique actuelle : **136 tests quotidiens réussis, 773 assertions, 0 échec**. Cinq scénarios lourds supplémentaires sont disponibles sur demande dans `benchmarks/`. Les **21 migrations SaaS locales** sont appliquées jusqu’à `2026_08_26_130000_create_quota_payments_and_permission.php`.

## 1. Multi-compagnies

- Un utilisateur global peut appartenir à plusieurs compagnies au moyen de `company_user`.
- Chaque adhésion possède son propre rôle, son statut et ses permissions locales.
- La compagnie active est résolue par `CompanyContext` et conservée en session.
- Le changement de compagnie vérifie l’adhésion, régénère la session et journalise l’action.
- Les pages et données affichées changent avec la compagnie active.
- Le sélecteur permet de revenir à l’application sans changer de compagnie ou de quitter sans choisir lorsqu’aucun contexte n’est actif.
- La liste et la modification des compagnies sont centralisées dans **Paramètres > Compagnie**.
- Une nouvelle compagnie crée automatiquement ses rôles initiaux, sa caisse principale, sa caisse de taxe et ses réglages. La taxe reste facultative.
- La création d’une compagnie supplémentaire demande confirmation avant toute bascule.

## 2. Inscription, connexion et invitations

- L’inscription SaaS crée atomiquement l’utilisateur propriétaire et sa première entreprise.
- Les pages de connexion et d’inscription utilisent l’interface de l’application et disposent de liens réciproques.
- La destination après connexion est choisie selon la première fonctionnalité autorisée ; aucun accès forcé au tableau de bord.
- Un utilisateur existant peut être rattaché à une autre compagnie sans perdre ses autres adhésions.
- L’action de clonage permet de choisir la compagnie cible et un rôle appartenant à celle-ci.
- Le parcours d’invitation couvre création, e-mail, acceptation, refus, renvoi, expiration à 48 heures et révocation.
- Les jetons sont aléatoires, stockés sous forme de hash et renouvelés au renvoi.
- Aucun rattachement n’est créé avant acceptation de l’invitation.
- Les confirmations serveur conservent leur fenêtre ouverte et affichent un loader jusqu’à la réponse.

## 3. Rôles, permissions et expérience 403

- Les rôles et permissions sont propres à chaque compagnie.
- Les grands modules sont présentés en français dans des accordéons.
- L’activation d’un module peut être effectuée depuis son en-tête.
- Les menus masquent les fonctionnalités non autorisées.
- Un accès direct interdit conserve l’interface de l’application et affiche une explication compréhensible, sans exposer uniquement une clé technique anglaise.
- Les bénéfices sont protégés côté serveur par `reports.view_margin`, y compris dans les réponses AJAX et PDF.
- La gestion des notifications dépend de `notifications.manage`.
- Le rôle propriétaire ne peut pas être attribué, supprimé ou rétrogradé arbitrairement.

## 4. Isolation et sécurité des données

- Les modèles métier principaux portent un `company_id` obligatoire et utilisent le scope de la compagnie active.
- Les routes métier utilisent la résolution de compagnie, l’obligation de sélection et les permissions de module.
- Des Policies protègent produits, catégories, clients, fournisseurs, ventes, inventaires, caisses et commandes.
- Les tentatives d’accès à une ressource d’une autre entreprise retournent `404` afin de ne pas révéler son existence.
- Les relations sensibles sont protégées par **16 clés étrangères composites** combinant identifiant métier et compagnie.
- Les rôles, adhésions, destinataires et managers E-commerce ne peuvent pas pointer vers une autre compagnie.
- Les anciennes lignes sans tenant ont été archivées dans `legacy_tenant_records`, sans rattachement artificiel.
- L’audit local indique : **2 compagnies, 0 relation inter-compagnies détectée, 0 `company_id` nul et 0 anomalie métier connue**.
- Le profil ignore tout identifiant utilisateur envoyé par le navigateur et modifie uniquement le compte connecté.

## 5. POS, ventes, stock et caisses

- La création des ventes est transactionnelle et refuse le stock insuffisant.
- Les quantités sont resynchronisées après fermeture du reçu.
- Le panier, le client, les remises et les commandes en attente sont conservés localement par compagnie après actualisation.
- Le catalogue POS et la recherche client sont paginés et chargés progressivement.
- L’animation produit vers panier fonctionne sur ordinateur et mobile depuis le point de clic.
- Une caisse ne peut jamais être simultanément principale et caisse de taxe.
- Les codes de caisse incluent la compagnie et restent uniques après changement de contexte.
- Les tableaux de bord affichent les statistiques de ventes, produits, catégories, clients et fournisseurs.
- Les coûts, marges et bénéfices ne sont retournés qu’aux rôles autorisés.

## 6. E-commerce

- Chaque boutique possède une adresse publique précise : `/boutique/{slug}`.
- Deux entreprises portant le même nom reçoivent des slugs distincts.
- Le propriétaire peut personnaliser le slug avec normalisation, contrôle de disponibilité et confirmation.
- Le panier public est séparé par entreprise.
- Les prix et produits sont toujours rechargés côté serveur dans la compagnie du slug.
- Une commande ne diminue pas immédiatement le stock.
- Une commande confirmée peut être convertie en vente en réutilisant le moteur du POS ; le stock et les caisses sont alors mis à jour atomiquement.
- Une commande non confirmée peut être annulée avec motif, auteur et date.
- Le client peut transmettre un lien Google Maps ou sa position GPS, validée puis reconstruite côté serveur.
- Les managers E-commerce actifs reçoivent l’e-mail stylisé de commande avec le lien de localisation.

## 7. Notifications et files d’attente

- Les notifications ventes et inventaire disposent d’activations globales séparées pour e-mail, WhatsApp et SMS.
- Les destinataires sont configurés par compagnie, catégorie et canal.
- L’adhésion active et les autorisations sont revérifiées au moment réel de l’envoi.
- Le nom de l’entreprise est conservé dans les messages.
- Les commandes E-commerce continuent de cibler leurs managers dédiés.
- Les jobs ont un nombre de tentatives, des délais et des timeouts bornés.
- `notification_deliveries` rend les livraisons idempotentes : une notification déjà envoyée n’est pas renvoyée après reprise du job.
- Les échecs restent relançables et les anciens registres peuvent être purgés avec prévisualisation.
- Les notifications sont déclenchées après validation de la transaction avec `afterCommit()`.

## 8. Performance SQL

- Les DataTables volumineuses paginent et filtrent côté SQL.
- Le POS ne charge plus tous les produits ni tous les clients au premier rendu.
- Les principales agrégations de ventes et transactions sont calculées directement en SQL sans charger chaque ligne.
- Les relations utilisées dans les tableaux sont chargées en lot pour éviter les requêtes N+1.
- La page des caisses utilise exactement deux requêtes pour tous ses résumés, indépendamment du nombre de caisses.
- Des index tenantés ciblent ventes, produits, inventaires, commandes, transactions, clients, fournisseurs, catégories, codes promotionnels, caisses et journaux.
- Le moniteur de requêtes lentes peut écrire les requêtes dépassant le seuil dans un journal séparé, sans enregistrer leurs valeurs sensibles.
- Les tests automatiques plafonnent le nombre de requêtes sur plusieurs parcours critiques.

## 9. PWA et interface

- Le manifeste, le service worker et les icônes permettent l’installation de la PWA.
- Sur iPhone, l’installation se fait via **Partager > Sur l’écran d’accueil** et crée une icône avec le logo.
- Les parcours mobiles majeurs ont été adaptés, notamment invitation, sélection de compagnie et POS.
- La convention globale impose un loader dans tout bouton attendant une réponse serveur et bloque les doubles clics.

## 10. Ce qui reste avant un pilote réel

### Priorité immédiate locale

1. Effectuer un scénario de charge plus réaliste avec plusieurs compagnies, utilisateurs simultanés et volumes importants.
2. Continuer l’observation des requêtes lentes et n’ajouter un index qu’après vérification avec `EXPLAIN`.
3. Poursuivre la vérification des derniers sélecteurs ou vitrines susceptibles de charger des collections complètes ; les sélecteurs Inventaire sont maintenant paginés.
4. Préparer un test fonctionnel manuel complet : inscription, seconde compagnie, invitation, rôles, vente, inventaire, commande E-commerce, notifications et changement de contexte.

### Lorsque le déploiement O2switch sera autorisé

1. Valider domaine, version PHP, extensions, chemin public, MySQL et tâches cron.
2. Configurer les secrets réels hors Git : base, SMTP, WhatsApp et SMS.
3. Utiliser une queue basée sur la base de données et un cron Laravel ; Redis reste facultatif et reporté.
4. Configurer HTTPS, permissions de fichiers, lien de stockage et caches Laravel.
5. Tester réellement les workers, e-mails, SMS, WhatsApp, PWA et tâches planifiées.
6. Mettre en place puis tester sauvegarde et restauration avant le pilote.
7. Activer la supervision des erreurs, de la queue et des requêtes lentes.
8. Lancer un pilote limité avec au moins deux compagnies réelles avant ouverture générale.

## 11. Abonnements futurs

Les abonnements ne doivent pas encore être confondus avec les permissions utilisateur. La prochaine architecture commerciale devra séparer :

- ce que le plan de la compagnie autorise ;
- ce que le rôle d’un utilisateur autorise dans cette compagnie ;
- les quotas consommés ;
- les périodes d’essai, renouvellements, impayés et webhooks.

Avant d’afficher des prix, créer un service central de capacités (`EntitlementService`) ainsi que les structures `plans`, `subscriptions`, `plan_features` et `usage_counters`. Les tarifs et quotas resteront à définir avec le propriétaire.

## Conclusion

Le projet n’est plus au stade d’une simple adaptation visuelle : le cœur multi-compagnies, l’isolation, les permissions, les invitations, le POS, l’E-commerce, les notifications et la performance SQL sont effectivement implantés et testés. Le principal travail restant concerne l’industrialisation en environnement réel, les essais de charge, la suppression des derniers héritages mono-entreprise et, plus tard, la couche d’abonnement.

Le benchmark reproductible à gros volume est documenté dans `docs/RAPPORT_TEST_VOLUME_SAAS_2026-08-25.md`. Les tests transactionnels simultanés sont documentés dans `docs/RAPPORT_CONCURRENCE_SAAS_2026-08-25.md`, la queue multiprocessus dans `docs/RAPPORT_CHARGE_NOTIFICATIONS_2026-08-25.md` et les limites DomPDF dans `docs/RAPPORT_EXPORTS_PDF_VOLUME_2026-08-26.md`. Ensemble, ils portent la note de performance locale à 95 %.

Les exports exhaustifs Produits, Inventaire et Ventes sont maintenant disponibles en CSV et Excel générés en flux. Détails : `docs/RAPPORT_EXPORTS_CSV_EXCEL_2026-08-26.md`.

Le déploiement O2switch demeure **non autorisé tant que le propriétaire ne donne pas un signal explicite**.
