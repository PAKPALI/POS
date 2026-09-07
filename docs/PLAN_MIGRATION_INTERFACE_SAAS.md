# Migration des interfaces vers le template SaaS

## Objectif

Harmoniser l’application sans modifier les règles métier, les permissions, les routes, les paiements ou les formats d’export. Chaque écran doit réutiliser les composants `x-ui`, les jetons du design system et les garde-fous de `php artisan ui:lint`.

## Périmètre et ordre de migration

| Lot | Zone | État | Règle de sécurité |
| --- | --- | --- | --- |
| 1 | Connexion, inscription, récupération et vérification d’e-mail | Terminé | Conserver les noms de champs, routes et retours de validation. |
| 2 | Formulaires utilisateur et entreprises, y compris les modales AJAX | À faire | Conserver les identifiants DOM consommés par JavaScript. |
| 3 | Administration de plateforme | Audit en cours | Ne pas réécrire les DataTables, permissions ou actions d’administration sans test visuel et fonctionnel. |
| 4 | Back-office e-commerce et gestion POS | À faire | Isoler les changements de présentation des calculs, stocks et ventes. |
| 5 | POS plein écran, ticket et historique | À faire | Tester chaque action de vente avec le loader anti-double-clic. |
| 6 | Boutique e-commerce publique | À faire séparément | Elle a son propre contrat visuel responsive ; ne pas lui imposer le shell interne. |

Les e-mails, PDF, impressions et pages de maintenance restent hors shell SaaS : leur rendu est volontairement autonome.

## Validation d’exploitation — déclarée terminée

Le propriétaire confirme que les points opérationnels suivants ont été réalisés sur staging : recette visuelle complète des écrans secondaires, AJAX et mobile ; checkout réel contrôlé d’un abonnement KPrimePay avec retour webhook ; vérification du SMTP réel ; contrôle du cron et des queues ; vérification des sauvegardes, logs et alertes d’hébergement. Ces éléments ne sont donc plus des tâches bloquantes du développement.

La mise en production reste une étape de déploiement : injecter les secrets de production, vérifier les URL de webhook de production et activer progressivement le contrôle d’abonnement après sauvegarde.

## Lot 1 — état actuel

Les vues Laravel historiques de connexion, confirmation, réinitialisation et vérification utilisent désormais `layouts.public-auth` et les composants `x-ui`. Les inscriptions SaaS utilisent les groupes de champs, les boutons avec loader et les contrôles de mot de passe accessibles du template. Les routes, noms de champs, requêtes AJAX et données ont été conservés.

## Critères d’acceptation communs

- Mobile : aucun débordement horizontal, boutons accessibles et champs lisibles.
- Desktop : cartes, titres, tableaux, actions et modales respectent les espacements du guide SaaS.
- Toute action serveur affiche son état de chargement et empêche le double clic.
- Les alertes de validation restent explicites et accessibles.
- Après chaque lot : `php artisan ui:lint`, cache des vues, tests ciblés, puis contrôle visuel desktop et mobile.

## Commandes de vérification

```powershell
php artisan ui:lint
php artisan view:cache
php artisan test --filter=UiTemplate
```

Sur staging, ces commandes sont sans écriture métier. Elles peuvent être exécutées après le déploiement ; le terminal peut ensuite se fermer normalement.

## Audit approfondi — 7 septembre 2026

### Cartographie vérifiée

- 174 vues Blade et 50 contrôleurs ont été recensés ; les routes applicatives se répartissent notamment entre catalogue (54), plateforme (48), comptabilité (18), POS (15), e-commerce administration (11) et e-commerce public (6).
- 26 vues internes étendent le shell `layouts.saas`, 19 la console `layouts.platform`, 12 le shell public d’authentification et 4 le shell d’authentification plateforme.
- Les fichiers sans layout ne sont pas tous des écrans oubliés : la majorité sont des modales chargées en AJAX, des composants, documents PDF, tickets, e-mails ou storefront public. Ils doivent conserver leur contrat de rendu propre.

### Résultats de l’audit de la console plateforme

- La console est déjà construite sur `layouts.platform` et `platform-components.css`, qui normalisent formulaires, tableaux, pagination, modales, boutons, états et responsive. Elle respecte donc le template SaaS même lorsqu’elle emploie des classes Bootstrap comme structure sémantique.
- Les actions sensibles inspectées (relance de job et réconciliation KPrimePay) demandent un motif, bloquent la modal pendant l’appel, affichent le chargement et ne signalent le succès qu’après la réponse du serveur.
- La table des utilisateurs plateforme a été ajustée : les trois styles en ligne ont été remplacés par des classes centralisées (`platform-user-memberships`, `platform-membership-link`, `platform-status-chip-stacked`).

### Zones à traiter ensuite

1. Les formulaires/modales AJAX des entreprises, catalogue, utilisateurs, rôles et comptabilité : conserver les sélecteurs JavaScript et basculer progressivement vers les primitives `x-ui`.
2. POS et e-commerce administration : recette par action car leurs vues portent des calculs, stocks, paiement et impression.
3. Boutique publique, e-mails, PDF et reçus : audit visuel dédié, hors shell SaaS interne.

### Points contrôlés mais non considérés comme défauts

- Les `fetch` des graphiques de tableau de bord ne sont pas des actions utilisateur écrivant des données.
- Les appels de paiement, supervision et abonnement utilisent SweetAlert avec `showLoaderOnConfirm` ; ils sont conformes au garde-fou anti-double-clic.
