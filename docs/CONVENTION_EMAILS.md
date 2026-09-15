# Convention des e-mails applicatifs

Ce document est la référence pour tout nouvel e-mail HTML envoyé par l’application.

## Gabarit obligatoire

Une vue e-mail doit inclure les éléments partagés suivants :

```blade
@include('emails.design.emailStyle')
@include('emails.design.emailHeader', ['subtitle' => 'Contexte du message'])
@include('emails.design.emailFooter', ['company' => null])
```

Le header commun affiche le nom de l’application et un sous-titre orange. Le footer commun affiche l’émetteur et le copyright. Pour un message rattaché à une entreprise, transmettre `company` au footer afin d’afficher son nom.

## Règles de contenu

- Utiliser une vue HTML dédiée plutôt que le rendu Markdown par défaut de `MailMessage`.
- Garder une largeur maximale de 600 px et un contenu lisible sur mobile.
- Utiliser le français, un objet explicite et une salutation personnalisée lorsque le destinataire est connu.
- Pour un code sensible, afficher un bloc de code visible, son délai d’expiration et la règle d’usage unique.
- Ajouter un avertissement si l’action peut être initiée par un tiers.
- Ne jamais inclure de secret permanent, mot de passe ou donnée sensible non nécessaire.
- Les notifications doivent passer par la file d’attente lorsqu’elles peuvent être différées et respecter les réglages d’envoi de l’application.

## Référence d’implémentation

Les e-mails de sécurité partenaire `twoFactorLogin`, `twoFactorSetup`, `withdrawalConfirmation` et `withdrawalAccountConfirmation` illustrent ce contrat. Toute nouvelle notification doit réutiliser ces partials et faire l’objet d’un test de rendu vérifiant au minimum le header, le contenu principal et le footer.
