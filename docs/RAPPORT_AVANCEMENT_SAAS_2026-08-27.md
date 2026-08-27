# Rapport d’avancement SaaS — 27 août 2026

## Synthèse

Le POS est devenu un SaaS multi-entreprises fonctionnel et validé sur le staging O2switch. Le cœur métier, l’isolation des compagnies, les rôles, le POS, l’E-commerce, les notifications, la PWA mobile et l’achat réel de quotas sont opérationnels. Les essais multi-compagnies, le paiement KPrimePay réel et l’absence de double crédit webhook ont été confirmés manuellement. Le travail restant concerne principalement l’exploitation, la sauvegarde, le pilote et la future couche d’abonnements.

| Axe | Avancement | Situation actuelle |
|---|---:|---|
| Migration fonctionnelle SaaS | **98 %** | Socle multi-compagnies validé sur staging |
| Isolation et sécurité multi-tenant | **97 %** | Tests automatisés et essais réels multi-compagnies validés |
| Utilisateurs, rôles et permissions | **97 %** | Rôle distinct par compagnie, invitations et changement de contexte |
| POS, ventes, stock et caisses | **97 %** | Parcours métier transactionnels, historique filtrable et classement produits |
| E-commerce multi-boutiques | **96 %** | Catalogue, recherche instantanée, commande et conversion en vente |
| Notifications et communications | **97 %** | Destinataires, canaux, quotas, pagination, anti-doublon et identité de compagnie |
| Paiement des quotas KPrimePay | **99 %** | Paiement réel, webhooks V1/V2, anti-doublon et réconciliation automatique |
| PWA et expérience mobile | **98 %** | Installation et connexion mobile validées sur staging |
| Performance et qualité locale | **97 %** | 147 tests, benchmarks, pagination SQL et exports volumineux |
| Préparation production SaaS | **96 %** | Staging, sauvegardes, supervision et délivrabilité validés ; pilote restant |
| Abonnements SaaS | **0 %** | Volontairement différés jusqu’à définition des plans |

Avancement général avant abonnements : **environ 98 % fonctionnel**. Préparation à une production commerciale : **environ 96 %**.

## Preuves de validation

- Suite complète actualisée le 27 août après la réconciliation KPrimePay : **147 tests réussis, 880 assertions, 0 échec**.
- Benchmarks séparés disponibles pour gros volume MySQL, concurrence des ventes, charge des notifications et limites PDF.
- Exports CSV et vrais classeurs Excel `.xlsx` disponibles pour Produits, Inventaire et Historique des ventes.
- Laravel Excel est installé sur le staging O2switch et l’export y a été confirmé fonctionnel.
- Le staging O2switch a été validé fonctionnellement avec plusieurs compagnies, une PWA mobile opérationnelle et un paiement KPrimePay réel sans double crédit webhook.
- Le propriétaire confirme également un test complet de sauvegarde/restauration, une surveillance des queues et tâches cron sur plusieurs jours, ainsi que la validation de la délivrabilité e-mail avec SPF, DKIM et DMARC.

## Fonctions SaaS terminées

- Un utilisateur peut appartenir à plusieurs compagnies et changer de contexte.
- Chaque adhésion possède son rôle et ses permissions dans la compagnie concernée.
- Les menus, routes, réponses AJAX et données suivent les permissions et la compagnie active.
- L’inscription crée le propriétaire, sa compagnie, les rôles initiaux, la caisse principale et la caisse de taxe.
- Les invitations couvrent nouvel utilisateur, utilisateur existant, expiration, renvoi, refus et révocation.
- Les compagnies portant le même nom disposent de slugs publics distincts et personnalisables.
- Les données métier critiques sont tenantées et protégées contre les relations inter-compagnies.
- Le loader serveur global bloque les doubles clics pendant les actions asynchrones.

## POS et E-commerce

- Les ventes diminuent le stock et mettent à jour les caisses dans une transaction.
- Les paniers POS sont conservés localement par compagnie après rechargement.
- Une commande E-commerce ne diminue pas le stock avant sa conversion en vente.
- Une commande peut être exécutée en vente ou annulée avec motif.
- La localisation Google Maps/GPS du client est validée côté serveur.
- La boutique publique est paginée et tenantée.
- La recherche globale E-commerce affiche des suggestions dès deux caractères, avec temporisation et annulation des anciennes requêtes.

## Notifications, e-mails et factures

- Les destinataires sont gérés par compagnie, catégorie et canal.
- Les canaux e-mail, WhatsApp et SMS sont activables séparément pour ventes et inventaire.
- Le registre `notification_deliveries` évite les doublons après reprise d’un job.
- Les e-mails présentent la compagnie concernée comme nom d’expéditeur visible.
- Le footer mentionne la compagnie comme émetteur métier et réserve le nom de l’application au copyright dynamique.
- Les boutons d’e-mail utilisent un texte blanc renforcé pour Gmail et Outlook.
- Les derniers tests couvrent également l’envoi des factures par les canaux autorisés et la consommation des quotas.
- Le menu `Communications > SMS & WhatsApp` centralise Configuration, Quota et Consommation.
- Les alertes internes et l’envoi des factures clients sont clairement séparés dans la configuration.
- Les notifications WhatsApp proactives de ventes et d’inventaire utilisent le modèle fournisseur approprié.
- Le SMS d’inventaire possède un contenu compact accepté par le fournisseur, tandis que WhatsApp conserve le détail complet.
- L’historique de consommation est paginé à 10 lignes par défaut avec choix 10/25/50 et conservation des filtres.

## Historique des ventes et analyse

- Le filtre de l’historique est regroupé dans un panneau repliable responsive.
- Une période, un client et un fournisseur peuvent être combinés.
- Le tableau, les indicateurs, les exports et le classement des produits partagent exactement le même périmètre.
- Le filtre fournisseur retient les ventes contenant ses produits et limite le classement aux produits concernés.
- La quantité totale vendue est calculée à partir des quantités, et non du simple nombre de lignes.

## Paiement KPrimePay

- Achat de SMS à 35 FCFA et WhatsApp à 30 FCFA, prix recalculés côté serveur.
- Permission dédiée `quota.manage`.
- Checkout KPrimePay V2 avec `Idempotency-Key`.
- Webhooks V1 et V2 reconnus.
- Toute confirmation est revérifiée auprès de KPrimePay avant crédit.
- Montant, devise, référence et transaction locale sont contrôlés.
- Le crédit des quotas est atomique et idempotent.
- Les checkouts expirés encore en attente sont revérifiés automatiquement toutes les dix minutes puis classés `paid`, `failed` ou `expired`.
- Le webhook et le scheduler partagent le même service atomique de règlement, empêchant un double crédit en cas de concurrence.
- Dans la PWA, le paiement s’ouvre dans une fenêtre séparée ; la page surveille le statut local, ferme la fenêtre et se recharge après confirmation.
- Une redirection complète reste disponible si les fenêtres sont bloquées.

## Point technique immédiat

Toutes les migrations présentes dans le dépôt sont désormais **appliquées dans la base locale**, y compris les réglages de facture, les codes pays et le journal de consommation. Le staging doit être contrôlé séparément après chaque livraison :

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan queue:restart
```

Le statut local ne prouve pas leur application sur O2switch. Toujours exécuter `php artisan migrate:status` sur le staging.

## Ce qui reste avant la production commerciale

1. Vérifier et archiver le statut des migrations à chaque livraison staging/production.
2. Formaliser une procédure simple pour les erreurs, jobs échoués, quotas anormaux et paiements bloqués.
3. Valider manuellement sur staging les scénarios KPrimePay abandonné, refusé et confirmé tardivement désormais couverts automatiquement côté serveur.
4. Réaliser un pilote limité avec quelques entreprises avant l’ouverture générale.
5. Définir ultérieurement plans, prix, essais et règles d’abonnement.

## Prochaine étape recommandée

La prochaine phase est le **pilote limité** et la formalisation de la procédure d’incident. Les sauvegardes/restaurations, la supervision prolongée des tâches et la délivrabilité e-mail sont confirmées.

Le projet est désormais suffisamment avancé pour entrer dans un pilote limité. L’ouverture commerciale à grande échelle reste conditionnée par la validation des sauvegardes, de la supervision et du retour du pilote.
