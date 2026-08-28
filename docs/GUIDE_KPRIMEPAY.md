# Guide permanent — KPrimePay, quotas et incidents

## Fonctionnement

Une entreprise achète des quotas SMS et WhatsApp depuis le checkout KPrimePay. Le navigateur ne crédite jamais les quotas : seul un webhook vérifié ou la réconciliation serveur peut confirmer le paiement.

Prix de référence actuels : 35 XOF par SMS et 30 XOF par WhatsApp. Les prix réellement appliqués sont mémorisés sur chaque paiement afin de garantir la non-rétroactivité.

## Configuration

```env
KPRIMEPAY_BASE_URL=https://api.kprimepay.com/v2
KPRIMEPAY_TOKEN=
KPRIMEPAY_MODE=2
KPRIMEPAY_WITH_FEES=1
KPRIMEPAY_SMS_UNIT_PRICE=35
KPRIMEPAY_WHATSAPP_UNIT_PRICE=30
```

La clé doit posséder les droits `payments:write` et `read`. Elle ne doit jamais être committée, affichée dans l’administration ou transmise dans les journaux.

Webhook public :

```text
POST /api/kprimepay/webhook
```

## Checkout et retour client

- création d’une transaction interne unique ;
- envoi de l’identifiant de paiement et de l’entreprise dans les métadonnées ;
- fenêtre de paiement séparée pour préserver l’expérience PWA ;
- polling local du statut toutes les trois secondes ;
- fermeture de la fenêtre et actualisation après confirmation ;
- redirection complète de secours lorsque les pop-ups sont bloquées.

L’URL de retour n’est jamais une preuve de paiement.

## Webhooks V1 et V2

Le contrôleur accepte le format V2 et le format V1 `payment.web.checkout`. Chaque événement est normalisé puis reconfirmé auprès de l’API KPrimePay. Le montant, la devise, le statut et la transaction interne doivent correspondre.

Protections contre les doublons :

- `transaction_id` unique ;
- `idempotency_key` unique ;
- `event_id` unique ;
- empreinte SHA-256 stable générée lorsque la V1 ne fournit aucun `event_id` ;
- transaction SQL et verrouillage avant crédit.

## Paiement abandonné

Le paiement reste `pending` jusqu’à son expiration. Aucun quota n’est ajouté. La réconciliation automatique le vérifie ensuite chez KPrimePay et le classe selon la réponse réelle.

## Paiement refusé

Le statut devient `failed`, la raison est conservée sans exposer de secret et aucun quota n’est crédité. L’utilisateur peut créer une nouvelle transaction.

## Confirmation tardive

La commande `payments:reconcile-kprimepay` interroge KPrimePay pour les paiements expirés encore en attente. Si le fournisseur confirme finalement le succès, le service atomique crédite les quotas une seule fois.

Planification recommandée :

```text
payments:reconcile-kprimepay --limit=100
```

toutes les dix minutes par le planificateur Laravel.

## Diagnostic d’un paiement bloqué

1. Rechercher la transaction dans **Administration SaaS > Paiements & quotas**.
2. Vérifier transaction, montant, devise, entreprise et statut.
3. Utiliser la réconciliation contrôlée avec un motif.
4. Consulter le journal d’audit et `storage/logs/laravel.log`.
5. Ne jamais modifier directement les quotas ou marquer manuellement le paiement comme payé.

## Jobs échoués

```bash
php artisan queue:failed
php artisan queue:retry UUID_DU_JOB
```

Avant une relance : identifier la cause, corriger la configuration ou la donnée, confirmer que l’opération est idempotente, puis relancer uniquement le job concerné. Ne pas utiliser `queue:retry all` sans analyse.

La console SaaS propose également les jobs échoués, les communications relançables et les alertes opérationnelles avec motif et audit.

## Tests manuels

1. Checkout abandonné sans paiement.
2. Paiement explicitement refusé.
3. Paiement confirmé normalement par webhook.
4. Rejeu du même webhook sans double crédit.
5. Confirmation tardive récupérée par réconciliation.
6. Montant ou devise incorrects refusés.
7. Fenêtre PWA fermée avant la confirmation puis statut retrouvé au rechargement.
8. Job échoué relancé après correction de sa cause.

## Informations d’incident à conserver

- date et heure ;
- environnement ;
- transaction interne et référence KPrimePay ;
- entreprise concernée ;
- statut local et statut fournisseur ;
- montant et devise ;
- action effectuée et administrateur ;
- résultat de la réconciliation ;
- UUID du job, exception résumée et nombre de tentatives.
