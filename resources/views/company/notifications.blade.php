@extends('layouts.saas')
@section('title', 'Configuration des communications')
@push('styles')
<link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260902-16" rel="stylesheet">
<link href="{{ asset('hub/assets/css/saas-page-fixes.css') }}?v=20260902-7" rel="stylesheet">
<style>
    .recipient-switch-cell { text-align: center; vertical-align: middle; }
    .recipient-toggle { display: inline-flex; align-items: center; justify-content: center; position: relative; cursor: pointer; padding: 4px 0; }
    .recipient-toggle .saas-switch-control { flex: 0 0 40px; width: 40px; height: 22px; margin: 0; }
    .recipient-toggle .saas-switch-control::after { width: 16px; height: 16px; top: 3px; left: 3px; }
    .recipient-toggle .saas-switch-input:checked + .saas-switch-control::after { transform: translateX(18px); }
    .notification-user-row td { vertical-align: middle; }
    .notification-user-name { font-weight: 650; color: var(--ds-text-primary); display: block; }
    .notification-user-email { font-size: .76rem; color: var(--ds-text-muted); display: block; }
    .recipient-toggle.is-phone-required { opacity: .62; }
    .recipient-toggle.is-phone-required:hover { opacity: .9; }
</style>
@endpush

@section('content')
<div class="saas-page-heading">
    <div><h1>Configuration SMS &amp; WhatsApp</h1><p>Canaux d'envoi et destinataires pour {{ $company->name }}.</p></div>
    <a class="saas-btn saas-btn-ghost" href="{{ route('communications.index') }}"><i class="bi bi-clock-history"></i> Voir la consommation</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<form method="POST" action="{{ route('notifications.update') }}">
    @csrf
    @method('PUT')

    <section class="saas-card">
        <div class="saas-card-head"><div><h2>Notifications internes</h2><p class="saas-card-description">Le canal global et la préférence du destinataire doivent être activés.</p></div></div>
        <div class="communication-settings-grid">
            @foreach(['sale' => 'Ventes', 'inventory' => 'Inventaire'] as $category => $label)
            <div>
                <div class="communication-channel-card">
                    <h5>{{ $label }}</h5>
                    <p>{{ $category === 'sale' ? 'Alertes générées après une vente.' : 'Alertes liées aux mouvements de stock.' }}</p>
                    @foreach(['email' => ['bi-envelope', 'E-mail'], 'whatsapp' => ['bi-whatsapp', 'WhatsApp'], 'sms' => ['bi-chat-text', 'SMS']] as $channel => [$icon, $channelLabel])
                        <label class="saas-switch-line" for="{{ $category }}_{{ $channel }}_enabled">
                            <span><i class="bi {{ $icon }}"></i><strong>{{ $channelLabel }}</strong></span>
                            <input class="saas-switch-input" type="checkbox" role="switch" name="{{ $category }}_{{ $channel }}_enabled" value="1" id="{{ $category }}_{{ $channel }}_enabled" {{ $company->{$category.'_'.$channel.'_enabled'} ? 'checked' : '' }}>
                            <span class="saas-switch-control" aria-hidden="true"></span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <section class="saas-card">
        <div class="saas-card-head"><div><h2>Factures clients</h2><p class="saas-card-description">Réglages indépendants des alertes internes. Chaque envoi consomme une unité du quota correspondant.</p></div></div>
        <div class="communication-settings-grid">
            <label class="saas-switch-line is-large" for="invoice_whatsapp_enabled">
                <span><i class="bi bi-whatsapp"></i><span><strong>WhatsApp</strong><small>{{ number_format($company->whatsapp_count, 0, ',', ' ') }} disponible(s)</small></span></span>
                <input class="saas-switch-input" type="checkbox" role="switch" name="invoice_whatsapp_enabled" value="1" id="invoice_whatsapp_enabled" {{ $company->invoice_whatsapp_enabled ? 'checked' : '' }}>
                <span class="saas-switch-control" aria-hidden="true"></span>
            </label>
            <label class="saas-switch-line is-large" for="invoice_sms_enabled">
                <span><i class="bi bi-chat-text"></i><span><strong>SMS</strong><small>{{ number_format($company->sms_count, 0, ',', ' ') }} disponible(s)</small></span></span>
                <input class="saas-switch-input" type="checkbox" role="switch" name="invoice_sms_enabled" value="1" id="invoice_sms_enabled" {{ $company->invoice_sms_enabled ? 'checked' : '' }}>
                <span class="saas-switch-control" aria-hidden="true"></span>
            </label>
        </div>
    </section>

    @foreach(['sale' => 'Notifications des ventes', 'inventory' => 'Notifications d\'inventaire'] as $category => $title)
    <section class="saas-card">
        <div class="saas-card-head"><div><h2>{{ $title }}</h2><p class="saas-card-description">Sélectionnez séparément les canaux autorisés pour chaque utilisateur.</p></div></div>
        <div class="table-responsive notification-users-scroll">
            <table class="saas-data-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th class="text-center">E-mail</th>
                        <th class="text-center">WhatsApp</th>
                        <th class="text-center">SMS</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    @php
                        $preference = $preferences->get($user->id.'-'.$category);
                        $roleKey = $user->memberships->first()?->role?->key;
                        $isPrivileged = in_array($roleKey, ['owner', 'admin'], true);
                    @endphp
                    <tr class="notification-user-row">
                        <td>
                            <span class="notification-user-name">{{ $user->name }}</span>
                            <span class="notification-user-email">{{ $user->email }} · {{ $user->phone ?: 'Téléphone non renseigné' }}</span>
                        </td>
                        <td data-label="Rôle"><span class="saas-status-badge {{ $isPrivileged ? 'is-info' : 'is-neutral' }}">{{ $user->memberships->first()?->role?->name ?: 'Sans rôle' }}</span></td>
                        @foreach(['email', 'whatsapp', 'sms'] as $channel)
                            @php
                                $isEnabled = $preference ? $preference->{$channel.'_enabled'} : ($isPrivileged && in_array($channel, ['email', 'whatsapp'], true));
                                if ($channel !== 'email' && !$user->phone) $isEnabled = false;
                            @endphp
                            <td class="recipient-switch-cell" data-label="{{ ucfirst($channel) }}">
                                <label class="recipient-toggle {{ $channel !== 'email' && !$user->phone ? 'is-phone-required' : '' }}" @if($channel !== 'email' && !$user->phone) data-phone-required="true" @endif>
                                    <input class="saas-switch-input" type="checkbox" role="switch" name="recipients[{{ $category }}][{{ $user->id }}][{{ $channel }}]" value="1" {{ $isEnabled ? 'checked' : '' }} @if($channel !== 'email' && !$user->phone) data-phone-required="true" aria-disabled="true" title="Veuillez renseigner un numéro de téléphone" @endif>
                                    <span class="saas-switch-control" aria-hidden="true"></span>
                                </label>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Aucun propriétaire ou administrateur actif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endforeach

    <div class="notifications-save-actions d-flex justify-content-end mb-5">
        <button class="saas-btn saas-btn-primary" type="submit" data-loading-text="Enregistrement…"><i class="bi bi-check2-circle"></i> Enregistrer les notifications</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.recipient-toggle[data-phone-required]');
        if (!toggle) return;
        const input = toggle.querySelector('input');
        event.preventDefault();
        input.checked = false;
        Swal.fire({
            icon: 'info',
            title: 'Numéro requis',
            text: 'Veuillez renseigner un numéro de téléphone avant d’activer WhatsApp ou SMS.',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' }
        });
    });
});
</script>
@endpush
