@php
    $emailCompany = isset($company) ? $company : (isset($invitation) ? $invitation->company : null);
    $emailCompanyName = $emailCompany?->name ?? 'Votre entreprise';
@endphp
<div class="footer">
    <p style="margin-bottom:8px;">Cet e-mail vous est envoyé par <strong style="color:#ff5b57;">{{ $emailCompanyName }}</strong>.</p>
    <p style="margin:0;font-size:12px;color:#d1d5db;">
        Copyright &copy; {{ now()->year }} <strong style="color:#ff5b57;">{{ config('app.name') }}</strong>. Tous droits réservés.
    </p>
</div>
