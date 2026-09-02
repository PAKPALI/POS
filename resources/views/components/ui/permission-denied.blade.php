@props(['title' => "Vous n’avez pas accès à cette rubrique", 'description' => 'Votre rôle actuel ne permet pas de consulter ce contenu.'])
<x-ui.empty-state icon="bi-shield-lock" :title="$title" :description="$description" {{ $attributes }} />
