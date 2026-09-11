@extends('errors.layout')

@section('title', 'Action indisponible')
@section('page-title', 'Action indisponible')

@section('error-content')
    @include('errors.partials.robot-card', [
        'status' => 405,
        'eyebrow' => 'Action non autorisée',
        'signalIcon' => 'bi-arrow-repeat',
        'robotContext' => 'is-not-found',
        'title' => 'Cette action ne peut pas être effectuée ici',
        'description' => 'Le robot a bien reçu votre demande, mais cette opération n’est pas disponible pour cette adresse.',
        'helpText' => 'Revenez à l’écran précédent et utilisez les actions proposées.',
    ])
@endsection
