(() => {
    'use strict';
    const shell = document.getElementById('saasShell');
    if (!shell) return;

    const storageKey = 'saas_sidebar_collapsed';
    const openButton = document.querySelector('[data-saas-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-saas-sidebar-close]');
    const collapseButton = document.querySelector('[data-saas-sidebar-collapse]');

    if (localStorage.getItem(storageKey) === '1') shell.classList.add('is-sidebar-collapsed');

    function setMobileMenu(open) {
        shell.classList.toggle('is-sidebar-open', open);
        document.body.classList.toggle('saas-menu-locked', open);
        openButton?.setAttribute('aria-expanded', String(open));
    }

    openButton?.addEventListener('click', () => setMobileMenu(true));
    closeButtons.forEach((button) => button.addEventListener('click', () => setMobileMenu(false)));
    collapseButton?.addEventListener('click', () => {
        const collapsed = shell.classList.toggle('is-sidebar-collapsed');
        localStorage.setItem(storageKey, collapsed ? '1' : '0');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMobileMenu(false);
    });
    window.matchMedia('(min-width: 1024px)').addEventListener?.('change', (event) => {
        if (event.matches) setMobileMenu(false);
    });

    document.querySelectorAll('.saas-nav a').forEach((link) => link.addEventListener('click', () => {
        if (window.innerWidth < 1024) setMobileMenu(false);
    }));

    // Shared accessibility contract for every Bootstrap modal in the SaaS shell.
    let modalReturnFocus = null;
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-bs-toggle="modal"], .edit, .view');
        if (trigger) modalReturnFocus = trigger;
    });
    document.addEventListener('shown.bs.modal', (event) => {
        const focusTarget = event.target.querySelector('.saas-modal-close, .btn-close, [data-bs-dismiss="modal"], input:not([type="hidden"]), select, textarea, button, [href]');
        window.setTimeout(() => focusTarget?.focus(), 0);
    });
    document.addEventListener('hidden.bs.modal', () => {
        const returnTarget = modalReturnFocus;
        modalReturnFocus = null;
        if (returnTarget && document.contains(returnTarget)) {
            window.setTimeout(() => returnTarget.focus(), 0);
        }
    });

    const appearanceModal = document.getElementById('navbarAppearanceModal');
    const appearanceForm = document.getElementById('navbarAppearanceForm');
    if (appearanceModal && appearanceForm && window.DesignSystem) {
        const root = document.documentElement;
        const accentInput = document.getElementById('navbarAccentInput');
        const accentPicker = document.getElementById('navbarAccentPicker');
        const accentValue = document.getElementById('navbarAccentValue');
        const feedback = document.getElementById('navbarAppearanceFeedback');
        let savedMode = root.dataset.dsThemePreference || 'system';
        let savedAccent = window.DesignSystem.normaliseHex(getComputedStyle(root).getPropertyValue('--ds-accent'));
        let committed = false;

        function selectedMode() {
            return appearanceForm.querySelector('input[name="appearance_mode"]:checked')?.value || 'system';
        }

        function updateControls(mode, accent) {
            appearanceForm.querySelectorAll('input[name="appearance_mode"]').forEach((input) => {
                input.checked = input.value === mode;
                input.closest('.navbar-mode-choice')?.classList.toggle('is-selected', input.checked);
            });
            accentInput.value = accent;
            accentPicker.value = accent;
            accentValue.textContent = accent;
            appearanceForm.querySelectorAll('[data-accent]').forEach((swatch) => {
                const active = swatch.dataset.accent === accent;
                swatch.classList.toggle('is-selected', active);
                swatch.setAttribute('aria-pressed', String(active));
            });
        }

        function preview(mode, accent) {
            const color = window.DesignSystem.normaliseHex(accent);
            updateControls(mode, color);
            window.DesignSystem.apply({ mode, accent: color });
        }

        appearanceModal.addEventListener('show.bs.modal', () => {
            savedMode = root.dataset.dsThemePreference || 'system';
            savedAccent = window.DesignSystem.normaliseHex(getComputedStyle(root).getPropertyValue('--ds-accent'));
            committed = false;
            feedback.textContent = '';
            feedback.className = 'navbar-appearance-feedback';
            updateControls(savedMode, savedAccent);
        });

        appearanceModal.addEventListener('hidden.bs.modal', () => {
            if (!committed) window.DesignSystem.apply({ mode: savedMode, accent: savedAccent });
        });

        appearanceForm.querySelectorAll('input[name="appearance_mode"]').forEach((input) => {
            input.addEventListener('change', () => preview(input.value, accentInput.value));
        });
        appearanceForm.querySelectorAll('[data-accent]').forEach((swatch) => {
            swatch.addEventListener('click', () => preview(selectedMode(), swatch.dataset.accent));
        });
        accentPicker.addEventListener('input', () => preview(selectedMode(), accentPicker.value));

        appearanceForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = event.submitter;
            const request = async () => {
                const response = await fetch(appearanceForm.action, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ appearance_mode: selectedMode(), accent_color: accentInput.value }),
                });
                const data = await response.json();
                if (!response.ok || !data.status) {
                    const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validationMessage || data.msg || 'Impossible d’enregistrer l’apparence.');
                }
                return data;
            };

            feedback.textContent = '';
            feedback.className = 'navbar-appearance-feedback';
            try {
                const data = window.ServerButtonLoader
                    ? await window.ServerButtonLoader.withLoader(button, request(), 'Enregistrement…')
                    : await request();
                savedMode = data.appearance.mode;
                savedAccent = data.appearance.accent;
                committed = true;
                preview(savedMode, savedAccent);
                feedback.textContent = 'Apparence enregistrée sur votre compte.';
                feedback.classList.add('is-success');
                window.setTimeout(() => bootstrap.Modal.getInstance(appearanceModal)?.hide(), 550);
            } catch (error) {
                feedback.textContent = error.message;
                feedback.classList.add('is-error');
            }
        });
    }
})();
