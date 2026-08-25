(function () {
    'use strict';

    const IOS_INSTALL_DISMISSED_KEY = 'pro-seller-ios-install-dismissed-at';
    const DISMISS_DURATION = 7 * 24 * 60 * 60 * 1000;

    function isIosDevice() {
        return /iphone|ipad|ipod/i.test(navigator.userAgent) ||
            (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function isStandalone() {
        return navigator.standalone === true ||
            window.matchMedia('(display-mode: standalone)').matches;
    }

    function isSafariOnIos() {
        return /safari/i.test(navigator.userAgent) &&
            !/crios|fxios|edgios|opios/i.test(navigator.userAgent);
    }

    function wasRecentlyDismissed() {
        try {
            const dismissedAt = Number(localStorage.getItem(IOS_INSTALL_DISMISSED_KEY));
            return dismissedAt > 0 && Date.now() - dismissedAt < DISMISS_DURATION;
        } catch (error) {
            return false;
        }
    }

    function rememberDismissal() {
        try {
            localStorage.setItem(IOS_INSTALL_DISMISSED_KEY, String(Date.now()));
        } catch (error) {
            // Le mode privé peut interdire le stockage : la fermeture reste effective pour la page.
        }
    }

    function showIosInstallGuide() {
        if (!isIosDevice() || isStandalone() || wasRecentlyDismissed()) return;
        if (document.getElementById('ios-pwa-install-guide')) return;

        const style = document.createElement('style');
        style.id = 'ios-pwa-install-style';
        style.textContent = `
            #ios-pwa-install-guide { position: fixed; left: 12px; right: 12px; bottom: max(12px, env(safe-area-inset-bottom)); z-index: 2147483000; max-width: 520px; margin: auto; padding: 16px; color: #f4f7fb; background: rgba(25, 29, 36, .98); border: 1px solid #3b4554; border-radius: 18px; box-shadow: 0 20px 55px rgba(0, 0, 0, .45); font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            .ios-pwa-head { display: flex; align-items: center; gap: 12px; }
            .ios-pwa-icon { width: 52px; height: 52px; flex: 0 0 52px; border-radius: 13px; background: #fff; object-fit: cover; }
            .ios-pwa-copy { flex: 1; min-width: 0; }
            .ios-pwa-copy strong { display: block; margin-bottom: 3px; font-size: 16px; }
            .ios-pwa-copy span { display: block; color: #bdc6d2; font-size: 13px; line-height: 1.4; }
            .ios-pwa-close { align-self: flex-start; width: 34px; height: 34px; border: 0; border-radius: 50%; color: #dce2ea; background: #303744; font-size: 20px; line-height: 1; cursor: pointer; }
            .ios-pwa-steps { display: grid; gap: 9px; margin-top: 14px; padding-top: 14px; border-top: 1px solid #343c49; }
            .ios-pwa-step { display: flex; align-items: center; gap: 10px; color: #e8ecf2; font-size: 14px; line-height: 1.4; }
            .ios-pwa-number { display: grid; place-items: center; width: 26px; height: 26px; flex: 0 0 26px; border-radius: 8px; color: #fff; background: #168b5b; font-size: 12px; font-weight: 800; }
            .ios-pwa-share { color: #55aaff; font-size: 22px; font-weight: 700; }
            @media (min-width: 600px) { #ios-pwa-install-guide { left: auto; right: 22px; bottom: 22px; width: 420px; } }
        `;

        const guide = document.createElement('aside');
        guide.id = 'ios-pwa-install-guide';
        guide.setAttribute('role', 'dialog');
        guide.setAttribute('aria-label', 'Installer PRO-SELLER sur votre iPhone');

        const browserNotice = isSafariOnIos()
            ? 'Installez l’application pour y accéder comme une application native.'
            : 'Ouvrez d’abord cette page dans Safari pour installer l’application.';

        guide.innerHTML = `
            <div class="ios-pwa-head">
                <img class="ios-pwa-icon" src="/icons/apple-touch-icon-180.png" alt="">
                <div class="ios-pwa-copy">
                    <strong>Installer PRO-SELLER</strong>
                    <span>${browserNotice}</span>
                </div>
                <button class="ios-pwa-close" type="button" aria-label="Fermer">&times;</button>
            </div>
            <div class="ios-pwa-steps">
                <div class="ios-pwa-step"><span class="ios-pwa-number">1</span><span>Dans Safari, touchez <span class="ios-pwa-share">⇧</span> <strong>Partager</strong>.</span></div>
                <div class="ios-pwa-step"><span class="ios-pwa-number">2</span><span>Choisissez <strong>Sur l’écran d’accueil</strong>.</span></div>
                <div class="ios-pwa-step"><span class="ios-pwa-number">3</span><span>Touchez <strong>Ajouter</strong> pour terminer.</span></div>
            </div>
        `;

        guide.querySelector('.ios-pwa-close').addEventListener('click', function () {
            rememberDismissal();
            guide.remove();
            style.remove();
        });

        document.head.appendChild(style);
        document.body.appendChild(guide);
    }

    window.addEventListener('load', function () {
        showIosInstallGuide();

        if (!('serviceWorker' in navigator)) return;
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(function (registration) {
                registration.update().catch(function () {});
                registration.addEventListener('updatefound', function () {
                    const worker = registration.installing;
                    if (!worker) return;
                    worker.addEventListener('statechange', function () {
                        if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                            window.dispatchEvent(new CustomEvent('pwa:update-available', {
                                detail: { registration: registration }
                            }));
                        }
                    });
                });
            })
            .catch(function (error) {
                console.warn('Impossible d’activer le mode application.', error);
            });
    });
})();
