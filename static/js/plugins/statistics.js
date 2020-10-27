$(document).on('search', function() {
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
                'tags': mem.lastSearchTags
            })
        }
    });
});