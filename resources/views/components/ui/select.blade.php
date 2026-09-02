@props(['id', 'label' => null, 'hint' => null, 'error' => null])
<div {{ $attributes->only('class')->class('saas-form-group') }}>
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    <select id="{{ $id }}" {{ $attributes->except('class') }} @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>{{ $slot }}</select>
    @if($hint)<small>{{ $hint }}</small>@endif
    @if($error)<small id="{{ $id }}-error" class="saas-field-error" role="alert">{{ $error }}</small>@endif
</div>
