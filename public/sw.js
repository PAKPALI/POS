const CACHE_VERSION = 'pro-seller-pwa-v4';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const STATIC_ASSETS = [
    '/offline.html', '/manifest.json', '/favicon.ico',
    '/icons/icon-192.png', '/icons/icon-512.png', '/icons/apple-touch-icon-180.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE)
        .then((cache) => cache.addAll(STATIC_ASSETS))
        .then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys()
        .then((keys) => Promise.all(keys
            .filter((key) => key.startsWith('pro-seller-pwa-') && key !== STATIC_CACHE)
            .map((key) => caches.delete(key))))
        .then(() => self.clients.claim()));
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Les pages connectées et les données SaaS ne sont jamais mises en cache.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    const isPublicStaticAsset = url.pathname.startsWith('/hub/assets/') ||
        url.pathname.startsWith('/icons/') ||
        ['/manifest.json', '/favicon.ico', '/offline.html'].includes(url.pathname);
    if (!isPublicStaticAsset) return;

    event.respondWith(caches.match(request).then((cachedResponse) => {
        if (cachedResponse) return cachedResponse;
        return fetch(request).then((networkResponse) => {
            if (!networkResponse || !networkResponse.ok) return networkResponse;
            const responseToCache = networkResponse.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(request, responseToCache));
            return networkResponse;
        });
    }));
});
