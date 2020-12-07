var oldTimeoutID = 0;
var refreshIntervalID = 0;
$(document).on('search', function() {
    clearTimeout(oldTimeoutID);
    oldTimeoutID = setTimeout(send, 1000);
});
$(document).on('bsloaded', function() {
    $('#hamburger .stats').click(function() {
        toggleMenu('#statistics');
        refreshIntervalID = setInterval(gather, 5000);
        gather();
    });
    $('#exit').click(function() {
        clearInterval(refreshIntervalID);
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
                'query': mem.lastSearchTags.filter(function(tag) {
                    return tag.length > 0;
                })
            })
        }
    });
}
function updateStatistic(className, data) {
    $('#statistics .' + className).removeClass('loading').text(data);
}
function databaseCounts(data) {
    $('#statistics .stat').addClass('loading').text('');
    var topQueries = '';
    updateStatistic('usersOnline', data.sessions);
    updateStatistic('numbersStored', data.objects);
    updateStatistic('termsIndexed', data.tags + data.tags_objects);
    updateStatistic('queriesPerformed', data.statistics);
    if(data.statistics == 0) return;
    updateStatistic('averageResponseTime', data.average_response_speed + ' seconds');
    updateStatistic('averageResultsReturned', Math.round(data.average_results_returned));
    Object.keys(data.top_search_queries).forEach(function(query, index) {
        if(index >= 20) return;
        query = query.replace('~', '');
        topQueries += query + ', '
    });
    topQueries = topQueries.substr(0, topQueries.length - 2);
    updateStatistic('popularQueries', topQueries + '.');
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