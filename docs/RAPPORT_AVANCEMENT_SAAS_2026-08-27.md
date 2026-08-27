# Rapport d’avancement SaaS — 27 août 2026

## Synthèse

Le POS est devenu un SaaS multi-entreprises fonctionnel et testable sur le staging O2switch. Le cœur métier, l’isolation des compagnies, les rôles, le POS, l’E-commerce, les notifications et l’achat de quotas sont opérationnels. Le travail restant concerne principalement la validation complète du staging, l’exploitation O2switch et la future couche d’abonnements.

| Axe | Avancement | Situation actuelle |
|---|---:|---|
| Migration fonctionnelle SaaS | **97 %** | Socle multi-compagnies opérationnel |
| Isolation et sécurité multi-tenant | **95 %** | Scopes, permissions, policies et contraintes SQL en place |
| Utilisateurs, rôles et permissions | **97 %** | Rôle distinct par compagnie, invitations et changement de contexte |
| POS, ventes, stock et caisses | **96 %** | Parcours métier transactionnels et optimisés |
| E-commerce multi-boutiques | **96 %** | Catalogue, recherche instantanée, commande et conversion en vente |
| Notifications et communications | **95 %** | Destinataires, canaux, quotas, anti-doublon et identité de compagnie |
| Paiement des quotas KPrimePay | **92 %** | Checkout réel, webhooks V1/V2 et crédit atomique validés |
| PWA et expérience mobile | **90 %** | Installation et parcours adaptés ; recette staging mobile à terminer |
| Performance et qualité locale | **96 %** | Tests, benchmarks, pagination SQL et exports volumineux |
| Préparation production SaaS | **82 %** | Staging actif ; exploitation, sauvegarde et recette finale restantes |
| Abonnements SaaS | **0 %** | Volontairement différés jusqu’à définition des plans |

Avancement général avant abonnements : **environ 95 % fonctionnel**. Préparation à une production commerciale : **environ 82 %**.

## Preuves de validation

- Suite complète après correction de la connexion PWA : **143 tests réussis, 824 assertions, 0 échec**.
- Benchmarks séparés disponibles pour gros volume MySQL, concurrence des ventes, charge des notifications et limites PDF.
- Exports CSV et vrais classeurs Excel `.xlsx` disponibles pour Produits, Inventaire et Historique des ventes.
- Laravel Excel est installé sur le staging O2switch et l’export y a été confirmé fonctionnel.
- Le staging O2switch est désormais en cours de validation ; il ne doit pas encore être assimilé à la production commerciale.

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

## Paiement KPrimePay

- Achat de SMS à 35 FCFA et WhatsApp à 30 FCFA, prix recalculés côté serveur.
- Permission dédiée `quota.manage`.
- Checkout KPrimePay V2 avec `Idempotency-Key`.
- Webhooks V1 et V2 reconnus.
- Toute confirmation est revérifiée auprès de KPrimePay avant crédit.
- Montant, devise, référence et transaction locale sont contrôlés.
- Le crédit des quotas est atomique et idempotent.
- Dans la PWA, le paiement s’ouvre dans une fenêtre séparée ; la page surveille le statut local, ferme la fenêtre et se recharge après confirmation.
- Une redirection complète reste disponible si les fenêtres sont bloquées.

## Point technique immédiat

Les migrations suivantes sont présentes dans le code mais encore **en attente dans la base locale contrôlée le 27 août** :

- `2026_08_26_140000_add_invoice_delivery_settings_and_client_phone` ;
- `2026_08_27_100000_add_country_codes_for_messaging` ;
- `2026_08_27_110000_create_communication_logs_and_permission`.

Avant de poursuivre les tests manuels liés aux factures et communications, sauvegarder la base puis exécuter localement :

```bash
php artisan migrate
php artisan migrate:status
```

Sur le staging, utiliser uniquement après sauvegarde :

```bash
php artisan migrate --force
php artisan optimize:clear
```

Vérifier séparément le statut des migrations du staging : le statut local ne prouve pas leur application sur O2switch.

## Ce qui reste avant la production commerciale

1. Appliquer et vérifier les trois migrations récentes en local puis sur le staging sauvegardé.
2. Terminer une recette fonctionnelle complète sur le staging avec au moins deux compagnies et plusieurs rôles.
3. Résoudre et valider définitivement la connexion depuis la PWA mobile du staging, y compris cookies, domaine de session et HTTPS.
4. Tester les crons du scheduler et de la queue pendant plusieurs heures avec de vrais e-mails, SMS et WhatsApp.
5. Vérifier KPrimePay en situation réelle : succès, échec, abandon, double webhook et retour tardif.
6. Tester sauvegarde et restauration MySQL ainsi que `storage/app`.
7. Vérifier SMTP, SPF, DKIM, DMARC et le nom d’expéditeur des différentes compagnies.
8. Contrôler les journaux d’erreurs, jobs échoués et requêtes lentes sur O2switch.
9. Réaliser un pilote limité avant ouverture générale.
10. Définir ultérieurement plans, prix, essais et règles d’abonnement.

## Prochaine étape recommandée

La prochaine phase est la **recette staging complète**, en commençant par les migrations en attente puis le parcours suivant : connexion mobile, changement de compagnie, vente, facture e-mail/SMS/WhatsApp, inventaire, commande E-commerce, paiement de quotas KPrimePay et contrôle des journaux/queues.

Le projet peut entrer en pilote après validation de cette recette, des sauvegardes et des tâches planifiées. Il n’est pas encore recommandé de l’ouvrir commercialement à grande échelle.
