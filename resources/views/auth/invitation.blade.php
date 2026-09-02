@extends('layouts.public-auth')

@section('title', 'Invitation — '.$invitation->company->name)

@push('styles')
<link href="{{ asset('hub/assets/css/invitation.css') }}?v=20260902-1" rel="stylesheet">
@endpush

@section('content')
<article class="invitation-flow" aria-labelledby="invitation-title">
    <header class="invitation-header">
        @if($invitation->company->logo)
            <img src="{{ asset($invitation->company->logo) }}" alt="Logo de {{ $invitation->company->name }}" class="invitation-company-logo" width="72" height="72">
        @else
            <div class="invitation-company-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($invitation->company->name, 0, 1)) }}</div>
        @endif
        <p class="auth-flow-kicker">Invitation professionnelle</p>
        <h1 id="invitation-title">Rejoindre {{ $invitation->company->name }}</h1>
        <x-ui.status variant="active"><i class="bi bi-person-check" aria-hidden="true"></i> Rôle proposé : {{ $invitation->role?->name ?? 'Non disponible' }}</x-ui.status>
    </header>

    <div class="invitation-scroll-body">
        @if(!$invitation->isPending())
            <x-ui.alert variant="warning">Cette invitation est {{ mb_strtolower($invitation->status_label) }} et ne peut plus être utilisée.</x-ui.alert>
        @else
            <dl class="invitation-meta" aria-label="Informations de l’invitation">
                <div><dt>Compte invité</dt><dd>{{ $invitation->email }}</dd></div>
                <div><dt>Valable jusqu’au</dt><dd>{{ $invitation->expires_at->format('d/m/Y à H:i') }}</dd></div>
            </dl>
            @if(auth()->check() && strcasecmp(auth()->user()->email, $invitation->email) !== 0)
                <x-ui.alert variant="warning" role="alert">Vous êtes connecté avec <strong>{{ auth()->user()->email }}</strong>. Cette invitation appartient au compte <strong>{{ $invitation->email }}</strong>. Reconnectez-vous avec ce compte pour continuer.</x-ui.alert>
            @endif
            <form method="POST" action="{{ route('invitations.accept', $token) }}" class="invitation-account-panel">
                @csrf
                @if($existingUser)
                    <h2>Votre compte est prêt</h2><p>Pour protéger votre compte, vous devez être connecté avec <strong>{{ $invitation->email }}</strong> avant d’accepter.</p>
                @else
                    <h2>Créez votre accès</h2><p>Ces informations vous permettront de vous reconnecter à toutes les entreprises auxquelles vous avez accès.</p>
                    <x-ui.input id="invitation-name" name="name" label="Nom complet" :value="old('name')" autocomplete="name" required />
                    <x-ui.input id="invitation-phone" name="phone" type="tel" label="Téléphone (facultatif)" :value="old('phone')" autocomplete="tel" />
                    <x-ui.password id="invitation-password" name="password" label="Mot de passe" hint="Au moins 8 caractères." autocomplete="new-password" minlength="8" required />
                    <x-ui.password id="invitation-password-confirmation" name="password_confirmation" label="Confirmer le mot de passe" autocomplete="new-password" minlength="8" required />
                @endif
                @if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
                <x-ui.button class="w-100" type="submit" variant="primary" data-loading-text="Validation en cours…" :disabled="$existingUser && auth()->check() && auth()->id() !== $existingUser->id">{{ $existingUser ? (auth()->check() ? 'Accepter l’invitation' : 'Me connecter pour accepter') : 'Créer mon compte et rejoindre l’entreprise' }}</x-ui.button>
            </form>
            <form method="POST" action="{{ route('invitations.decline', $token) }}" class="invitation-decline-form" data-confirm-message="Refuser définitivement cette invitation ?">@csrf<x-ui.button class="w-100" type="submit" variant="danger" data-loading-text="Refus en cours…">Refuser l’invitation</x-ui.button></form>
            <p class="invitation-security-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Lien sécurisé, personnel et utilisable une seule fois. Ne le transférez à personne.</p>
        @endif
    </div>
</article>
@endsection

@push('scripts')
<script>document.querySelector('.invitation-decline-form')?.addEventListener('submit',function(event){if(!window.confirm(this.dataset.confirmMessage))event.preventDefault();});</script>
@endpush
