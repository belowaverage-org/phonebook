(function () {
    if ( typeof window.CustomEvent === "function" ) { 
        return false;
    }
    function CustomEvent ( event, params ) {
        params = params || { bubbles: false, cancelable: false, detail: null };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    }
    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;
})();
var bootstrap = {
    loaded: new CustomEvent('bsloaded'),
    loadFile: function(src, callback) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4 && xhr.status == 200) {
                callback.call(xhr, xhr.response);
            }
        };
        xhr.open('GET', src, true);
        xhr.send();
    }, loadNextPlugin: function(srcArray, index, callback) {
        if(typeof srcArray[index] !== 'undefined') {
            this.loadFile('./static/js/plugins/' + srcArray[index], function(data) {
                eval(data);
                bootstrap.loadNextPlugin(srcArray, ++index, callback);
            });
        } else {
            callback.call();
        }
    }, loadPlugins: function(callback) {
        this.loadFile('./static/js/plugins.json', function(data) {
            var plugins = JSON.parse(data);
            bootstrap.loadNextPlugin(plugins, 0, function() { 
                callback.call();
            });
        });
    }
};
onload = function() {
    bootstrap.loadPlugins(function() {
        document.dispatchEvent(bootstrap.loaded);
    });
};