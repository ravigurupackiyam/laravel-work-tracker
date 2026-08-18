const CACHE_VERSION = 'work-timer-v1';
const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only ever handle GET — never intercept the POST calls that start/end
    // the timer or submit forms; those must always hit the network live.
    if (request.method !== 'GET') {
        return;
    }

    // Page navigations: try the network first (data must be fresh),
    // fall back to the cached offline page if there's no connection.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Static assets (icons, manifest, CDN css/js): cache-first, then
    // refresh the cache in the background for next time.
    event.respondWith(
        caches.match(request).then((cached) => {
            const network = fetch(request)
                .then((response) => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_VERSION).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => cached);

            return cached || network;
        })
    );
});
