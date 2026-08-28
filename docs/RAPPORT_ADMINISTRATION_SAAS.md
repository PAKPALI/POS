# Rapport permanent — Administration SaaS

Dernière mise à jour : 28 août 2026 — sécurisation avancée des comptes plateforme

## Règle de suivi

Ce fichier est l’unique rapport d’avancement de la partie administrative SaaS. Toute évolution de la console centrale doit être ajoutée ici après son implémentation et sa vérification. Aucun nouveau rapport séparé par phase ne doit être créé.

## État actuel

La console d’administration centrale est opérationnelle et séparée des comptes `owner` et `admin` propres aux entreprises. Elle est accessible par `/admin-saas` ou `/platform/login`.

Dernière non-régression complète connue : **185 tests, 1 109 assertions, 0 échec**.

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

## Améliorations suivantes envisagées

- recette complète de la console sur staging avec chaque rôle ;
- alertes d’exploitation automatiques selon des seuils définis.

## Historique des mises à jour

- **28 août 2026** : consolidation de toute l’administration SaaS dans ce rapport permanent ; accès sécurisé, tableaux globaux, entreprises, utilisateurs, paiements, rentabilité, audit, supervision et rôles administratifs documentés.
- **28 août 2026** : ajout de la double authentification e-mail, de la récupération sécurisée du mot de passe, de la révocation des anciennes sessions et de la réinitialisation 2FA auditée par le super-administrateur.
- **28 août 2026** : ajout des alertes automatiques d’exploitation, des seuils configurables, des destinataires, de l’anti-spam et du cycle de prise en charge/résolution.
- **28 août 2026** : ajout du module global Communications avec statistiques, filtres, masquage, consommation par entreprise, exports et relances contrôlées.
- **28 août 2026** : ajout des paramètres généraux, de l’identité dynamique, des interrupteurs de services, des délais de sécurité, du mode maintenance et correction du cache Blade.
