@extends('layouts.public-auth')
@section('title', 'Maintenance')
@section('content')
<div class="auth-flow text-center">
    <div class="maintenance-icon" aria-hidden="true"><i class="bi bi-tools"></i></div>
    <div class="auth-flow-heading"><p class="auth-flow-kicker">Intervention planifiée</p><h1>Maintenance en cours</h1><p>{{ $message }}</p></div>
    @if($supportEmail)<a href="mailto:{{ $supportEmail }}" class="saas-btn saas-btn-secondary">Contacter le support</a>@endif
</div>
@endsection
@push('styles')<link href="{{ asset('hub/assets/css/error-pages.css') }}?v=20260902-1" rel="stylesheet">@endpush
