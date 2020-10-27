var oldTimeout = 0;
$(document).on('search', function() {
    clearTimeout(oldTimeout);
    oldTimeout = setTimeout(send, 1000);
});
function send() {
    if(mem.lastSearchTags.length == 0 || mem.lastSearchTags[0].length <= 1) return;
    $.ajax({
        type: 'post',
        async: true,
        url: apiURI,
        dataType: 'json',
        data: {
            api: 'stats',
            stats: 'feedback',
            feedback: JSON.stringify({
                'speed': mem.lastSearchSpeed,
                'count': Object.keys(mem.objectsFromLastCall).length,
                'tags': mem.lastSearchTags.filter(tag => tag.length > 0)
            })
        }
    });
}