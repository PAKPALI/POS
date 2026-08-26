# Intégration KPrimePay — checkout V2 et webhooks V1/V2

Date : 26 août 2026

## Fonctionnement

Un utilisateur disposant de la permission `quota.manage` choisit une quantité de SMS et/ou de messages WhatsApp. Le serveur calcule le prix sans faire confiance au navigateur :

- SMS : 35 FCFA l’unité ;
- WhatsApp : 30 FCFA l’unité.

Le serveur crée un `quota_payment`, envoie une requête idempotente à `POST /v2/checkout`, puis redirige le navigateur vers le checkout hébergé par KPrimePay. Le retour du navigateur ne crédite jamais les quotas.

Le callback accepte le webhook V2 (`collection.succeeded`) et le format V1 actuellement envoyé par KPrimePay (`status: success`, `payment_status: TRANSACTION-COMPLETED`). Dans les deux cas, le système appelle `POST /v2/transactions/debit-status` avec la clé serveur et vérifie le statut, la devise et les montants attendus avant toute modification.

Le paiement est verrouillé en transaction SQL. Un webhook répété ne crédite jamais deux fois les quotas.

## Configuration `.env`

```dotenv
KPRIMEPAY_BASE_URL=https://api.kprimepay.com/v2
KPRIMEPAY_TOKEN=nouvelle_cle_secrete_regeneree
KPRIMEPAY_MODE=1
KPRIMEPAY_WITH_FEES=1
KPRIMEPAY_SMS_UNIT_PRICE=35
KPRIMEPAY_WHATSAPP_UNIT_PRICE=30
```

- Utiliser `KPRIMEPAY_MODE=1` pendant les essais et `2` uniquement en production.
- La clé doit posséder `payments:write` pour créer le checkout et `read` pour confirmer son statut.
- `KPRIMEPAY_WITH_FEES=1` fait supporter les frais de collecte au payeur.
- Ne jamais envoyer la clé au navigateur, la journaliser ou la committer dans Git.

Après toute modification :

```bash
php artisan config:clear
```

## Webhook

Dans le tableau de bord KPrimePay, configurer :

```text
https://VOTRE-DOMAINE/api/kprimepay/webhook
```

Exemple si le domaine final est `pos.exemple.com` :

```text
https://pos.exemple.com/api/kprimepay/webhook
```

Cette URL est publique, limitée à 120 requêtes par minute et n’utilise pas de session utilisateur. Elle contrôle strictement les en-têtes du format V2. Pour la V1, elle exige `object: payment` et `type: payment.web.checkout`, fabrique une empreinte stable anti-doublon, retrouve uniquement une transaction créée localement et reconfirme le paiement auprès de l’API KPrimePay. Tout autre format reçoit `INVALID_WEBHOOK`.

Une URL `127.0.0.1` ou `localhost` n’est pas joignable par KPrimePay. Pour un test local réel, utiliser un tunnel HTTPS temporaire et enregistrer son URL dans le tableau de bord KPrimePay.

## Retour client

KPrimePay renvoie le navigateur vers :

```text
https://VOTRE-DOMAINE/setting/sms-quota/return
```

Cette page indique seulement que la confirmation est en attente. Elle n’accorde aucun quota, car un client pourrait appeler lui-même une URL de retour.

## Sécurité et reprise

- `transaction_id` et `Idempotency-Key` sont uniques.
- `event_id` est unique lorsqu’il est enregistré.
- Le montant attendu est enregistré avant l’appel au fournisseur.
- Le statut distant doit être `success`, la devise `XOF` et les montants local, webhook et API doivent correspondre.
- En V1, le succès exige simultanément `status: success` et `payment_status: TRANSACTION-COMPLETED`.
- En V2, l'identifiant d'événement KPrimePay sert à l'idempotence ; en V1, une empreinte déterministe est enregistrée comme `event_id`.
- Le crédit SMS/WhatsApp et le passage du paiement à `paid` sont atomiques.
- Un événement `collection.failed` marque la tentative échouée sans modifier les quotas.
- L’historique est isolé par compagnie.

## Test manuel

1. Régénérer la clé exposée et lui attribuer `payments:write` et `read`.
2. Configurer les variables d’environnement en mode test.
3. Attribuer `quota.manage` au rôle voulu.
4. Ouvrir **Quotas SMS & WhatsApp**.
5. Saisir 2 SMS et 3 WhatsApp : le montant doit être 160 FCFA.
6. Cliquer sur le paiement et vérifier le loader puis la redirection KPrimePay.
7. Terminer le paiement et attendre le webhook.
8. Recharger la page : les quotas doivent augmenter de 2 et 3, avec un historique `Payé`.
9. Rejouer le même webhook : les nombres ne doivent pas augmenter une seconde fois.

## Validation automatique

- Checkout, bearer token et idempotence contrôlés avec un fournisseur simulé.
- Confirmation distante et correspondance de montant contrôlées.
- Crédit simultané SMS/WhatsApp et répétition du webhook contrôlés.
- Permission dédiée contrôlée.
- Suite complète : **136 tests, 773 assertions, 0 échec**.
