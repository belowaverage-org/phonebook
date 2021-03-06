var removeTimeout = 0;
var detailItem = '<div class="detail"><span class="key"></span><span class="value"></span></div>';
$(document).on('bsloaded', function() {
    $('#numbers').on('click', 'div .description', function() {
        clearTimeout(removeTimeout);
        var targetObject = $(this).parent('div').parent('div').toggleClass('visible');
        if(targetObject.hasClass('visible')) {
            retrieveDetails(targetObject);
        } else {
            removeTimeout = setTimeout(function() {
                targetObject.find('div:not(:first-child)').remove();
            }, 300);
        }
    });
});
window.retrieveDetails = function (targetObject, contentObject) {
    targetObject.find('div:not(:first-child)').remove();
    var objectId = targetObject.attr('objectid');
    var contentObject = $('<div class="details loading"></div>').appendTo(targetObject);
    for (var c = 0; c < 3; c++) {
        $(detailItem).appendTo(contentObject);
    }
    $.ajax({
        method: 'POST',
        dataType: 'json',
        url: apiURI,
        data: {
            api: 'search',
            search: JSON.stringify({
                'SEARCH': {
                    'objectid': objectId
                },
                'OUTPUT': {
                    'OPTIONS': [
                        'showAvailableTags'
                    ],
                    'ATTRIBUTES': Object.keys(mem.schema)
                }
            })
        }
    }).done(function (data) {
        displayDetails(data, contentObject, objectId);
    });
};
window.displayDetails = function (data, contentObject, objectId) {
    contentObject.removeClass('loading').html('');
    $.each(data.objects[objectId], function (key, value) {
        var schema = mem.schema[key];
        if (!schema.visible) return;
        var item = $(detailItem).appendTo(contentObject);
        item.find('.key').text(schema.name);
        item.find('.value').text(value);
    });
    var tagList = $(detailItem).addClass('tags').appendTo(contentObject);
    tagList.html('');
    $.each(data.tags, function () {
        $('<span class="type"></span>').text(this).appendTo(tagList);
    });
};