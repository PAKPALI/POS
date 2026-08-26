(function (window, document) {
    'use strict';

    const buttonStates = new WeakMap();
    const requestCounts = new WeakMap();
    const explicitlyManagedButtons = new WeakSet();
    let pendingButton = null;
    let pendingButtonTimer = null;
    let jqueryEventsBound = false;

    function isEligible(element) {
        return element
            && !element.matches('[data-no-server-loader]')
            && !element.closest('[data-no-server-loader]');
    }

    function findButton(target) {
        return target && target.closest
            ? target.closest('button, input[type="submit"], input[type="button"], a[data-server-action]')
            : null;
    }

    function start(button, loadingText) {
        if (!isEligible(button) || buttonStates.has(button)) return button;

        const isInput = button.tagName === 'INPUT';
        const state = {
            disabled: Boolean(button.disabled),
            html: isInput ? null : button.innerHTML,
            value: isInput ? button.value : null,
            minWidth: button.style.minWidth,
            pointerEvents: button.style.pointerEvents,
        };

        buttonStates.set(button, state);
        button.style.minWidth = Math.ceil(button.getBoundingClientRect().width) + 'px';
        button.setAttribute('aria-busy', 'true');
        button.classList.add('server-action-loading');

        if ('disabled' in button) button.disabled = true;
        if (button.tagName === 'A') {
            button.setAttribute('aria-disabled', 'true');
            button.style.pointerEvents = 'none';
        }

        const label = loadingText
            || button.getAttribute('data-loading-text')
            || 'Veuillez patienter…';

        if (isInput) {
            button.value = label;
        } else {
            button.replaceChildren();
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm me-2';
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-hidden', 'true');
            button.append(spinner, document.createTextNode(label));
        }

        return button;
    }

    function stop(button) {
        const state = button && buttonStates.get(button);
        if (!state) return button;

        if (state.html === null) button.value = state.value;
        else button.innerHTML = state.html;

        if ('disabled' in button) button.disabled = state.disabled;
        button.style.minWidth = state.minWidth;
        button.style.pointerEvents = state.pointerEvents;
        button.removeAttribute('aria-busy');
        button.removeAttribute('aria-disabled');
        button.classList.remove('server-action-loading');
        buttonStates.delete(button);
        requestCounts.delete(button);

        return button;
    }

    function incrementRequests(button) {
        requestCounts.set(button, (requestCounts.get(button) || 0) + 1);
    }

    function decrementRequests(button) {
        const remaining = Math.max((requestCounts.get(button) || 1) - 1, 0);
        if (remaining === 0) stop(button);
        else requestCounts.set(button, remaining);
    }

    function withLoader(button, promiseOrFactory, loadingText) {
        start(button, loadingText);
        explicitlyManagedButtons.add(button);

        let promise;
        try {
            promise = typeof promiseOrFactory === 'function' ? promiseOrFactory() : promiseOrFactory;
        } catch (error) {
            explicitlyManagedButtons.delete(button);
            stop(button);
            return Promise.reject(error);
        }

        return Promise.resolve(promise).finally(function () {
            explicitlyManagedButtons.delete(button);
            stop(button);
        });
    }

    function download(button, url, loadingText) {
        start(button, loadingText || 'Préparation…');
        return window.fetch(url, {credentials: 'same-origin'})
            .then(async function (response) {
                if (!response.ok) {
                    throw new Error((await response.text()) || 'Impossible de préparer le fichier.');
                }
                const blob = await response.blob();
                const disposition = response.headers.get('content-disposition') || '';
                const encoded = disposition.match(/filename\*=UTF-8''([^;]+)/i);
                const plain = disposition.match(/filename="?([^";]+)"?/i);
                const filename = encoded ? decodeURIComponent(encoded[1]) : (plain ? plain[1] : 'export');
                const objectUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(function () { window.URL.revokeObjectURL(objectUrl); }, 1000);
            })
            .finally(function () { stop(button); });
    }

    function rememberPendingButton(button) {
        pendingButton = button;
        window.clearTimeout(pendingButtonTimer);
        pendingButtonTimer = window.setTimeout(function () {
            if (pendingButton === button) pendingButton = null;
        }, 1000);
    }

    window.ServerButtonLoader = {start: start, stop: stop, withLoader: withLoader, download: download};

    document.addEventListener('click', function (event) {
        const button = findButton(event.target);
        if (!isEligible(button)) return;

        rememberPendingButton(button);
    }, true);

    document.addEventListener('submit', function (event) {
        const form = event.target;
        const button = event.submitter
            || (pendingButton && form.contains(pendingButton) ? pendingButton : null)
            || form.querySelector('button[type="submit"], input[type="submit"]');

        if (!isEligible(button)) return;
        rememberPendingButton(button);
        start(button);

        window.setTimeout(function () {
            if (event.defaultPrevented
                && !explicitlyManagedButtons.has(button)
                && (requestCounts.get(button) || 0) === 0) stop(button);
        }, 0);
    }, true);

    if (typeof window.fetch === 'function') {
        const originalFetch = window.fetch.bind(window);
        window.fetch = function () {
            const button = pendingButton;
            const request = originalFetch.apply(null, arguments);

            if (!isEligible(button)) return request;

            start(button);
            incrementRequests(button);
            return Promise.resolve(request).finally(function () {
                decrementRequests(button);
            });
        };
    }

    function bindJqueryEvents() {
        if (jqueryEventsBound || !window.jQuery) return false;
        jqueryEventsBound = true;

        window.jQuery(document).ajaxSend(function (_event, xhr) {
            const button = pendingButton;
            if (!isEligible(button) || explicitlyManagedButtons.has(button)) return;

            start(button);
            incrementRequests(button);
            xhr.__serverButtonLoader = button;
        });

        window.jQuery(document).ajaxComplete(function (_event, xhr) {
            if (xhr.__serverButtonLoader) decrementRequests(xhr.__serverButtonLoader);
        });

        return true;
    }

    if (!bindJqueryEvents()) {
        document.addEventListener('DOMContentLoaded', bindJqueryEvents, {once: true});
        window.addEventListener('load', bindJqueryEvents, {once: true});
        window.setTimeout(bindJqueryEvents, 250);
        window.setTimeout(bindJqueryEvents, 1000);
    }
})(window, document);
