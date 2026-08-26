# Rapport de concurrence multi-utilisateurs SaaS

Date : 25 août 2026  
Environnement : PHP 8.2.24, Laravel 10 et MySQL local.  
Base : `pos_testing` exclusivement, avec refus automatique de toute base ne se terminant pas par `_testing`.

## Objectif

Vérifier avec de véritables processus PHP concurrents que les transactions et verrouillages MySQL empêchent :

- la vente d’un stock supérieur à la quantité disponible ;
- la création de plusieurs ventes depuis une même commande E-commerce ;
- la double mise à jour du stock ou de la caisse.

Commande reproductible :

```powershell
php artisan test benchmarks/SaasConcurrencyBenchmark.php
```

Ce benchmark est volontairement exclu de la suite quotidienne.

## Scénario 1 — ventes simultanées du même produit

- Stock initial : 10 unités.
- Processus caissiers lancés simultanément : 10.
- Quantité demandée par processus : 2 unités.
- Demande totale : 20 unités pour seulement 10 disponibles.

### Résultat

| Mesure | Résultat attendu | Résultat obtenu |
|---|---:|---:|
| Ventes acceptées | 5 | **5** |
| Refus pour stock insuffisant | 5 | **5** |
| Stock final | 0 | **0** |
| Ventes enregistrées | 5 | **5** |
| Lignes de vente enregistrées | 5 | **5** |
| Solde final de la caisse | 10 000 FCFA | **10 000 FCFA** |

Le passage final a terminé les dix traitements en **654 ms**. La durée individuelle allait de 156 à 473 ms, avec une moyenne de 378 ms.

Conclusion : le verrou `lockForUpdate()` appliqué au produit sérialise correctement les modifications concurrentes. Les ventes perdantes sont intégralement annulées par leur transaction ; elles ne laissent ni vente, ni ligne, ni mouvement de caisse partiel.

## Scénario 2 — conversion simultanée d’une commande

- Une commande E-commerce en attente.
- Stock initial du produit : 10 unités.
- Quantité commandée : 3 unités.
- Processus de conversion lancés simultanément : 8.

### Résultat

| Mesure | Résultat attendu | Résultat obtenu |
|---|---:|---:|
| Conversions acceptées | 1 | **1** |
| Tentatives en double refusées | 7 | **7** |
| Ventes créées | 1 | **1** |
| Stock final | 7 | **7** |
| Statut final | converted | **converted** |

Les huit traitements ont terminé en **638 ms**. Les durées individuelles allaient de 232 à 265 ms, avec une moyenne de 245 ms.

Conclusion : le verrou `lockForUpdate()` de la commande et le contrôle de `sale_id/status` garantissent l’idempotence. Une commande ne peut produire qu’une seule vente, même lorsque plusieurs opérateurs cliquent pratiquement au même moment.

## Validation

- Benchmark : **2 scénarios réussis, 33 assertions**.
- Suite quotidienne séparée actuelle : **127 tests réussis, 694 assertions**.
- Aucune survente détectée.
- Aucun stock négatif.
- Aucun doublon commande-vers-vente.
- Aucun écart de caisse.
- Aucun état partiel après rejet.

## Limites

- Le test utilise 10 processus locaux, pas encore plusieurs serveurs Web distincts.
- Il mesure MySQL local ; les temps O2switch pourront différer.
- Les fournisseurs externes e-mail, SMS et WhatsApp sont désactivés pendant ce benchmark.
- Le test ne remplace pas un futur essai HTTP complet avec plusieurs sessions navigateur sur une préproduction.

## Conclusion et avancement

Les deux risques de concurrence les plus critiques du POS sont correctement maîtrisés par les transactions existantes. La note **Performance et qualité locale** peut passer de **90 % à 92 %**.

Pour atteindre environ 95 %, il reste principalement à tester :

1. plusieurs sessions HTTP simultanées sur une préproduction ;
2. les exports volumineux ;
3. les files de notifications sous charge ;
4. des ventes réparties sur plusieurs mois ;
5. la charge avec des ressources équivalentes à l’hébergement réel.
