(() => {
    'use strict';

    const root = document.documentElement;
    const media = window.matchMedia('(prefers-color-scheme: light)');

    function normaliseHex(value) {
        const hex = String(value || '').trim().toUpperCase();
        return /^#[0-9A-F]{6}$/.test(hex) ? hex : '#3B82F6';
    }

    function hexToRgb(hex) {
        const value = normaliseHex(hex).slice(1);
        return [0, 2, 4].map((offset) => parseInt(value.slice(offset, offset + 2), 16));
    }

    function channel(value) {
        const n = value / 255;
        return n <= .03928 ? n / 12.92 : Math.pow((n + .055) / 1.055, 2.4);
    }

    function contrastText(rgb) {
        const luminance = .2126 * channel(rgb[0]) + .7152 * channel(rgb[1]) + .0722 * channel(rgb[2]);
        const whiteContrast = 1.05 / (luminance + .05);
        const blackContrast = (luminance + .05) / .05;
        return whiteContrast >= blackContrast ? '#FFFFFF' : '#111827';
    }

    function shade(rgb, amount) {
        return rgb.map((value) => Math.max(0, Math.min(255, Math.round(value + (amount * 255)))));
    }

    function toHex(rgb) {
        return `#${rgb.map((value) => value.toString(16).padStart(2, '0')).join('')}`.toUpperCase();
    }

    function resolveMode(mode) {
        return mode === 'system' ? (media.matches ? 'light' : 'dark') : mode;
    }

    function apply({ mode, accent }) {
        const preference = ['light', 'dark', 'system'].includes(mode) ? mode : 'system';
        const color = normaliseHex(accent);
        const rgb = hexToRgb(color);
        const resolved = resolveMode(preference);
        const direction = resolved === 'light' ? -.08 : .08;

        root.dataset.dsThemePreference = preference;
        root.dataset.dsTheme = resolved;
        root.dataset.bsTheme = resolved;
        root.style.setProperty('--ds-accent', color);
        root.style.setProperty('--ds-accent-rgb', rgb.join(', '));
        root.style.setProperty('--ds-accent-hover', toHex(shade(rgb, direction)));
        root.style.setProperty('--ds-accent-active', toHex(shade(rgb, -.1)));
        root.style.setProperty('--ds-accent-contrast', contrastText(rgb));

        document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => {
            meta.content = resolved === 'light' ? '#F3F6FA' : '#070B14';
        });
        window.dispatchEvent(new CustomEvent('designsystem:change', { detail: { mode: preference, resolved, accent: color } }));
    }

    media.addEventListener?.('change', () => {
        if (root.dataset.dsThemePreference === 'system') {
            apply({ mode: 'system', accent: getComputedStyle(root).getPropertyValue('--ds-accent') });
        }
    });

    window.DesignSystem = { apply, normaliseHex, contrastText, hexToRgb };
    apply({
        mode: root.dataset.dsThemePreference || 'system',
        accent: getComputedStyle(root).getPropertyValue('--ds-accent'),
    });

    function localiseDataTableLoader(scope) {
        const candidates = [];
        if (scope instanceof Element && scope.matches('.dataTables_processing, .dt-processing')) candidates.push(scope);
        if (scope.querySelectorAll) candidates.push(...scope.querySelectorAll('.dataTables_processing, .dt-processing'));

        candidates.forEach((loader) => {
            loader.setAttribute('role', 'status');
            loader.setAttribute('aria-live', 'polite');
            loader.childNodes.forEach((node) => {
                if (node.nodeType === Node.TEXT_NODE && /^\s*(processing|loading)\.{0,3}\s*$/i.test(node.nodeValue || '')) {
                    node.nodeValue = 'Chargement des données…';
                }
            });
            const readableLabel = Array.from(loader.childNodes)
                .filter((node) => node.nodeType === Node.TEXT_NODE)
                .map((node) => node.nodeValue || '')
                .join('')
                .trim();
            if (!readableLabel) loader.insertBefore(document.createTextNode('Chargement des données…'), loader.firstChild);
        });
    }

    localiseDataTableLoader(document);
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-password-toggle]');
        if (!toggle) return;
        const input = document.getElementById(toggle.dataset.passwordToggle);
        if (!input) return;
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!showing));
        toggle.setAttribute('aria-label', showing ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        const icon = toggle.querySelector('i');
        if (icon) icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
        input.focus({ preventScroll: true });
    });

    function enhancePasswords(scope = document) {
        const inputs = [];
        if (scope instanceof HTMLInputElement && scope.type === 'password') inputs.push(scope);
        if (scope.querySelectorAll) inputs.push(...scope.querySelectorAll('input[type="password"]'));
        inputs.forEach((input) => {
            if (!input.id) input.id = `ds-password-${Math.random().toString(36).slice(2)}`;
            input.dataset.passwordEnhanced = 'true';
            const parent = input.parentElement;
            if (!parent) return;
            const existing = parent.querySelector(`[data-password-toggle="${CSS.escape(input.id)}"], .password-toggle[data-target="${CSS.escape(input.id)}"]`);
            if (existing) {
                existing.dataset.passwordToggle = input.id;
                return;
            }
            parent.classList.add('ds-password-field');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'ds-password-toggle';
            button.dataset.passwordToggle = input.id;
            button.setAttribute('aria-label', 'Afficher le mot de passe');
            button.setAttribute('aria-pressed', 'false');
            button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
            input.insertAdjacentElement('afterend', button);
        });
    }

    enhancePasswords();

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE) {
                localiseDataTableLoader(node);
                enhancePasswords(node);
            }
        }));
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
