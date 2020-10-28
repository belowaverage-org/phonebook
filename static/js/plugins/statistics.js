var oldTimeout = 0;
$(document).on('search', function() {
    clearTimeout(oldTimeout);
    oldTimeout = setTimeout(send, 1000);
});
$(document).on('bsloaded', function() {
    $('#hamburger .stats').click(function() {
        toggleMenu('#statistics');
        gather();
    });
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
                'apispeed': mem.lastSearchSpeed,
                'count': Object.keys(mem.objectsFromLastCall).length,
                'query': mem.lastSearchTags.filter(tag => tag.length > 0)
            })
        }
    });
}
function updateStatistic(className, data) {
    $('#statistics .' + className).removeClass('loading').text(data);
}
function databaseCounts(data) {
    updateStatistic('usersOnline', data.sessions);
    updateStatistic('numbersStored', data.objects);
    updateStatistic('termsIndexed', data.tags + data.tags_objects);
    updateStatistic('queriesPerformed', data.statistics);
}
function gather() {
    $.ajax({
        type: 'post',
        async: true,
        url: apiURI,
        dataType: 'json',
        data: {
            api: 'stats',
            stats: 'count'
        },
        success: databaseCounts
    });
}