var CACHE_NAME = 'phonebook-v2.0.0.6';
var urlsToCache = [
    './index.htm',
    './static/font.ttf',
    './static/css/index.css',
    './static/css/keyboard.css',
    './static/img/back.svg',
    './static/img/c.gif',
    './static/img/export.svg',
    './static/img/feedb.svg',
    './static/img/hamburger.svg',
    './static/img/help.svg',
    './static/img/logo.png',
    './static/img/logo.svg',
    './static/img/logo_tiny.svg',
    './static/img/info.svg',
    './static/img/load.gif',
    './static/img/print.svg',
    './static/img/stats.svg',
    './static/img/x.svg',
    './static/js/plugins/csv.js',
    './static/js/plugins/jquery.js',
    './static/js/plugins/keyboard.js',
    './static/js/plugins/phonebook.js',
    './static/js/plugins/seedrandom.js',
    './static/js/plugins/startswith.js',
    './static/js/plugins/statistics.js',
    './static/js/plugins/feedback.js'
];
self.addEventListener('install', function(event) {
    event.waitUntil(caches.open(CACHE_NAME).then(function(cache) {
        return cache.addAll(urlsToCache);
    }));
});
self.addEventListener('fetch', function(event) {
    event.respondWith(caches.match(event.request).then(function(response) {
        if(response) {
            return response;
        }
        return fetch(event.request);
    }));
});