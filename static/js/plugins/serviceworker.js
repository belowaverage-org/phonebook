$(document).on('bsloaded', function() {
    if('serviceWorker'in navigator) { //Caches to android homescreen.
        navigator.serviceWorker.register('./serviceworker.js');
    }
});