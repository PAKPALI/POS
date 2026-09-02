@extends('layouts.public-auth')

@section('title', $title)

@section('content')
<x-ui.empty-state icon="bi-shield-check" :title="$title" :description="$message"><a href="{{ route('user_login') }}" class="saas-btn saas-btn-primary">Accéder à la connexion</a></x-ui.empty-state>
@endsection
