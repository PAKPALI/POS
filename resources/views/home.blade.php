@extends('layouts.saas')
@section('title', 'Accueil')
@section('page-title', 'Accueil')
@section('content')
<x-ui.empty-state icon="bi-arrow-right-circle" title="Votre espace est prêt" description="Utilisez la navigation pour accéder à la première fonctionnalité autorisée."><a class="saas-btn saas-btn-primary" href="{{ route('dashboard') }}">Ouvrir le tableau de bord</a></x-ui.empty-state>
@endsection
