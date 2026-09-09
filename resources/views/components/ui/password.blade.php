@props(['id', 'label' => null, 'hint' => null, 'error' => null])
<div class="saas-password-control">
    <div {{ $attributes->only('class')->class('saas-form-group') }}>
        @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
        <input id="{{ $id }}" type="password" {{ $attributes->except('class') }} @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
        <button class="ds-password-toggle" type="button" data-password-toggle="{{ $id }}" aria-label="Afficher le mot de passe" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
        @if($hint)<small>{{ $hint }}</small>@endif
        @if($error)<small id="{{ $id }}-error" class="saas-field-error" role="alert">{{ $error }}</small>@endif
    </div>
</div>
