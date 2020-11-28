var CACHE_VERSION = '2.0.12';
var CURRENT_CACHES = {
    cache: 'phonebook-v' + CACHE_VERSION
};
self.addEventListener('activate', function (event) {
    var expectedCacheNamesSet = new Set(Object.values(CURRENT_CACHES));
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.map(function (cacheName) {
                    if (!expectedCacheNamesSet.has(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
self.addEventListener('fetch', function (event) {
    event.respondWith(
        caches.open(CURRENT_CACHES.cache).then(function (cache) {
            return cache.match(event.request).then(function (response) {
                if (response) {
                    return response;
                }
                return fetch(event.request.clone()).then(function (response) {
                    if (
                        response.status == 200 &&
                        !response.url.includes('/api/')
                    ) {
                        cache.put(event.request, response.clone());
                    }
                    return response;
                });
            }).catch(function (error) {
                throw error;
            });
        })
    );
});