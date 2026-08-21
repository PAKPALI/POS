# Convention UI — attente des actions serveur

Cette règle est obligatoire dans tout le POS : après un clic qui déclenche une requête serveur, l’utilisateur doit immédiatement voir un loader dans le bouton utilisé. Le bouton reste désactivé jusqu’à la réponse afin d’éviter les doubles soumissions.

## Composant commun

Le fichier `public/hub/assets/js/server-button-loader.js` est chargé par les layouts principaux, les pages de compagnie, les invitations et la boutique.

Il couvre automatiquement :

- les soumissions HTML classiques ;
- les formulaires AJAX jQuery lancés immédiatement après le clic ;
- les requêtes `fetch` lancées immédiatement après un clic ou une soumission ;
- les boutons portant `data-server-action` lorsqu’ils déclenchent une requête jQuery.

Pour personnaliser le texte :

```html
<button type="submit" data-loading-text="Enregistrement…">Enregistrer</button>
```

Pour une requête `fetch` différée, Axios ou une Promise personnalisée :

```javascript
const button = event.currentTarget;
await ServerButtonLoader.withLoader(
    button,
    fetch('/endpoint', {method: 'POST'}),
    'Envoi en cours…'
);
```

Les rares boutons qui ne doivent pas recevoir ce comportement peuvent porter `data-no-server-loader`.

## Confirmations SweetAlert

Une action serveur exécutée après confirmation doit utiliser `showLoaderOnConfirm: true` et lancer la requête dans `preConfirm`. Pendant `Swal.isLoading()`, les clics extérieurs et la touche Échap doivent être bloqués. En cas d’erreur, garder la fenêtre ouverte avec `Swal.showValidationMessage(...)`.

## Critères de validation

- Le loader apparaît dès le début de l’attente serveur.
- Le bouton ne peut pas être soumis deux fois.
- Le texte et l’état initial du bouton sont restaurés après une erreur.
- Le succès n’est affiché qu’après une réponse positive.
- L’erreur serveur reste lisible et permet une nouvelle tentative.
