# Cahier d’architecture — Plateforme Partenaires Maxanou

**Statut :** architecture cible prête pour implémentation  
**Version :** 1.0 — 8 septembre 2026  
**Périmètre :** acquisition par code partenaire, réduction du premier abonnement, commissions récurrentes, portefeuille et retraits Mobile Money  
**Application hôte :** POS SaaS Maxanou — Laravel, MySQL, Blade, KPrimePay

---

## 1. Objet et résultat attendu

Maxanou doit permettre à une personne de devenir partenaire, de partager un code promotionnel unique et de recevoir une commission sur les abonnements des clients qu’elle apporte.

Le module doit garantir simultanément :

- un parcours partenaire autonome et distinct du POS ;
- une réduction de 10 % uniquement sur le premier abonnement payant du client attribué ;
- une attribution définitive du compte d’abonnement au partenaire après le premier paiement confirmé ;
- des commissions récurrentes, auditables et impossibles à créditer deux fois ;
- des retraits Mobile Money protégés par double authentification e-mail ;
- une administration permettant de contrôler les partenaires, commissions, retraits, risques et paramètres ;
- une intégration sans régression du règlement d’abonnement et des quotas déjà en production.

Ce document fixe les décisions d’architecture, les règles métier, le schéma de données, les flux financiers, les tests et le plan de livraison. Il ne constitue pas encore une autorisation de paiement ou de mise en production.

## 2. Décisions d’architecture

### 2.1 Architecture retenue : monolithe modulaire

La première version sera développée **dans le même projet Laravel et la même base MySQL que Maxanou**, sous forme d’un module métier isolé.

Le portail partenaire sera exposé sur un sous-domaine, par exemple :

```text
https://partners.maxanou.com
```

Le sous-domaine pointera vers le même déploiement Laravel. Il ne s’agit pas d’un second serveur ni d’une seconde application. La séparation est assurée par les routes, le garde d’authentification, les middlewares, les vues, les permissions et les tables partenaires.

Cette solution évite :

- les appels HTTP internes inutiles entre le POS et le portail ;
- la duplication des plans, paiements et comptes clients ;
- les problèmes de cohérence distribuée au moment d’un webhook ;
- le coût d’exploitation prématuré de deux applications.

La charge du portail partenaire ne ralentira pas mécaniquement les ventes du POS si les agrégats sont pré-calculés, les exports et e-mails sont mis en queue, les index sont présents et aucune requête graphique non bornée n’est exécutée.

### 2.2 Frontières du module

Créer un espace logique `Partner` :

```text
app/Domain/Partner/
  Actions/
  Contracts/
  Data/
  Events/
  Exceptions/
  Jobs/
  Listeners/
  Services/
  Support/

app/Http/Controllers/Partner/
app/Http/Middleware/Partner/
app/Models/Partner*.php
app/Notifications/Partner/
resources/views/partner/
tests/Feature/Partner/
tests/Unit/Partner/
```

Les noms peuvent être adaptés aux conventions actuelles, mais les règles financières ne doivent pas être dispersées dans les contrôleurs ou le JavaScript.

### 2.3 Authentification séparée

Le partenaire est une identité distincte de `users` et de `platform_admins` :

- modèle/table `partners` ;
- garde Laravel `partner` de type `session` ;
- provider `partners` ;
- broker de réinitialisation `partner_password_reset_tokens` ;
- clés de session préfixées `partner_*` ;
- middleware `partner.auth`, `partner.active`, `partner.2fa` ;
- cookies `Secure`, `HttpOnly`, `SameSite=Lax` en production ;
- régénération de session après connexion et 2FA.

Le domaine ne doit jamais être la seule barrière de sécurité. Une route partenaire vérifie toujours le garde partenaire ; une route d’administration vérifie toujours le garde plateforme et sa permission.

### 2.4 Pas d’API HTTP interne en V1

Le portail appelle directement les services métier Laravel. Les échanges entre abonnement et partenaires passent par des services, événements et jobs internes, dans la même transaction lorsque l’atomicité financière l’exige.

Une API versionnée ne sera créée que pour une application mobile, une extraction en microservice ou une intégration externe. Dans ce cas, les contrats définis ici serviront de frontière.

### 2.5 Possibilité d’extraction future

Une base/application partenaire séparée devient pertinente seulement si au moins un des critères suivants apparaît :

- équipe et cycle de déploiement réellement indépendants ;
- contraintes juridiques ou comptables imposant l’isolement ;
- charge partenaire dominant durablement la charge POS ;
- besoin de plusieurs produits sources de commissions ;
- nécessité d’une disponibilité indépendante.

L’extraction devra alors utiliser des événements transactionnels/outbox ; elle ne devra jamais reconstituer les commissions à partir de pages ou de callbacks navigateur.

## 3. Principes financiers non négociables

1. Tous les montants XOF sont stockés en **entiers**, jamais en flottants.
2. La base de données et le serveur sont la source de vérité ; le navigateur ne décide jamais d’un montant, taux, solde ou statut.
3. Tout mouvement est inscrit dans un **grand livre immuable**. Le solde est une projection, pas une valeur modifiable arbitrairement.
4. Tout appel KPrimePay qui déplace de l’argent porte une clé d’idempotence unique et persistée.
5. Un webhook ou une réconciliation rejoué ne crée ni second abonnement, ni seconde commission, ni second retrait.
6. Une réponse de retour navigateur ne prouve jamais un paiement ou un transfert.
7. Le taux, la base de calcul, les prix et la règle promotionnelle sont copiés dans un snapshot au checkout et dans la commission réglée.
8. Une panne SMTP ne doit jamais annuler un règlement ou modifier un solde financier.
9. Une correction financière se fait par une écriture compensatrice, jamais par suppression ou modification silencieuse d’une écriture réglée.
10. Les secrets d’encaissement et de reversement KPrimePay sont distincts et ne sont jamais affichés, journalisés ou envoyés au navigateur.

## 4. Vocabulaire métier

- **Compte d’abonnement :** `subscription_account` existant, commun aux entreprises du même propriétaire.
- **Client attribué :** compte d’abonnement lié définitivement à un partenaire après son premier abonnement payant confirmé avec un code valide.
- **Client qualifié :** client attribué dont le premier paiement n’est ni annulé, ni remboursé, ni classé frauduleux.
- **Premier abonnement :** premier `subscription_payment` payé du compte, hors essai gratuit et fixture locale.
- **Renouvellement :** nouveau paiement payant sur un compte déjà attribué, y compris une montée de plan.
- **Montant catalogue brut :** prix serveur avant réduction partenaire.
- **Montant net encaissé :** montant brut moins réduction client, hors frais éventuellement ajoutés par le prestataire.
- **Commission :** dette de Maxanou envers le partenaire, calculée sur le montant catalogue brut selon le taux acquis par ce client.
- **Solde en attente :** commissions confirmées mais pas encore arrivées à maturité.
- **Solde disponible :** commissions matures, non réservées, non payées et non annulées.
- **Solde réservé :** montant bloqué par une demande de retrait en cours.
- **Retrait :** transfert du solde partenaire vers un numéro Mobile Money vérifié.

## 5. Règles métier définitives

### 5.1 Inscription et compte partenaire

- Champs requis : nom, pseudonyme, e-mail, téléphone, pays, mot de passe, acceptation des conditions.
- E-mail et pseudonyme sont uniques sans tenir compte de la casse.
- Le téléphone est normalisé au format E.164 et unique pour les comptes actifs.
- Le pseudonyme contient 3 à 30 caractères : lettres ASCII, chiffres, tiret et underscore ; aucun espace.
- Les noms réservés (`admin`, `support`, `maxanou`, noms de plans, insultes et marques protégées) sont refusés.
- L’e-mail doit être vérifié avant création ou diffusion d’un code.
- Le partenaire passe par les états `pending_email`, `active`, `suspended`, `closed`.
- La suspension bloque la connexion, la création de codes et les retraits, sans supprimer les attributions ni l’historique comptable.

### 5.2 Code promotionnel

- Un partenaire actif possède un code principal actif.
- Le code généré par défaut dérive du pseudonyme avec suffixe aléatoire non prédictible.
- Format canonique : 4 à 24 caractères, lettres majuscules et chiffres ; comparaison insensible à la casse.
- Un index unique porte sur la valeur canonique.
- La personnalisation est autorisée si le code est disponible et non réservé.
- Après le premier client qualifié, un changement de code ne modifie jamais les attributions existantes.
- Par défaut, un changement de code est limité à une fois tous les 30 jours. L’ancien code est désactivé ; il ne doit pas être réattribué à un autre partenaire pendant au moins 24 mois.
- La validation publique ne révèle que `valid`, le pourcentage de réduction et un message neutre ; elle ne divulgue ni nom, ni e-mail, ni revenus du partenaire.
- L’endpoint est limité en fréquence par IP, session et compte d’abonnement.

### 5.3 Éligibilité d’un client à la réduction

Le code est facultatif. Sans code valide :

- réduction = 0 ;
- aucune attribution ;
- aucune commission.

Un code valide donne 10 % de réduction seulement si :

- le compte d’abonnement n’a jamais eu de paiement d’abonnement `paid` ;
- il n’a aucune attribution partenaire ;
- le code et le partenaire sont actifs au moment de la création du checkout ;
- le compte n’est pas le compte du partenaire lui-même et ne déclenche pas une règle antifraude bloquante ;
- le checkout est confirmé par KPrimePay avant expiration.

Saisir un code, s’inscrire ou démarrer un checkout **ne lie pas** le client. L’attribution définitive est créée uniquement pendant le règlement transactionnel du premier paiement confirmé.

### 5.4 Attribution à vie

- Un `subscription_account` ne peut avoir qu’un seul partenaire : contrainte unique en base.
- Une attribution confirmée ne peut être remplacée par un autre code.
- Une nouvelle entreprise rattachée au même compte d’abonnement hérite automatiquement de l’attribution.
- Un changement de propriétaire ou une fusion de comptes exige une procédure administrative auditée ; aucun contrôleur standard ne modifie l’attribution.
- La clôture du compte partenaire gèle les nouvelles commissions selon la décision administrative, mais ne réattribue jamais les clients.
- Les données personnelles visibles du client sont limitées au nécessaire : raison sociale/nom d’affichage, pays, date d’attribution, plan/statut, dates et montants de commission. L’e-mail et le téléphone complets ne sont pas affichés par défaut.

### 5.5 Réduction du premier paiement

Pour le premier abonnement attribué :

```text
montant_brut = prix serveur du plan et de la durée
remise = floor(montant_brut × 10 / 100)
montant_net = montant_brut - remise
```

- De 1 à 11 mois, le brut vaut `monthly_price × duration_months`.
- À 12 mois, le brut vaut `annual_price`, donc la promotion s’ajoute à la réduction annuelle déjà prévue.
- La réduction partenaire ne s’applique jamais aux renouvellements, montées de plan ultérieures, quotas SMS/WhatsApp ou autres achats.
- `subscription_payments.amount` reste le montant net réellement attendu de KPrimePay.
- `gross_amount`, `discount_amount`, le code, l’ID partenaire et la version de règle sont persistés.
- Le règlement serveur compare exactement devise et montant net avec le statut KPrimePay vérifié.

### 5.6 Base et coût de la commission

La commission est calculée sur le **montant catalogue brut de l’abonnement**, avant réduction client, hors frais KPrimePay, taxes distinctes, quotas et remboursements :

```text
commission = floor(montant_brut × taux_client / 100)
```

Conséquence commerciale assumée : lors du premier achat au taux de 10 %, le coût d’acquisition maximal est 20 % du brut — 10 % de remise client et 10 % de commission partenaire — avant frais de paiement. Lors des renouvellements, seule la commission reste due.

### 5.7 Paliers de commission

Le taux augmente uniquement pour les **nouveaux clients qualifiés**. Il démarre à 10 %, atteint au maximum 25 %, et le nombre de nouveaux clients nécessaire pour gagner le prochain point double après chaque bloc de 5 points de pourcentage gagnés :

| Rang du nouveau client qualifié | Taux acquis par ce client | Taille du palier |
|---:|---:|---:|
| 1 à 5 | 10 % | 5 clients |
| 6 à 10 | 11 % | 5 clients |
| 11 à 15 | 12 % | 5 clients |
| 16 à 20 | 13 % | 5 clients |
| 21 à 25 | 14 % | 5 clients |
| 26 à 35 | 15 % | 10 clients |
| 36 à 45 | 16 % | 10 clients |
| 46 à 55 | 17 % | 10 clients |
| 56 à 65 | 18 % | 10 clients |
| 66 à 75 | 19 % | 10 clients |
| 76 à 95 | 20 % | 20 clients |
| 96 à 115 | 21 % | 20 clients |
| 116 à 135 | 22 % | 20 clients |
| 136 à 155 | 23 % | 20 clients |
| 156 à 175 | 24 % | 20 clients |
| 176 et suivants | 25 % | plafond atteint |

Décisions nécessaires à l’audit :

- le taux est calculé sous verrou à partir du nombre de clients déjà qualifiés ;
- le client conserve à vie le taux obtenu à son acquisition ; ses renouvellements utilisent ce même taux ;
- une annulation/remboursement du premier abonnement invalide la qualification et produit une compensation, sans recalculer rétroactivement les taux des autres clients ;
- le passage de 10 à 15 % exige 5 nouveaux clients par point ;
- à partir de 15 %, le palier double à 10 nouveaux clients par point jusqu’à 20 % ;
- à partir de 20 %, il double à 20 nouveaux clients par point jusqu’à 25 % ;
- 25 % est le plafond : aucun taux supérieur n’est attribué, quel que soit le nombre de clients ;
- toute évolution future du plafond ou de la grille créera une nouvelle version sans modifier les taux déjà acquis.

Formule de référence pour déterminer le taux du prochain client qualifié à partir de son rang `r` :

```text
1  <= r <= 25  : 10 + floor((r - 1) / 5)
26 <= r <= 75  : 15 + floor((r - 26) / 10)
76 <= r <= 175 : 20 + floor((r - 76) / 20)
r >= 176       : 25
```

### 5.8 Maturité, disponibilité et retrait

- Une commission confirmée est immédiatement `available` après le webhook de paiement vérifié, si le paiement n’est pas remboursé, annulé ou signalé. Cette décision produit remplace le délai de conservation initialement proposé de 7 jours ; le paramètre `partners.commission_hold_days` reste versionné pour une réactivation future sans modifier les snapshots existants.
- Le retrait est autorisé si le partenaire possède au moins 3 clients qualifiés et un solde disponible supérieur ou égal au minimum configuré.
- Minimum proposé au lancement : 5 000 XOF, à valider commercialement et au regard des frais KPrimePay.
- Le compte Mobile Money doit être vérifié et appartenir au partenaire ou avoir été validé manuellement.
- Chaque demande de retrait impose le mot de passe courant et un code 2FA envoyé par e-mail, même si le 2FA de connexion est désactivé.
- Une seule demande `requested`, `verifying`, `approved` ou `processing` est autorisée par partenaire à la fois.
- Le montant est réservé atomiquement avant l’appel externe.
- En cas d’échec final avec restauration confirmée, une écriture libère la réserve. Un état inconnu reste réservé jusqu’à réconciliation.
- Une suspension, une alerte fraude ou des informations Mobile Money modifiées imposent une revue.

### 5.9 Remboursements, litiges et fraude

- Toute annulation d’un abonnement payé crée une commission négative de même source, jamais une suppression.
- Si le solde disponible est insuffisant, le portefeuille peut devenir négatif ; les commissions futures le compensent avant tout retrait.
- Si une commission a déjà été retirée, l’administration reçoit une alerte et le partenaire est bloqué au retrait jusqu’à régularisation.
- Sont au minimum détectés : auto-parrainage, e-mail/téléphone partagé, rafale de comptes, appareil/IP anormalement communs, codes test en production, paiements successivement remboursés.
- Les signaux IP/appareil sont des indicateurs de risque, pas l’unique preuve d’une fraude.

## 6. Schéma de données cible

Toutes les tables utilisent des clés étrangères, timestamps UTC, index explicites et suppression restrictive pour les données financières.

### 6.1 `partners`

| Colonne | Type / règle |
|---|---|
| `id` | bigint PK |
| `name` | varchar(120) |
| `username` | varchar(30), canonique, unique |
| `email` | varchar(255), canonique, unique |
| `email_verified_at` | timestamp nullable |
| `phone_country_code` | char(2) ISO |
| `phone_e164` | varchar(20), unique pour comptes ouverts |
| `country_code` | char(2) ISO |
| `password` | hash Laravel |
| `status` | pending_email/active/suspended/closed, index |
| `two_factor_login_enabled` | bool false par défaut |
| `auth_version` | int, révocation des sessions |
| `appearance_mode` | system/light/dark, même contrat que le SaaS |
| `accent_color` | valeur validée par le design system SaaS |
| `qualified_clients_count` | entier de projection, contrôlé par rapprochement |
| `current_rate_bps` | points de base, projection ; 1000 = 10 % |
| `last_login_at`, `last_login_ip` | audit |
| `suspended_at`, `suspension_reason` | nullable |

Ne pas stocker un code 2FA en clair dans cette table.

### 6.2 `partner_password_reset_tokens`

Table dédiée au broker partenaire. Token hashé, date de création, expiration et throttling identiques ou plus stricts que l’administration plateforme.

### 6.3 `partner_two_factor_challenges`

| Champ clé | Rôle |
|---|---|
| `partner_id`, `purpose` | connexion, retrait, changement e-mail, compte de retrait |
| `code_hash` | code 6 chiffres hashé |
| `expires_at` | 10 minutes par défaut |
| `attempts`, `max_attempts` | 5 par défaut |
| `consumed_at` | usage unique |
| `request_ip`, `user_agent_hash` | contrôle du contexte |

Index sur `(partner_id, purpose, consumed_at, expires_at)`. Un nouveau code invalide les codes non consommés du même usage.

### 6.4 `partner_promo_codes`

- `id`, `partner_id` FK restrictive ;
- `code`, `normalized_code` unique ;
- `status` : active/disabled/retired ;
- `is_primary` ;
- `activated_at`, `disabled_at`, `last_used_at` ;
- index `(partner_id, status)`.

### 6.5 `partner_checkout_intents`

Snapshot temporaire du code au moment de créer un paiement :

- `subscription_payment_id` unique ;
- `subscription_account_id`, `partner_id`, `partner_promo_code_id` ;
- `rule_version`, `discount_bps`, `candidate_commission_bps` ;
- `gross_amount`, `discount_amount`, `net_amount`, `currency` ;
- `status` : pending/settled/expired/rejected ;
- `expires_at`.

Cette table empêche qu’une modification du code ou de la grille change un checkout déjà créé.

### 6.6 `partner_attributions`

| Colonne | Règle |
|---|---|
| `subscription_account_id` | unique, une attribution à vie |
| `partner_id` | index |
| `partner_promo_code_id` | code d’origine |
| `first_subscription_payment_id` | unique |
| `acquisition_rank` | 1, 2, 3… sous verrou |
| `commission_rate_bps` | taux acquis et immuable |
| `rule_version` | grille ayant décidé le taux |
| `attributed_at` | date du premier paiement confirmé |
| `status` | active/reversed/fraudulent |
| `reversed_at`, `reversal_reason` | nullable |

Contraintes uniques obligatoires sur `subscription_account_id` et `first_subscription_payment_id`.

### 6.7 Extension de `subscription_payments`

Ajouter sans casser les paiements existants :

- `gross_amount` unsigned bigint ;
- `discount_amount` unsigned bigint défaut 0 ;
- `partner_checkout_intent_id` nullable unique ;
- `promotion_rule_version` nullable ;
- éventuellement `net_amount` virtuel/documentaire ; `amount` reste la somme réellement encaissée.

Le JSON `snapshot` doit aussi contenir les valeurs lisibles pour audit, mais les colonnes financières utilisées par les requêtes restent typées.

### 6.8 `partner_commissions`

- `id`, `partner_id`, `partner_attribution_id` ;
- `subscription_payment_id` unique : une commission positive par paiement ;
- `type` : acquisition/renewal/upgrade/reversal/manual_adjustment ;
- `gross_amount`, `discount_amount`, `net_paid_amount` ;
- `commission_rate_bps`, `commission_amount`, `currency` ;
- `status` : pending/available/reserved/paid/reversed ;
- `available_at`, `reserved_at`, `paid_at`, `reversed_at` ;
- `rule_version`, `calculation_snapshot` JSON ;
- index `(partner_id, status, available_at)`.

### 6.9 `partner_wallet_entries`

Grand livre append-only :

| Colonne | Description |
|---|---|
| `partner_id` | propriétaire du portefeuille |
| `entry_type` | commission_credit, maturity, withdrawal_reserve, withdrawal_release, payout_debit, reversal, adjustment |
| `bucket` | pending/available/reserved/paid |
| `direction` | credit/debit |
| `amount`, `currency` | entier XOF |
| `source_type`, `source_id` | référence polymorphe contrôlée |
| `idempotency_key` | unique |
| `occurred_at` | ordre comptable |
| `metadata` | snapshot non sensible |

Les soldes affichés sont la somme des écritures par bucket. Une table `partner_wallet_balances` peut servir de projection verrouillable, mais elle doit être rapprochable avec le grand livre.

### 6.10 `partner_withdrawal_accounts`

- partenaire, pays, opérateur/gateway KPrimePay, numéro E.164 chiffré au repos, nom du bénéficiaire ;
- empreinte partielle pour affichage (`•••• 1234`) ;
- `verified_at`, `is_primary`, `status` ;
- toute modification invalide la vérification et déclenche une notification de sécurité.

### 6.11 `partner_withdrawals`

- `partner_id`, `partner_withdrawal_account_id` ;
- `transaction_id` unique, `idempotency_key` unique, `kpp_reference` nullable unique ;
- `amount`, `fees_amount`, `currency`, `with_fees` ;
- `status` : requested/otp_verified/approved/processing/succeeded/failed/unknown/cancelled ;
- `provider_status`, `failure_code`, `failure_reason` ;
- `requested_at`, `verified_at`, `approved_at`, `submitted_at`, `paid_at`, `failed_at` ;
- `reviewed_by_platform_admin_id`, `review_reason` ;
- `request_snapshot` et `provider_snapshot` expurgés.

### 6.12 `partner_payout_events`

- `event_id` unique pour déduplication webhook ;
- `withdrawal_id`, `event_type`, `provider_status` ;
- `payload_hash`, payload expurgé/chiffré si conservation nécessaire ;
- `received_at`, `processed_at`, `processing_error`.

### 6.13 `partner_audit_logs`

Journaliser connexion, 2FA, changement de profil, code, compte Mobile Money, demande/revue/retrait, suspension, export et ajustement. Inclure acteur, cible, avant/après expurgé, raison, IP et user-agent. Aucun secret, mot de passe, OTP ou numéro complet.

## 7. Machines d’état

### 7.1 Commission

```text
payment confirmé
      │
      ▼
   pending ── maturité ──► available ── demande ──► reserved ── succès ──► paid
      │                         │                      │
      └── annulation ─► reversed ◄── compensation ───┴── échec confirmé
```

`unknown` n’est pas un état de commission : un retrait inconnu conserve la commission en `reserved`.

### 7.2 Retrait

```text
requested → otp_verified → approved → processing → succeeded
     │             │           │           ├──────→ failed
     └─────────────┴───────────┴───────────→ cancelled
                                         └──────→ unknown → réconciliation
```

Un succès ne peut revenir vers failed. Un échec tardif du prestataire produit un événement compensateur explicite.

## 8. Flux fonctionnels et transactionnels

### 8.1 Inscription partenaire

1. Valider et normaliser les champs côté serveur.
2. Vérifier les uniques en application et en base.
3. Créer le partenaire `pending_email` et son token de vérification hashé.
4. Envoyer l’e-mail par job ; une panne d’e-mail laisse le compte en attente et permet un renvoi limité.
5. Après validation, passer `active`, générer le code principal et auditer.
6. La connexion applique le rate limit ; si le 2FA de connexion est actif, émettre un challenge e-mail avant d’ouvrir la session.

### 8.2 Application d’un code au checkout

1. Le propriétaire/administrateur choisit un plan et 1–12 mois.
2. Le code facultatif est envoyé avec la demande de prévisualisation.
3. Le serveur charge sous verrou le compte d’abonnement, le plan publié et l’historique payé.
4. Il recalcule brut, réduction et net. Le navigateur n’envoie aucune valeur monétaire de confiance.
5. Il crée `subscription_payment` et `partner_checkout_intent` dans la même transaction.
6. Il crée le checkout KPrimePay sur le montant net avec l’idempotency key du paiement.
7. Le modal affiche avant confirmation : plan, durée, brut, remise, total, date estimée. Il suit `ServerButtonLoader` et les règles SweetAlert de `AGENTS.md`.

Si deux checkouts concurrents portent deux codes différents, aucun ne gagne avant paiement. Le premier paiement confirmé crée l’attribution ; le second paiement doit être placé en revue/remboursement et ne doit ni remplacer l’attribution ni créer une seconde commission.

### 8.3 Règlement du premier abonnement

Étendre `SubscriptionSettlementService::creditVerified()` sans déplacer ses garanties actuelles :

1. Reconfirmer statut, devise et montant net via KPrimePay.
2. Ouvrir une transaction et verrouiller le paiement, le compte d’abonnement et l’intention partenaire.
3. Si le paiement est déjà `paid`, retourner sans effet.
4. Créer l’abonnement et créditer les quotas comme actuellement.
5. Si l’intention est encore éligible :
   - verrouiller le partenaire et sa projection de compteur ;
   - déterminer le rang et le taux ;
   - créer l’attribution unique ;
   - créer la commission unique et l’écriture `pending` ;
   - incrémenter le compteur qualifié ;
   - marquer l’intention `settled`.
6. Marquer le paiement payé et créer les événements uniques.
7. Commit.
8. Après commit seulement, distribuer les e-mails/notifications par queue.

L’abonnement ne doit pas être annulé si le traitement partenaire échoue pour une raison non financière après confirmation. En revanche, la création de l’attribution et de la commission doit être atomique avec le règlement afin d’éviter une dette invisible. Une exception déclenche réconciliation et alerte critique.

### 8.4 Renouvellement ou montée de plan

1. Le checkout trouve l’attribution active du compte.
2. Aucune remise client n’est appliquée.
3. Le paiement conserve dans son snapshot l’attribution et le taux acquis.
4. Au règlement, créer une seule commission sur le brut avec le taux du client.
5. Les règles existantes de durée, annualisation, interdiction de downgrade et quotas restent inchangées.

### 8.5 Demande de retrait

1. Afficher solde disponible et critères d’éligibilité calculés serveur.
2. Le partenaire choisit un compte Mobile Money vérifié et un montant.
3. Vérifier 3 clients qualifiés, minimum, statut, absence de risque et solde sous verrou.
4. Demander le mot de passe puis émettre un OTP e-mail lié au montant, au compte de retrait et à la session.
5. Après OTP valide, créer le retrait et réserver le montant dans une transaction idempotente.
6. Selon le niveau de risque, passer en `approved` automatiquement ou attendre une validation plateforme à deux personnes.
7. Un job appelle KPrimePay avec la clé dédiée payout et l’`Idempotency-Key` persistée.
8. Stocker la réponse expurgée et passer `processing` ou `failed`.
9. Le webhook et la réconciliation interrogent le statut crédit, dédupliquent `event_id`, puis finalisent `succeeded`, `failed` ou `unknown`.
10. Envoyer le reçu au partenaire et l’alerte administration après commit.

## 9. Intégration KPrimePay

### 9.1 Séparation des usages

Conserver le service d’encaissement actuel pour :

- `/checkout` des abonnements et quotas ;
- `/transactions/debit-status` pour confirmer l’argent reçu.

Créer `KprimePayPayoutService` séparé pour :

- `/payouts/transfers` ;
- `/transactions/credit-status` ;
- `/account/payout-balance` ;
- `/gateways` et les passerelles par pays.

Variables proposées :

```dotenv
KPRIMEPAY_BASE_URL=https://api.kprimepay.com/v2
KPRIMEPAY_COLLECTION_TOKEN=
KPRIMEPAY_PAYOUT_TOKEN=
KPRIMEPAY_WEBHOOK_SECRET=
KPRIMEPAY_PAYOUT_WITH_FEES=false
PARTNER_PAYOUT_MIN_XOF=5000
PARTNER_COMMISSION_HOLD_DAYS=7
PARTNER_DOMAIN=partners.maxanou.com
```

La clé collection reçoit seulement `payments:write` et `read`. La clé payout reçoit seulement `payouts:write`, éventuellement `contacts:write`, et `read`. L’IP du serveur de production doit être autorisée côté KPrimePay pour les reversements.

### 9.2 Création d’un transfert

Utiliser un identifiant interne non ambigu, par exemple `PTR-{withdrawal_id}-{random}`. Envoyer le bénéficiaire, son pays, sa passerelle et son téléphone normalisé. Ne pas déduire l’opérateur uniquement du préfixe : charger les gateways disponibles depuis KPrimePay et les mettre en cache brièvement.

Le endpoint de transfert depuis une collecte spécifique n’est pas retenu en V1 : un portefeuille partenaire agrège plusieurs commissions et ne correspond pas à une seule collecte source.

### 9.3 Webhook unique, routage explicite

Le webhook existant `/api/kprimepay/webhook` peut recevoir collection et transfert. Le contrôleur doit :

1. vérifier la signature selon la version documentée ;
2. enregistrer/dédupliquer l’`event_id` ;
3. router `collection.*` vers abonnement/quota via `transaction_id` ;
4. router `transfer.*` vers retrait partenaire ;
5. répondre rapidement 200 après persistance sûre ;
6. déléguer le traitement lourd à une queue idempotente.

Une alternative `/api/kprimepay/payout-webhook` n’est acceptable que si KPrimePay permet deux URLs configurées et que les mêmes vérifications sont conservées.

### 9.4 Réconciliation

Étendre ou compléter `payments:reconcile-kprimepay` :

- collections abonnement/quota : statut débit comme aujourd’hui ;
- retraits `processing`/`unknown` : statut crédit ;
- alerte si `unknown` dépasse 15 minutes, puis critique à 2 heures ;
- rapprochement quotidien du solde KPrimePay payout avec les retraits en cours ;
- aucun retry aveugle avec une nouvelle transaction/idempotency key.

KPrimePay documente en V2 les scopes distincts, l’IP autorisée, l’idempotence obligatoire, les transferts Mobile Money, les statuts crédit, les soldes et les événements webhook. Référence normative : <https://developers.kprimepay.com/index>.

## 10. Services, événements et jobs à créer

### Services

- `PartnerRegistrationService`
- `PartnerAuthenticationService`
- `PartnerPromoCodeService`
- `PartnerAttributionEligibilityService`
- `PartnerPricingService`
- `PartnerTierService`
- `PartnerCommissionService`
- `PartnerWalletService`
- `PartnerWithdrawalService`
- `KprimePayPayoutService`
- `PartnerReconciliationService`
- `PartnerRiskService`
- `PartnerDashboardQueryService`

Chaque méthode financière reçoit des objets/DTO typés et retourne une décision explicite. Les contrôleurs restent minces.

### Événements

- `PartnerRegistered`
- `PartnerEmailVerified`
- `PartnerCodeChanged`
- `PartnerAttributed`
- `PartnerCommissionCreated`
- `PartnerCommissionMatured`
- `PartnerWithdrawalRequested`
- `PartnerWithdrawalSucceeded`
- `PartnerWithdrawalFailed`
- `PartnerRiskFlagged`

### Jobs

- envoi des e-mails de vérification/2FA/notifications ;
- maturation des commissions, toutes les heures ou quotidiennement ;
- soumission du payout ;
- réconciliation des payouts ;
- recalcul/contrôle des projections portefeuille et dashboard ;
- export CSV asynchrone ;
- purge des OTP et intentions expirés.

## 11. Routes et permissions

### 11.1 Portail partenaire

Groupe `Route::domain(config('partners.domain'))->name('partner.')` :

- invité : inscription, connexion, vérification e-mail, mot de passe oublié, 2FA ;
- authentifié actif : dashboard, code, clients, commissions, retraits, comptes Mobile Money, paramètres, profil, sécurité, déconnexion ;
- actions sensibles : throttle + mot de passe récent + 2FA d’opération.

En local, prévoir `PARTNER_DOMAIN=` vide et un préfixe `/partner`, ou configurer `partners.maxanou.test` dans le fichier hosts. Les tests doivent couvrir les deux configurations.

### 11.2 Espace client abonnement

- `POST /subscription/promo/preview` : prévisualisation bornée et authentifiée ;
- le `POST` existant de checkout accepte seulement `plan_key`, `duration_months`, `promo_code` ;
- le serveur renvoie les montants calculés ou une erreur fonctionnelle exploitable.

### 11.3 Console plateforme

Ajouter des permissions séparées :

- `platform.partners.view`
- `platform.partners.manage`
- `platform.partner_commissions.view`
- `platform.partner_withdrawals.view`
- `platform.partner_withdrawals.approve`
- `platform.partner_adjustments.create`
- `platform.partner_settings.manage`

L’ajustement financier exige super-administrateur, mot de passe, motif, double confirmation, audit et écriture compensatrice. Aucun bouton « modifier le solde ».

## 12. Interfaces attendues

### 12.1 Contrat UI/UX obligatoire : template SaaS Maxanou

Le portail partenaire doit utiliser **en pratique** le template SaaS déjà livré dans Maxanou. Il est interdit de créer un thème partenaire autonome, de recopier une feuille CSS complète dans les vues ou de réintroduire l’ancien template.

- Créer `layouts.partner` comme adaptation du shell `layouts.saas` : même structure, tokens, typographie, espacements, surfaces, transitions, modes clair/sombre/système et couleur dominante ; seule la navigation métier partenaire change.
- Mutualiser les partials et assets du shell SaaS au lieu de copier `layouts.saas`. Si une hypothèse liée à la compagnie empêche la réutilisation directe, extraire le fragment partagé avant de construire le layout partenaire.
- Réutiliser les composants Blade réellement disponibles sous `resources/views/components/ui` : `page-header`, `button`, `action-button`, `action-group`, `input`, `select`, `textarea`, `password`, `card`, `glass-panel`, `badge`, `status`, `modal`, `alert`, `notice`, `empty-state`, `stat-card`, `table-shell`, `filter-panel`, `export-panel`, `details-list`, `progress`, `switch`, `tabs`, `skeleton` et `permission-denied`.
- Toute nouvelle primitive générique nécessaire au portail doit être ajoutée à `x-ui.*`, documentée dans le cahier du design system, testée puis réutilisable par le POS et l’administration. Elle ne doit pas rester un composant local `partner-*` si son usage est générique.
- Les variantes visuelles restent celles du système : `primary`, `secondary`, `success`, `danger`, `warning`, `ghost`. Pas de couleurs métier codées en dur dans les vues.
- Les DataTables partenaires utilisent `x-ui.table-shell`, les mêmes recherche, filtres, pagination, densité, états vides, badges de statut et comportement mobile que la console SaaS.
- Les formulaires utilisent les composants `x-ui` avec labels, aide, erreurs, focus et zones tactiles normalisés. Aucun champ Bootstrap brut si un composant partagé existe.
- Les modales utilisent `x-ui.modal`. Les confirmations destructives ou financières utilisent SweetAlert conformément à `AGENTS.md`, sans créer une convention concurrente.
- Les graphiques reprennent les couleurs issues des tokens du thème et restent lisibles en clair, sombre, daltonisme et impression/export.
- Le partenaire peut choisir sombre, clair ou système et une couleur dominante selon les mêmes contraintes que l’utilisateur POS ; le choix est personnel et ne modifie ni la marque Maxanou ni les interfaces des clients.
- Le portail reste identifiable comme « Partenaires Maxanou », mais le logo, la hiérarchie de marque et les composants restent ceux du SaaS.
- Le layout doit fonctionner dès 320 px, sans défilement horizontal. Le menu devient un tiroir avec backdrop sur mobile, fermeture par Échap et restitution correcte du focus.

Matrice pratique minimale :

| Besoin partenaire | Outil SaaS obligatoire |
|---|---|
| Titre, description, actions principales | `x-ui.page-header` |
| Revenus, clients, taux, progression | `x-ui.stat-card`, `x-ui.progress` |
| Statut commission/retrait/partenaire | `x-ui.status` ou `x-ui.badge` |
| Listes clients, commissions, retraits | `x-ui.table-shell` + `x-ui.filter-panel` |
| Export | `x-ui.export-panel` |
| Formulaires profil, code, Mobile Money | composants de formulaire `x-ui.*` |
| Confirmation et détail | `x-ui.modal` ou SweetAlert selon l’action |
| Aucun résultat | `x-ui.empty-state` |
| Chargement différé | `x-ui.skeleton` |
| Accès refusé | `x-ui.permission-denied` |
| Réglages binaires | `x-ui.switch` |

Chaque pull request qui ajoute ou modifie une interface partenaire doit fournir :

1. `php artisan ui:lint` sans nouvelle violation ;
2. `php artisan view:cache` ;
3. un test Feature de rendu et de permission ;
4. une recette visuelle à 1440, 1024, 768 et 390 px ;
5. une vérification clavier/focus, thème clair/sombre et `prefers-reduced-motion` ;
6. une preuve que les actions serveur montrent un loader et bloquent les doubles clics.

Une phase UI n’est pas terminée si elle fonctionne techniquement mais ne respecte pas ce contrat.

### Portail partenaire

1. Inscription, vérification e-mail, connexion, 2FA et récupération.
2. Dashboard : solde disponible/en attente/réservé/payé, taux actuel, progression vers le palier, clients qualifiés, abonnés actifs/inactifs, revenus par période.
3. Code promotionnel : code, copie, lien partageable, QR code optionnel, historique de changement.
4. Clients : liste paginée et filtrée, attribution, plan/statut, dernière activité, commissions cumulées ; données personnelles masquées.
5. Commissions : grand livre paginé, filtres, détail du calcul, export CSV asynchrone.
6. Retraits : éligibilité, compte Mobile Money, OTP, historique et statuts.
7. Paramètres : 2FA connexion, préférences e-mail, langue/fuseau, confidentialité.
8. Profil : pseudonyme, nom, téléphone, e-mail avec mot de passe + vérification, mot de passe.

### Console plateforme

1. Tableau de bord partenaires et alertes.
2. Liste/détail partenaires, suspension/réactivation et audit.
3. Attributions et détection de conflits/fraude.
4. Commissions, rapprochements et écritures compensatrices.
5. File des retraits et validation.
6. Paramètres versionnés : remise, grille, plafond, maturité, minimum, nombre de clients, frais, limites et règles de risque.
7. Santé : jobs, webhooks, retraits inconnus, solde payout insuffisant.

Toutes les interfaces respectent `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` et le contrat ci-dessus. Chaque action serveur applique `window.ServerButtonLoader`. Les SweetAlert utilisent `showLoaderOnConfirm`, la requête dans `preConfirm`, bloquent la fermeture pendant le chargement et n’affichent le succès qu’après la réponse positive.

## 13. Tableau de bord et performance

Ne pas recalculer les revenus sur toutes les lignes à chaque affichage.

- Les listes utilisent pagination serveur, filtres bornés et index.
- Maintenir des agrégats journaliers `partner_daily_metrics` : clics/validations si suivis, nouveaux attribués, actifs/inactifs, brut, remise, commissions, retraits.
- Mettre en cache 1 à 5 minutes les cartes dashboard ; invalider après événement financier.
- Les graphiques demandent une période maximale (30 jours par défaut, 24 mois maximum en agrégation mensuelle).
- Les exports partent en queue et livrent un lien temporaire.
- Éviter N+1 avec eager loading ciblé et `select` explicite.
- Index minimum : statut/date, partenaire/statut/date, compte attribution unique, paiement commission unique, transaction/event/idempotency uniques.
- Mesurer le nombre de requêtes et le p95 sur dashboard, clients, commissions et retrait.

Objectifs initiaux raisonnables sur hébergement mutualisé, hors réseau prestataire : p95 page HTML < 800 ms, p95 API interne < 400 ms, aucune page > 30 requêtes SQL, aucune requête non paginée sur le grand livre.

## 14. Sécurité, conformité et confidentialité

- Mots de passe conformes à la politique plateforme (12 caractères, complexité) et hash Laravel.
- OTP hashés, usage unique, expiration 10 minutes, 5 essais, renvoi limité.
- CSRF, validation FormRequest, rate limits, session fixation, révocation `auth_version`.
- Chiffrement applicatif des numéros de retrait et minimisation des payloads provider.
- Journalisation structurée avec correlation ID, sans secret ni PII complète.
- Protection contre l’énumération des e-mails, pseudos et codes.
- Notifications de sécurité lors de changement e-mail, mot de passe, téléphone, compte Mobile Money, 2FA et retrait.
- Validation juridique avant lancement : conditions partenaires, fiscalité/retenues, KYC éventuel, protection des données, durée de conservation, droit de contestation et politique de remboursement.
- La liste clients ne doit pas permettre au partenaire de contacter directement les clients sans base légale/consentement distinct.
- Sauvegardes et restauration testées avant activation des retraits.

## 15. Paramètres administrables

Les paramètres sont versionnés et audités. Les paiements/attributions existants conservent leur snapshot.

| Clé proposée | Défaut |
|---|---:|
| `partners.enabled` | false avant recette |
| `partners.registration_enabled` | false avant lancement |
| `partners.first_discount_bps` | 1000 |
| `partners.max_commission_bps` | 2500 |
| `partners.commission_hold_days` | 7 |
| `partners.payout_min_xof` | 5000 |
| `partners.payout_min_qualified_clients` | 3 |
| `partners.auto_approval_max_xof` | 0 au lancement |
| `partners.code_change_cooldown_days` | 30 |
| `partners.payouts_enabled` | false avant validation KPrimePay |
| `partners.risk_review_enabled` | true |

Deux interrupteurs distincts sont obligatoires : acquisition/commission et retraits. Désactiver les retraits ne doit pas arrêter l’inscription comptable des commissions dues.

## 16. Notifications

### Partenaire

- vérification d’e-mail ;
- code 2FA ;
- connexion inhabituelle ;
- nouveau client attribué ;
- commission disponible ;
- demande de retrait ;
- retrait réussi/échoué ;
- changement de profil ou sécurité ;
- suspension/réactivation.

### Administration

- nouveau partenaire à risque ;
- première attribution ;
- retrait demandé, réussi, échoué ou inconnu ;
- payout balance insuffisant ;
- webhook invalide/répété anormalement ;
- incohérence de grand livre ;
- remboursement avec solde partenaire insuffisant.

Les e-mails partent après commit, sont idempotents et leur échec apparaît dans la supervision. Ils ne conditionnent jamais un règlement.

## 17. Stratégie de tests

### 17.1 Tests unitaires

- normalisation/unicité du code et pseudonyme ;
- calcul brut 1, 3, 11 et 12 mois ;
- réduction 10 %, arrondi entier et montant net ;
- grille : clients 1, 5, 6, 10, 11, 15, 16, 20, 21, 25, 26, 35, 36, 45, 46, 55, 56, 65, 66, 75, 76, 95, 96, 115, 116, 135, 136, 155, 156, 175 et 176 ;
- doublement de la taille des paliers à 15 % puis à 20 %, et plafond 25 % ;
- taux acquis conservé sur renouvellement ;
- éligibilité 3 clients et minimum de retrait ;
- soldes du grand livre et compensations.

### 17.2 Tests Feature

- inscription, vérification, login, throttling, 2FA on/off ;
- changement e-mail/pseudo/mot de passe sécurisé ;
- code facultatif, invalide, désactivé, expiré, appartenant au client ;
- premier checkout avec remise et affichage dynamique ;
- renouvellement sans remise mais avec commission ;
- attribution unique et permanente ;
- partenaire/client suspendu ;
- permissions et isolation entre partenaires ;
- données clients masquées ;
- retrait refusé avec 0–2 clients, solde insuffisant ou compte non vérifié ;
- OTP invalide/expiré/rejoué ;
- administration et audit des actions.

### 17.3 Tests financiers et concurrence

- deux webhooks identiques : une commission ;
- webhook + commande de réconciliation simultanés : une commission ;
- deux premiers paiements concurrents avec codes différents : une attribution ;
- deux demandes de retrait simultanées : aucune dépense supérieure au disponible ;
- timeout KPrimePay après envoi : retry avec la **même** idempotency key ;
- réponse failed avec `balance_restored` : réserve libérée une fois ;
- statut inconnu : réserve conservée ;
- remboursement avant/après maturité et après retrait ;
- panne SMTP/queue : règlement et grand livre conservés ;
- rollback DB : aucun solde partiellement modifié.

Utiliser des barrières/processus séparés ou un test d’intégration MySQL réel pour la concurrence ; SQLite en mémoire ne prouve pas les verrous.

### 17.4 Tests KPrimePay contractuels

Avec `Http::fake()` :

- headers Bearer et `Idempotency-Key` ;
- payload de checkout au montant net ;
- payload payout et téléphone/gateway ;
- succès, pending, failed, malformed, 401, 403, 422, 429, 500 et timeout ;
- signature webhook correcte/incorrecte ;
- `collection.succeeded`, `transfer.succeeded`, échec et rejeu ;
- débit et crédit status correctement distingués.

### 17.5 Recette visuelle et accessibilité

Vérifier 1440, 1024, 768 et 390 px : dashboard, tableaux, code, clients, commission, retrait, profil, auth/2FA et pages plateforme. Contrôler clavier, focus, labels, contrastes, loaders, doubles clics, erreurs serveur, longues traductions et absence de débordement horizontal.

### 17.6 Recette staging sans risque

1. Clés sandbox/test distinctes et payouts désactivés.
2. Premier checkout avec code : montant net, attribution et commission pending.
3. Rejeu webhook/réconciliation.
4. Renouvellement : aucune remise, commission correcte.
5. Maturité accélérée en configuration staging.
6. Payout de montant minimal vers un numéro de test explicitement autorisé.
7. Vérification Mobile Money, webhook, credit-status, e-mails et audit.
8. Test d’échec/restauration avec scénario provider prévu.
9. Rapprochement grand livre, retraits et balance KPrimePay.

## 18. Plan de livraison complet

Chaque phase inclut ses tests pendant le développement. Une phase ne passe à la suivante que si ses critères sont verts et documentés dans `FREEBUFF_HANDOFF.md`.

### Phase 0 — Validation produit, juridique et KPrimePay

**Livrables :** règles signées, contrat partenaire, fiscalité/KYC, frais, minimum, délai de maturité, politique de remboursement, accès KPrimePay payout et IP autorisée.  
**Tests/preuves :** appel non financier `/gateways` et `/account/payout-balance`, inventaire des webhooks.  
**Gate :** aucune implémentation de retrait sans documentation et identifiants payout staging.

### Phase 1 — Fondation technique et migrations

**Livrables :** configuration, feature flags, modèles, migrations, factories, permissions, audit et indexes.  
**Tests :** migrations up/down sur base de test, contraintes uniques/FK, modèles et isolation.  
**Rollback :** flags off ; migrations additives sans toucher les données d’abonnement existantes.

### Phase 2 — Authentification et sécurité partenaire

**Livrables :** inscription, vérification e-mail, login, reset, garde, session, 2FA connexion, profil et premier `layouts.partner` construit sur le shell SaaS partagé.  
**Tests :** rate limits, sessions, OTP, mots de passe, changement e-mail, révocation, mobile/desktop, thèmes et composants `x-ui`.  
**Gate :** audit sécurité ciblé vert, `ui:lint` vert et recette du shell aux quatre largeurs.

### Phase 3 — Codes et portail initial

**Livrables :** génération/personnalisation, validation, page code, dashboard vide, paramètres.  
**Tests :** collision/concurrence, codes réservés, fréquence de changement, confidentialité et UI.  
**Gate :** aucun code ne peut être dupliqué ou énuméré ; aucune primitive UI concurrente au template SaaS n’a été introduite.

### Phase 4 — Prix promotionnel et attribution

**Livrables :** prévisualisation, extension checkout, intents, attribution transactionnelle.  
**Tests :** tous les mois/plans, premier paiement, second paiement, course de checkouts, downgrade existant, quotas inchangés.  
**Gate :** montant KPrimePay vérifié égal au net serveur ; aucune attribution avant paiement.

### Phase 5 — Commissions et grand livre

**Livrables :** grille versionnée, taux acquis, commission acquisition/renouvellement, maturité, compensations, rapprochement.  
**Tests :** bornes de paliers, idempotence, concurrence, remboursements, solde négatif, invariants comptables.  
**Gate :** somme grand livre = projections pour toutes les fixtures.

### Phase 6 — Dashboard, clients et exports

**Livrables :** cartes, graphiques, listes paginées, détails calcul, CSV en queue.  
**Tests :** isolation, PII masquée, périodes, volumes, N+1, accessibilité et responsive.  
**Gate :** objectifs SQL/p95 respectés sur données volumétriques de test.

### Phase 7 — Retraits et KPrimePay payout

**Livrables :** comptes Mobile Money, OTP obligatoire, réservation, revue admin, service payout, webhook et réconciliation.  
**Tests :** matrice KPrimePay, doubles clics, timeout, unknown, balance restored, permissions, alertes.  
**Gate :** payout staging complet et rapproché ; aucun secret collection utilisé.

### Phase 8 — Administration plateforme

**Livrables :** partenaires, commissions, retraits, risques, paramètres versionnés, ajustements compensateurs, santé.  
**Tests :** RBAC, confirmation/mot de passe, audit, suspension, paramètres et absence de modification historique.  
**Gate :** séparation des tâches finance/super-admin validée.

### Phase 9 — Durcissement et charge

**Livrables :** indexes vérifiés, cache/agrégats, queues, alertes, sauvegarde/restauration, runbooks.  
**Tests :** charge réaliste POS + partenaires, concurrence financière, reprise queue/webhook, analyse lente SQL, test de restauration.  
**Gate :** aucun impact critique sur ventes/encaissements POS.

### Phase 10 — Lancement progressif

1. Déployer avec `partners.enabled=false` et `partners.payouts_enabled=false`.
2. Exécuter migrations, caches, workers, cron et contrôles santé.
3. Activer pour quelques partenaires internes.
4. Observer une semaine : erreurs, latence, webhooks, grand livre, balance.
5. Activer inscriptions par lot.
6. Activer les payouts manuels/revus.
7. Autoriser éventuellement l’approbation automatique après données suffisantes.

**Rollback :** désactiver inscription et payouts ; conserver la maturation et la comptabilité des obligations existantes ; ne jamais supprimer les écritures.

## 19. Critères d’acceptation finale

Le module est terminé seulement si :

- un partenaire peut s’inscrire, vérifier son e-mail, se connecter et sécuriser son compte ;
- son code unique applique exactement 10 % au premier paiement éligible ;
- aucun code n’applique une remise après le premier paiement ;
- le client est attribué une fois et à vie après confirmation serveur ;
- chaque paiement futur crée exactement une commission au taux acquis ;
- les paliers progressifs 10–25 %, leurs doublements et le plafond sont prouvés à toutes les bornes ;
- le grand livre explique chaque franc des quatre soldes ;
- les retraits sont impossibles avant 3 clients qualifiés, le minimum et le 2FA ;
- un payout réussi/échoué/inconnu est rapproché sans double débit ;
- les administrateurs disposent des permissions, audits et alertes nécessaires ;
- les tests unitaires, Feature, concurrence, KPrimePay, sécurité, charge et UI passent ;
- sauvegarde/restauration, cron, queue, SMTP, logs et alertes sont validés en staging ;
- la documentation et `FREEBUFF_HANDOFF.md` reflètent l’état réel.

## 20. Risques et décisions à confirmer avant codage financier

Les défauts suivants sont proposés par cette architecture et doivent être confirmés par le responsable produit/juridique avant la Phase 4 ou 7 :

1. Commission calculée sur le brut catalogue, et non le net après remise.
2. Taux acquis par client conservé sur ses renouvellements.
3. Plafond fixé à 25 %, avec des paliers de 5 clients par point jusqu’à 15 %, 10 jusqu’à 20 %, puis 20 jusqu’à 25 %.
4. Maturité de 7 jours.
5. Minimum de retrait de 5 000 XOF en plus des 3 clients qualifiés.
6. Ancien code non réattribuable pendant 24 mois.
7. Revue manuelle de tous les retraits au lancement.
8. Traitement fiscal/KYC et responsabilité des frais KPrimePay.

Modifier ces points après lancement change la marge ou la dette envers les partenaires. Toute modification doit donc être versionnée, auditée, testée et non rétroactive sauf écriture compensatrice explicite.

## 21. Fichiers Maxanou à préserver et points d’intégration

- `app/Services/SubscriptionCheckoutService.php` : source actuelle du prix et de la durée ; intégrer le calcul promo serveur.
- `app/Services/SubscriptionSettlementService.php` : point atomique de création d’attribution/commission après vérification.
- `app/Services/KprimePayService.php` : conserver pour les encaissements ; ne pas y mélanger la clé payout.
- `app/Http/Controllers/Api/KprimePayWebhookController.php` : routeur des événements collection/transfert.
- `app/Console/Commands/ReconcileKprimePayPayments.php` : modèle de réconciliation à étendre ou compléter.
- `subscription_accounts` : niveau correct de l’attribution, et non une entreprise isolée.
- `subscription_payments` : source unique d’une commission d’abonnement.
- `docs/GUIDE_KPRIMEPAY.md` : exploitation des collections existantes.
- `docs/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` et `AGENTS.md` : règles UI obligatoires.

## 22. Commandes de validation minimales par lot

Adapter les noms au fur et à mesure de la création :

```bash
php artisan test tests/Unit/Partner tests/Feature/Partner --no-coverage
php artisan test tests/Feature/SubscriptionPaymentTest.php tests/Feature/SubscriptionWebhookTest.php tests/Feature/SubscriptionDurationTest.php tests/Feature/QuotaPaymentTest.php --no-coverage
php artisan route:list --name=partner
php artisan view:cache
php artisan ui:lint
git diff --check
```

La suite complète est obligatoire avant staging. Ne jamais utiliser `migrate:fresh` sur une base applicative ou de staging pour contourner un problème de tests.

---

## Conclusion

La plateforme partenaire doit être construite comme une extension financière du domaine Abonnement, pas comme un simple système de codes promo. Le choix du monolithe modulaire et de la base actuelle donne la meilleure cohérence au moment du webhook, limite la charge opérationnelle et reste compatible avec une extraction future. La réussite dépend surtout du grand livre immuable, des snapshots, des verrous, de l’idempotence et de la séparation stricte entre encaissement et payout.
