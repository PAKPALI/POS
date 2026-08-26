# Rapport de simulation SaaS à gros volume

Date : 25 août 2026  
Environnement : Laravel 10, PHP 8.2.24, MySQL local, mémoire PHP limitée à 512 Mo.  
Base utilisée : **`pos_testing` exclusivement**. La base locale de développement et ses données métier n’ont pas été utilisées.

## Objectif

Mesurer le comportement des principaux écrans lorsque la base contient un volume nettement supérieur au volume de développement courant, sans modifier les données réelles.

Le scénario est reproductible avec :

```powershell
php artisan test benchmarks/SaasVolumeBenchmark.php
```

Le benchmark refuse de s’exécuter si le nom de la base ne se termine pas par `_testing`. Il est volontairement placé hors des suites Unit et Feature habituelles afin de ne pas ralentir les tests quotidiens.

## Volume généré

| Donnée | Volume |
|---|---:|
| Compagnies | 5 |
| Utilisateurs dans la compagnie mesurée | 50 |
| Produits | 10 000 |
| Clients | 5 000 |
| Ventes | 50 000 |
| Lignes de vente | 100 000 |
| Commandes E-commerce | 10 000 |

La génération complète a pris entre **15 et 18 secondes** selon le passage. Le pic mémoire PHP observé est de **96 Mo**, soit environ 19 % de la limite disponible de 512 Mo.

## Résultats du passage diagnostique final

| Parcours | Temps total | Temps SQL | Requêtes | Verdict |
|---|---:|---:|---:|---|
| Tableau de bord général | 781 ms | 740 ms | 13 | Acceptable, agrégations encore coûteuses |
| Ouverture initiale du POS | 773 ms | 758 ms | 14 | Acceptable |
| Recherche produits | 94 ms | 78 ms | 5 | Très bon |
| Recherche clients | 17 ms | 14 ms | 5 | Excellent |
| Liste utilisateurs | 17 ms | 7 ms | 5 | Excellent |
| Liste commandes | 14 ms | 7 ms | 5 | Excellent |
| Historique des ventes | 696 ms | 687 ms | 10 | Acceptable, agrégations encore coûteuses |

Toutes les routes sont restées sous la limite automatique de **2,5 secondes** et sous **40 requêtes**. Le benchmark a réussi ses **22 assertions**.

Les temps varient entre les passages à cause du cache MySQL, du cache système et de la compilation des vues. Un premier passage à froid a atteint environ 1,2 à 1,4 seconde sur les trois écrans d’agrégation, sans dépasser la limite. Les recherches et listes sont restées rapides.

## Optimisations appliquées après le premier diagnostic

- Les filtres `DATE(created_at)` du POS ont été remplacés par des plages `created_at BETWEEN début AND fin`. MySQL peut ainsi exploiter un index temporel.
- Le bénéfice des ventes est maintenant calculé dans l’agrégation déjà utilisée pour le total, supprimant une requête supplémentaire.
- Le même regroupement a été appliqué à l’historique des ventes.
- L’index composite `sale_details(company_id, created_at, product_id)` a été ajouté par la migration `2026_08_25_140000_add_sale_detail_period_performance_index.php`.
- Le nombre de requêtes du POS est passé de **15 à 14** et celui de l’historique de **11 à 10**.

Le jeu de test concentre volontairement toutes les ventes sur une même journée. L’index temporel offre donc peu de sélectivité dans ce cas extrême. Son bénéfice sera plus visible lorsque les données seront réparties sur plusieurs mois et qu’un utilisateur filtrera une journée ou une période courte.

## Requêtes les plus coûteuses

Les principaux coûts restants viennent de :

1. l’agrégation des quantités par produit dans `sale_details` ;
2. le comptage des lignes vendues sur une période ;
3. les sommes de chiffre d’affaires et de bénéfice dans `sales` ;
4. le comptage nécessaire à la pagination des produits les plus vendus du tableau de bord.

Ces requêtes traitent volontairement 50 000 à 100 000 lignes. Elles restent sous une seconde localement mais devront être surveillées avec les volumes et ressources du serveur réel.

## Contrôle de non-régression

Après l’optimisation et l’application de la migration locale :

- benchmark de volume : **1 scénario réussi, 22 assertions** ;
- suite quotidienne actuelle : **127 tests réussis, 694 assertions** ;
- aucune donnée de la base locale métier n’a été modifiée par le benchmark.

## Limites de cette simulation

- Le test mesure des requêtes séquentielles, pas encore plusieurs utilisateurs simultanés.
- La machine locale n’a pas les mêmes processeur, disque, mémoire et limites que l’hébergement O2switch.
- Les appels réels SMTP, SMS et WhatsApp ne font pas partie de ce test SQL.
- Les 100 000 lignes de vente sont concentrées sur une seule date, ce qui représente un cas volontairement défavorable pour les filtres temporels.
- Le benchmark mesure le serveur Laravel et MySQL, pas le temps réseau d’un téléphone mobile.

## Conclusion et progression

Le système supporte correctement ce premier volume de référence : aucune explosion du nombre de requêtes, aucune erreur mémoire, aucune fuite inter-compagnies observée et aucune route mesurée au-dessus du seuil fixé.

La note **Performance et qualité locale** peut raisonnablement passer de **88 % à 90 %**. Elle ne monte pas encore à 95 %, car il reste à réaliser :

1. un test de concurrence avec plusieurs utilisateurs simultanés ;
2. une répartition des ventes sur plusieurs mois ;
3. des mesures sur exports PDF et tâches de notification volumineuses ;
4. un test sur une préproduction possédant des ressources proches d’O2switch ;
5. une éventuelle stratégie de tables de synthèse si les agrégations dépassent régulièrement une seconde en production.

La note **Préparation production** reste à **70 %**, car aucun serveur réel, worker, cron, SMTP, sauvegarde/restauration ou supervision O2switch n’a été validé. Ce benchmark améliore la confiance locale mais ne remplace pas une préproduction.
