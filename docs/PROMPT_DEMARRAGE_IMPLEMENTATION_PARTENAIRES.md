# Prompt de démarrage — Implémentation de la plateforme Partenaires

Copier intégralement le texte ci-dessous dans une nouvelle discussion Codex ouverte sur le projet local `C:\POS`.

---

Travaille dans le projet local `C:\POS`.

Tu dois commencer l’implémentation de la plateforme Partenaires Maxanou décrite dans le cahier d’architecture du projet. Cette fonctionnalité touche à l’argent : avance avec prudence, ne simplifie aucune règle financière et ne réalise aucun paiement ou retrait réel sans mon autorisation explicite.

## 1. Lecture obligatoire avant toute modification

Avant d’écrire du code, lis entièrement, dans cet ordre :

1. `C:\POS\AGENTS.md` ;
2. `C:\POS\docs\FREEBUFF_HANDOFF.md` ;
3. `C:\POS\docs\CAHIER_ARCHITECTURE_PLATEFORME_PARTENAIRES.md` ;
4. `C:\POS\docs\CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` ;
5. `C:\POS\docs\GUIDE_KPRIMEPAY.md` ;
6. `C:\POS\docs\README.md` ;
7. les fichiers de code, migrations, routes, modèles, contrôleurs, services et tests liés aux abonnements, quotas, KPrimePay, administration plateforme, authentification, 2FA, multi-tenant et design system.

Inspecte aussi l’état Git et préserve tous les changements existants qui ne t’appartiennent pas. Ne supprime, n’écrase et ne réinitialise aucun travail utilisateur.

Après cette lecture, résume brièvement :

- ta compréhension de l’architecture actuelle ;
- les points d’intégration identifiés ;
- les risques du premier lot ;
- la première fonctionnalité précise que tu proposes de développer.

## 2. Architecture à respecter

La plateforme Partenaires reste dans la même application Laravel et la même base MySQL que Maxanou, sous forme de monolithe modulaire.

- Le portail partenaire utilise des routes, un garde, une session, des middlewares, des modèles, des permissions et des vues séparés.
- Le futur sous-domaine partenaire pointe vers la même application.
- N’ajoute pas d’API HTTP interne inutile entre le POS et le module partenaire.
- Utilise des services métier, événements et jobs internes.
- L’attribution se fait au niveau de `subscription_account`, jamais au niveau d’une entreprise isolée.
- Les encaissements KPrimePay et les payouts partenaires utilisent des services, secrets, scopes et clés d’idempotence distincts.
- Ne mélange pas le token payout avec `KprimePayService` utilisé pour les encaissements.

## 3. Règles financières impératives

- Tous les montants XOF sont des entiers ; aucun flottant.
- Le serveur recalcule toujours prix, durée, réduction, total et commission.
- Sans code partenaire valide : aucune réduction, attribution ou commission.
- Avec un code valide, le client reçoit 10 % de réduction uniquement sur son premier abonnement payant.
- Saisir un code ou créer un checkout ne lie pas le client.
- Le client est attribué définitivement au partenaire uniquement après confirmation serveur du premier paiement.
- Une attribution confirmée ne peut pas être remplacée par un autre code.
- Les renouvellements ne donnent plus de réduction au client, mais continuent de produire une commission partenaire.
- Le taux acquis par un client lors de son attribution est conservé pour ses renouvellements.
- Chaque paiement ne peut créer qu’une commission positive : contraintes uniques, verrouillage et idempotence obligatoires.
- Toute correction se fait par une écriture compensatrice dans un grand livre immuable ; ne supprime ou ne modifie jamais silencieusement une écriture réglée.
- Une panne SMTP ne doit jamais annuler un paiement, un abonnement, une commission ou un retrait confirmé.
- Un retrait KPrimePay dont le résultat est inconnu reste réservé jusqu’à réconciliation.

La grille de commission s’arrête à 25 % :

- rangs 1–5 : 10 % ;
- 6–10 : 11 % ;
- 11–15 : 12 % ;
- 16–20 : 13 % ;
- 21–25 : 14 % ;
- 26–35 : 15 % ;
- 36–45 : 16 % ;
- 46–55 : 17 % ;
- 56–65 : 18 % ;
- 66–75 : 19 % ;
- 76–95 : 20 % ;
- 96–115 : 21 % ;
- 116–135 : 22 % ;
- 136–155 : 23 % ;
- 156–175 : 24 % ;
- rang 176 et suivants : 25 %, plafond définitif.

Ainsi, il faut 5 nouveaux clients par point jusqu’à 15 %, 10 clients par point jusqu’à 20 %, puis 20 clients par point jusqu’à 25 %.

## 4. UI/UX obligatoire

Le portail Partenaires doit utiliser concrètement le template SaaS Maxanou.

- Construis `layouts.partner` à partir du shell, des tokens, thèmes et assets partagés de `layouts.saas`.
- Ne crée pas de thème autonome, de feuille CSS complète parallèle ou de copie du template.
- Réutilise les composants `x-ui.*` existants pour boutons, formulaires, badges, statuts, cartes, modales, tableaux, filtres, exports, états vides, skeletons et accès refusés.
- Les tableaux doivent suivre `x-ui.table-shell` et la convention DataTable SaaS.
- Les interfaces doivent fonctionner en clair, sombre et système, avec la couleur dominante personnelle.
- Assure le responsive à 1440, 1024, 768, 390 et au minimum 320 px, sans débordement horizontal.
- Respecte clavier, focus, contraste, zones tactiles, annonces accessibles et `prefers-reduced-motion`.
- Toute action serveur affiche un loader et bloque les doubles clics avec `window.ServerButtonLoader`.
- Toute confirmation SweetAlert utilise `showLoaderOnConfirm`, la requête dans `preConfirm` et bloque la fermeture pendant `Swal.isLoading()`.
- N’affiche jamais un succès avant la réponse positive du serveur.

## 5. Méthode de travail obligatoire : une fonctionnalité à la fois

Tu ne dois pas développer plusieurs phases ou plusieurs grosses fonctionnalités sans validation intermédiaire.

Pour chaque fonctionnalité :

1. annoncer précisément le périmètre du lot et les fichiers susceptibles d’être touchés ;
2. vérifier les risques, dépendances et invariants ;
3. implémenter uniquement cette fonctionnalité ;
4. écrire les tests unitaires/Feature/intégration nécessaires en même temps que le code ;
5. exécuter les tests ciblés, les tests de non-régression concernés, `php artisan view:cache` si des vues sont touchées, `php artisan ui:lint` si une interface est touchée, puis `git diff --check` ;
6. inspecter toi-même l’interface dans le navigateur local lorsque le lot possède une interface ;
7. mettre à jour `docs/FREEBUFF_HANDOFF.md` avec ce qui est réellement terminé, les fichiers centraux, les tests et résultats exacts, les migrations appliquées, les risques et le prochain point de reprise ;
8. produire un rapport de fin de fonctionnalité clair ;
9. proposer un scénario de test manuel pas à pas, avec les données de test et le résultat attendu ;
10. t’arrêter et attendre ma vérification manuelle ;
11. ne poursuivre qu’après une réponse explicite de ma part autorisant la suite, par exemple « test validé, continue ».

Même si les tests automatisés passent, tu n’as pas l’autorisation d’enchaîner automatiquement sur la fonctionnalité suivante. Ma validation manuelle est un gate obligatoire.

Si je signale une anomalie pendant la recette :

- reproduis-la ;
- explique la cause avec des preuves ;
- corrige uniquement le lot concerné ;
- relance les tests ;
- mets à jour le rapport et `FREEBUFF_HANDOFF.md` ;
- propose de nouveau le test manuel ;
- attends une nouvelle validation explicite.

## 6. Format obligatoire du rapport après chaque fonctionnalité

Utilise cette structure :

```text
Fonctionnalité :
Statut réel : terminée / partielle / bloquée

Ce qui a été développé :
- ...

Fichiers principaux modifiés :
- ...

Migrations et données :
- ...

Sécurité et règles financières vérifiées :
- ...

Tests automatisés exécutés :
- commande
- nombre de tests/assertions
- résultat exact

Recette visuelle réalisée par le développeur :
- pages et largeurs
- résultat

Test manuel proposé au propriétaire :
1. ...
2. ...
Résultat attendu : ...

Risques ou décisions restantes :
- ...

État de FREEBUFF_HANDOFF.md : mis à jour

STOP : attente de la validation manuelle et de l’autorisation de continuer.
```

Ne prétends jamais qu’un test manuel, un paiement, un e-mail réel ou un webhook réel a été validé si tu ne disposes pas de la preuve correspondante.

## 7. Interdictions et garde-fous

- Aucun paiement ou payout réel sans autorisation explicite.
- Aucun secret affiché dans le terminal, les logs, le navigateur ou la documentation.
- Aucun `migrate:fresh`, `db:wipe`, suppression massive ou réinitialisation destructive sur une base applicative.
- Aucun contournement des middlewares, permissions, verrous ou contraintes uniques pour faire passer un test.
- Aucun calcul financier de confiance dans le JavaScript.
- Aucun changement rétroactif des snapshots, taux acquis ou écritures réglées.
- Aucun faux succès ou rapport exagéré.
- Ne marque pas une phase complète si son test manuel n’est pas validé.

## 8. Démarrage demandé dans cette discussion

Commence seulement par :

1. effectuer la lecture et l’audit initial demandés ;
2. comparer l’état réel du dépôt avec la Phase 0 et la Phase 1 du cahier ;
3. relever les décisions bloquantes qui nécessitent réellement mon choix ;
4. proposer le découpage de la **première fonctionnalité implémentable et testable manuellement** ;
5. si aucune décision bloquante n’empêche cette première fonctionnalité, l’implémenter, la tester, la documenter et me proposer sa recette manuelle ;
6. t’arrêter ensuite jusqu’à mon autorisation explicite de continuer.

Maintiens `docs/FREEBUFF_HANDOFF.md` à jour dès ce premier lot afin qu’une autre discussion puisse reprendre sans perte si le quota se termine.

---

Fin du prompt.
