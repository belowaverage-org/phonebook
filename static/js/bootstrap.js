var bootstrap = {
    loaded: new Event('bsloaded'),
    loadFile: function(src, callback = function() {}) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4 && xhr.status == 200) {
                callback.call(xhr, xhr.response);
            }
        };
        xhr.open('GET', src, true);
        xhr.send();
    }, loadNextPlugin: function(srcArray, index, callback = function() {}) {
        if(typeof srcArray[index] !== 'undefined') {
            this.loadFile('./static/js/plugins/' + srcArray[index], function(data) {
                eval(data);
                bootstrap.loadNextPlugin(srcArray, ++index, callback);
            });
        } else {
            callback.call();
        }
    }, loadPlugins: function(callback = function() {}) {
        this.loadFile('./static/js/plugins.json', function(data) {
            var plugins = JSON.parse(data);
            bootstrap.loadNextPlugin(plugins, 0, function() { 
                callback.call();
            });
        });
    }
}
onload = function() {
    bootstrap.loadPlugins(function() {
        document.dispatchEvent(bootstrap.loaded);
    });
};