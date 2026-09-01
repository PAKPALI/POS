(() => {
    'use strict';

    const root = document.documentElement;
    const media = window.matchMedia('(prefers-color-scheme: light)');

    function normaliseHex(value) {
        const hex = String(value || '').trim().toUpperCase();
        return /^#[0-9A-F]{6}$/.test(hex) ? hex : '#FF9F43';
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
    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE) localiseDataTableLoader(node);
        }));
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
