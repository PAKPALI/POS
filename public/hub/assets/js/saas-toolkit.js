(() => {
    'use strict';

    const variants = new Set(['info', 'success', 'warning', 'danger']);
    const icons = {info: 'bi-info-circle', success: 'bi-check-circle', warning: 'bi-exclamation-triangle', danger: 'bi-x-octagon'};

    function stack() {
        let element = document.querySelector('.saas-ui-toast-stack');
        if (!element) {
            element = document.createElement('div');
            element.className = 'saas-ui-toast-stack';
            element.setAttribute('aria-live', 'polite');
            element.setAttribute('aria-atomic', 'true');
            document.body.append(element);
        }
        return element;
    }

    function toast(message, options = {}) {
        const variant = variants.has(options.variant) ? options.variant : 'info';
        const duration = Math.max(0, Number(options.duration ?? 5000));
        const item = document.createElement('div');
        item.className = `saas-ui-toast is-${variant}`;
        item.setAttribute('role', variant === 'danger' ? 'alert' : 'status');
        item.innerHTML = `<i class="bi ${icons[variant]}" aria-hidden="true"></i><div><strong>${options.title || 'Information'}</strong><span></span></div>`;
        item.querySelector('span').textContent = String(message || '');
        stack().append(item);
        if (duration) window.setTimeout(() => item.remove(), duration);
        return item;
    }

    function busy(button, promiseOrFactory, text) {
        if (!window.ServerButtonLoader) return Promise.resolve(typeof promiseOrFactory === 'function' ? promiseOrFactory() : promiseOrFactory);
        return window.ServerButtonLoader.withLoader(button, promiseOrFactory, text);
    }

    window.SaasUI = Object.freeze({toast, busy});
})();
