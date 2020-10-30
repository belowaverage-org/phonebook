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
    $('#statistics .stat-l1').addClass('loading').text('');
    updateStatistic('usersOnline', data.sessions);
    updateStatistic('numbersStored', data.objects);
    updateStatistic('termsIndexed', data.tags + data.tags_objects);
    updateStatistic('queriesPerformed', data.statistics);
}
function resultsCounts(data) {
    var averageApiTime = 0;
    var averageResults = 0;
    var popularQueries = {};
    var popularQueriesSorted = [];
    var popularQueriesString = "";
    $('#statistics .stat-l2').addClass('loading').text('');
    data.forEach(function(event) {
        event.query.forEach(function(term) {
            if(popularQueries[term] == null) {
                popularQueries[term] = 1;
            } else {
                popularQueries[term] += 1;
            }
        });
        averageApiTime += parseFloat(event.apispeed);
        averageResults += parseInt(event.count);
    });
    Object.keys(popularQueries).forEach(function(key) {
        popularQueriesSorted.push([key, popularQueries[key]]);
    });
    popularQueriesSorted = popularQueriesSorted.sort(function(a, b) {
        return b[1] - a[1];
    });
    averageApiTime = averageApiTime / data.length;
    averageResults = averageResults / data.length;
    if(!isNaN(averageApiTime)) updateStatistic('averageResponseTime', averageApiTime + ' seconds');
    if(!isNaN(averageResults)) updateStatistic('averageResultsReturned', Math.round(averageResults));
    popularQueriesSorted.some(function(query, index, array) {
        if(index < 20) {
            popularQueriesString += query[0] + ', ';
        }
    });
    popularQueriesString = popularQueriesString.substr(0, popularQueriesString.length - 2);
    if(popularQueriesString !== '') updateStatistic('popularQueries', popularQueriesString + '.');
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