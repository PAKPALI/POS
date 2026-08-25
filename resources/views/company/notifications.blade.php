@extends('layouts.layout')

@push('css-scripts')
<style>
    .notification-users-scroll { max-height: 245px; overflow-y: auto; }
    .notification-users-scroll thead th { position: sticky; top: 0; z-index: 2; background: var(--bs-body-bg, #1e1e2f); }
</style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="page-header mb-1">Notifications</h1>
            <p class="text-muted mb-0">Autorisations et destinataires pour {{ $company->name }}</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('notifications.update') }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-body">
                <h4>Activation des canaux</h4>
                <p class="text-muted">Ces interrupteurs autorisent globalement les envois par e-mail, WhatsApp et SMS de la catégorie.</p>
                <div class="row g-3">
                    @foreach(['sale' => 'Ventes', 'inventory' => 'Inventaire'] as $category => $label)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5>{{ $label }}</h5>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="{{ $category }}_email_enabled" value="1" id="{{ $category }}_email_enabled" {{ $company->{$category.'_email_enabled'} ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $category }}_email_enabled">Autoriser les e-mails</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="{{ $category }}_whatsapp_enabled" value="1" id="{{ $category }}_whatsapp_enabled" {{ $company->{$category.'_whatsapp_enabled'} ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $category }}_whatsapp_enabled">Autoriser WhatsApp</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="{{ $category }}_sms_enabled" value="1" id="{{ $category }}_sms_enabled" {{ $company->{$category.'_sms_enabled'} ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $category }}_sms_enabled">Autoriser les SMS</label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @foreach(['sale' => 'Notifications des ventes', 'inventory' => 'Notifications d’inventaire'] as $category => $title)
        <div class="card mb-4">
            <div class="card-body">
                <h4>{{ $title }}</h4>
                <p class="text-muted">Tous les utilisateurs actifs sont disponibles. Les propriétaires et administrateurs sont placés en tête et activés par défaut.</p>
                <div class="table-responsive notification-users-scroll">
                    <table class="table table-striped align-middle">
                        <thead><tr><th>Utilisateur</th><th>Rôle</th><th class="text-center">E-mail</th><th class="text-center">WhatsApp</th><th class="text-center">SMS</th></tr></thead>
                        <tbody>
                        @forelse($users as $user)
                            @php
                                $preference = $preferences->get($user->id.'-'.$category);
                                $roleKey = $user->memberships->first()?->role?->key;
                                $isPrivileged = in_array($roleKey, ['owner', 'admin'], true);
                            @endphp
                            <tr>
                                <td><strong>{{ $user->name }}</strong><div class="small text-muted">{{ $user->email }} · {{ $user->phone ?: 'Téléphone non renseigné' }}</div></td>
                                <td>{{ $user->memberships->first()?->role?->name }}</td>
                                @foreach(['email', 'whatsapp', 'sms'] as $channel)
                                @php $isEnabled = $preference ? $preference->{$channel.'_enabled'} : ($isPrivileged && in_array($channel, ['email', 'whatsapp'], true)); @endphp
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox" name="recipients[{{ $category }}][{{ $user->id }}][{{ $channel }}]" value="1" {{ $isEnabled ? 'checked' : '' }} {{ $channel !== 'email' && !$user->phone ? 'disabled' : '' }}>
                                </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucun propriétaire ou administrateur actif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-end mb-5">
            <button class="btn btn-theme" type="submit"><i class="bi bi-check2-circle me-2"></i>Enregistrer les notifications</button>
        </div>
    </form>
</div>
@endsection
