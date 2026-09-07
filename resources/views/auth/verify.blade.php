@extends('layouts.public-auth')

@section('title', 'Vérifier votre adresse e-mail')

@section('content')
    <div class="auth-flow auth-login-flow">
        <div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-envelope-check" aria-hidden="true"></i> Validation requise</span><h1>Vérifiez votre e-mail.</h1><p>Ouvrez le lien envoyé à votre adresse avant de poursuivre.</p></div>
        @if (session('resent'))<x-ui.alert variant="success">Un nouveau lien de vérification vient de vous être envoyé.</x-ui.alert>@endif
        <x-ui.card class="auth-info-card">
            <p>Vous n’avez pas reçu le message ? Vous pouvez demander un nouvel envoi.</p>
            <form method="POST" action="{{ route('verification.resend') }}">@csrf <x-ui.button type="submit" variant="secondary" loading-text="Envoi…">Renvoyer le lien</x-ui.button></form>
        </x-ui.card>
    </div>
@endsection
