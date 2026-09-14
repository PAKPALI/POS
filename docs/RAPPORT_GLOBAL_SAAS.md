# Rapport global permanent — SaaS POS

Dernière mise à jour : 14 septembre 2026 — clôture du développement fonctionnel et validation staging confirmée.

## Rôle du document

Ce document remplace les anciens rapports d’avancement datés et les rapports techniques séparés de charge, concurrence et exports. Il doit être actualisé pour les évolutions générales du SaaS. La partie administration centrale conserve son registre dédié `RAPPORT_ADMINISTRATION_SAAS.md`.

## État général

- environnement staging validé par le propriétaire ;
- fonctionnement réel avec plusieurs entreprises et changement de contexte ;
- PWA mobile opérationnelle et identité harmonisée sous **MAXANOU** (manifeste, écran hors connexion et messages d’installation) ;
- paiements KPrimePay réels, webhooks idempotents et absence de double crédit confirmée ;
- sauvegarde et restauration testées ;
- queues et tâches cron surveillées ;
- SPF, DKIM et DMARC validés ;
- dernière suite complète documentée : **356 tests, 2 034 assertions, 0 échec** ;
- développement fonctionnel et validation staging : **terminés** ;
- reste uniquement le déploiement production, la configuration des secrets/URLs et l’activation progressive des contrôles.

## Fonctions SaaS consolidées

- inscription avec création de l’utilisateur, de sa première entreprise, des caisses principales et fiscales, et de la taxe facultative ;
- utilisateur rattachable à plusieurs entreprises avec rôle propre à chacune ;
- invitations sécurisées, révocables, expirantes et utilisables par les nouveaux utilisateurs ;
- changement d’entreprise avec isolation du contexte, des données et des permissions ;
- gestion des rôles et permissions par entreprise ;
- isolation multi-tenant renforcée dans les contrôleurs, policies, scopes et contraintes SQL ;
- POS, ventes, caisse, stock, inventaires, clients et fournisseurs ;
- boutique e-commerce publique avec slug unique, recherche progressive et cycle commande-vers-vente ;
- notifications e-mail, SMS et WhatsApp configurables par entreprise, catégorie et destinataire ;
- quotas SMS/WhatsApp payés par KPrimePay ;
- abonnements commerciaux fonctionnels : essai, plans tarifés, durée flexible, montée de plan, limites et fonctionnalités contrôlées côté serveur ;
- paiements d’abonnement distincts des paiements de quotas, avec webhooks KPrimePay idempotents et notifications e-mail administratives ;
- réglage d’enforcement désactivé par défaut pour le développement local et activable progressivement en production ;
- exceptions d’enforcement par entreprise, avec héritage du réglage global, activation/désactivation ciblée et audit plateforme ;
- PWA Android/iOS, panier persistant et interfaces mobiles ;
- console centrale SaaS documentée séparément dans `RAPPORT_ADMINISTRATION_SAAS.md`.

## Mise à jour du 11 septembre 2026 — retraits et cohérence PWA

- Le programme partenaires inclut le retrait sécurisé vers Mobile Money : compte vérifié par code e-mail, protections contre les renvois abusifs, états de demande et contrôles d’éligibilité côté serveur. Les détails d’exploitation et de paiement sont maintenus dans `GUIDE_KPRIMEPAY.md`.
- La console plateforme propose le suivi des encaissements confirmés, des engagements partenaires et des sorties administrateur. Les numéros Mobile Money administrateur sont chiffrés, masqués et uniques par administrateur ; le rôle Finance est en lecture seule. Le suivi détaillé figure dans `RAPPORT_ADMINISTRATION_SAAS.md`.
- La PWA est cohérente avec la marque MAXANOU sur les shells publics, partenaire et plateforme. Le cache courant est `maxanou-pwa-v8` et purge les caches historiques ; les pages authentifiées et leurs données restent exclues du cache applicatif.
- La validation d’installation réelle demeure une étape de recette HTTPS sur le domaine de déploiement, avec désinstallation/réinstallation d’un ancien raccourci lorsque le manifeste est mis à jour.

## Validation SQL à gros volume

Benchmark reproductible :

```powershell
php artisan test benchmarks/SaasVolumeBenchmark.php
```

La commande refuse une base dont le nom ne se termine pas par `_testing`.

Le benchmark accepte aussi des volumes intermédiaires via `PERF_PRODUCTS`, `PERF_CLIENTS`, `PERF_SALES`, `PERF_SALE_DETAILS` et `PERF_ORDERS`, sans modifier ses seuils ni son scénario maximal par défaut. Cela permet une recette locale reproductible avant de lancer le volume maximal dans un terminal persistant.

Volume : 5 entreprises, 50 utilisateurs, 10 000 produits, 5 000 clients, 50 000 ventes, 100 000 lignes de vente et 10 000 commandes. Génération en 15 à 18 secondes, pic mémoire de 96 Mo.

| Parcours | Temps final | Requêtes |
|---|---:|---:|
| Tableau de bord | 781 ms | 13 |
| Ouverture du POS | 773 ms | 14 |
| Recherche produits | 94 ms | 5 |
| Recherche clients | 17 ms | 5 |
| Liste utilisateurs | 17 ms | 5 |
| Liste commandes | 14 ms | 5 |
| Historique des ventes | 696 ms | 10 |

Toutes les routes sont restées sous 2,5 secondes et 40 requêtes. Les filtres temporels utilisent des plages indexables et l’index `sale_details(company_id, created_at, product_id)` protège les agrégations principales.

### Exécution locale contrôlée — 7 septembre 2026

Base isolée utilisée : `pos_testing`.

| Scénario | Résultat |
|---|---|
| Volume intermédiaire : 2 500 produits, 2 000 clients, 12 000 ventes, 24 000 lignes, 3 000 commandes | Succès, 22 assertions, génération en 3,98 s, pic de 60 Mo. Dashboard 193 ms, POS 176 ms, recherche produits 28 ms, clients 11 ms, utilisateurs 22 ms, commandes 25 ms, historique 262 ms. |
| Concurrence stock : 10 caissiers, stock initial 10 | Succès : 5 ventes, 5 refus corrects, stock final 0, caisse 10 000, durée 627 ms. |
| Concurrence e-commerce : 8 conversions simultanées d’une même commande | Succès : 1 seule conversion, 7 refus, stock final 7, durée 424 ms. |
| Queue notifications : 4 workers, 100 jobs contenant 50 doublons volontaires | Succès : 1 000 livraisons uniques, 0 doublon, 0 échec, 12,67 s, 78,93 livraisons/s, 40 Mo. |

Le volume maximal (10 000 produits, 50 000 ventes et 100 000 lignes) reste le scénario de référence. Son exécution complète doit être lancée depuis un terminal local persistant ou staging disposant d’une fenêtre supérieure à celle de l’automatisation interactive.

## Validation de la concurrence

Benchmark :

```powershell
php artisan test benchmarks/SaasConcurrencyBenchmark.php
```

- 10 ventes simultanées demandant 20 unités sur un stock de 10 : 5 acceptées, 5 refusées, stock final 0 et caisse exacte ;
- 8 conversions simultanées d’une même commande : une seule vente créée, 7 refus, stock final correct ;
- aucune survente, aucun stock négatif, aucun doublon et aucun état partiel.

Les transactions et `lockForUpdate()` protègent les ventes et les conversions e-commerce.

## Charge des notifications

Benchmark :

```powershell
php artisan test benchmarks/NotificationQueueBenchmark.php
```

Scénario : 50 ventes, 20 destinataires par vente, 1 000 livraisons uniques et 4 workers MySQL. Une première exécution avait révélé 13 revendications doubles. `NotificationDeliveryService` utilise désormais un verrou transactionnel, un état `processing` et une reprise après dix minutes.

Résultat corrigé : 1 000 livraisons uniques, aucun doublon, aucun échec, file vide, traitement en 9,29 secondes, environ 107,66 livraisons locales par seconde et 38 Mo de mémoire.

## Exports PDF

DomPDF épuisait initialement 512 Mo sur plusieurs milliers de lignes. Les plafonds appliqués avant chargement sont :

- 300 produits ;
- 500 mouvements d’inventaire ;
- 100 ventes.

Ils sont configurables avec `PDF_PRODUCTS_MAX_ROWS`, `PDF_INVENTORIES_MAX_ROWS` et `PDF_SALES_MAX_ROWS`. Les benchmarks filtrés ont produit les documents en 1,34 à 3,16 secondes avec un pic total de 162 Mo. Les gros volumes doivent utiliser CSV ou Excel.

## Exports CSV et Excel

- Produits, Inventaire et Historique des ventes disponibles en CSV et véritable XLSX ;
- CSV UTF-8 avec BOM et séparateur `;` ;
- Laravel Excel 3.1 et extension PHP `zip` pour XLSX ;
- lecture progressive et conservation des filtres actifs ;
- respect de la compagnie, des policies et de `reports.view_margin` ;
- neutralisation des valeurs commençant par `=`, `+`, `-` ou `@` ;
- loaders, gestion des erreurs et mise en page responsive.

## Risques résiduels et lancement

- les temps O2switch peuvent différer des benchmarks locaux ;
- une surveillance externe reste nécessaire pour détecter l’arrêt total du cron ;
- les agrégations du tableau de bord et de l’historique doivent rester surveillées avec la croissance réelle ;
- le lancement recommandé reste progressif avec quelques entreprises pilotes ;
- la validation staging des abonnements, webhooks, SMTP, workers, cron, sauvegardes, logs, alertes et recette visuelle est acquise ; la production nécessite encore les secrets/URLs propres à l’environnement, la migration, la supervision et une activation progressive de l’enforcement.

## Statut de bascule production — 14 septembre 2026

Le périmètre de développement est clôturé. La mise en production ne doit pas ouvrir un nouveau chantier fonctionnel : elle consiste à sauvegarder la base, déployer la version validée, injecter les secrets et URLs de production, exécuter les migrations et caches, démarrer les workers, configurer le cron, effectuer les smoke tests puis activer progressivement les réglages sensibles.

## Documents complémentaires conservés

- déploiement : `DEPLOIEMENT_O2SWITCH.md` ;
- administration centrale : `RAPPORT_ADMINISTRATION_SAAS.md` ;
- KPrimePay : `GUIDE_KPRIMEPAY.md` ;
- reprise technique : `FREEBUFF_HANDOFF.md` ;
- audits de sécurité et d’isolation : fichiers `AUDIT_*.md` ;
- design system et conventions UI : `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md`.
