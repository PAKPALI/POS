# Audit de sécurité offensif — POS SaaS

Date : 25 août 2026  
Périmètre : analyse statique du dépôt `C:\POS` et tests locaux non destructifs. Aucun serveur distant n'a été contacté.

> Statut de remédiation : les neuf mesures décrites ci-dessous ont été implémentées localement le 25 août 2026. Leur efficacité en production dépend encore du déploiement des fichiers serveur, de la définition de `KPRIME_SMS_CALLBACK_SECRET` et de la configuration correspondante chez le fournisseur SMS.

## Addendum de statut — 15 septembre 2026

Les sections SEC-01 à SEC-09 sont la preuve détaillée de l’état observé le 25 août et de la remédiation engagée. Elles ne doivent pas être relues comme un état courant non corrigé : le code, les tests d’isolation et la recette staging ont depuis été consolidés. Avant production, il reste à reproduire la configuration sécurisée validée en staging (document root `public`, secrets, hôtes autorisés, HTTPS, en-têtes et secret de callback) et à conserver les preuves de smoke tests.

La clôture technique actuelle et les résultats de vérification sont consignés dans l’addendum final ci-dessous. Les recommandations formulées dans les sections historiques ne constituent donc pas des anomalies encore ouvertes.

## Synthèse historique — état du 25 août 2026

L'isolation multi-tenant est globalement bien couverte par les scopes, policies, contraintes SQL et tests existants. En revanche, deux chemins permettent une compromission grave : l'acceptation d'une invitation peut ouvrir la session d'un compte existant sans authentification, et les logos d'entreprise permettent le dépôt de fichiers arbitraires dans le répertoire public. Avec la configuration Nginx fournie, ce second défaut peut mener à l'exécution de code sur le serveur.

Priorités : **2 critiques**, **2 élevées**, **3 moyennes**, **2 faibles / durcissement**.

## SEC-01 — Prise de contrôle d'un compte existant via une invitation

- Sévérité : **Critique** (estimation CVSS 9.1)
- Fichiers : `app/Http/Controllers/Auth/InvitationAcceptanceController.php:30-59`, `tests/Feature/CompanyInvitationFlowTest.php:55-71`, `tests/Feature/CompanyInvitationFlowTest.php:95-112`
- Impact : accès à toutes les entreprises et données auxquelles le compte invité appartient déjà, pas uniquement à l'entreprise qui émet l'invitation.

Le détenteur du lien d'invitation peut envoyer la requête d'acceptation. Si l'adresse correspond à un utilisateur existant, le contrôleur sélectionne ce compte puis appelle `Auth::login($user)` sans mot de passe, MFA ni session préalable. Il déconnecte même un autre utilisateur déjà connecté avant d'ouvrir la session cible. Les tests actuels valident explicitement ce comportement.

Correctif recommandé : pour un compte existant, exiger une session authentifiée portant exactement le même identifiant utilisateur. Sinon, rediriger vers la connexion en conservant l'intention d'accepter l'invitation. Ne jamais appeler `Auth::login` sur la seule preuve de possession du lien. Après connexion, accepter dans une transaction et régénérer la session. Ajouter des tests garantissant qu'un visiteur anonyme et qu'un autre compte ne peuvent pas accepter l'invitation.

## SEC-02 — Téléversement arbitraire dans `public/images` et exécution PHP possible

- Sévérité : **Critique** (estimation CVSS 9.0, compte authentifié requis)
- Fichiers : `app/Http/Controllers/Company/CompanyController.php:68-103`, `app/Http/Controllers/Company/CompanyController.php:146-184`, `app/Http/Controllers/Ecommerce/SettingController.php:71-78`, `routes/web.php:74-78`, `conf/nginx/nginx-site.conf:43-49`
- Impact : exécution de code, lecture du `.env`, accès à la base de données, modification ou exfiltration de toutes les données du serveur.

Les actions de logo ne valident ni MIME, ni extension, ni taille. Le nom original est conservé et le fichier est déplacé dans `public/images`. La route de création d'entreprise exige uniquement `auth`, donc tout compte connecté dispose d'un chemin vers cet upload. La configuration Nginx transmet tout chemin se terminant en `.php` à PHP-FPM, y compris sous `public/images`.

Correctif recommandé : accepter uniquement des formats raster décodables (`jpeg`, `png`, éventuellement `webp`), refuser SVG et toute extension active, imposer une taille et des dimensions, générer un nom aléatoire côté serveur et stocker hors du document root. Servir les images via une route contrôlée ou un stockage statique configuré pour ne jamais exécuter PHP. Ajouter aussi une règle Nginx interdisant PHP dans `/images` et limiter la création d'entreprise selon la règle métier.

## SEC-03 — Callback SMS public, non authentifié et journalisation intégrale

- Sévérité : **Élevée**
- Fichiers : `routes/api.php:22`, `app/Http/Controllers/Api/SmsController.php:16-28`
- Impact : falsification de callbacks, pollution ou saturation des journaux, stockage de données sensibles contrôlées par un attaquant, réponses 500 répétées.

Le endpoint accepte toute requête sans signature fournisseur et journalise tout le corps deux fois. `Log::success()` n'est pas une méthode standard de la façade Laravel et peut provoquer une erreur après la première écriture. Aucune validation ni réponse explicite n'est présente.

Correctif recommandé : vérifier une signature HMAC ou un secret fourni par le prestataire avec comparaison en temps constant, valider strictement le schéma et la taille, appliquer une limitation de débit dédiée, journaliser uniquement les champs nécessaires et retourner explicitement un code 2xx/4xx. Ne jamais journaliser token ou contenu SMS complet.

## SEC-04 — Connexion sans limitation de tentatives et énumération des comptes

- Sévérité : **Élevée**
- Fichiers : `routes/web.php:258`, `app/Http/Controllers/Auth/LoginController.php:94-156`
- Impact : brute force et credential stuffing facilités ; découverte des adresses enregistrées.

La méthode personnalisée `login()` n'utilise pas le mécanisme de limitation fourni par `AuthenticatesUsers`, et la route n'a pas de middleware `throttle`. Les messages distinguent « email incorrect » de « mot de passe incorrect ».

Correctif recommandé : utiliser `RateLimiter` par combinaison email normalisé + IP, verrouiller temporairement après plusieurs échecs, remettre le compteur à zéro après succès et retourner un message générique. Conserver une journalisation de sécurité sans mot de passe.

## SEC-05 — SVG actif dans les images produit/menu

- Sévérité : **Moyenne**
- Fichiers : `app/Http/Controllers/Component/ProductController.php:214-258`, `app/Http/Controllers/Component/MenuController.php:110-147`
- Impact : contenu actif servi depuis le même domaine, pouvant favoriser XSS stockée, hameçonnage ou abus de confiance lorsqu'un SVG est ouvert directement.

Les validateurs autorisent explicitement `svg` et les fichiers sont servis depuis le répertoire public. Retirer SVG des formats acceptés ou le nettoyer avec une bibliothèque dédiée, puis le servir avec des en-têtes restrictifs depuis un domaine sans cookies.

## SEC-06 — Validation des hôtes désactivée

- Sévérité : **Moyenne**
- Fichier : `app/Http/Kernel.php:17`
- Impact : attaques par en-tête `Host` sur les URL absolues générées (liens de réinitialisation, invitations ou redirections), selon la configuration du proxy/hébergeur.

Le middleware `TrustHosts` existe mais est commenté. L'activer et définir précisément le domaine de production et ses sous-domaines autorisés. Vérifier également que le reverse proxy remplace l'en-tête Host au lieu de faire confiance à une valeur client arbitraire.

## SEC-07 — En-têtes de défense incomplets

- Sévérité : **Moyenne**
- Fichier : `conf/nginx/nginx-site.conf:24-26`

Nginx ajoute `X-Frame-Options` et `X-Content-Type-Options`, mais pas HSTS, CSP, `Referrer-Policy` ni `Permissions-Policy`. `X-XSS-Protection` est obsolète. Ajouter ces politiques après inventaire des scripts inline ; commencer par une CSP en mode Report-Only avant blocage. Activer HSTS uniquement lorsque HTTPS est garanti sur tous les sous-domaines concernés.

## SEC-08 — Inscription propriétaire parallèle insuffisamment durcie

- Sévérité : **Faible à moyenne**, selon que l'inscription SaaS est volontairement publique
- Fichiers : `routes/web.php:67`, `app/Http/Controllers/User/UserController.php:332-397`

`admin_register` est un second chemin d'inscription, sans middleware `guest` ni limitation de débit, et dépend d'un `user_type=2` fourni par le client. La création d'un propriétaire semble être une fonction métier voulue, mais ce doublon augmente la surface d'abus et permet la création automatisée d'entreprises. Garder un seul flux, fixer le rôle côté serveur, ajouter throttling, vérification d'adresse et éventuellement CAPTCHA après seuil de risque.

## SEC-09 — Artefacts et surface statique inutiles

- Sévérité : **Faible / durcissement**
- Éléments : `login.html`, `realpage.html`, `response.html`, `php_error.log`, `public/hub/`, scripts Cloudflare copiés sous `public/cdn-cgi/`

Les artefacts racine ne sont pas servis si le document root pointe correctement vers `public`, mais ils deviennent exposés en cas d'erreur de configuration. `public/hub` est directement accessible et augmente fortement la surface statique à maintenir. Retirer du déploiement tout fichier non nécessaire et conserver le document root sur `public`.

## Contrôles positifs

- Les fichiers `.env` réels ne sont pas suivis par Git ; seul le modèle de production l'est.
- Les routes métier sensibles utilisent majoritairement `auth`, contexte entreprise et permissions.
- Les modèles, policies et contraintes composites offrent une défense en profondeur contre les IDOR inter-tenant.
- Les prix des commandes e-commerce sont recalculés côté serveur et les relations étrangères sont contrôlées.
- Les tokens d'invitation sont aléatoires, stockés sous forme de hash et expirent après 48 heures.
- CSRF est actif sur le groupe web ; les cookies sont `HttpOnly` et `SameSite=Lax`.

## Vérifications exécutées — état historique du 25 août 2026, actualisé par l’addendum final

- `php artisan route:list` : 306 routes examinées.
- `php artisan test` : **375 tests réussis, 2 144 assertions**.
- `npm audit --omit=dev` : aucune vulnérabilité signalée dans l'arbre de production (un seul paquet de production déclaré).
- `composer audit --locked` : aucune vulnérabilité signalée.
- Recherche statique ciblée : injections SQL, sorties Blade non échappées, appels système, scopes retirés, uploads, secrets, callbacks, cookies et en-têtes HTTP.

## Ordre de remédiation historique — état du 25 août 2026

1. Bloquer immédiatement la connexion automatique lors des invitations existantes.
2. Désactiver les uploads de logo actuels jusqu'à validation stricte et blocage PHP dans `/images`.
3. Sécuriser ou désactiver le callback SMS.
4. Ajouter la limitation de connexion et un message d'échec générique.
5. Retirer SVG, activer `TrustHosts`, compléter les en-têtes et nettoyer les artefacts.
6. Ajouter des tests de non-régression pour chaque point, puis exécuter l'audit Composer en CI.

## Addendum final — clôture de la passe d’authentification — 15 septembre 2026

La passe défensive locale/staging sur les formulaires d’authentification est terminée pour le périmètre testé :

- un seul flux canonique d’inscription est exposé (`GET /register` et `POST /register`) ; l’ancien endpoint `/admin_register` répond `404` ;
- les mots de passe d’inscription, de modification et de réinitialisation exigent 12 caractères avec majuscule, minuscule, chiffre et symbole ;
- les échecs de connexion et de réinitialisation sont génériques, avec limitations par IP et par compte ;
- une réinitialisation réussie ne connecte plus automatiquement le compte ; les déconnexions et expulsions invalident la session complète ;
- l’acceptation d’une invitation ne peut plus ouvrir la session d’un compte existant : la session active doit déjà correspondre à l’adresse invitée ; le parcours anonyme est couvert par un test dédié ;
- `/old-entry` ne révèle plus l’existence d’un compte ; CSRF a été vérifié sur les connexions web et plateforme (`419` sans jeton) ;
- les essais navigateur ont utilisé exclusivement des identifiants fictifs locaux ; aucune information d’un compte enregistré n’a été transmise.

État des dépendances vérifié : Laravel `12.69.2`, Sanctum `4.3.3`, Yajra DataTables `12.7.2`, PHPUnit `11.5.56`, PHP local `8.3.33`. Les audits Composer et npm ne signalent aucune vulnérabilité. La suite complète est verte : **375 tests, 2 144 assertions**. Deux avertissements PHPUnit concernent uniquement des métadonnées de tests par commentaires doc, sans échec fonctionnel.

Conditions restant nécessaires avant la bascule production : PHP `>= 8.2` avec `ext-zip`, `APP_DEBUG=false`, HTTPS, cookies sécurisés, hôtes de confiance et document root `public`, secrets/SMTP/KPrimePay propres à la production, workers et cron actifs, sauvegarde restaurable, supervision et smoke tests post-déploiement.
