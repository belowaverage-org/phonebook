var CACHE_NAME = 'phonebook';
var urlsToCache = [
    './index.htm',
    './serviceworker.js',
    './api/schema.cfg.json',
    './static/font.woff2',
    './static/manifest.json',
    './static/css/index.css',
    './static/css/keyboard.css',
    './static/img/back.svg',
    './static/img/c.gif',
    './static/img/export.svg',
    './static/img/feedb.svg',
    './static/img/hamburger.svg',
    './static/img/help.svg',
    './static/img/ico.png',
    './static/img/info.svg',
    './static/img/load.gif',
    './static/img/print.svg',
    './static/img/stats.svg',
    './static/img/x.svg',
    './static/js/csv.js',
    './static/js/index.js',
    './static/js/keyboard.js'
];
self.addEventListener('install', function(event) {
    event.waitUntil(caches.open(CACHE_NAME).then(function(cache) {
        return cache.addAll(urlsToCache);
    }));
});
self.addEventListener('fetch', function(event) {
    event.respondWith(caches.match(event.request).then(function(response) {
        if (response) {
            return response;
        }
        return fetch(event.request);
    }));
});
