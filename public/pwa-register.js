(function () {
    'use strict';

    const IOS_INSTALL_DISMISSED_KEY = 'pro-seller-ios-install-dismissed-at';
    const ANDROID_INSTALL_DISMISSED_KEY = 'pro-seller-android-install-dismissed-at';
    const DISMISS_DURATION = 7 * 24 * 60 * 60 * 1000;
    let deferredInstallPrompt = null;
    let pageLoaded = document.readyState === 'complete';

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

    function androidPromptWasRecentlyDismissed() {
        try {
            const dismissedAt = Number(localStorage.getItem(ANDROID_INSTALL_DISMISSED_KEY));
            return dismissedAt > 0 && Date.now() - dismissedAt < DISMISS_DURATION;
        } catch (error) {
            return false;
        }
    }

    function rememberAndroidDismissal() {
        try {
            localStorage.setItem(ANDROID_INSTALL_DISMISSED_KEY, String(Date.now()));
        } catch (error) {}
    }

    function removeAndroidInstallPrompt() {
        document.getElementById('android-pwa-install-prompt')?.remove();
        document.getElementById('android-pwa-install-style')?.remove();
    }

    function showAndroidInstallPrompt() {
        if (!deferredInstallPrompt || isStandalone() || androidPromptWasRecentlyDismissed()) return;
        if (document.getElementById('android-pwa-install-prompt')) return;

        const style = document.createElement('style');
        style.id = 'android-pwa-install-style';
        style.textContent = `
            #android-pwa-install-prompt { position:fixed; left:12px; right:12px; bottom:max(12px,env(safe-area-inset-bottom)); z-index:2147483000; max-width:520px; margin:auto; padding:15px; color:#f8fafc; background:rgba(15,23,42,.98); border:1px solid #334155; border-radius:18px; box-shadow:0 20px 55px rgba(0,0,0,.42); font-family:system-ui,-apple-system,"Segoe UI",sans-serif; }
            .android-pwa-content { display:flex; align-items:center; gap:12px; }
            .android-pwa-icon { width:54px; height:54px; flex:0 0 54px; border-radius:13px; background:#fff; object-fit:cover; }
            .android-pwa-copy { min-width:0; flex:1; }
            .android-pwa-copy strong { display:block; margin-bottom:3px; font-size:15px; }
            .android-pwa-copy span { display:block; color:#cbd5e1; font-size:12px; line-height:1.4; }
            .android-pwa-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:13px; }
            .android-pwa-actions button { min-height:40px; padding:8px 14px; border-radius:10px; font-size:13px; font-weight:750; cursor:pointer; }
            .android-pwa-later { color:#cbd5e1; background:transparent; border:1px solid #475569; }
            .android-pwa-install { display:inline-flex; align-items:center; justify-content:center; color:#fff; background:#168b5b; border:1px solid #168b5b; }
            .android-pwa-install:disabled { opacity:.72; cursor:wait; }
            .android-pwa-spinner { width:14px; height:14px; margin-right:7px; border:2px solid rgba(255,255,255,.45); border-top-color:#fff; border-radius:50%; animation:androidPwaSpin .7s linear infinite; }
            @keyframes androidPwaSpin { to { transform:rotate(360deg); } }
            @media (min-width:600px) { #android-pwa-install-prompt { left:auto; right:22px; bottom:22px; width:430px; } }
        `;

        const prompt = document.createElement('aside');
        prompt.id = 'android-pwa-install-prompt';
        prompt.setAttribute('role', 'dialog');
        prompt.setAttribute('aria-label', 'Installer PRO-SELLER');
        prompt.innerHTML = `
            <div class="android-pwa-content">
                <img class="android-pwa-icon" src="/icons/icon-192.png" alt="">
                <div class="android-pwa-copy">
                    <strong>Installer PRO-SELLER</strong>
                    <span>Accédez plus rapidement à votre espace depuis l’écran d’accueil.</span>
                </div>
            </div>
            <div class="android-pwa-actions">
                <button class="android-pwa-later" type="button">Plus tard</button>
                <button class="android-pwa-install" type="button">Installer</button>
            </div>
        `;

        prompt.querySelector('.android-pwa-later').addEventListener('click', function () {
            rememberAndroidDismissal();
            removeAndroidInstallPrompt();
        });
        prompt.querySelector('.android-pwa-install').addEventListener('click', async function (event) {
            const button = event.currentTarget;
            if (!deferredInstallPrompt || button.disabled) return;
            button.disabled = true;
            button.replaceChildren();
            const spinner = document.createElement('span');
            spinner.className = 'android-pwa-spinner';
            button.append(spinner, document.createTextNode('Installation…'));
            const installPrompt = deferredInstallPrompt;
            deferredInstallPrompt = null;
            installPrompt.prompt();
            const choice = await installPrompt.userChoice;
            if (choice.outcome === 'dismissed') rememberAndroidDismissal();
            removeAndroidInstallPrompt();
        });

        document.head.appendChild(style);
        document.body.appendChild(prompt);
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;
        if (pageLoaded) showAndroidInstallPrompt();
    });

    window.addEventListener('appinstalled', function () {
        deferredInstallPrompt = null;
        removeAndroidInstallPrompt();
        try { localStorage.removeItem(ANDROID_INSTALL_DISMISSED_KEY); } catch (error) {}
    });

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
        pageLoaded = true;
        showIosInstallGuide();
        showAndroidInstallPrompt();

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
