@extends('errors.layout')

@section('title', 'Erreur technique')
@section('page-title', 'Erreur technique')

@section('error-content')
    @include('errors.partials.robot-card', [
        'status' => 500,
        'eyebrow' => 'Intervention technique',
        'tone' => 'is-danger',
        'signalIcon' => 'bi-tools',
        'robotContext' => 'is-server-error',
        'title' => 'Le robot fait une vérification technique',
        'description' => 'Une erreur inattendue est survenue. Nos équipes peuvent la diagnostiquer ; vous pouvez réessayer dans un instant.',
        'helpText' => 'Si le problème persiste, contactez le support en précisant ce que vous étiez en train de faire.',
    ])
@endsection
