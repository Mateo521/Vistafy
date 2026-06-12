const CACHE_NAME = 'f33-cache-v2'; 
const urlsToCache = [
    '/offline.html',
    '/pwa-icons/icon-192x192.png',
    '/pwa-icons/icon-512x512.png'
];


self.addEventListener('install', event => {
    self.skipWaiting(); 
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Purgando caché antiguo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim()) 
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request)
                    .then(cacheResponse => {
                        return cacheResponse || caches.match('/offline.html');
                    });
            })
    );
});