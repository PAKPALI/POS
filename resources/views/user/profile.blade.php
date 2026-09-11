@extends('layouts.saas')

@section('title', 'Mon profil')
@section('eyebrow', 'Compte personnel')
@section('page-title', 'Mon profil')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
@php
    $user = auth()->user();
    $mode = in_array($user->appearance_mode, ['system', 'dark', 'light'], true) ? $user->appearance_mode : 'dark';
    $accent = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $user->accent_color) ? strtoupper($user->accent_color) : '#3B82F6';
    $phoneCountry = $user->country_code ?? 'TG';
    $phoneRule = config('african_phone_rules.'.$phoneCountry, []);
    $storedPhone = preg_replace('/\D+/', '', (string) $user->phone);
    $profilePhone = $storedPhone;
    if ($storedPhone !== '' && !empty($phoneRule['dial_code']) && str_starts_with($storedPhone, $phoneRule['dial_code'])) {
        $candidate = substr($storedPhone, strlen($phoneRule['dial_code']));
        if (strlen($candidate) >= ($phoneRule['min_length'] ?? 0) && strlen($candidate) <= ($phoneRule['max_length'] ?? PHP_INT_MAX)) $profilePhone = $candidate;
    }
@endphp

<section class="saas-page-heading profile-page-heading">
    <div><h1>Paramètres du compte</h1><p>Gérez vos accès et personnalisez votre environnement de travail.</p></div>
</section>

<div id="profileFeedback" class="profile-feedback" role="status" aria-live="polite" hidden></div>

<div class="profile-layout">
    <aside class="profile-summary saas-panel">
        <div class="profile-avatar-large">{{ strtoupper(substr(trim($user->name), 0, 1)) }}</div>
        <h2>{{ $user->name }}</h2>
        <p>{{ $user->email }}</p>
        <div class="profile-company"><span><i class="bi bi-buildings"></i>Entreprise active</span><strong>{{ $activeCompany->name ?? 'Non sélectionnée' }}</strong></div>
        <div class="profile-company"><span><i class="bi bi-shield-check"></i>Rôle actuel</span><strong>{{ $currentMembership?->role?->name ?? 'Membre' }}</strong></div>
        <div class="profile-company"><span><i class="bi bi-phone"></i>Téléphone</span><strong>{{ $profilePhone ? (!empty($phoneRule['dial_code']) ? '+'.$phoneRule['dial_code'].' ' : $phoneCountry.' ').$profilePhone : 'Non renseigné' }}</strong></div>
        <a href="{{ route('companies.select') }}" class="profile-company-link"><i class="bi bi-arrow-left-right"></i>Changer d’entreprise</a>
    </aside>

    <section class="profile-settings saas-panel">
        <div class="profile-tabs" role="tablist" aria-label="Paramètres du profil">
            <button type="button" class="profile-tab is-active" id="profileTabEmail" data-profile-tab="email" role="tab" aria-selected="true" aria-controls="profilePanelEmail"><i class="bi bi-envelope"></i><span>Adresse e-mail</span></button>
            <button type="button" class="profile-tab" id="profileTabPhone" data-profile-tab="phone" role="tab" aria-selected="false" aria-controls="profilePanelPhone"><i class="bi bi-phone"></i><span>Téléphone</span></button>
            <button type="button" class="profile-tab" id="profileTabPassword" data-profile-tab="password" role="tab" aria-selected="false" aria-controls="profilePanelPassword"><i class="bi bi-key"></i><span>Mot de passe</span></button>
            <button type="button" class="profile-tab" id="profileTabAppearance" data-profile-tab="appearance" role="tab" aria-selected="false" aria-controls="profilePanelAppearance"><i class="bi bi-palette"></i><span>Apparence</span></button>
        </div>

        <div class="profile-panel is-active" id="profilePanelEmail" data-profile-panel="email" role="tabpanel" aria-labelledby="profileTabEmail">
            <div class="profile-panel-heading"><span class="profile-panel-icon"><i class="bi bi-envelope-check"></i></span><div><h2>Modifier l’adresse e-mail</h2><p>Cette adresse sert à vous connecter et à recevoir les informations de votre compte.</p></div></div>
            <form id="profileEmailForm" class="profile-form" action="{{ route('profile.email.update') }}" method="POST">
                @csrf
                <div class="profile-field"><label for="currentEmail">Adresse actuelle</label><input id="currentEmail" type="email" value="{{ $user->email }}" readonly></div>
                <div class="profile-form-grid">
                    <div class="profile-field"><label for="newEmail">Nouvelle adresse e-mail</label><input id="newEmail" name="NE" type="email" autocomplete="email" required placeholder="nom@exemple.com"></div>
                    <div class="profile-field"><label for="confirmEmail">Confirmer l’adresse</label><input id="confirmEmail" name="CE" type="email" autocomplete="email" required placeholder="Répétez la nouvelle adresse"></div>
                </div>
                <div class="profile-field"><label for="emailCurrentPassword">Mot de passe actuel</label><div class="profile-password-control"><input id="emailCurrentPassword" name="current_password" type="password" autocomplete="current-password" required placeholder="Confirmez votre identité"><button type="button" data-password-toggle="emailCurrentPassword" aria-label="Afficher le mot de passe"><i class="bi bi-eye"></i></button></div></div>
                <div class="profile-form-actions"><button type="submit" class="saas-primary-action" data-loading-text="Modification…"><i class="bi bi-check2"></i>Modifier mon e-mail</button></div>
            </form>
        </div>

        <div class="profile-panel" id="profilePanelPhone" data-profile-panel="phone" role="tabpanel" aria-labelledby="profileTabPhone" hidden>
            <div class="profile-panel-heading"><span class="profile-panel-icon"><i class="bi bi-phone-fill"></i></span><div><h2>Ajouter ou modifier mon téléphone</h2><p>Ce numéro est utilisé uniquement pour les notifications SMS et WhatsApp que vous autorisez.</p></div></div>
            <form id="profilePhoneForm" class="profile-form" action="{{ route('profile.phone.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="profile-form-grid profile-phone-grid">
                    <div class="profile-field"><label for="profilePhoneCountry">Pays du numéro</label><select id="profilePhoneCountry" name="country_code" class="country-select" data-placeholder="Rechercher un pays" required>@foreach(config('african_countries', []) as $iso => $countryName)<option value="{{ $iso }}" @selected($phoneCountry === $iso)>{{ $countryName }} ({{ $iso }})</option>@endforeach</select></div>
                    <div class="profile-field"><label for="profilePhone">Numéro de téléphone local</label><input id="profilePhone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" value="{{ $profilePhone }}" placeholder="Ex. 90859488" aria-describedby="profilePhoneHelp"><small id="profilePhoneHelp">Saisissez le numéro local, sans indicatif. Vous pouvez laisser vide pour le retirer.</small></div>
                </div>
                <div class="profile-form-actions"><button type="submit" class="saas-primary-action" data-loading-text="Enregistrement…"><i class="bi bi-check2-circle"></i>Enregistrer mon téléphone</button></div>
            </form>
        </div>

        <div class="profile-panel" id="profilePanelPassword" data-profile-panel="password" role="tabpanel" aria-labelledby="profileTabPassword" hidden>
            <div class="profile-panel-heading"><span class="profile-panel-icon"><i class="bi bi-shield-lock"></i></span><div><h2>Modifier le mot de passe</h2><p>Utilisez au moins huit caractères avec une majuscule, une minuscule et un chiffre.</p></div></div>
            <form id="profilePasswordForm" class="profile-form" action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                <div class="profile-field"><label for="currentPassword">Mot de passe actuel</label><div class="profile-password-control"><input id="currentPassword" name="AM" type="password" autocomplete="current-password" required><button type="button" data-password-toggle="currentPassword" aria-label="Afficher le mot de passe"><i class="bi bi-eye"></i></button></div></div>
                <div class="profile-form-grid">
                    <div class="profile-field"><label for="newPassword">Nouveau mot de passe</label><div class="profile-password-control"><input id="newPassword" name="NM" type="password" autocomplete="new-password" minlength="8" required><button type="button" data-password-toggle="newPassword" aria-label="Afficher le mot de passe"><i class="bi bi-eye"></i></button></div></div>
                    <div class="profile-field"><label for="confirmPassword">Confirmer le nouveau mot de passe</label><div class="profile-password-control"><input id="confirmPassword" name="CM" type="password" autocomplete="new-password" minlength="8" required><button type="button" data-password-toggle="confirmPassword" aria-label="Afficher le mot de passe"><i class="bi bi-eye"></i></button></div></div>
                </div>
                <div class="profile-form-actions"><button type="submit" class="saas-primary-action" data-loading-text="Modification…"><i class="bi bi-shield-check"></i>Modifier mon mot de passe</button></div>
            </form>
        </div>

        <div class="profile-panel" id="profilePanelAppearance" data-profile-panel="appearance" role="tabpanel" aria-labelledby="profileTabAppearance" hidden>
            <div class="profile-panel-heading"><span class="profile-panel-icon"><i class="bi bi-stars"></i></span><div><h2>Personnaliser l’interface</h2><p>Ces préférences sont personnelles et vous suivent sur ordinateur, mobile et PWA.</p></div></div>
            <form id="profileAppearanceForm" class="profile-form" action="{{ route('profile.appearance.update') }}" method="POST">
                @csrf
                @method('PUT')
                <details class="profile-appearance-collapse">
                    <summary><span class="profile-collapse-icon"><i class="bi bi-circle-half"></i></span><span class="profile-collapse-copy"><strong>Mode d’affichage</strong><small>Choisissez la luminosité générale de l’application.</small></span><span class="profile-collapse-value" id="profileModeSummary">{{ ['system' => 'Selon l’appareil', 'dark' => 'Sombre', 'light' => 'Clair'][$mode] }}</span><i class="bi bi-chevron-down profile-collapse-chevron"></i></summary>
                    <fieldset class="profile-fieldset"><legend class="visually-hidden">Mode d’affichage</legend><div class="profile-mode-grid">
                        @foreach([
                            'system' => ['bi-circle-half', 'Selon l’appareil', 'Suit automatiquement les réglages de votre téléphone ou ordinateur.'],
                            'dark' => ['bi-moon-stars', 'Sombre', 'Réduit la luminosité tout en gardant les informations bien contrastées.'],
                            'light' => ['bi-sun', 'Clair', 'Utilise des surfaces lumineuses et des contrastes doux pour la journée.'],
                        ] as $value => [$icon, $label, $description])
                            <label class="profile-mode {{ $mode === $value ? 'is-selected' : '' }}"><input class="visually-hidden" type="radio" name="appearance_mode" value="{{ $value }}" data-mode-label="{{ $label }}" @checked($mode === $value)><i class="bi {{ $icon }}"></i><span class="profile-mode-copy"><strong>{{ $label }}</strong><small>{{ $description }}</small></span><span class="profile-mode-check"><i class="bi bi-check"></i></span></label>
                        @endforeach
                    </div></fieldset>
                </details>
                <details class="profile-appearance-collapse">
                    <summary><span class="profile-collapse-icon"><i class="bi bi-palette"></i></span><span class="profile-collapse-copy"><strong>Couleur dominante</strong><small>Personnalisez les actions et les éléments actifs.</small></span><span class="profile-collapse-color" id="profileColorSummary" style="--summary-color:{{ $accent }}"><i></i>{{ $accent }}</span><i class="bi bi-chevron-down profile-collapse-chevron"></i></summary>
                    <fieldset class="profile-fieldset"><legend class="visually-hidden">Couleur dominante</legend><p>Cette couleur ne modifie pas les couleurs fonctionnelles des succès, avertissements ou erreurs.</p>
                        <div class="ds-color-grid profile-color-grid" id="profileAccentSwatches">
                            @foreach(['#7C5CFC', '#20BFA9', '#3B82F6', '#FF9F43', '#E94F86', '#62A744'] as $color)
                                <button type="button" class="ds-color-swatch {{ $accent === $color ? 'is-selected' : '' }}" style="--swatch:{{ $color }}" data-accent="{{ $color }}" aria-label="Choisir la couleur {{ $color }}" aria-pressed="{{ $accent === $color ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <div class="profile-accent-row">
                            <div class="profile-field"><label for="profileAccentText">Couleur personnalisée</label><div class="profile-color-control"><input type="color" id="profileAccentPicker" value="{{ $accent }}" aria-label="Sélectionner une couleur"><input type="text" id="profileAccentText" name="accent_color" value="{{ $accent }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}" required></div></div>
                            <div class="profile-theme-preview"><div><strong>Aperçu du thème</strong><small id="profileContrastStatus">Contraste calculé automatiquement.</small></div><button type="button" class="saas-primary-action">Action principale</button></div>
                        </div>
                    </fieldset>
                </details>
                <div class="profile-form-actions profile-form-actions-split"><button type="button" class="profile-secondary-button" id="profileResetAppearance"><i class="bi bi-arrow-counterclockwise"></i>Couleur par défaut</button><button type="submit" class="saas-primary-action" data-loading-text="Enregistrement…"><i class="bi bi-check2-circle"></i>Enregistrer mon apparence</button></div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('select.country-select').select2({ width: '100%', placeholder: 'Rechercher un pays', minimumResultsForSearch: 0 });
    }
    const phoneRules = @json(config('african_phone_rules'));
    const profilePhoneCountry = document.getElementById('profilePhoneCountry');
    const profilePhone = document.getElementById('profilePhone');
    const profilePhoneHelp = document.getElementById('profilePhoneHelp');
    function updatePhoneRuleHint() {
        const rule = phoneRules[profilePhoneCountry.value];
        if (!rule) return;
        const count = rule.min_length === rule.max_length ? `${rule.min_length} chiffres` : `entre ${rule.min_length} et ${rule.max_length} chiffres`;
        profilePhone.minLength = rule.min_length;
        profilePhone.maxLength = rule.max_length;
        profilePhone.placeholder = `Ex. ${'0'.repeat(rule.min_length)}`;
        profilePhoneHelp.textContent = `Saisissez le numéro local pour ce pays : ${count}, sans l’indicatif +${rule.dial_code}. Vous pouvez laisser vide pour le retirer.`;
    }
    profilePhoneCountry.addEventListener('change', updatePhoneRuleHint);
    profilePhone.addEventListener('input', () => { profilePhone.value = profilePhone.value.replace(/\D/g, ''); });
    updatePhoneRuleHint();
    const feedback = document.getElementById('profileFeedback');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const tabs = [...document.querySelectorAll('[data-profile-tab]')];
    const panels = [...document.querySelectorAll('[data-profile-panel]')];

    function showFeedback(type, message) {
        feedback.hidden = false;
        feedback.className = `profile-feedback is-${type}`;
        feedback.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i><span></span>`;
        feedback.querySelector('span').textContent = message;
        feedback.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' });
    }

    function activateTab(name, updateHash = true) {
        tabs.forEach((tab) => { const active = tab.dataset.profileTab === name; tab.classList.toggle('is-active', active); tab.setAttribute('aria-selected', String(active)); });
        panels.forEach((panel) => { const active = panel.dataset.profilePanel === name; panel.classList.toggle('is-active', active); panel.hidden = !active; });
        if (updateHash) history.replaceState(null, '', `#${name}`);
    }
    tabs.forEach((tab) => tab.addEventListener('click', () => activateTab(tab.dataset.profileTab)));
    const initialTab = ({ '#pills-appearance': 'appearance', '#pills-profile': 'password', '#pills-home': 'email' })[location.hash] || location.hash.slice(1);
    if (['email', 'phone', 'password', 'appearance'].includes(initialTab)) activateTab(initialTab, false);

    document.querySelectorAll('[data-password-toggle]').forEach((button) => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.querySelector('i').className = `bi ${visible ? 'bi-eye' : 'bi-eye-slash'}`;
        button.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
    }));

    async function submitJson(form, successCallback) {
        const response = await fetch(form.action, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: new FormData(form) });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.status === false) throw new Error(data.msg || data.message || Object.values(data.errors || {})[0]?.[0] || 'La modification n’a pas pu être enregistrée.');
        successCallback?.(data);
        showFeedback('success', data.msg || 'Vos modifications ont été enregistrées.');
    }

    document.getElementById('profileEmailForm').addEventListener('submit', function (event) {
        event.preventDefault(); const button = event.submitter;
        window.ServerButtonLoader.withLoader(button, () => submitJson(this, () => setTimeout(() => location.reload(), 900)), 'Modification…').catch((error) => showFeedback('error', error.message));
    });
    document.getElementById('profilePasswordForm').addEventListener('submit', function (event) {
        event.preventDefault(); const button = event.submitter;
        window.ServerButtonLoader.withLoader(button, () => submitJson(this, () => this.reset()), 'Modification…').catch((error) => showFeedback('error', error.message));
    });
    document.getElementById('profilePhoneForm').addEventListener('submit', function (event) {
        event.preventDefault(); const button = event.submitter;
        window.ServerButtonLoader.withLoader(button, () => submitJson(this), 'Enregistrement…').then(() => setTimeout(() => location.reload(), 900)).catch((error) => showFeedback('error', error.message));
    });

    const appearanceForm = document.getElementById('profileAppearanceForm');
    const accentText = document.getElementById('profileAccentText');
    const accentPicker = document.getElementById('profileAccentPicker');
    const currentMode = () => appearanceForm.querySelector('input[name="appearance_mode"]:checked')?.value || 'dark';
    const appearanceCollapses = [...appearanceForm.querySelectorAll('.profile-appearance-collapse')];
    appearanceCollapses.forEach((collapse) => collapse.addEventListener('toggle', () => {
        if (collapse.open) appearanceCollapses.filter((item) => item !== collapse).forEach((item) => { item.open = false; });
    }));
    function preview(color) {
        const accent = window.DesignSystem.normaliseHex(color); accentText.value = accent; accentPicker.value = accent;
        window.DesignSystem.apply({ mode: currentMode(), accent });
        const colorSummary = document.getElementById('profileColorSummary');
        colorSummary.style.setProperty('--summary-color', accent); colorSummary.lastChild.textContent = accent;
        document.querySelectorAll('#profileAccentSwatches [data-accent]').forEach((swatch) => { const active = swatch.dataset.accent === accent; swatch.classList.toggle('is-selected', active); swatch.setAttribute('aria-pressed', String(active)); });
        const text = window.DesignSystem.contrastText(window.DesignSystem.hexToRgb(accent));
        document.getElementById('profileContrastStatus').textContent = `Texte ${text === '#FFFFFF' ? 'blanc' : 'sombre'} appliqué automatiquement pour garantir la lisibilité.`;
    }
    appearanceForm.querySelectorAll('input[name="appearance_mode"]').forEach((input) => input.addEventListener('change', () => { document.querySelectorAll('.profile-mode').forEach((choice) => choice.classList.toggle('is-selected', choice.contains(input))); document.getElementById('profileModeSummary').textContent = input.dataset.modeLabel; preview(accentText.value); }));
    document.querySelectorAll('#profileAccentSwatches [data-accent]').forEach((swatch) => swatch.addEventListener('click', () => preview(swatch.dataset.accent)));
    accentPicker.addEventListener('input', () => preview(accentPicker.value)); accentText.addEventListener('change', () => preview(accentText.value));
    document.getElementById('profileResetAppearance').addEventListener('click', () => preview('#3B82F6'));
    appearanceForm.addEventListener('submit', function (event) { event.preventDefault(); const button = event.submitter; window.ServerButtonLoader.withLoader(button, () => submitJson(this, (data) => window.DesignSystem.apply({ mode: data.appearance.mode, accent: data.appearance.accent })), 'Enregistrement…').catch((error) => showFeedback('error', error.message)); });
    preview(accentText.value);
});
</script>
@endpush
