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
    updateStatistic('usersOnline', data.sessions);
    updateStatistic('numbersStored', data.objects);
    updateStatistic('termsIndexed', data.tags + data.tags_objects);
    updateStatistic('queriesPerformed', data.statistics);
}
function resultsCounts(data) {
    var averageApiTime = 0;
    var averageResults = 0;
    var popularQueries = {};
    data.forEach(function(event) {
        event.query.forEach(function(term) {
            if(popularQueries[term] == null) {
                popularQueries[term] = 0;
            } else {
                popularQueries[term] += 1;
            }
        });
        averageApiTime += parseFloat(event.apispeed);
        averageResults += parseInt(event.count);
    });
    console.log(popularQueries);
    averageApiTime = averageApiTime / data.length;
    averageResults = averageResults / data.length;
    updateStatistic('averageResponseTime', averageApiTime + ' seconds');
    updateStatistic('averageResultsReturned', averageResults);
    updateStatistic('popularQueries', '<b>asdf</b>');
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
    $.ajax({
        type: 'post',
        async: true,
        url: apiURI,
        dataType: 'json',
        data: {
            api: 'stats',
            stats: 'results'
        },
        success: resultsCounts
    });
}