@if(auth()->check() && !empty($socialNetworks) && !auth()->user()->social_network_prompt_hidden && (!auth()->user()->social_network_prompt_snoozed_until || auth()->user()->social_network_prompt_snoozed_until->isPast()))
    <style>
        .social-invite-swal { width: min(560px, calc(100vw - 24px)) !important; padding: 26px 26px 22px !important; }
        .social-invite-swal .swal2-title { margin: 0 !important; color: var(--ds-text-primary) !important; font-size: 1.24rem !important; letter-spacing: -.02em; }
        .social-invite-swal .swal2-html-container { margin: 12px 0 0 !important; color: var(--ds-text-secondary) !important; font-size: .86rem !important; line-height: 1.55; }
        .social-invite-copy { margin: 0 0 14px; text-align: left; }
        .social-invite-network-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; margin: 0 0 15px; text-align: left; }
        .social-invite-network { display: flex; align-items: center; gap: 9px; min-width: 0; padding: 11px 12px; color: var(--ds-text-primary) !important; background: var(--ds-glass-1); border: 1px solid var(--ds-border-soft); border-radius: 11px; text-decoration: none !important; font-size: .76rem; font-weight: 750; cursor: pointer; }
        .social-invite-network:hover, .social-invite-network:focus-visible { border-color: var(--ds-accent); outline: 0; }
        .social-invite-network i { flex: 0 0 auto; color: var(--ds-accent); font-size: 1.05rem; }
        .social-invite-network span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .social-invite-network.is-whatsapp i { color: #25d366; }
        .social-invite-help { margin: 0 0 14px; padding: 10px 12px; text-align: left; color: var(--ds-text-muted); background: var(--ds-bg-elevated); border-radius: 10px; font-size: .74rem; }
        .social-invite-help a { color: var(--ds-accent); font-weight: 750; }
        .social-invite-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px; }
        .social-invite-actions button { min-height: 38px; padding: 8px 12px; border: 1px solid var(--ds-border-soft); border-radius: 9px; color: var(--ds-text-secondary); background: transparent; font-size: .73rem; font-weight: 750; cursor: pointer; }
        .social-invite-actions button:hover, .social-invite-actions button:focus-visible { color: var(--ds-text-primary); border-color: var(--ds-accent); outline: 0; }
        .social-invite-actions [data-social-hide] { color: var(--ds-danger, #ff626e); }
        .social-whatsapp-rules { margin: 0; padding: 0; list-style: none; text-align: left; }
        .social-whatsapp-rules li { display: flex; gap: 8px; margin: 8px 0; font-size: .78rem; }
        .social-whatsapp-rules i { color: var(--ds-success, #35c98b); }
        .social-whatsapp-consent { display: flex; align-items: flex-start; gap: 8px; margin-top: 15px; color: var(--ds-text-secondary); text-align: left; font-size: .78rem; cursor: pointer; }
        .social-whatsapp-consent input { margin-top: 3px; accent-color: var(--ds-accent); }
        @media (max-width: 575px) { .social-invite-swal { padding: 22px 18px 18px !important; } .social-invite-network-list { grid-template-columns: 1fr; } .social-invite-actions { flex-direction: column; } .social-invite-actions button { width: 100%; } }
    </style>
    <script>
        (() => {
            const networks = Object.entries(@json($socialNetworks)).map(([key, network]) => ({ key, ...network }));
            const preferenceUrl = @json(route('profile.social-network-prompt.preference'));
            const helpUrl = @json(route('marketing.help'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let preferenceSaved = false;
            let promptWasShown = false;

            const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
            const networkCards = networks.map(network => {
                const label = escapeHtml(network.label);
                const icon = escapeHtml(network.icon || 'bi-share');
                if (network.key === 'whatsapp') {
                    return `<button type="button" class="social-invite-network is-whatsapp" data-social-whatsapp><i class="bi ${icon}" aria-hidden="true"></i><span>${label}</span></button>`;
                }
                return `<a class="social-invite-network" href="${escapeHtml(network.url)}" target="_blank" rel="noopener noreferrer"><i class="bi ${icon}" aria-hidden="true"></i><span>${label}</span></a>`;
            }).join('');
            const whatsapp = networks.find(network => network.key === 'whatsapp');

            const requestPreference = (action, button = null) => {
                if (preferenceSaved) return Promise.resolve();
                const request = () => fetch(preferenceUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ action })
                }).then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.status) throw new Error(data.msg || 'Impossible d’enregistrer cette préférence.');
                    return data;
                });
                const promise = window.ServerButtonLoader && button
                    ? window.ServerButtonLoader.withLoader(button, request, action === 'hide' ? 'Désactivation…' : 'Rappel…')
                    : request();
                return promise.then(data => {
                    preferenceSaved = true;
                    Swal.close();
                    window.setTimeout(() => {
                        if (action === 'hide') {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Proposition désactivée', html: `Les informations restent disponibles dans <a href="${helpUrl}" target="_blank" rel="noopener noreferrer">Aide sur le site</a>.`, showConfirmButton: false, timer: 6500, timerProgressBar: true });
                        }
                    }, 120);
                    return data;
                }).catch(error => {
                    if (Swal.isVisible()) {
                        Swal.showValidationMessage(error.message || 'Impossible d’enregistrer cette préférence.');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Préférence non enregistrée', text: error.message || 'Impossible d’enregistrer cette préférence.' });
                    }
                    return null;
                });
            };

            const openWhatsAppRules = () => {
                if (!whatsapp) return;
                Swal.fire({
                    customClass: { popup: 'social-invite-swal' },
                    title: 'Rejoindre la communauté WhatsApp',
                    html: `<p class="social-invite-copy">Avant de rejoindre le groupe, merci de respecter ce cadre :</p><ul class="social-whatsapp-rules"><li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Les échanges concernent Maxanou et l’utilisation de l’application.</span></li><li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Les insultes, images et sujets inappropriés sont interdits.</span></li><li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Chaque membre reste responsable de ses publications et peut être retiré du groupe si nécessaire.</span></li></ul><label class="social-whatsapp-consent"><input type="checkbox" data-social-whatsapp-consent><span>J’ai lu ces règles et j’accepte de les respecter.</span></label>`,
                    showCancelButton: true, confirmButtonText: 'Rejoindre WhatsApp', cancelButtonText: 'Retour',
                    confirmButtonColor: '#25d366', focusConfirm: false,
                    preConfirm: () => {
                        if (!document.querySelector('[data-social-whatsapp-consent]')?.checked) {
                            Swal.showValidationMessage('Cochez la case pour confirmer votre accord.');
                            return false;
                        }
                        return true;
                    }
                }).then(result => { if (result.isConfirmed) window.open(whatsapp.url, '_blank', 'noopener'); });
            };

            const showPrompt = () => {
                if (!networks.length || !window.Swal || promptWasShown) return;
                promptWasShown = true;
                Swal.fire({
                    customClass: { popup: 'social-invite-swal' },
                    title: 'Rejoignez la communauté Maxanou',
                    html: `<p class="social-invite-copy">Suivez nos actualités, conseils et échanges sur les réseaux officiels.</p><div class="social-invite-network-list">${networkCards}</div><p class="social-invite-help">Vous pourrez aussi retrouver ces informations depuis la page <a href="${helpUrl}" target="_blank" rel="noopener noreferrer">Aide du site</a>.</p><div class="social-invite-actions"><button type="button" data-social-snooze>Ignorer pendant 7 jours</button><button type="button" data-social-hide>Ne plus voir</button></div>`,
                    showConfirmButton: false, showCloseButton: true, allowOutsideClick: false,
                    didOpen: popup => {
                        popup.querySelector('[data-social-whatsapp]')?.addEventListener('click', openWhatsAppRules);
                        popup.querySelector('[data-social-snooze]')?.addEventListener('click', event => requestPreference('snooze', event.currentTarget));
                        popup.querySelector('[data-social-hide]')?.addEventListener('click', event => requestPreference('hide', event.currentTarget));
                    }
                });
            };

            window.setTimeout(() => {
                if (document.visibilityState === 'visible' && !Swal.isVisible()) showPrompt();
            }, 15000);
        })();
    </script>
@endif
