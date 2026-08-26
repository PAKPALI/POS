# Rapport de charge des notifications

Date : 25 août 2026  
Environnement : Laravel 10, PHP 8.2.24, queue MySQL et mailer de test local.  
Base : `pos_testing` exclusivement.

## Objectif

Valider la configuration envisagée pour le premier déploiement O2switch, sans Redis : une file basée sur MySQL, plusieurs workers et un registre empêchant les notifications en double.

Commande reproductible :

```powershell
php artisan test benchmarks/NotificationQueueBenchmark.php
```

## Scénario

- 50 ventes et 20 destinataires actifs par vente.
- 1 000 livraisons uniques attendues.
- Chaque job est volontairement ajouté deux fois, soit 100 jobs en file.
- 4 workers traitent simultanément la file `notifications`.
- Les fournisseurs externes sont remplacés par le mailer local de test.

## Défaut détecté pendant le premier passage

Le premier benchmark a trouvé **13 livraisons revendiquées deux fois**. La contrainte unique empêchait la création de deux lignes, mais deux workers pouvaient lire simultanément une même livraison encore `pending`, puis exécuter tous les deux l’expéditeur.

Cette anomalie n’apparaissait pas dans les tests séquentiels précédents. Elle a été révélée uniquement par le traitement multiprocessus.

## Correction

`NotificationDeliveryService` effectue maintenant une prise de possession transactionnelle :

- verrouillage de la ligne avec `lockForUpdate()` ;
- une livraison déjà `sent` est ignorée ;
- une livraison `processing` récemment revendiquée est ignorée ;
- un traitement abandonné depuis plus de dix minutes peut être repris ;
- le compteur de tentatives est incrémenté uniquement par le worker ayant obtenu la livraison.

Un test quotidien vérifie qu’un traitement actif ne peut pas être revendiqué et qu’un verrou ancien reste récupérable.

## Résultat après correction

| Mesure | Résultat |
|---|---:|
| Workers concurrents | 4 |
| Jobs mis en file, doublons inclus | 100 |
| Livraisons uniques attendues | 1 000 |
| Livraisons uniques terminées | **1 000** |
| Livraisons exécutées plusieurs fois | **0** |
| Jobs échoués | **0** |
| Jobs restant dans la file | **0** |
| Temps de mise en file | 331 ms |
| Temps de traitement | 9,29 s |
| Débit observé | **107,66 livraisons/s** |
| Pic mémoire du processus de contrôle | 38 Mo |

Le benchmark passe ses **11 assertions**. La suite quotidienne passe avec **127 tests et 694 assertions**.

## Interprétation et limites

La queue MySQL est suffisante pour un premier pilote sans Redis. Le débit réel des SMS, WhatsApp et e-mails sera toutefois limité par les fournisseurs externes, leurs quotas et la latence réseau. Les 108 livraisons par seconde mesurent le moteur interne avec un transport local.

Aucun appel réel SMTP, SMS ou WhatsApp n’a été effectué. Le comportement après arrêt brutal réel d’un worker devra être vérifié sur le serveur pilote. Les exports PDF volumineux restent la prochaine étape locale.

La note **Performance et qualité locale** passe de **92 % à 94 %**. La note **Préparation production** reste à **70 %**, faute de préproduction O2switch et de fournisseurs réels configurés.
