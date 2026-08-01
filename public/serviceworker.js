/* PrintRealtors PWA — v2 clears sticky /public redirect caches (Firefox) */
const CACHE_VERSION = "pwa-v2-20260801";
const staticCacheName = CACHE_VERSION;

self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then((cache) => {
            return fetch("/build/manifest.json")
                .then((response) => response.json())
                .then((assets) => {
                    const filesToCache = [
                        "/offline",
                        "/build/" +
                            assets[
                                "modules/Storefront/Resources/assets/public/sass/app.scss"
                            ].file,
                        "/build/" +
                            assets[
                                "modules/Storefront/Resources/assets/public/js/app.js"
                            ].file,
                        "/build/" +
                            assets[
                                "modules/Storefront/Resources/assets/public/js/main.js"
                            ].file,
                        "/pwa/icons/48x48.png",
                        "/pwa/icons/72x72.png",
                        "/pwa/icons/96x96.png",
                        "/pwa/icons/128x128.png",
                        "/pwa/icons/144x144.png",
                        "/pwa/icons/152x152.png",
                        "/pwa/icons/192x192.png",
                        "/pwa/icons/384x384.png",
                        "/pwa/icons/512x512.png",
                    ];
                    return cache.addAll(filesToCache);
                })
                .catch(() => undefined);
        })
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) =>
                Promise.all(
                    cacheNames
                        .filter((cacheName) => cacheName !== staticCacheName)
                        .map((cacheName) => caches.delete(cacheName))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Never keep users on /public — send them to the clean site URL
    if (url.origin === self.location.origin && url.pathname.startsWith("/public")) {
        const cleanPath = url.pathname.replace(/^\/public/, "") || "/";
        event.respondWith(Response.redirect(url.origin + cleanPath + url.search, 302));
        return;
    }

    // Navigations: always network-first so old redirect responses are not reused
    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request).catch(() => caches.match("/offline"))
        );
        return;
    }

    event.respondWith(
        caches.match(request).then((response) => {
            return response || fetch(request);
        }).catch(() => caches.match("/offline"))
    );
});
