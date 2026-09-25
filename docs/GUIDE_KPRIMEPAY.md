# Guide permanent — KPrimePay, quotas et incidents

Dernière mise à jour : 25 septembre 2026 — synchronisation avec les évolutions KPrimePay API v2 et l’échéance de fin de l’API v1.

La confirmation concerne le parcours principal de monétisation. Les scénarios d’échec, de webhook rejoué, de paiement tardif, de réconciliation, de queue et de reprise restent à contrôler avec les preuves indiquées dans ce guide.

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

### Retraits partenaires depuis la balance de collecte

Les retraits utilisent une clé dédiée, distincte de la clé checkout ci-dessus :

```env
KPRIMEPAY_PAYOUT_BASE_URL=https://api.kprimepay.com/v2
KPRIMEPAY_PAYOUT_TOKEN=
KPRIMEPAY_PAYOUT_WITH_FEES=0
KPRIMEPAY_PAYOUT_CA_BUNDLE=D:\laragon\etc\ssl\cacert.pem
```

- La clé payout doit disposer de `payouts:write` et du droit de lecture du statut ; l’IP sortante doit être autorisée par KPrimePay.
- `KPRIMEPAY_PAYOUT_WITH_FEES=0` est requis pour la politique partenaire actuelle : aucun supplément technique n’est ajouté au bénéficiaire. Les frais propres à KPrimePay sont traités séparément et supportés par le partenaire.
- Le portail réserve un plafond de frais configuré dans **Programme partenaires**, puis régularise le portefeuille selon le coût réellement confirmé par KPrimePay. Configurez ce plafond au moins au tarif actif de l’opérateur ; l’excédent est rendu automatiquement.
- Cette configuration ne change pas `KPRIMEPAY_WITH_FEES`, qui reste réservé aux checkouts et quotas.

### Certificat SSL en local (Laragon)

Si PHP affiche `cURL error 60` parce qu’aucun bundle CA n’est déclaré, indiquez le certificat CA de Laragon dans `.env` :

```env
KPRIMEPAY_CA_BUNDLE=D:\laragon\etc\ssl\cacert.pem
```

La vérification SSL reste active. En staging et en production, installez le bundle CA dans PHP ou renseignez un chemin adapté à l’environnement ; n’utilisez pas `verify=false`.

Webhook public :

```text
POST /api/kprimepay/webhook
```

## Mise à jour fournisseur — API v2

La dernière notification KPrimePay confirme que l’API v2 est la version de référence pour les nouveaux encaissements et transferts. L’API v1 sera arrêtée définitivement le **30 septembre 2026**. Après cette date, aucun nouvel appel v1 ne doit être utilisé pour les encaissements ou les transferts.

Le projet utilise déjà `https://api.kprimepay.com/v2` pour les checkouts et les reversements. Le contrôleur de webhook conserve une normalisation de l’ancien format pour absorber les callbacks historiques pendant la transition, mais cela ne doit pas être interprété comme une autorisation de créer de nouveaux appels v1.

La mise à jour KPrimePay n’impose pas de changement pour les appels actuels qui n’utilisent pas les options ci-dessous. Ces options sont disponibles lorsque le produit souhaite enrichir ou restreindre la page de paiement.

### 1. Région et opérateurs supplémentaires

KPrimePay peut afficher sur sa page hébergée l’option `OTHER_REGION` pour un client situé hors de la couverture directe. Le client est dirigé vers le parcours sécurisé KPrimePay puis revient sur l’URL de retour de MAXANOU. Cette option est pilotée par KPrimePay et ne doit pas être forcée si elle n’est pas retournée comme disponible.

### 2. Préremplissage des informations client

Lorsque MAXANOU connaît déjà le client et que ces données peuvent être transmises dans le cadre du parcours concerné, le checkout v2 accepte un bloc `customer` facultatif :

```json
{
  "customer": {
    "full_name": "Awa Kodjo",
    "email": "awa.kodjo@example.com",
    "phone_number": "90010203"
  }
}
```

Le client peut corriger les informations sur la page KPrimePay. Le webhook et la vérification serveur restent la source de vérité : il faut utiliser les informations réellement validées par le client, et non supposer que le préremplissage a été accepté sans modification.

### 3. Choix des moyens de paiement

Le checkout v2 accepte aussi `payment_methods` pour limiter les moyens affichés :

```json
{
  "payment_methods": ["MIXX-YAS-TG", "CARD", "OTHER_REGION"]
}
```

Les valeurs ne doivent pas être inventées ni figées dans l’interface. Elles doivent être découvertes avec `GET /v2/gateways`, dans le champ `checkout_method`, puis filtrées selon la politique commerciale et le pays du parcours. Si `payment_methods` n’est pas envoyé, KPrimePay continue de présenter les moyens disponibles comme aujourd’hui.

Références fournisseur : [checkout API v2](https://developers.kprimepay.com/index?v=v2#checkout) et [catalogue dynamique des moyens de paiement](https://developers.kprimepay.com/index?v=v2#gateways).

### Décision d’intégration MAXANOU

Le flux actuel conserve le checkout v2 minimal et compatible : montant, devise, description, URL de retour et métadonnées internes. Le préremplissage `customer` et la restriction `payment_methods` restent des extensions optionnelles à activer dans un lot séparé, avec tests du payload, de l’affichage hébergé, du webhook et de la vérification du montant. Aucune liste d’opérateurs supplémentaire ne doit être ajoutée uniquement dans le front sans confirmation de `GET /v2/gateways`.

## Checkout et retour client

- création d’une transaction interne unique ;
- envoi de l’identifiant de paiement et de l’entreprise dans les métadonnées ;
- fenêtre de paiement séparée pour préserver l’expérience PWA ;
- polling local du statut toutes les trois secondes ;
- fermeture de la fenêtre et actualisation après confirmation ;
- redirection complète de secours lorsque les pop-ups sont bloquées.

L’URL de retour n’est jamais une preuve de paiement.

## Webhooks v2 et compatibilité historique v1

Le contrôleur traite prioritairement les événements v2 et conserve une compatibilité de lecture avec le format v1 `payment.web.checkout` pour les callbacks historiques. Chaque événement est normalisé puis reconfirmé auprès de l’API KPrimePay. Le montant, la devise, le statut et la transaction interne doivent correspondre. Après le 30 septembre 2026, tout nouvel appel v1 doit être considéré comme non supporté et signalé comme incident d’intégration.

Protections contre les doublons :

- `transaction_id` unique ;
- `idempotency_key` unique ;
- `event_id` unique ;
- empreinte SHA-256 stable conservée uniquement pour les callbacks historiques qui ne fournissent aucun `event_id` ;
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
