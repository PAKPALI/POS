@extends('layouts.partner')
@section('title', 'Confirmer le retrait')
@section('page-title', 'Confirmer le retrait')
@section('content')
<x-ui.page-header title="Confirmez votre retrait" eyebrow="Sécurité" icon="bi-shield-lock" description="Saisissez le code à six chiffres envoyé à votre adresse e-mail. Il expire dans 10 minutes." />
<x-ui.card title="Code de confirmation" description="Aucun transfert externe n’est encore effectué à cette étape.">
    <form method="POST" action="{{ route('partner.withdrawals.confirm.submit') }}">@csrf
        <div class="saas-form-group"><label for="withdrawalCode">Code reçu par e-mail</label><input id="withdrawalCode" name="code" class="form-control" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" required autofocus></div>
        <div class="d-flex gap-2"><x-ui.button type="submit" loading-text="Vérification…"><i class="bi bi-check2-circle" aria-hidden="true"></i> Confirmer la demande</x-ui.button><x-ui.button :href="route('partner.withdrawals')" variant="ghost">Annuler</x-ui.button></div>
    </form>
</x-ui.card>
@endsection
