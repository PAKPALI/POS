(() => {
    const track = (name, detail = {}) => {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ event: name, ...detail });
    };

    const menuToggle = document.querySelector('.marketing-menu-toggle');
    const nav = document.querySelector('#marketing-nav');
    menuToggle?.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        menuToggle.setAttribute('aria-expanded', String(open));
    });
    nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
        nav.classList.remove('is-open');
        menuToggle?.setAttribute('aria-expanded', 'false');
    }));

    const appearance = document.querySelector('[data-marketing-appearance]');
    if (appearance) {
        const root = document.documentElement;
        const modeKey = 'marketing_appearance_mode';
        const accentKey = 'marketing_appearance_accent';
        const readPreference = key => { try { return window.localStorage.getItem(key); } catch { return null; } };
        const validMode = value => ['dark', 'light'].includes(value) ? value : null;
        const validAccent = value => /^#[0-9A-Fa-f]{6}$/.test(value || '') ? value.toUpperCase() : null;
        const rgbFromHex = hex => hex.match(/[A-Fa-f0-9]{2}/g).map(part => Number.parseInt(part, 16)).join(',');
        const palette = {
            '#3B82F6': { hover: '#60A5FA', active: '#2563EB' },
            '#20BFA9': { hover: '#35C9B4', active: '#159B88' },
            '#7C5CFC': { hover: '#9B85FF', active: '#6547E7' },
            '#EC4899': { hover: '#F472B6', active: '#DB2777' },
            '#84B547': { hover: '#A3D064', active: '#6C9636' },
            '#FF9F43': { hover: '#FFB15F', active: '#ED8730' }
        };
        let mode = validMode(readPreference(modeKey)) || validMode(root.dataset.dsThemePreference) || (root.dataset.dsTheme === 'light' ? 'light' : 'dark');
        let accent = validAccent(readPreference(accentKey)) || validAccent(getComputedStyle(root).getPropertyValue('--ds-accent')) || '#3B82F6';
        const trigger = appearance.querySelector('.marketing-appearance-trigger');
        const panel = appearance.querySelector('.marketing-appearance-panel');
        const status = appearance.querySelector('[data-marketing-appearance-status]');
        const saveUrl = appearance.dataset.saveUrl;

        const apply = () => {
            const colors = palette[accent] || { hover: `color-mix(in srgb, ${accent} 78%, white)`, active: `color-mix(in srgb, ${accent} 82%, black)` };
            root.dataset.dsThemePreference = mode;
            root.dataset.dsTheme = mode;
            root.dataset.bsTheme = mode;
            root.style.setProperty('--ds-accent', accent);
            root.style.setProperty('--ds-accent-rgb', rgbFromHex(accent));
            root.style.setProperty('--ds-accent-hover', colors.hover);
            root.style.setProperty('--ds-accent-active', colors.active);
            root.style.setProperty('--ds-accent-soft', `rgba(${rgbFromHex(accent)},.14)`);
            root.style.setProperty('--ds-focus-ring', `rgba(${rgbFromHex(accent)},.42)`);
            appearance.querySelectorAll('[data-marketing-mode]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.marketingMode === mode)));
            appearance.querySelectorAll('[data-marketing-accent]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.marketingAccent === accent)));
            appearance.querySelector('[data-marketing-accent-custom]').value = accent;
        };
        const persist = () => {
            try {
                window.localStorage.setItem(modeKey, mode);
                window.localStorage.setItem(accentKey, accent);
            } catch {}
            if (!saveUrl) return;
            fetch(saveUrl, { method: 'PUT', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ appearance_mode: mode, accent_color: accent }) })
                .then(response => { if (!response.ok) throw new Error('appearance-save-failed'); if (status) status.textContent = 'Préférence synchronisée avec votre compte.'; })
                .catch(() => { if (status) status.textContent = 'Préférence mémorisée sur cet appareil.'; });
        };
        const choose = (nextMode = mode, nextAccent = accent) => { mode = nextMode; accent = nextAccent; apply(); persist(); };
        trigger?.addEventListener('click', () => { const open = !panel.hidden; panel.hidden = open; trigger.setAttribute('aria-expanded', String(!open)); });
        appearance.querySelectorAll('[data-marketing-mode]').forEach(button => button.addEventListener('click', () => choose(button.dataset.marketingMode, accent)));
        appearance.querySelectorAll('[data-marketing-accent]').forEach(button => button.addEventListener('click', () => choose(mode, button.dataset.marketingAccent)));
        appearance.querySelector('[data-marketing-accent-custom]')?.addEventListener('input', event => { const nextAccent = validAccent(event.target.value); if (nextAccent) choose(mode, nextAccent); });
        document.addEventListener('click', event => { if (!appearance.contains(event.target)) { panel.hidden = true; trigger?.setAttribute('aria-expanded', 'false'); } });
        document.addEventListener('keydown', event => { if (event.key === 'Escape') { panel.hidden = true; trigger?.setAttribute('aria-expanded', 'false'); trigger?.focus(); } });
        apply();
    }

    document.querySelectorAll('[data-scroll-to]').forEach(link => link.addEventListener('click', event => {
        const target = document.getElementById(link.dataset.scrollTo);
        if (!target) return;
        event.preventDefault();
        target.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
        track(link.dataset.event || 'section_open');
    }));
    document.querySelectorAll('[data-event]').forEach(element => element.addEventListener('click', () => track(element.dataset.event)));

    const demo = document.querySelector('[data-demo]');
    if (demo) {
        const steps = [...demo.querySelectorAll('[data-demo-step]')];
        const toggle = document.querySelector('[data-demo-toggle]');
        const status = document.querySelector('[data-demo-status]');
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let current = 0;
        let timer = null;
        let playing = !reduced;
        const showStep = index => steps.forEach((step, stepIndex) => step.classList.toggle('is-active', stepIndex === index));
        const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
        const start = () => {
            stop();
            if (!playing || reduced) return;
            timer = window.setInterval(() => { current = (current + 1) % steps.length; showStep(current); }, 1250);
        };
        const sync = () => {
            const pause = !playing;
            toggle?.setAttribute('aria-label', pause ? 'Lire la démonstration' : 'Mettre la démonstration en pause');
            if (toggle) toggle.innerHTML = pause ? '<svg class="marketing-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 6 9 6-9 6V6Z"/></svg>' : '<svg class="marketing-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5v14M15 5v14"/></svg>';
            if (status) status.textContent = pause ? 'Démonstration en pause' : 'Lecture automatique · 5 secondes';
        };
        const observer = new IntersectionObserver(entries => { if (entries[0].isIntersecting) { start(); observer.disconnect(); } }, { threshold: .28 });
        observer.observe(demo);
        toggle?.addEventListener('click', () => { playing = !playing; sync(); if (playing) { track('demo_play'); start(); } else stop(); });
        if (reduced) { showStep(0); sync(); }
    }

    document.querySelectorAll('[data-pricing-period]').forEach(button => button.addEventListener('click', () => {
        const period = button.dataset.pricingPeriod;
        document.querySelectorAll('[data-pricing-period]').forEach(item => item.classList.toggle('is-active', item === button));
        document.querySelectorAll('[data-price-monthly]').forEach(price => {
            const value = Number(price.dataset[`price${period === 'annual' ? 'Annual' : 'Monthly'}`]);
            price.textContent = new Intl.NumberFormat('fr-FR').format(value);
        });
        document.querySelectorAll('[data-period-label]').forEach(label => { label.textContent = period === 'annual' ? 'an' : 'mois'; });
        track('pricing_toggle', { period });
    }));
})();
