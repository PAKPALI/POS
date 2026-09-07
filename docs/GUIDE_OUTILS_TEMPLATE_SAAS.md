# Guide des outils du template SaaS

Dernière mise à jour : 7 septembre 2026.

## Règle de départ

Une nouvelle interface interne ne crée pas ses propres couleurs, rayons, modales ou tableaux. Elle utilise les composants `x-ui.*`, les tokens `--ds-*` et les feuilles partagées. Les styles métier restent permis uniquement lorsqu’ils ne redéfinissent pas l’apparence fondamentale d’un composant.

Les fondations sont chargées par `partials/design-system-head` dans les layouts standards. Le layout `layouts.saas` charge aussi `saas-pages.css` : une vue SaaS ne doit donc plus ajouter cette feuille individuellement.

## Catalogue disponible

| Besoin | Outil à utiliser |
|---|---|
| Titre, description et action d’écran | `x-ui.page-header` avec slot `actions` |
| Bouton ou lien | `x-ui.button` (`primary`, `secondary`, `success`, `danger`, `warning`, `ghost`) |
| Action compacte dans une ligne de table | `x-ui.action-group` + `x-ui.action-button` |
| Statut ou badge | `x-ui.badge` ou `x-ui.status` |
| Carte, panneau, métrique | `x-ui.card`, `x-ui.glass-panel`, `x-ui.stat-card` |
| Champ, mot de passe et aide | `x-ui.input`, `x-ui.select`, `x-ui.textarea`, `x-ui.password` |
| Interrupteur | `x-ui.switch` |
| Formulaire et ses actions | `x-ui.form-actions` |
| Modal | `x-ui.modal`, avec slot `footer` si les actions doivent rester séparées |
| Alerte, information, avertissement | `x-ui.alert` ou `x-ui.notice` |
| Message dynamique JavaScript | `window.SaasUI.toast('Texte', { variant: 'success', title: 'Enregistré' })` |
| Requête JavaScript avec loader | `window.SaasUI.busy(button, promise, 'Enregistrement…')` |
| Tableau serveur ou DataTable | `x-ui.table-shell`, slots `toolbar`, `table` et `footer` |
| Filtres et exports | `x-ui.filter-panel`, `x-ui.export-panel` |
| Onglets | `x-ui.tabs` + `x-ui.tab` |
| Liste clé/valeur | `x-ui.details-list` + `x-ui.detail` |
| Progression | `x-ui.progress` |
| État vide, refus, chargement | `x-ui.empty-state`, `x-ui.permission-denied`, `x-ui.skeleton` |

## Exemples courts

```blade
<x-ui.page-header title="Produits" eyebrow="Catalogue" icon="bi-box-seam"
    description="Gérez le catalogue de votre entreprise.">
    <x-slot:actions>
        <x-ui.button href="{{ route('product.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Ajouter</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.action-group>
    <x-ui.action-button label="Modifier" href="{{ route('product.edit', $product) }}"><i class="bi bi-pencil" aria-hidden="true"></i></x-ui.action-button>
    <x-ui.action-button label="Supprimer" variant="danger" type="submit" loading-text="Suppression…"><i class="bi bi-trash" aria-hidden="true"></i></x-ui.action-button>
</x-ui.action-group>
```

Chaque action serveur utilise `data-loading-text` ou la prop `loading-text`. Le chargeur global bloque le double clic et restaure le bouton sur erreur.

## Garde-fou automatisé

Exécuter avant une livraison :

```powershell
php artisan ui:lint
php artisan ui:lint --changed
```

La commande vérifie le catalogue de composants, le chargement des fondations dans les layouts standards, les références à des composants inexistants et interdit les styles locaux dans les composants `x-ui` (sauf la variable de progression strictement nécessaire). Elle doit être ajoutée au pipeline CI avec les tests.

Les modèles de documents (PDF, e-mails) et la boutique publique possèdent un contrat de rendu distinct : ils sont exclus du contrôle de shell SaaS, mais restent soumis à leurs règles d’accessibilité et de contraste. Toute future migration de ces surfaces doit réutiliser les tokens plutôt que dupliquer une palette.

## Évolutions prévues

Le kit est prêt à recevoir, sans créer de conventions concurrentes : menu contextuel, calendrier, téléversement de fichier, recherche asynchrone, éditeur riche, graphiques et composants de facturation. Avant d’en ajouter un, créer le composant `x-ui`, son style partagé, son test et documenter son contrat dans ce guide.
