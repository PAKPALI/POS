@extends('errors.layout')

@section('title', 'Trop de tentatives')
@section('page-title', 'Trop de tentatives')

@section('error-content')
    @include('errors.partials.robot-card', [
        'status' => 429,
        'eyebrow' => 'Rythme trop rapide',
        'tone' => 'is-warning',
        'signalIcon' => 'bi-speedometer',
        'robotContext' => 'is-rate-limited',
        'title' => 'Prenons une courte pause',
        'description' => 'Trop de demandes ont été envoyées en peu de temps. Attendez quelques instants avant de réessayer.',
        'helpText' => 'Cette limite protège votre compte et la plateforme.',
    ])
@endsection
