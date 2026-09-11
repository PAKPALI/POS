@extends('layouts.partner')
@section('title', 'Confirmer le compte Mobile Money')
@section('page-title', 'Confirmer le compte Mobile Money')
@section('content')
<x-ui.page-header title="Confirmez votre compte Mobile Money" eyebrow="Vérification sécurisée" icon="bi-phone-vibrate" description="Saisissez le code envoyé à votre adresse e-mail pour rendre ce compte disponible pour vos retraits." />
<x-ui.card title="Code reçu par e-mail" description="Le code expire dans 10 minutes et ne peut être utilisé qu’une seule fois.">
    <form method="POST" action="{{ route('partner.withdrawals.accounts.confirm.submit') }}">@csrf
        <div class="saas-form-group"><label for="withdrawalAccountCode">Code de confirmation</label><input id="withdrawalAccountCode" name="code" class="form-control" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" required autofocus></div>
        <div class="d-flex gap-2"><x-ui.button type="submit" loading-text="Vérification…"><i class="bi bi-check2-circle" aria-hidden="true"></i> Vérifier le compte</x-ui.button><x-ui.button :href="route('partner.withdrawals')" variant="ghost">Annuler</x-ui.button></div>
    </form>
</x-ui.card>
@endsection
