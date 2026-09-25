# Déploiement de MAXANOU — POS SaaS sur O2switch

Ce guide prépare un premier déploiement SaaS avec **Laravel 12**, **PHP 8.2 minimum**, **MySQL**, une queue en base de données et sans Redis.

> **Statut au 25 septembre 2026 : staging validé et cœur de monétisation production confirmé.** Le propriétaire confirme que les paiements passent en production et que les abonnements fonctionnent. Cette validation ne clôt pas les contrôles d’exploitation périphériques : secrets/URLs, SMTP, URL webhook, cron, workers, sauvegardes, supervision, sécurité et activation progressive de l’enforcement doivent rester vérifiés et archivés.

## État de recette production

Le parcours principal de paiement et d’abonnement est classé **validé pour ouverture contrôlée** sur la base de la confirmation du propriétaire du 18 septembre 2026. Ajouter au journal de mise en production la date, l’entreprise de test, la référence interne et la référence KPrimePay de chaque transaction de recette, sans jamais publier de secret.

Ne pas déduire de cette réussite que les scénarios d’échec, de réconciliation, de webhook rejoué, de queue ou de reprise après incident sont validés : ils doivent rester couverts par les contrôles ci-dessous.

> Ne jamais envoyer le fichier `.env` réel sur Git et ne jamais copier les mots de passe dans une documentation. Remplacer tous les exemples avant le déploiement.

## 1. Architecture retenue

- Code Laravel : `/home/UTILISATEUR_CPANEL/proseller`
- Racine publique du domaine : `/home/UTILISATEUR_CPANEL/proseller/public`
- Base durable : MySQL, administrable avec phpMyAdmin
- Cache : fichiers
- Sessions : fichiers
- Queue : table MySQL `jobs`
- HTTPS : certificat SSL O2switch obligatoire
- Redis : non utilisé pour ce premier lancement

Dans **cPanel > Domaines**, faire pointer le domaine ou sous-domaine vers le dossier `proseller/public`. Ne jamais faire pointer le domaine vers la racine du projet : `.env`, `vendor`, `storage` et le code PHP ne doivent pas être publiquement accessibles.

## 2. Préparer O2switch

1. Dans cPanel, sélectionner PHP **8.2** pour le domaine et activer l'extension **ZIP**, requise par les exports Excel `.xlsx`.
2. Créer une base MySQL, un utilisateur MySQL et lui attribuer tous les privilèges sur cette base.
3. Activer le certificat SSL du domaine et vérifier que `https://votre-domaine.com` répond.
4. Ouvrir le Terminal cPanel ou établir une connexion SSH.
5. Repérer les commandes installées :

```bash
which php
php -v
which composer
```

Conserver le chemin retourné par `which php` : il sera utilisé dans les tâches cron.

## 3. Transférer le projet

Transférer le projet dans `/home/UTILISATEUR_CPANEL/proseller`, en excluant au minimum :

- `.env` local ;
- `.git` si Git n'est pas utilisé sur le serveur ;
- `node_modules` ;
- `storage/logs/*.log` ;
- les fichiers de test locaux inutiles en production.
- `login.html`, `realpage.html`, `response.html` et `php_error.log` ;
- `public/hub` uniquement si ses assets ne sont plus référencés par l’interface (il est encore utilisé actuellement).

Le fichier `public/hot` est un marqueur du serveur de développement : il doit être absent en production.

## 4. Installer les dépendances

Toujours transférer les versions à jour de `composer.json` **et** `composer.lock`. Depuis le Terminal cPanel, vérifier d'abord que ZIP est actif, puis installer exactement les versions verrouillées :

```bash
cd /home/UTILISATEUR_CPANEL/proseller
php -m | grep -i zip
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer show maatwebsite/excel
composer show phpoffice/phpspreadsheet
```

La commande ZIP doit afficher `zip`. Les deux dernières commandes doivent confirmer la présence de Laravel Excel et PhpSpreadsheet. Dans la version actuellement validée du projet, les versions attendues sont `maatwebsite/excel 3.1.70` et `phpoffice/phpspreadsheet 1.30.6`.

Ne pas utiliser `composer update` sur le serveur : `composer install` doit respecter `composer.lock`. Une erreur `Interface "Maatwebsite\\Excel\\Concerns\\FromGenerator" not found` signifie que le code déployé est plus récent que le dossier `vendor`; relancer cette procédure puis `php artisan optimize:clear`.

Si Composer refuse la version PHP, vérifier que le terminal utilise bien PHP 8.2 et demander au support O2switch le chemin Composer/PHP correspondant au domaine. Si ZIP n'apparaît pas, l'activer dans le gestionnaire d'extensions PHP de cPanel avant de relancer Composer.

## 5. Créer le `.env` de production

Copier le modèle fourni :

```bash
cp .env.production.example .env
```

Modifier `.env` avec les informations réelles du domaine, de MySQL, de la boîte e-mail SMTP et du service SMS. Les valeurs essentielles sont :

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com
SEO_INDEXING_ENABLED=true
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
KPRIME_SMS_CALLBACK_SECRET=une_valeur_aleatoire_longue
```

Sur staging, conserver `APP_ENV=staging` et définir `SEO_INDEXING_ENABLED=false`. Les réponses HTTP ajoutent alors `X-Robots-Tag: noindex, nofollow, noarchive`, les pages marketing utilisent `noindex`, `/robots.txt` interdit l'exploration et `/sitemap.xml` est indisponible. En production, utiliser `APP_ENV=production` et `SEO_INDEXING_ENABLED=true` pour activer les balises canoniques, `robots.txt` et le sitemap.

Le secret du callback SMS doit être différent de la clé API et configuré de la même manière chez le fournisseur. Sans ce secret, le callback répond volontairement `503`.

Générer la clé une seule fois lors de la première installation :

```bash
php artisan key:generate
```

Ne jamais régénérer `APP_KEY` après la mise en service : cela invaliderait les données chiffrées et les sessions existantes.

## 6. Permissions et stockage

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

Ne pas utiliser `chmod -R 777`. Vérifier ensuite que PHP peut écrire dans `storage/logs`, `storage/framework` et `bootstrap/cache`.

## 7. Base de données

Avant toute migration sur une base existante, créer une sauvegarde depuis cPanel/phpMyAdmin.

`migrate --force` ne supprime pas les données par lui-même : il exécute uniquement les migrations encore absentes et désactive la confirmation interactive en production. Une migration peut toutefois modifier ou supprimer des données si elle a été écrite ainsi ; contrôler son contenu et son plan avant de l'exécuter.

Sur une base existante, contrôler d'abord l'état et le SQL prévu :

```bash
php artisan migrate:status
php artisan migrate --pretend
```

Pour une première installation vide :

```bash
php artisan migrate --force
```

Ne jamais exécuter `migrate:fresh`, `db:wipe` ou une commande de suppression sur le serveur de production.

## 8. Nettoyer et optimiser Laravel

```bash
rm -f public/hot
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan view:cache
php artisan queue:restart
```

Le cache des routes est volontairement exclu pour le moment : le projet contient encore un nom de route dupliqué (`codePromo.pdf`), ce qui fait échouer `php artisan route:cache`. Ne pas remplacer cette procédure par `php artisan optimize` tant que ce doublon n'est pas corrigé et validé. Le cache des événements, des vues et de la configuration peut être activé avec la procédure ci-dessus.

### Script de déploiement fourni

Le script `scripts/00-laravel-deploy.sh` automatise l'installation Composer, la compilation Vite, le contrôle UI, les migrations, les caches compatibles et les vérifications finales. Il s'arrête dès qu'une commande échoue et n'active ni le mode maintenance ni `route:cache`.

Depuis la racine du projet, après avoir adapté le chemin :

```bash
APP_DIR=/home/UTILISATEUR_CPANEL/proseller bash scripts/00-laravel-deploy.sh
```

Si Node.js/npm n'est pas disponible sur O2switch, compiler `public/build` sur une machine disposant de Node.js puis transférer ce dossier, et lancer le script avec :

```bash
APP_DIR=/home/UTILISATEUR_CPANEL/proseller RUN_FRONTEND_BUILD=false bash scripts/00-laravel-deploy.sh
```

Dans ce cas, vérifier impérativement que `public/build/manifest.json` existe avant l'ouverture du site.

## 9. Configurer les tâches cron

Dans **cPanel > Tâches cron**, créer deux tâches exécutées chaque minute. Remplacer les chemins et le chemin PHP par ceux retournés par `which php`.

### Planificateur Laravel

```bash
* * * * * flock -n /home/UTILISATEUR_CPANEL/.proseller-schedule.lock -c 'cd /home/UTILISATEUR_CPANEL/proseller && /CHEMIN/PHP artisan schedule:run' >> /home/UTILISATEUR_CPANEL/proseller/storage/logs/scheduler.log 2>&1
```

Il exécute notamment le nettoyage tenanté du journal, le rapport hebdomadaire d'inventaire, la rétention à 180 jours du registre idempotent des livraisons de notifications (`notifications:clean-deliveries`) et la réconciliation des paiements KPrimePay expirés toutes les dix minutes (`payments:reconcile-kprimepay --limit=100`).

### E-mails et notifications en attente

```bash
* * * * * flock -n /home/UTILISATEUR_CPANEL/.proseller-queue.lock -c 'cd /home/UTILISATEUR_CPANEL/proseller && /CHEMIN/PHP artisan queue:work database --stop-when-empty --sleep=3 --tries=3 --timeout=120' >> /home/UTILISATEUR_CPANEL/proseller/storage/logs/queue.log 2>&1
```

Cette solution convient à l'hébergement mutualisé : `flock` empêche les doubles workers et `--stop-when-empty` termine le processus lorsqu'il n'y a plus de travail. Une notification peut attendre jusqu'à environ une minute avant son traitement.

Vérifications utiles :

```bash
php artisan schedule:list
php artisan queue:failed
php artisan payments:reconcile-kprimepay --limit=100 --pretend
```

Pour diagnostiquer un job échoué ou un paiement KPrimePay bloqué, suivre `docs/GUIDE_KPRIMEPAY.md`. Ne jamais utiliser `queue:retry all` ni créditer les quotas directement sans rapprochement avec KPrimePay.

## 10. Sauvegardes minimales

Configurer dans cPanel :

- une sauvegarde régulière de la base MySQL ;
- une sauvegarde de `storage/app` et des fichiers téléversés ;
- une conservation hors de l'hébergement pour au moins une copie récente ;
- un test réel de restauration avant le lancement commercial.

Une sauvegarde non restaurée au moins une fois n'est pas encore une sauvegarde validée.

## 11. Vérifications après déploiement

Exécuter :

```bash
php artisan about
php artisan migrate:status
php artisan schedule:list
php artisan queue:failed
```

Puis tester manuellement :

- inscription d'un propriétaire et création de sa compagnie ;
- connexion et déconnexion ;
- changement entre deux compagnies ;
- isolation des produits, utilisateurs, rôles et caisses ;
- vente, diminution du stock, caisse principale et taxe ;
- commande e-commerce, e-mail manager et conversion en vente ;
- invitations et réinitialisation du mot de passe ;
- e-mails, SMS et WhatsApp selon les autorisations ;
- installation PWA depuis Android et Safari/iPhone ;
- sur Android éligible, affichage de la bannière interne « Installer MAXANOU » et lancement de l’invite système ;
- sur les navigateurs mobiles sans API d’installation, affichage après quelques secondes d’un guide indiquant l’option du menu « Installer l’application » ou « Ajouter à l’écran d’accueil » ;
- connexion depuis la PWA installée, fermeture complète puis réouverture : l’application s’ouvre via `/user_login`, puis rejoint l’espace autorisé lorsque la session reste valide, sans retour artificiel au formulaire ;
- page 403 et menus selon les permissions ;
- page hors connexion de la PWA.
- exports CSV et Excel des produits, de l'inventaire et des ventes ; vérifier que le fichier Excel téléchargé porte bien l'extension `.xlsx` et s'ouvre sans réparation.

Consulter également `storage/logs/laravel.log`, `scheduler.log`, `queue.log`, `slow-queries-AAAA-MM-JJ.log` et les tâches présentes dans la table `failed_jobs`.

### Surveiller les requêtes lentes

Le modèle `.env.production.example` active un journal séparé pour les requêtes dépassant 300 ms :

```dotenv
PERFORMANCE_SLOW_QUERY_LOG=true
PERFORMANCE_SLOW_QUERY_MS=300
PERFORMANCE_LOG_CHANNEL=performance
PERFORMANCE_LOG_SQL=true
```

Conserver également les plafonds PDF validés sous 512 Mo. Ils empêchent DomPDF de saturer la mémoire lorsque plusieurs milliers de lignes sont demandées :

```dotenv
PDF_PRODUCTS_MAX_ROWS=300
PDF_INVENTORIES_MAX_ROWS=500
PDF_SALES_MAX_ROWS=100
```

Un dépassement demande à l’utilisateur de préciser ses filtres avant de relancer l’export. Ne pas augmenter ces valeurs sur l’hébergement mutualisé sans refaire `benchmarks/PdfExportBenchmark.php` avec la limite mémoire réelle. Pour les exports exhaustifs, privilégier ultérieurement un format CSV/Excel généré en flux.

Les événements sont écrits dans `storage/logs/slow-queries-AAAA-MM-JJ.log` et conservés 14 jours. Chaque entrée indique la durée, la route, le chemin, la connexion, la compagnie et l’utilisateur lorsque ces contextes existent. La structure SQL est enregistrée avec ses marqueurs ; les valeurs liées et les saisies utilisateur ne sont jamais ajoutées par le moniteur.

Pendant le pilote, contrôler ce fichier chaque jour. Une même requête dépassant régulièrement 300 ms doit être analysée avec `EXPLAIN` dans phpMyAdmin avant d’ajouter un index. Ne jamais copier une requête de production contenant des données métier dans un outil public.

Après toute modification de ces variables, appliquer :

```bash
php artisan config:cache
```

## 12. Procédure de mise à jour ultérieure

1. Sauvegarder la base et les fichiers.
2. Activer le mode maintenance.
3. Transférer la nouvelle version.
4. Installer les dépendances.
5. Exécuter les migrations non destructives.
6. Nettoyer/reconstruire la configuration.
7. Redémarrer la queue.
8. Désactiver le mode maintenance et effectuer les tests rapides.

```bash
set -Eeuo pipefail
cd /home/UTILISATEUR_CPANEL/proseller

php artisan down --retry=60
git pull --ff-only origin master
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# À exécuter si Node.js/npm est disponible sur le serveur.
npm ci --no-audit --no-fund
npm run build

rm -f public/hot
php artisan ui:lint
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan view:cache
php artisan queue:restart

php artisan about
php artisan migrate:status
php artisan schedule:list
php artisan queue:failed
php artisan up
```

Si npm n'est pas disponible, ne pas supprimer le dossier `public/build` déjà préparé : compiler les assets en amont et transférer `public/build` avant de lancer la procédure avec `RUN_FRONTEND_BUILD=false`.

Si une commande échoue, ne pas poursuivre aveuglément : conserver le mode maintenance, lire le message et restaurer la sauvegarde si nécessaire.

## 13. Checklist avant ouverture aux clients

- [ ] Domaine et certificat HTTPS actifs
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production` et `SEO_INDEXING_ENABLED=true` uniquement sur le domaine de production
- [ ] Sur staging, `SEO_INDEXING_ENABLED=false` et `/robots.txt` renvoie `Disallow: /`
- [ ] `.env` inaccessible depuis le Web
- [ ] Racine du domaine configurée sur `/public`
- [ ] Base MySQL sauvegardée
- [ ] Migrations terminées
- [ ] Liens de stockage fonctionnels
- [ ] Extension PHP `zip` active et packages `maatwebsite/excel` / `phpoffice/phpspreadsheet` installés par Composer
- [ ] Exports Excel `.xlsx` validés sur le serveur
- [ ] SMTP vérifié avec un vrai destinataire
- [ ] Cron du scheduler actif
- [ ] Cron de queue actif
- [ ] Aucun job dans `failed_jobs`
- [ ] Test avec au moins deux compagnies
- [ ] Test des permissions avec un utilisateur non administrateur
- [ ] PWA installée et testée sous HTTPS
- [ ] Connexion, fermeture et réouverture de la PWA validées sur le domaine canonique exact (`www` ou sans `www`)
- [ ] Upload d’un fichier `.php` refusé et accès à `/images/test.php` bloqué
- [ ] Signature du callback SMS vérifiée avec le fournisseur
- [ ] En-têtes HSTS, CSP Report-Only, Referrer-Policy et Permissions-Policy présents
- [ ] Sauvegarde restaurée sur un environnement de test

## Valeurs à demander au moment du déploiement

Pour produire les commandes finales prêtes à copier, relever uniquement : le nom du domaine, le chemin `/home/...`, le résultat de `which php`, les noms MySQL (sans publier les mots de passe) et les paramètres SMTP fournis par O2switch.
## Paiements KPrimePay pour les quotas

Avant d’activer l’achat de quotas, renseigner les variables `KPRIMEPAY_*` décrites dans `docs/GUIDE_KPRIMEPAY.md`. La clé doit avoir les scopes `payments:write` et `read`.

L’intégration cible est l’API KPrimePay v2. Selon la dernière notification du fournisseur, l’API v1 sera arrêtée définitivement le **30 septembre 2026**. Vérifier avant ouverture que `KPRIMEPAY_BASE_URL` et `KPRIMEPAY_PAYOUT_BASE_URL` terminent bien par `/v2` et qu’aucun nouveau flux ne pointe vers une URL v1.

Configurer dans KPrimePay l’URL de rappel HTTPS :

```text
https://VOTRE-DOMAINE/api/kprimepay/webhook
```

Les nouveautés v2 `OTHER_REGION`, `customer` et `payment_methods` sont facultatives. Elles sont documentées dans `docs/GUIDE_KPRIMEPAY.md` ; ne pas les ajouter dans le déploiement sans tester le checkout hébergé et le webhook correspondant. Pour une sélection de moyens de paiement, utiliser les codes renvoyés par `GET /v2/gateways` (`checkout_method`) au lieu d’une liste codée en dur.

Tester d’abord avec `KPRIMEPAY_MODE=1`. Ne passer à `KPRIMEPAY_MODE=2` qu’après validation du checkout, du webhook, du montant et de l’idempotence. La clé communiquée pendant le développement doit être révoquée et remplacée avant ce test.
# Console d’administration SaaS

Après déploiement des fichiers de la console plateforme :

```bash
php artisan migrate --force
php artisan platform-admin:create --from-user=pakpalididier@gmail.com
php artisan optimize:clear
```

La commande copie l’identité et le mot de passe **chiffré** du compte POS existant sans afficher son mot de passe. Lors de la première connexion sur `/platform/login`, le super-administrateur doit obligatoirement choisir un nouveau mot de passe robuste avant d’accéder aux statistiques.

Pour créer un compte plateforme sans utilisateur POS existant, utiliser la commande interactive :

```bash
php artisan platform-admin:create
```

Ne jamais passer le mot de passe dans une commande conservée dans l’historique du terminal. Vérifier ensuite que `/platform` refuse un utilisateur POS ordinaire.

La migration `2026_08_28_140000_create_platform_settings_and_store_quota_unit_prices` ajoute les tarifs administrables et les prix unitaires mémorisés par paiement. Après déploiement, ouvrir `/platform/settings`, contrôler les valeurs initiales issues de `.env`, puis enregistrer les tarifs commerciaux voulus. Les anciens paiements restent valides mais ne possèdent pas le détail unitaire historique.

La migration `2026_08_28_160000_store_quota_unit_costs_and_backfill_legacy_prices` ajoute les coûts unitaires historiques. Les valeurs de secours sont `KPRIMEPAY_SMS_UNIT_COST=15` et `KPRIMEPAY_WHATSAPP_UNIT_COST=15`. Après migration, vérifier dans **Administration SaaS > Paramètres** que les prix sont 35/30 XOF et les coûts 15/15 XOF.

La migration `2026_08_28_180000_create_platform_system_heartbeats` active la supervision du cron. Le cron O2switch doit continuer à exécuter chaque minute :

```bash
php /chemin/vers/artisan schedule:run
```

Après déploiement, exécuter une fois `php artisan platform:heartbeat`, attendre deux minutes, puis ouvrir **Administration SaaS > Santé du système**. Le statut doit rester **Opérationnel**. S'il devient « En retard » ou « Critique », vérifier la tâche cron O2switch.

Après déploiement des rôles plateforme, exécuter `php artisan optimize:clear`. Connectez-vous avec le super-administrateur existant et créez les comptes Support, Finance ou Technique depuis **Administration SaaS > Administrateurs**. Ne partagez jamais un même compte entre plusieurs personnes.
