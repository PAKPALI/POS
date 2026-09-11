@extends('errors.layout')

@section('title', 'Session expirée')
@section('page-title', 'Session expirée')

@section('error-content')
    @include('errors.partials.robot-card', [
        'status' => 419,
        'eyebrow' => 'Session à renouveler',
        'tone' => 'is-warning',
        'signalIcon' => 'bi-clock-history',
        'robotContext' => 'is-session-expired',
        'title' => 'Votre session de sécurité a expiré',
        'description' => 'Par mesure de protection, le formulaire ne peut plus être envoyé. Revenez à la page précédente puis réessayez.',
        'helpText' => 'Aucune modification n’a été enregistrée.',
    ])
@endsection
