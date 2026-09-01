# Rapport global permanent — SaaS POS

Dernière mise à jour : 28 août 2026

## Rôle du document

Ce document remplace les anciens rapports d’avancement datés et les rapports techniques séparés de charge, concurrence et exports. Il doit être actualisé pour les évolutions générales du SaaS. La partie administration centrale conserve son registre dédié `RAPPORT_ADMINISTRATION_SAAS.md`.

## État général

- environnement staging validé par le propriétaire ;
- fonctionnement réel avec plusieurs entreprises et changement de contexte ;
- PWA mobile opérationnelle ;
- paiements KPrimePay réels, webhooks idempotents et absence de double crédit confirmée ;
- sauvegarde et restauration testées ;
- queues et tâches cron surveillées ;
- SPF, DKIM et DMARC validés ;
- dernière suite complète : **185 tests, 1 109 assertions, 0 échec** ;
- préparation technique estimée à **96–97 %** avant lancement commercial contrôlé.

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
- PWA Android/iOS, panier persistant et interfaces mobiles ;
- console centrale SaaS documentée séparément dans `RAPPORT_ADMINISTRATION_SAAS.md`.

## Validation SQL à gros volume

Benchmark reproductible :

```powershell
php artisan test benchmarks/SaasVolumeBenchmark.php
```

La commande refuse une base dont le nom ne se termine pas par `_testing`.

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
- les abonnements commerciaux peuvent être ajoutés ultérieurement sans empêcher le premier lancement.

## Documents complémentaires conservés

- déploiement : `DEPLOIEMENT_O2SWITCH.md` ;
- administration centrale : `RAPPORT_ADMINISTRATION_SAAS.md` ;
- KPrimePay : `GUIDE_KPRIMEPAY.md` ;
- reprise technique : `FREEBUFF_HANDOFF.md` ;
- audits de sécurité et d’isolation : fichiers `AUDIT_*.md` ;
- design system et conventions UI : `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md`.
