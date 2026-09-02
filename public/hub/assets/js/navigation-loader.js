(function (window, document) {
    'use strict';
    var loader = document.getElementById('navigationLoader');
    if (!loader) return;
    var visible = false;
    function show() { if (visible) return; visible = true; loader.classList.add('is-visible'); loader.setAttribute('aria-hidden', 'false'); }
    function hide() { visible = false; loader.classList.remove('is-visible'); loader.setAttribute('aria-hidden', 'true'); }
    function isNavigable(link, event) {
        if (!link || !link.href || link.hasAttribute('download') || ['_blank', '_parent', '_top'].includes(link.target)) return false;
        if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0)) return false;
        if (link.hasAttribute('data-no-navigation-loader') || link.closest('[data-no-navigation-loader]')) return false;
        var url; try { url = new URL(link.href, window.location.href); } catch (_) { return false; }
        if (url.origin !== window.location.origin || (url.pathname === window.location.pathname && url.search === window.location.search && url.hash)) return false;
        return url.protocol === window.location.protocol;
    }
    document.addEventListener('click', function (event) { var link = event.target.closest && event.target.closest('a'); if (isNavigable(link, event)) show(); }, true);
    window.addEventListener('pageshow', hide); window.addEventListener('pagehide', hide);
    window.NavigationLoader = {show: show, hide: hide};
})(window, document);
