@extends('errors.layout')

@section('title', 'Page introuvable')
@section('page-title', 'Page introuvable')

@section('error-content')
    @include('errors.partials.robot-card', [
        'status' => 404,
        'eyebrow' => 'Chemin introuvable',
        'signalIcon' => 'bi-search',
        'robotContext' => 'is-not-found',
        'title' => 'Cette page semble avoir disparu',
        'description' => 'Le robot a vérifié l’adresse demandée, mais aucun écran ne correspond à ce lien.',
        'helpText' => 'Vérifiez le lien ou revenez à un espace connu.',
    ])
@endsection
