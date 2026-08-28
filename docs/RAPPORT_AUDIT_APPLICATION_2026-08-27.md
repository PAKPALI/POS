# Rapport d'audit global de l'application

Date : 27 août 2026

## Conclusion

L'application est fonctionnellement stable sur l'environnement local audité. La suite automatisée, les routes et les migrations ne présentent aucun échec. Le principal risque restant avant une ouverture large en production concerne les dépendances PHP, dont plusieurs versions installées font l'objet d'avis de sécurité publics.

## Résultats contrôlés

| Contrôle | Résultat | État |
|---|---:|---|
| Tests Laravel | 147 tests, 880 assertions, 0 échec | Conforme |
| Chargement des routes | 185 routes | Conforme |
| Migrations | Toutes appliquées | Conforme |
| Syntaxe PHP ciblée | Aucune erreur | Conforme |
| Audit npm de production | 0 vulnérabilité | Conforme |
| Configuration de production exemple | debug désactivé, cookies sécurisés, queue database | Conforme |
| Recherche de secret KPrimePay hors `.env` | Aucun jeton réel détecté | Conforme |
| Audit Composer de production | 43 avis concernant 13 paquets | À corriger en priorité |

## Points rassurants

- Les tests couvrent notamment l'isolation multi-entreprise, les permissions, le profil, les invitations, les paiements et les flux principaux.
- Les groupes de routes métier sont protégés par l'authentification, le contexte de compagnie et les permissions attendues.
- Les routes publiques d'écriture identifiées correspondent à des besoins légitimes : webhook KPrimePay, callback SMS, commande e-commerce, invitation et réinitialisation du mot de passe. Elles disposent des protections prévues, notamment le throttling lorsque nécessaire.
- Le planificateur contient bien la réconciliation KPrimePay toutes les dix minutes.
- La configuration de production fournie impose `APP_DEBUG=false`, `LOG_LEVEL=warning`, `QUEUE_CONNECTION=database` et `SESSION_SECURE_COOKIE=true`.
- Les dépendances JavaScript de production ne présentent aucune vulnérabilité connue selon `npm audit`.

## Risque prioritaire : dépendances PHP

L'audit de la version actuellement verrouillée remonte 43 avis de sécurité de production. Les paquets concernés sont notamment Laravel, Dompdf, Guzzle, CommonMark et plusieurs composants Symfony.

Une simulation Composer réussie propose 87 mises à jour sans passer Laravel en version majeure :

- Laravel `10.48.20` vers `10.50.3` ;
- Dompdf `3.0.0` vers `3.1.6` ;
- Guzzle `7.9.2` vers `7.15.5` ;
- Symfony `6.4.x` vers les correctifs `6.4.44` pour les composants Laravel concernés ;
- CommonMark `2.5.3` vers `2.10.0` ;
- mise à jour des dépendances indirectes associées.

Cette simulation ne modifie aucun fichier. La mise à jour réelle doit être traitée dans un lot séparé, suivie des tests complets et d'une validation sur staging.

## Point de qualité Composer

La dépendance `yajra/laravel-datatables-oracle` utilise actuellement une contrainte non bornée `*`. Elle doit être verrouillée sur une branche compatible avec Laravel 10, par exemple `^10.0`, pour éviter qu'une future installation ne récupère automatiquement une version majeure incompatible.

## Lecture des journaux

Les anciennes erreurs visibles dans `storage/logs/laravel.log` correspondent principalement à des incidents déjà corrigés au fil du développement. Les dernières erreurs locales de connexion à KPrimePay ont été produites pendant cet audit par une tentative de réconciliation dans l'environnement local, où l'accès externe est bloqué. Elles ne démontrent pas une panne du staging, dont l'intégration réelle a déjà été validée.

## Ce qui reste à vérifier manuellement

L'audit automatisé ne remplace pas une recette visuelle authentifiée sur tous les navigateurs. Les points suivants restent à rejouer après la mise à jour Composer :

1. connexion et changement de compagnie sur navigateur et PWA ;
2. création d'une vente, impression et actualisation du stock ;
3. notifications e-mail, SMS et WhatsApp de vente et d'inventaire ;
4. commande e-commerce, conversion en vente et annulation ;
5. génération PDF et exports Excel/CSV ;
6. achat de quotas KPrimePay, webhook et réconciliation tardive ;
7. refus d'accès et menus selon plusieurs rôles.

## Plan recommandé

1. créer une sauvegarde du projet, de `composer.json`, de `composer.lock` et de la base de staging ;
2. remplacer la contrainte Yajra `*` par `^10.0` ;
3. exécuter la mise à jour compatible avec Laravel 10 ;
4. relancer `composer audit --locked --no-dev` ;
5. exécuter toute la suite de tests ;
6. déployer d'abord sur staging et réaliser la recette manuelle ci-dessus ;
7. seulement après validation, reporter le nouveau `composer.lock` en production.

## Appréciation actuelle

- stabilité fonctionnelle automatisée : **très bonne** ;
- isolation et permissions : **bonne, couverte par les tests existants** ;
- préparation opérationnelle : **bonne selon les validations staging communiquées** ;
- sécurité des dépendances : **insuffisante tant que les mises à jour Composer ne sont pas appliquées**.

La priorité suivante est donc le lot de mise à jour sécurisée des dépendances PHP, sans migration vers Laravel 11 ou 12 à ce stade.
