@props([
    'id',
    'title',
    'eyebrow' => null,
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $dialogSizes = [
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'modal fade saas-modal', 'id' => $id, 'tabindex' => '-1', 'aria-hidden' => 'true']) }} aria-labelledby="{{ $id }}-title">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable {{ $dialogSizes[$size] ?? '' }}">
        <div class="modal-content saas-modal-content saas-modal-{{ $variant }}">
            <div class="modal-header saas-modal-header">
                <div>
                    @if($eyebrow)
                        <p class="saas-modal-eyebrow">{{ $eyebrow }}</p>
                    @endif
                    <h2 class="modal-title" id="{{ $id }}-title">{{ $title }}</h2>
                </div>
                <button type="button" class="saas-modal-close" data-bs-dismiss="modal" aria-label="Fermer">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body saas-modal-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
