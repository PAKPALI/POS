# Règles de contribution POS

## Retour visuel des actions serveur

- Toute action déclenchée par l’utilisateur qui attend une réponse du serveur doit afficher un loader dans le bouton déclencheur et empêcher les doubles clics jusqu’à la fin de la requête.
- Utiliser le composant global `window.ServerButtonLoader` décrit dans `docs/UI_CONVENTIONS.md`. Les formulaires ainsi que les appels jQuery/Fetch immédiats sont pris en charge automatiquement ; utiliser `withLoader` pour Axios, un Fetch différé ou un traitement asynchrone personnalisé.
- Pour une action confirmée par SweetAlert, utiliser `showLoaderOnConfirm`, exécuter la requête dans `preConfirm` et bloquer la fermeture pendant `Swal.isLoading()`.
- Toujours restaurer le bouton après une erreur et afficher un message exploitable. Ne jamais afficher un succès avant la réponse positive du serveur.
- Appliquer cette règle à chaque nouvelle fonctionnalité ou modification d’une action serveur.
