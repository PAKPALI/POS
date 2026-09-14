# Rapport permanent — Administration SaaS

Dernière mise à jour : 14 septembre 2026 — pilotage partenaires, graphiques, alertes e-mail et clôture staging

## Mise à jour du 14 septembre 2026 — pilotage partenaires et alertes

- **Monétisation > Partenaires** fournit une vue d’ensemble des partenaires, de leur activité, des clients qualifiés, des commissions, des soldes et des retraits, avec filtres serveur et fenêtres de 30, 90 ou 365 jours.
- Chaque partenaire dispose d’une fiche détaillée avec identité, code promotionnel, attributions, commissions, retraits et journal d’audit. Les droits `platform.partner_commissions.view` et `platform.partner_withdrawals.view` protègent les données financières sensibles.
- Les graphiques ApexCharts sont responsives, utilisent les tokens de `design-system.css` et exposent leurs valeurs dans des tableaux consultables sans survol.
- **Paramètres > Partenaires** permet d’activer les alertes e-mail, choisir des destinataires et sélectionner les événements : inscription, activation, changement de code, commission, demande de retrait, réussite, échec ou réponse inconnue.
- Les alertes sont déclenchées après commit depuis les services métier et dédupliquées par clé d’événement. Une panne de planification est journalisée sans annuler l’opération métier.
- Validation locale : `php artisan ui:lint --changed`, `php artisan view:cache`, routes partenaires et **9 tests ciblés / 43 assertions** réussis.

## Mise à jour du 11 septembre 2026 — trésorerie : comptes Mobile Money visibles et doublons refusés

- La page **Monétisation > Trésorerie & retraits** affiche désormais les comptes Mobile Money enregistrés sous le formulaire : numéro masqué, pays/opérateur, état de vérification et indicateur de compte principal. Les données sensibles restent chiffrées et ne sont jamais réaffichées en clair.
- Un même numéro normalisé ne peut plus être ajouté deux fois pour un même administrateur. Le contrôle s’appuie sur son empreinte, est refait côté serveur dans une transaction et renvoie un message explicite sans créer de second compte.
- Les formulaires ont été réorganisés en étapes distinctes : ajout et vérification d’un bénéficiaire, puis demande de sortie. Les informations de capacité, de frais et de sécurité restent séparées des actions afin de préserver la lecture sur ordinateur comme sur mobile.
- Les cartes « Compte Mobile Money administrateur » et « Demander un retrait de trésorerie » disposent d’un espacement responsive constant ; les champs de mot de passe et les actions de vérification restent contenus dans leur carte.
- Vérification effectuée avec une session super-administrateur : affichage de la liste masquée, formulaires et espacement contrôlés dans le navigateur. `php -l`, le cache des vues et `git diff --check` sont passés. La suite ciblée `PlatformTreasuryTest` n’a pas pu démarrer dans l’environnement Windows local à cause d’une erreur `proc_open` de conversion de commande, avant toute assertion ; elle reste à rejouer dans un environnement PHP fonctionnel.

## Mise à jour du 7 septembre 2026 — contrôle d’abonnement par entreprise

- **Paramètres généraux** expose maintenant une section « Contrôle d’abonnement individuel » listant les entreprises et leur mode effectif : héritage du réglage global, activation forcée ou désactivation forcée.
- La DataTable conserve désormais une lecture propre (entreprise, statut, réglage, action) ; les champs sensibles et le choix du mode s’ouvrent dans une fenêtre SaaS dédiée par entreprise, responsive et scrollable sur mobile.
- Contrôle navigateur effectué : les modales non ouvertes sont désormais masquées, la modal sélectionnée s’affiche en overlay avec backdrop et le corps de page ne défile plus derrière elle.
- Le changement est protégé par `platform.admins.manage`, le mot de passe plateforme et un motif obligatoire. Il est journalisé dans `platform_audit_logs` sous l’action `company.subscription_enforcement.updated`.
- La valeur `NULL` conserve le comportement global ; les valeurs `1` et `0` permettent de tester ou d’exploiter une entreprise indépendamment du reste de la plateforme. Aucun abonnement, paiement ou quota n’est modifié.
- Migration : `2026_09_07_100000_add_subscription_enforcement_override_to_companies.php`. La feuille `platform.css` est versionnée `20260907-1` pour le nouveau formulaire responsive.

## Règle de suivi

Ce fichier est l’unique rapport d’avancement de la partie administrative SaaS. Toute évolution de la console centrale doit être ajoutée ici après son implémentation et sa vérification. Aucun nouveau rapport séparé par phase ne doit être créé.

## État actuel

La console d’administration centrale est opérationnelle et séparée des comptes `owner` et `admin` propres aux entreprises. Elle est accessible par `/admin-saas` ou `/platform/login`.

Dernière non-régression complète documentée : **356 tests, 2 034 assertions, 0 échec**. Des suites ciblées supplémentaires ont ensuite couvert le catalogue, le pré-contrôle, les abonnements, l’expiration, les notifications et le pilotage partenaires.

## Accès et sécurité plateforme

- garde Laravel distincte `platform` ;
- comptes dédiés dans `platform_admins` ;
- middleware séparé des utilisateurs POS ;
- limitation des tentatives de connexion et refus des comptes désactivés ;
- déconnexion POST protégée par CSRF ;
- changement obligatoire du mot de passe initial ;
- mot de passe robuste d’au moins 12 caractères avec casse, chiffre et symbole ;
- commande `platform-admin:create` pour créer ou promouvoir un administrateur ;
- promotion d’un utilisateur POS par copie sécurisée de son hash, sans mot de passe en clair ;
- journalisation des connexions, déconnexions et actions sensibles ;
- page 403 adaptée au contexte plateforme ;
- loaders et blocage des doubles clics sur les actions serveur.

Sécurité avancée désormais disponible :

- double authentification activée par défaut pour les comptes plateforme ;
- code numérique à six chiffres envoyé par e-mail après validation du mot de passe ;
- code stocké sous forme de hash, valable 10 minutes et limité à cinq essais ;
- renvoi du code limité pour empêcher les abus ;
- récupération du mot de passe sur une interface exclusivement réservée à la plateforme ;
- lien de récupération aléatoire, hashé, valable 60 minutes et utilisable une seule fois ;
- réponse neutre lors d’une demande de récupération afin de ne pas révéler les comptes existants ;
- invalidation des anciennes sessions après changement ou récupération du mot de passe ;
- réinitialisation de la double authentification d’un administrateur par un super-administrateur, avec mot de passe, motif, confirmation, loader et audit ;
- activation ou désactivation individuelle de la double authentification depuis **Administrateurs**, avec les mêmes contrôles de sécurité ;
- e-mails de sécurité conformes au modèle visuel de l’application.

Le compte POS et le compte plateforme d’une même adresse restent volontairement indépendants. La connexion POS peut ouvrir une entreprise tandis que la connexion plateforme ouvre exclusivement la console SaaS.

## Tableau de bord global

Le tableau de bord central présente notamment :

- entreprises et utilisateurs ;
- adhésions multi-entreprises ;
- ventes et commandes ;
- communications SMS et WhatsApp ;
- paiements de quotas ;
- synthèse des entreprises et paiements récents.

## Entreprises

- liste globale paginée, recherchable et filtrable ;
- recherche par nom, e-mail, slug ou identifiant public ;
- propriétaire, membres, commandes et quotas visibles ;
- fiche détaillée avec ventes, chiffre d’affaires, commandes, produits, inventaires, communications et paiements ;
- affichage des membres et de leurs rôles ;
- suspension et réactivation avec motif obligatoire ;
- confirmation avec loader ;
- changement de statut et audit enregistrés dans une transaction atomique.

Une entreprise suspendue est refusée par le contrôle multi-tenant dès la requête suivante.

## Utilisateurs

- liste globale paginée, recherchable et filtrable ;
- noms des entreprises affichés à la place de leurs identifiants numériques ;
- nombre d’entreprises et détail des adhésions actives ;
- dernier accès connu ;
- fiche regroupant toutes les entreprises et tous les rôles ;
- historique récent des invitations liées à l’adresse e-mail.

## Paiements, quotas et rentabilité

- liste globale des paiements KPrimePay avec recherche, période et statut ;
- fiche détaillée : entreprise, initiateur, quotas, montants, références et erreurs ;
- réconciliation KPrimePay contrôlée, motivée et auditée ;
- aucun crédit manuel sans confirmation du prestataire ;
- protection contre une seconde réconciliation après crédit ;
- prix et coûts unitaires mémorisés sur chaque nouveau paiement ;
- aucun changement rétroactif des paiements historiques ;
- seuls les paiements confirmés entrent dans les résultats financiers.

Paramètres actuellement retenus :

| Canal | Prix de vente | Coût fournisseur | Marge unitaire |
|---|---:|---:|---:|
| SMS | 35 XOF | 15 XOF | 20 XOF |
| WhatsApp | 30 XOF | 15 XOF | 15 XOF |

La console affiche séparément puis globalement le chiffre d’affaires, le coût fournisseur et le bénéfice. Les prix et coûts sont modifiables avec mot de passe, motif, confirmation et historique. Un coût ne peut pas dépasser son prix de vente.

## Journal d’audit

- liste globale paginée ;
- recherche par action, cible ou motif ;
- filtres par administrateur, résultat et période ;
- détail avec adresse IP, navigateur, motif, anciennes et nouvelles valeurs ;
- aucune modification ou suppression depuis l’interface.

## Santé et exploitation

- heartbeat du planificateur Laravel chaque minute ;
- état du cron : opérationnel, en retard, critique ou non observé ;
- nombre de jobs en attente et âge du plus ancien ;
- liste des jobs échoués ;
- relance unitaire confirmée, motivée et auditée ;
- détection des paiements KPrimePay bloqués ;
- suivi des webhooks récents ;
- délivrabilité e-mail, SMS et WhatsApp sur sept jours ;
- distinction entre attente, traitement, succès et échec.

Les payloads complets et exceptions sensibles ne sont pas exposés dans l’interface.

## Alertes automatiques d’exploitation

- vérification automatique toutes les cinq minutes par `platform:check-alerts` ;
- détection d’un heartbeat cron absent ou en retard ;
- seuil configurable de jobs échoués ;
- détection de l’ancienneté anormale de la file d’attente ;
- détection des paiements KPrimePay bloqués ;
- contrôle du taux d’échec des e-mails, SMS et WhatsApp sur la dernière heure ;
- seuils, volume minimum et délai anti-spam configurables ;
- sélection des super-administrateurs et techniciens destinataires ;
- repli automatique sur tous les super-administrateurs et techniciens actifs sans sélection explicite ;
- historique paginé avec statut ouverte, prise en charge ou résolue ;
- acquittement et résolution manuelle auditée ;
- résolution automatique lorsqu’une anomalie n’est plus détectée ;
- vérification manuelle disponible depuis la console ;
- panne d’envoi d’un e-mail isolée afin de ne pas interrompre les autres contrôles.

Les alertes sont actuellement délivrées par e-mail. Elles contrôlent bien les échecs SMS et WhatsApp, mais ne dépendent pas de ces deux canaux pour avertir l’équipe lorsqu’ils sont eux-mêmes en panne. Une panne totale du cron doit également être surveillée extérieurement, car une application dont le cron ne s’exécute plus ne peut pas envoyer elle-même une alerte pendant cet arrêt.

## Communications globales

- statistiques séparées des e-mails, SMS et messages WhatsApp ;
- statuts en attente, traitement, envoyé et échoué ;
- filtres SQL par canal, statut, catégorie, période, entreprise ou événement ;
- liste globale paginée avec entreprise, destinataire, tentatives et erreur résumée ;
- coordonnées des destinataires masquées dans l’interface ;
- synthèse de la consommation principale par entreprise et canal ;
- exports CSV et Excel respectant les filtres actifs ;
- accès réservé aux super-administrateurs et administrateurs techniques ;
- relance uniquement pour les événements pouvant être reconstruits avec les jobs existants ;
- relance atomique, auditée et protégée contre un double clic ou une double mise en file ;
- aucun bouton de relance pour un événement dont la reconstruction ne serait pas sûre.

## Administrateurs et rôles plateforme

| Rôle | Accès principal |
|---|---|
| Super-administrateur | Accès complet, paramètres, administrateurs et actions sensibles |
| Support | Tableau de bord, entreprises et utilisateurs en consultation |
| Finance | Paiements, quotas, rentabilité et réconciliation KPrimePay |
| Technique | Santé du système, journal d’audit et relance des jobs |

La gestion des comptes comprend :

- création avec rôle et mot de passe initial ;
- modification du nom, de l’e-mail et du rôle ;
- désactivation et réactivation avec motif et mot de passe ;
- permissions appliquées aux menus et aux routes serveur ;
- audit de chaque changement ;
- interdiction de modifier son propre rôle ou de désactiver son compte ;
- protection du dernier super-administrateur actif ;
- aucune suppression physique des comptes.

## Paramètres généraux de la plateforme

- nom commercial dynamique de l’application ;
- logo de la console stocké sur le disque public ;
- coordonnées et horaires du support ;
- devise et pays par défaut ;
- état de configuration des services externes sans affichage des clés ;
- activation globale des e-mails opérationnels, SMS, WhatsApp et nouveaux checkouts KPrimePay ;
- délais configurables des invitations, codes 2FA et paiements ;
- mode maintenance applicatif avec message personnalisable ;
- console SaaS et webhooks API maintenus accessibles pendant la maintenance ;
- confirmation par mot de passe, motif, historique et audit pour chaque changement ;
- cache de configuration invalidé immédiatement après enregistrement ;
- composants Blade hérités restaurés : `php artisan view:cache` fonctionne désormais.

## Déploiement et exploitation

Commandes principales pour une nouvelle installation :

```bash
php artisan migrate --force
php artisan platform-admin:create --from-user=pakpalididier@gmail.com
php artisan optimize:clear
```

Le cron serveur doit exécuter `php artisan schedule:run` chaque minute afin d’alimenter le heartbeat et les tâches planifiées.

## Vérifications manuelles recommandées

1. Tester la connexion plateforme et le changement obligatoire du mot de passe.
2. Contrôler les statistiques globales et les listes sur ordinateur et mobile.
3. Rechercher une entreprise, la suspendre avec un motif, vérifier son blocage puis la réactiver.
4. Contrôler un utilisateur rattaché à plusieurs entreprises et ses rôles.
5. Vérifier un paiement en attente auprès de KPrimePay et contrôler l’audit.
6. Modifier temporairement un tarif, vérifier le nouveau checkout, puis restaurer la valeur souhaitée.
7. Contrôler le cron, les queues, les communications et la relance d’un job de test corrigé.
8. Tester séparément les accès Support, Finance et Technique, y compris une URL interdite.

## Suite après migration production

- surveillance de la console en production après migration : erreurs, queues, cron, temps de réponse et alertes ;
- revue périodique des seuils d’exploitation et des droits des rôles, sans nouveau blocage de développement identifié.

## Historique des mises à jour

- **11 septembre 2026** : amélioration du parcours de trésorerie : liste lisible des comptes Mobile Money masqués, contrôle serveur empêchant le doublon d’un numéro pour un même administrateur et formulaires responsive séparant clairement bénéficiaire et sortie de trésorerie.
- **11 septembre 2026** : ajout de la console « Trésorerie & retraits ». Elle consolide les encaissements confirmés de quotas et d’abonnements, sépare les engagements partenaires de la capacité administrateur et réserve toute action de sortie au super-administrateur. Les comptes Mobile Money admin sont chiffrés, masqués et confirmés par code e-mail ; une demande nécessite aussi le mot de passe plateforme et un second code. Les transferts empruntent `from-collection-balance`, une clé d’idempotence et la queue dédiée ; leur règlement attend systématiquement `credit-status`. Le rôle Finance est explicitement en lecture seule. Aucune donnée POS n’a été modifiée lors des tests : la migration a été validée sur `pos_testing`.
- **11 septembre 2026** : le checkout d’abonnement exige désormais l’acceptation visible des termes avant initialisation : redirection vers KPrimePay et frais éventuels du moyen de paiement indépendants de MAXANOU. Le bouton est désactivé sans case cochée et la validation serveur bloque également les requêtes directes.

- **28 août 2026** : consolidation de toute l’administration SaaS dans ce rapport permanent ; accès sécurisé, tableaux globaux, entreprises, utilisateurs, paiements, rentabilité, audit, supervision et rôles administratifs documentés.
- **28 août 2026** : ajout de la double authentification e-mail, de la récupération sécurisée du mot de passe, de la révocation des anciennes sessions et de la réinitialisation 2FA auditée par le super-administrateur.
- **28 août 2026** : ajout des alertes automatiques d’exploitation, des seuils configurables, des destinataires, de l’anti-spam et du cycle de prise en charge/résolution.
- **28 août 2026** : ajout du module global Communications avec statistiques, filtres, masquage, consommation par entreprise, exports et relances contrôlées.
- **28 août 2026** : ajout des paramètres généraux, de l’identité dynamique, des interrupteurs de services, des délais de sécurité, du mode maintenance et correction du cache Blade.
# Mise à jour du 3 septembre 2026 — pré-contrôle abonnement plateforme

- La console SaaS dispose de la page super-administrateur `platform/subscriptions/preflight` (« Abonnements » dans Monétisation). Elle est strictement en lecture seule : aucune action de cette page ne modifie un plan, un prix, une souscription, un paiement ou un quota.
- Le pré-contrôle affiche l’état effectif de l’enforcement, la disponibilité logique de KPrimePay sans jamais révéler de secret, les comptes facturation, abonnements en cours/expirés/proches de l’échéance, paiements en attente et paiements expirés à réconcilier.
- Le catalogue des plans est exposé uniquement comme contrôle : version, prix, limites et fonctionnalités. La règle financière est explicitée : un engagement déjà souscrit reste fondé sur son snapshot ; une évolution commerciale devra créer une nouvelle version au lieu de réécrire un prix existant.
- Le réglage « Vérifier les abonnements avant les accès métier » reste dans Paramètres généraux, désactivé par défaut pour le développement local. Le pré-contrôle y renvoie mais ne peut pas l’activer lui-même.
- Autorisation : la route est protégée par `platform.admins.manage` ; un administrateur Finance ne peut pas y accéder. Test : `PlatformSubscriptionPreflightTest` vérifie l’accès super-administrateur, l’absence de fuite du token KPrimePay et le refus du rôle Finance.
- Validation du lot : `php artisan test tests/Feature/PlatformSubscriptionPreflightTest.php tests/Feature/SubscriptionAccessTest.php tests/Feature/SubscriptionWebhookTest.php --no-coverage` — **10 tests, 45 assertions, 0 échec** ; `php artisan view:cache` et `git diff --check` passent.
- Recette visuelle locale authentifiée : le pré-contrôle a été ouvert avec une session super-administrateur ; aucun débordement horizontal à 1440, 1024, 768 et 390 px, et le déclencheur de navigation mobile apparaît bien aux deux petites largeurs. Aucun avertissement ni erreur console détecté. Aucun formulaire ni paiement n’a été soumis.

# Mise à jour du 3 septembre 2026 — catalogue abonnement versionné

- La console plateforme fournit maintenant `platform/subscriptions/catalog`, accessible uniquement au super-administrateur. Elle ne permet jamais de modifier un plan déjà souscrit.
- Une évolution crée une version brouillon identifiée par une clé distincte (`bronze-v2`, par exemple), avec les prix, limites, quotas et fonctionnalités de la nouvelle offre. Le serveur impose le tarif annuel à exactement onze mensualités et exige motif + mot de passe plateforme.
- La publication est une action distincte avec nouveau motif et mot de passe. Elle rend la version brouillon disponible pour les futurs checkouts et masque les versions précédentes de la même famille ; les abonnements, paiements et snapshots existants restent inchangés.
- Chaque création et publication est inscrite dans `platform_audit_logs`. Les rôles non super-administrateurs sont refusés et les routes sont limitées à cinq tentatives par minute.
- Validation : `PlatformSubscriptionCatalogTest` couvre la création sans mutation de la source, la publication, la validation du prix annuel et le refus du rôle Finance : **4 tests, 20 assertions, 0 échec**. Recette locale authentifiée sans soumission à 1440/1024/768/390 px : aucun débordement ni erreur console.
- Reprise technique : garder `subscriptions.enforcement_enabled` désactivé tant que les rappels réels, le parcours KPrimePay sûr et la suite complète sur une base de test stabilisée ne sont pas validés.

# Mise à jour du 3 septembre 2026 — expiration abonnement

- La commande planifiée `subscriptions:expire` expire les abonnements arrivés à échéance et enregistre les jalons J-3/J-2/J-1 ainsi que l’expiration dans `subscription_events`.
- Ce mécanisme est strictement journalisé : il n’envoie pas encore de message externe. Son idempotence est validée par `SubscriptionExpiryCommandTest`, y compris lors de deux exécutions consécutives.
- Les canaux réels ne devront être activés qu’après définition du consentement, des destinataires, des horaires et des contenus approuvés.

# Mise à jour du 3 septembre 2026 — rappels e-mail abonnement

- Le canal retenu est l’e-mail au propriétaire du compte de facturation et aux administrateurs actifs de la compagnie facturée.
- Le réglage plateforme `services.email.enabled` est respecté. L’état d’envoi est conservé par destinataire dans l’événement d’abonnement afin d’éviter les renvois en cas de rejeu ; les échecs restent traçables.
- `SubscriptionExpiryNotification` utilise un contenu distinct pour les rappels J-3/J-2/J-1 et l’expiration, avec accès direct au menu Abonnement. Aucun SMS ou WhatsApp n’est utilisé.
- Le branchement technique est testé avec `SubscriptionExpiryCommandTest` : **2 tests, 13 assertions, 0 échec**. Avant production, le relais SMTP, SPF/DKIM/DMARC, le fuseau et la politique de consentement doivent être validés.

# Mise à jour du 3 septembre 2026 — durée flexible des plans

- Le client peut sélectionner 1 à 12 mois sur chaque plan payant et voit immédiatement le montant total ainsi que l’expiration estimée.
- Aucune remise n’est appliquée de 1 à 11 mois. La remise annuelle est appliquée uniquement à 12 mois, avec 12 mois d’accès et 11 mensualités facturées.
- Le serveur recalcule les valeurs, enregistre la durée dans le paiement et l’abonnement, et conserve les anciennes périodicités pour compatibilité. Aucun montant affiché côté navigateur n’est considéré comme une preuve de paiement.
- La migration `2026_09_03_210000_add_subscription_duration_months` est appliquée sur la base locale.
- La sélection de durée est regroupée dans la fenêtre de confirmation du plan : le bouton « Choisir la durée » ouvre le sélecteur 1–12 mois, avec montant, réduction et date recalculés avant validation. Le nom du prestataire de paiement n’est pas exposé dans cette interface.
- Pour limiter le risque financier, la durée initiale proposée est 1 mois ; l’engagement de 12 mois et sa remise nécessitent une sélection explicite.
- Le sélecteur de durée du modal utilise désormais une liste tactile explicitement scrollable sur mobile (2 colonnes, hauteur limitée et `overflow-y: auto`) afin que les 12 durées restent accessibles dans les WebView qui tronquent les listes natives.
- Après confirmation serveur d’un paiement d’abonnement, tous les administrateurs plateforme actifs reçoivent un e-mail détaillé (`SubscriptionActivatedNotification`) : entreprise, plan, durée, montant/devise, période, transaction et référence de paiement. L’envoi respecte `services.email.enabled`, est séparé de la transaction financière et est idempotent par destinataire dans `subscription_events`.
- Validation complémentaire : les tests abonnement ciblés passent (**14 tests, 72 assertions, 0 échec**) et `php artisan view:cache` est valide. La recette navigateur responsive du modal a depuis été effectuée sur mobile et desktop ; l’activation production reste conditionnée aux secrets, à la supervision et à l’enforcement progressif.

# Mise à jour du 3 septembre 2026 — notification des paiements de quotas

- Après confirmation serveur d’un paiement SMS/WhatsApp, les administrateurs plateforme actifs reçoivent `QuotaPaymentConfirmedNotification` avec l’entreprise, l’acheteur, les quantités créditées, le montant/devise, la transaction, la référence et la date de confirmation.
- L’envoi est effectué après le crédit des quotas et respecte `services.email.enabled`. Les états par administrateur sont conservés dans `quota_payments.administration_email_status` pour empêcher les doublons lors des webhooks rejoués.
- Migration appliquée : `2026_09_03_220000_add_administration_email_status_to_quota_payments`. `QuotaPaymentTest` passe : **6 tests, 51 assertions, 0 échec**.

# Validation staging — abonnements, quotas et notifications

- Le propriétaire confirme le bon fonctionnement en staging du checkout de test, des retours webhook KPrimePay, du SMTP réel et de la recette visuelle mobile/desktop.
- Le développement fonctionnel des abonnements et des notifications de paiements est considéré terminé. La mise en production reste conditionnée uniquement au renseignement contrôlé des secrets/URLs de production, à la supervision et à l’activation progressive de l’enforcement.

# Mise à jour du 10 septembre 2026 — activation des retraits partenaires

- La page **Administration SaaS > Paramètres > Partenaires** expose désormais l’interrupteur audité `partners.payouts_enabled` dans la section « Garde-fou / Retraits partenaires ».
- L’état opérationnel (activé/désactivé), les seuils, la revue de risque et les opérateurs disponibles sont regroupés dans un panneau SaaS responsive. Toute modification exige un motif et le mot de passe plateforme, puis est enregistrée dans `platform_audit_logs` et `platform_settings_history`.
- Le réglage reste désactivé par défaut ; son activation ne supprime aucun contrôle partenaire (solde, seuil, compte Mobile Money vérifié, 2FA et revue de risque).
- Validation : `PlatformPartnerSettingTest` passe (**6 tests, 25 assertions**) et les vues Blade/lint UI sont valides.
