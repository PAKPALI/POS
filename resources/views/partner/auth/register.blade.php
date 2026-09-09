@extends('layouts.public-auth')
@section('title', 'Inscription partenaire')
@section('content')
<div class="auth-flow"><div class="auth-flow-heading"><span class="auth-flow-kicker"><i class="bi bi-person-plus" aria-hidden="true"></i> Partenaires Maxanou</span><h1>Rejoignez le programme.</h1><p>Créez votre espace. Votre adresse sera vérifiée avant toute activation.</p></div>
@if($errors->any())<x-ui.alert variant="danger">{{ $errors->first() }}</x-ui.alert>@endif
<form method="POST" action="{{ route('partner.register.submit') }}">@csrf
<x-ui.input id="name" name="name" label="Nom complet" :value="old('name')" required autocomplete="name" :error="$errors->first('name')" />
<x-ui.input id="username" name="username" label="Pseudonyme partenaire" :value="old('username')" required autocomplete="username" help="3 à 30 caractères : lettres, chiffres, tiret ou underscore." :error="$errors->first('username')" />
<x-ui.input id="email" name="email" type="email" label="Adresse e-mail" :value="old('email')" required autocomplete="email" :error="$errors->first('email')" />
<div class="profile-form-grid">
    <x-ui.select id="country_code" name="country_code" label="Pays de résidence" required :error="$errors->first('country_code')">
        @foreach($countries as $country)
            <option value="{{ $country['code'] }}" data-dial-code="{{ $country['dial_code'] }}" data-min-length="{{ $country['phone_min_length'] }}" data-max-length="{{ $country['phone_max_length'] }}" @selected(old('country_code', $countries[0]['code']) === $country['code'])>{{ $country['name'] }} ({{ $country['dial_code'] }})</option>
        @endforeach
    </x-ui.select>
    <div class="saas-form-group">
        <label for="phone_number">Numéro</label>
        <div class="partner-phone-control">
            <span class="partner-phone-prefix" id="partnerDialCode" aria-hidden="true">{{ $countries[0]['dial_code'] }}</span>
            <input id="phone_number" name="phone_number" type="tel" inputmode="numeric" autocomplete="tel-national" value="{{ old('phone_number') }}" placeholder="90 00 00 00" aria-describedby="partnerPhoneHint @if($errors->has('phone_number')) phone_number-error @endif" required>
        </div>
        <small id="partnerPhoneHint">Saisissez le numéro national, sans indicatif.</small>
        @if($errors->has('phone_number'))<small id="phone_number-error" class="saas-field-error" role="alert">{{ $errors->first('phone_number') }}</small>@endif
    </div>
</div>
<x-ui.password id="password" name="password" label="Mot de passe" hint="12 caractères minimum, avec majuscule, minuscule, chiffre et symbole." required autocomplete="new-password" :error="$errors->first('password')" /><x-ui.password id="password_confirmation" name="password_confirmation" label="Confirmer le mot de passe" required autocomplete="new-password" />
<label class="saas-check-control" for="accepted_terms"><input id="accepted_terms" name="accepted_terms" type="checkbox" value="1" @checked(old('accepted_terms')) required><span>J’accepte les conditions du programme partenaire.</span></label>
<x-ui.form-actions class="auth-form-actions"><x-ui.button type="submit" class="w-100 auth-submit" loading-text="Création en cours…">Créer mon compte</x-ui.button></x-ui.form-actions>
<p class="auth-flow-link"><a href="{{ route('partner.login') }}">Retour à la connexion</a></p></form></div>
@endsection
@push('scripts')
<script>
(() => {
    const country = document.getElementById('country_code');
    const dialCode = document.getElementById('partnerDialCode');
    const hint = document.getElementById('partnerPhoneHint');
    const refreshPhoneFormat = () => {
        const option = country.options[country.selectedIndex];
        const min = option.dataset.minLength;
        const max = option.dataset.maxLength;
        dialCode.textContent = option.dataset.dialCode;
        document.getElementById('phone_number').setAttribute('aria-label', 'Numéro national, indicatif ' + option.dataset.dialCode);
        hint.textContent = min === max
            ? 'Saisissez les ' + min + ' chiffres du numéro national, sans indicatif.'
            : 'Saisissez entre ' + min + ' et ' + max + ' chiffres, sans indicatif.';
    };
    country.addEventListener('change', refreshPhoneFormat);
    refreshPhoneFormat();
})();
</script>
@endpush
