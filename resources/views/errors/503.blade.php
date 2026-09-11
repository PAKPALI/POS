@extends('errors.layout')

@section('title', 'Service temporairement indisponible')
@section('page-title', 'Service temporairement indisponible')

@section('error-content')
    @include('errors.partials.robot-card', [
        'status' => 503,
        'eyebrow' => 'Maintenance en cours',
        'tone' => 'is-violet',
        'signalIcon' => 'bi-wrench-adjustable-circle',
        'robotContext' => 'is-maintenance',
        'title' => 'Le robot améliore votre espace',
        'description' => 'Le service est momentanément indisponible pendant une opération de maintenance. Revenez dans quelques minutes.',
        'showBack' => false,
        'helpText' => 'Vos données restent protégées pendant cette intervention.',
    ])
@endsection
