@props(['id', 'name' => 'image', 'label' => 'Image', 'current' => null])

<div class="saas-image-upload" data-image-upload>
    <input id="{{ $id }}" name="{{ $name }}" type="file" class="visually-hidden" accept="image/jpeg,image/png,image/gif,image/webp" data-image-upload-input>
    <label class="saas-image-upload-picker" for="{{ $id }}">
        <i class="bi bi-image" aria-hidden="true"></i>
        <span class="saas-image-upload-copy">
            <strong>{{ $label }}</strong>
            <small>JPEG, PNG, GIF ou WebP · jusqu’à 10 Mo</small>
        </span>
        <em>Parcourir</em>
    </label>
    <div class="saas-image-upload-preview" data-image-upload-preview @if(!$current) hidden @endif>
        <span class="saas-image-upload-thumb"><img src="{{ $current ?: '' }}" alt="Aperçu de l’image" data-image-upload-image></span>
        <span class="saas-image-upload-file">
            <strong data-image-upload-name>{{ $current ? 'Image actuelle' : '' }}</strong>
            <small data-image-upload-meta>{{ $current ? 'Une nouvelle image remplacera celle-ci.' : '' }}</small>
        </span>
        <button type="button" class="saas-image-upload-clear" data-image-upload-clear @if(!$current) hidden @endif aria-label="Retirer l’image sélectionnée">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>
    <div class="saas-image-upload-progress" data-image-upload-progress hidden aria-live="polite">
        <span class="saas-image-upload-progress-track"><i data-image-upload-progress-bar></i></span>
        <small data-image-upload-progress-text>Préparation…</small>
    </div>
</div>
