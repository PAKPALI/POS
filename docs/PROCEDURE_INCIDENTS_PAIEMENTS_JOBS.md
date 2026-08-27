# Procédure staging — paiements KPrimePay et jobs

## Principes de sécurité

- Ne jamais modifier directement `sms_count` ou `whatsapp_count` pour débloquer un paiement.
- Ne jamais considérer le retour du navigateur comme une preuve de paiement : seul le webhook, revérifié auprès de KPrimePay, crédite les quotas.
- Ne pas lancer `php artisan queue:retry all` sur une base contenant d’anciens jobs.
- Avant toute correction manuelle, noter la compagnie, `transaction_id`, `kpp_reference`, le statut local et les quotas avant intervention.

## 1. Paiement abandonné

1. Noter les quotas actuels de la compagnie.
2. Créer un achat minimal depuis `Communications > SMS & WhatsApp > Quota`.
3. Sur le checkout KPrimePay, ne rien payer et fermer la fenêtre.
4. Attendre au moins jusqu’à `expires_at` retourné par KPrimePay.
5. Vérifier que les quotas n’ont pas changé et que la transaction n’est jamais passée à `paid`.

Résultat attendu : la tâche planifiée vérifie la transaction après `expires_at`, puis la classe automatiquement `paid`, `failed` ou `expired`. Une confirmation réelle tardive crédite les quotas une seule fois.

## 2. Paiement refusé

1. Utiliser uniquement un scénario de refus prévu par KPrimePay sur staging : compte de test insuffisant, code volontairement erroné ou moyen de paiement de test refusé.
2. Ne pas multiplier les tentatives sur un vrai compte si KPrimePay ne fournit pas de scénario de test officiel.
3. Vérifier que le paiement local devient `failed`, que `failure_reason` et `failed_at` sont renseignés et que les quotas restent inchangés.
4. Vérifier dans les logs que le webhook a répondu HTTP 200. Un événement d’échec valide doit être accepté, pas remis en boucle par KPrimePay.

## 3. Confirmation tardive

1. Créer un petit paiement et noter `transaction_id` ainsi que les quotas avant paiement.
2. Effectuer le paiement, puis arrêter temporairement le traitement de queue si nécessaire. Le webhook KPrimePay n’utilise toutefois pas la queue : pour simuler un retard webhook, utiliser la fonction de renvoi KPrimePay lorsqu’elle est disponible ou demander au support de rejouer l’événement staging.
3. Après le délai choisi, laisser KPrimePay renvoyer le webhook.
4. Vérifier que l’API `/transactions/debit-status` confirme `success`, que le paiement devient `paid` et que les quotas sont ajoutés une seule fois.
5. Rejouer exactement le même webhook et confirmer que les quotas ne changent plus.

Ne jamais fabriquer un webhook de succès sur le staging avec une transaction inexistante : la revérification distante doit échouer, mais cela ne teste pas une vraie confirmation tardive.

## 4. Diagnostic d’un paiement bloqué

Ordre de contrôle :

1. Chercher la transaction dans l’interface Quota et noter son statut.
2. Vérifier `storage/logs/laravel.log` autour de son `transaction_id`.
3. Contrôler si KPrimePay considère la transaction comme réussie, échouée ou expirée.
4. Si KPrimePay indique `success` mais que le statut local n’est pas `paid`, demander un renvoi du webhook. Ne pas créditer les quotas à la main.
5. Si la vérification distante échoue temporairement, le webhook retourne HTTP 503 afin que KPrimePay puisse le retenter.
6. Si montant, devise ou référence ne correspondent pas, conserver le HTTP 422 et ouvrir un incident avec les références, sans crédit manuel.

## 5. Job échoué

Lister les jobs :

```bash
php artisan queue:failed
```

Avant de relancer, identifier la cause dans `storage/logs/laravel.log`, corriger la configuration ou le code, déployer, puis redémarrer les workers :

```bash
php artisan optimize:clear
php artisan queue:restart
```

Relancer uniquement le job concerné :

```bash
php artisan queue:retry UUID_DU_JOB
```

Après succès, confirmer la livraison et la consommation dans `Communications > SMS & WhatsApp > Consommation`. Le registre `notification_deliveries` empêche de renvoyer les canaux déjà livrés.

Supprimer un job échoué uniquement s’il est définitivement obsolète et après avoir noté sa référence :

```bash
php artisan queue:forget UUID_DU_JOB
```

## 6. Informations à conserver dans un rapport d’incident

- date et heure avec fuseau ;
- compagnie et utilisateur ;
- fonctionnalité concernée ;
- `transaction_id`, `kpp_reference` ou UUID du job ;
- statut local avant/après ;
- réponse HTTP et message fournisseur ;
- quotas avant/après ;
- action réalisée et résultat ;
- confirmation de l’absence de double envoi ou double crédit.

## Réconciliation automatique

La commande suivante est exécutée automatiquement toutes les dix minutes par le scheduler :

```bash
php artisan payments:reconcile-kprimepay --limit=100
```

Pour contrôler sans modifier les statuts ni les quotas :

```bash
php artisan payments:reconcile-kprimepay --limit=100 --pretend
```

Elle examine uniquement les paiements `pending` dont `expires_at` est dépassé, interroge KPrimePay et applique le règlement atomique partagé avec le webhook. Un statut inconnu reste inchangé et est journalisé.
