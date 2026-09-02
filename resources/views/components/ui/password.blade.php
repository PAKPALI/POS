@props(['id', 'label' => null, 'hint' => null, 'error' => null])
<div class="saas-password-control">
    <x-ui.input :id="$id" :label="$label" :hint="$hint" :error="$error" type="password" {{ $attributes }} />
    <button class="saas-password-toggle" type="button" data-password-toggle="{{ $id }}" aria-label="Afficher le mot de passe" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
</div>
