/*
Phone Book
----------
Client JS
----------
Dylan Bickerstaff
----------
Contains the logic and communication between the API and the client.
*/
eval(function(p,a,c,k,e,d){e=function(c){return c.toString(36)};if(!''.replace(/^/,String)){while(c--){d[c.toString(a)]=k[c]||c.toString(a)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('$(7).8(0(){$(\'#6\').5(0(){3($(\'#4 > 9:a-g(2)\').e()==1(\'d==\')){$(\'b\').c(1(\'f\'))}})});',17,17,'function|atob||if|input|dblclick|info|document|ready|span|nth|body|append|MTQ3MjU4MzY5MA|text|PGltZyBjbGFzcz0iYyIgc3JjPSJzdGF0aWMvaW1nL2MuZ2lmIj4|child'.split('|'),0,{}));
//Global Variables
window.apiURI = './api/';
window.mem = {
    availableTags: [],
    tagsFromLastCall: [],
    allTags: {},
    allTagsRetrieving: false,
    cache: 0,
    scrollTriggered: false,
    scrollPageOffset: 0,
    lastSearchTags: [],
    jLastSearchTags: "",
    lastSearchOffset: 0,
    lastSearchSpeed: 0,
    scrollPageEnd: false,
    schema: {},
    objectsFromLastCall: []
};
var printRows = 30;
var firstLoad = true;
var firstType = true;
var loadCount = 100;
var colorRangeMin = 100;
var colorRangeMax = 200;
var numberMode = false;
var descriptionMode = false;
var searchOffset = 0;
var cacheTimeout = 10; //Seconds
var ajaxSearchQuery = {abort: function() {}};
var ajaxSearchNumbers = {abort: function() {}};
var pingInterval = 59; //Seconds
var placeDashes = true;
var blurToggle = '#main, #hamburger, #hamopen';
//Functions
function time() { //Return unix timestamp
    return Math.round((new Date()).getTime() / 1000);
}
function seedRandom(seed, min, max)
{
    Math.seedrandom(seed);
    return Math.floor(Math.random()*(max-min+1)+min);
}
function autoFillTag(term) { //Returns rest of tag
    if(mem.cache < time() - cacheTimeout && !mem.allTagsRetrieving) { //Renew cache
        mem.allTagsRetrieving = true;
        $.ajax({ //Request tags
            type: 'post',
            async: true,
            dataType: 'json',
            url: apiURI,
            data: {
                'api': 'export',
                'export': 'tags'
            },
            success: function(data) {
                mem.allTags = $.makeArray(data).sort(function(a, b) { //Create an array from data object, then sort it by string length
                    return b.length - a.length;
                });
                mem.cache = time(); //Update cache timestamp
                if(firstLoad) {
                    firstLoad = false;
                    $.getJSON(apiURI + 'schema.cfg.json', function(schema) {
                        mem.schema = schema;
                        $('#loading').addClass('hidden');
                        $(blurToggle).removeClass('blur');
                    });
                }
                mem.allTagsRetrieving = false;
            }
        });
    }
    var ret = '';
    function getAutoFill() { //Foreach tags as tag, find the first one that starts with the search term
        if(this.startsWith(term)) {
            ret = this.replace(term, ''); //Cut out the term, and return the rest
            return;
        }
    }
    if($.isEmptyObject(mem.availableTags) || $('#input > span').length == 1) {
        $.each(mem.allTags, getAutoFill);
    } else {
        var availableMinusBubbles = [];
        mem.availableTags.forEach(function(value) {
            if ($.inArray(value, getSearchTags()) > -1) return; //Do not allow duplicates through autofill.
            availableMinusBubbles.push(value);
        });
        $.each(availableMinusBubbles, getAutoFill);
    }
    return ret;
}
function deleteSelectedBubble() { //Delete the type bubble
    mem.availableTags = mem.tagsFromLastCall;
    if($('#input span').length !== 1) { //If not last bubble
        $('#input .type').remove(); //Remove it
    }
}
function selectBubble(jqueryBubble) { //Make jquery selected bubble the type bubble
    if(jqueryBubble.is('#input > span')) { //If next bubble is a bubble
        $('#input .type').removeClass('type'); //remove type from any bubble
        jqueryBubble.addClass('type'); //Add type to bubble.
    }
}
function allFilled() { //Check bubbles to see if they all contain text
    var result = true;
    $.each($('#input span'), function() { //For every bubble
        if($(this).text() == '') { //If bubble is empty
            result = false;
            return false;
        }
    });
    return result;
}
function allValid() { //Check all bubbles to see if they are all valid
    var result = true;
    $.each($('#input > span'), function() {
        if(!$(this).hasClass('valid')) { //If is valid
            result = false;
            return;
        }
    });
    return result;
}
function typeFilled() { //Check if type is filled
    if($('#input span.type').text() == '') {
        return false;
    } else {
        return true;
    }
}
function typeValid() { //Check if type is a valid tag
    if(typeFilled()) {
        if($('#input > span').length > 1 && mem.availableTags.indexOf($('#input span.type').text()) !== -1) {
            return true;
        } else if($('#input > span').length == 1 && mem.allTags.indexOf($('#input span.type').text()) !== -1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function typeUnique() { //Check if type is unique to all other bubbles
    var result = true;
    $.each($('#input > span'), function() {
        if(!$(this).hasClass('type') && $(this).text() == $('#input span.type').text()) { //If bubble is not type and equal to type
            result = false;
            return;
        }
    });
    return result;
}
function formatPhoneNumber(number) {
    if(placeDashes && number.toString().length == 10) {
        number = number.toString();
        var three = number.slice(0, 3) + '-';
        var six = number.slice(3, 6) + '-';
        var ten = number.slice(6);
        number = three + six + ten;
    } else if(placeDashes && number.toString().length == 11) {
        number = number.toString();
        var co = '+' + number.slice(0, 1) + ' ';
        var three = number.slice(1, 4) + '-';
        var six = number.slice(4, 7) + '-';
        var ten = number.slice(7);
        number = co + three + six + ten;
    } else if(placeDashes && number.toString().length > 11) {
        number = number.toString();
        var three = number.slice(0, 3) + '-';
        var six = number.slice(3, 6) + '-';
        var ten = number.slice(6, 10);
        var ext = ' +' + number.slice(10);
        number = three + six + ten + ext;
    }
    return number;
}
function loadNumberTags(num) {
    $('#input span').remove(); //Remove all bubbles
    $('<span class="number type">'+num+'</span>').appendTo('#input'); //Create number bubble
    $('input[type=text]').val(''); //Clear description
    $.ajax({ //Request tags
        type: 'post',
        async: true,
        dataType: 'json',
        url: apiURI,
        data: {
            api: 'export',
            export: 'numbers',
            includeTags: true,
            numbers: '["'+num+'"]'
        },
        success: function(results) {
            $.each(results, function(k) {
                if($('#input .number').text() == num) { //If loaded number is still the number set in the first bubble
                    $('input[type=text]').val(this.description); //Set description field
                    $.each(this.tags, function() { //Create all bubbles
                        $('<span class="valid saved">'+this+'</span>').appendTo('#input');
                    });
                }
                return;
            });
        }
    });
}
function alertToSend() { //Show alert before sending data
    descriptionMode = true;
    $('#question').show();
    $(blurToggle).addClass('blur');
    $('#question').on('click', 'span', function() { //Listen for click on yes.
        if($(this).hasClass('yes')) {
            sendTagsAndDescription();
        }
        closeAlert();
    });
    $('#question').on('keypress', function(e) { //Listen for key press enter.
        if(e.keyCode == 13) { //Enter
            sendTagsAndDescription();
        }
        closeAlert();
    }).focus();
}
function closeAlert() {
    $('#question').hide().unbind(); //Unbind all events and remove alert
    $(blurToggle).removeClass('blur'); 
    descriptionMode = false;
}
function sendTagsAndDescription() { //Send all tags to database
    var tags = [];
    var number = {};
    var num = $('#input > span:first').text();
    $.each($('#input > span:not(:first)'), function() { //Each bubble
        selectBubble($(this));
        if(typeFilled()) {
            tags.push($(this).text()); //Push data to array
        }
    });
    number[num] = {
        'description': $('input[type=text]').val(), //Push description to array
        'tags': tags
    };
    $.ajax({ //Send data
        type: 'post',
        async: true,
        url: apiURI,
        data: {
            api: 'import',
            import: JSON.stringify(number)
        },
        success: function() {
            loadNumberTags(num); //Reload tag to display updated data
        }
    });
}
function getSearchTags() {
    var tags = [];
    $.each($('#input > span'), function() { //For each bubble
        if($(this)[0] !== $('#input > span:last-child')[0]) {
            tags.push($(this).text()); //push to array to send later
        }
    });
    if($('#input > span').length > 1) {
        var text = $('#input .type').text();
        if($.inArray(text, tags) == -1) {
            tags.push(text);
        }
    } else {
        var text = $('#input > span').clone().children().remove().end().text();
        if($.inArray(text, tags) == -1) {
            tags.push(text);    
        }
    }
    return tags;
}
function searchTagsRaw(callback, attributes) {
    ajaxSearchQuery = $.ajax({ //Send search query
        type: 'post',
        async: true,
        url: apiURI,
        dataType: 'json',
        data: {
            api: 'search',
            search: JSON.stringify({
                'SEARCH': {
                    'TAGS': getSearchTags(),
                    'ORDER': {
                        'number': 'ASC'
                    }
                },
                'OUTPUT': {
                    'ATTRIBUTES': attributes
                }
            })
        },
        success: function(results) {
            callback.call(results);
        }
    });
}
function searchTags(arg1, arg2) { //grab all tags and search the database and return the result on screen.
    var callback = function() {};
    var keepContent = false;
    args = [arg1, arg2];
    $.each(args, function(k, v) {
        if(typeof v == 'function') {
            callback = v;
        }
        if(typeof v == 'boolean') {
            keepContent = v;
        }
    });
    var tags = getSearchTags();
    jtags = JSON.stringify(tags);
    if(jtags !== mem.jLastSearchTags || (keepContent && !mem.scrollPageEnd) ) {
        mem.lastSearchTags = tags;
        mem.jLastSearchTags = jtags;
        ajaxSearchQuery.abort(); //Abort the previous requests.
        if(!keepContent) {
            mem.scrollPageOffset = 0;
        } else if(mem.scrollPageOffset == 0) {
            mem.scrollPageOffset += loadCount;
        }
        ajaxSearchQuery = $.ajax({ //Send search query
            type: 'post',
            async: true,
            url: apiURI,
            dataType: 'json',
            data: {
                api: 'search',
                search: JSON.stringify({
                    'SEARCH': {
                        'TAGS': tags,
                        'ORDER': {
                            'number': 'ASC'
                        },
                        'LIMIT': [
                            mem.scrollPageOffset,
                            loadCount
                        ]
                    },
                    'OUTPUT': {
                        'OPTIONS': [
                            'showAvailableTags'
                        ],
                        'ATTRIBUTES': [
                            'number',
                            'description',
                            'type'
                        ]
                    }
                })
            },
            success: function(results, status, xhr) { //On success
                mem.lastSearchSpeed = parseFloat(xhr.getResponseHeader('phonebook-api-response-time'));
                $('.resultsMessage').hide();
                if(!keepContent) {
                    mem.scrollPageEnd = false;
                    $('#numbers').html(''); //Clear numbers
                }
                var validTagCount = $('#input > span.valid').length;
                mem.tagsFromLastCall = results.tags;
                if($.isEmptyObject(results.objects)) {
                    mem.scrollPageEnd = true;
                    if(keepContent) {
                        $('#endresult').show();
                    } else {
                        $('#noresult').show();
                    }
                } else {
                    mem.objectsFromLastCall = results.objects;
                    $.each(results.objects, function(k) {
                        var r1 = colorRangeMin;
                        var r2 = colorRangeMax;
                        var color = 'background-color:rgb('+seedRandom(k+1,r1,r2)+','+seedRandom(k+2,r1,r2)+','+seedRandom(k+3,r1,r2)+');';
                        var number = $('<div objectid="'+k+'" type="'+this.type+'"><div><span class="tn-border" style="'+color+'"><span class="tn-image"></span></span><span class="number">'+formatPhoneNumber(this.number)+'</span><span class="description">'+this.description+'</span></div></div>') //Show each number on screen
                        .appendTo('#numbers');
                    });
                    if(keepContent) {
                        mem.scrollPageOffset += loadCount;
                    }
                }
                $('#numbers').trigger('search');
                $(document).trigger('search');
                callback.call();
            }
        });
    }
}
function toggleHamburger() {
    $('#hamburger').toggleClass('hidden');
    $('#main').toggleClass('hamburger');
    $('#hamopen').toggleClass('hidden');
}
window.toggleMenu = function(id) {
    $(id).toggleClass('hidden');
    $(blurToggle).toggleClass('blur');
    if($(id).hasClass('hidden')) {
        $('#exit').addClass('hidden').unbind('click');
        descriptionMode = false;
    } else {
        $('#exit').removeClass('hidden').click(function() {
            toggleMenu(id);
        });
        descriptionMode = true;
    }
}
function filterPrintRows(cols) {
    $.each(cols, function() {
        allEmpty = true;
        $.each(this, function() {
            if($(this).text() !== '' && $(this)[0].tagName == 'TD') {
                allEmpty = false;
                return false;
            }
        });
        if(allEmpty) {
            $(this).remove();
        }
    });
}
function getObjectKeys(object) {
    var keys = [];
    $.each(object, function(k, v) {
        keys.push(k);
    });
    return keys;
}
function exportResults() {
    $('#loading').removeClass('hidden').find('h1').text('Sending request...');
    $(blurToggle).addClass('blur');
    function xport() {
        mem.CSVLibraryLoaded = true;
        searchTagsRaw(function() {
            $('#loading').find('h1').text('Generating CSV...');
            var objectsArray = {
                fields: [],
                records: []
            };
            $.each(mem.schema, function(k) {
                objectsArray.fields.push({id: k});
            });
            $.each(this.objects, function() {
                objectsArray.records.push(this);
            });
            var csv = new Blob([CSV.serialize(objectsArray)], {type: 'text/csv'});
            if(window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveBlob(csv, "export.csv");
            } else {
                var link = $('<a></a>')
                .attr('href', URL.createObjectURL(csv))
                .attr('download', 'export.csv')
                .appendTo('body');
                link[0].click();
                link.remove();
            }
            $('#loading').addClass('hidden');
            $(blurToggle).removeClass('blur');
        }, getObjectKeys(mem.schema));
    }
    xport();
}
function printResults() {
    $('#loading').removeClass('hidden').find('h1').text('Requesting print info...');
    printAttributes = [];
    $.each(mem.schema, function(k, v) {
        if(typeof v['print'] !== 'undefined' && v['print']) {
            printAttributes.push(k);
        }
    });
    searchTagsRaw(function() {
        var result = this;
        $('#loading h1').text('Generating print page...');
        setTimeout(function() {
            var psrn = $('#printscrn').html('');
            var tabl = $('<table></table>').appendTo(psrn);
            var thed = $('<tr></tr>').appendTo(tabl);
            var cols = {};
            var count = printRows;
            $.each(result.objects, function() {
                var row = this;
                if(count-- == 0) {
                    filterPrintRows(cols);
                    cols = {};
                    tabl = $('<table></table>').appendTo(psrn);
                    thed = $('<tr></tr>').appendTo(tabl);
                    count = printRows;
                }
                var tr = $('<tr></tr>').appendTo(tabl);
                var col = 0;
                $.each(mem.schema, function(k, v) {
                    if(typeof cols[++col] == 'undefined') {
                        cols[col] = $();
                    }
                    if(v == null) {
                        v = '';
                    }
                    if(count + 1 == printRows) {
                        cols[col] = cols[col].add($('<th></th>').text(v.name).appendTo(thed));
                    }
                    cols[col] = cols[col].add($('<td></td>').text(row[k]).appendTo(tr));
                });
            });
            filterPrintRows(cols);
            $('#loading').addClass('hidden');
            window.print();
        }, 100);
    }, printAttributes);
}
//On doc ready
$(document).on('bsloaded', function() {
    $('#hamburger .help').click(function() {
        toggleMenu('#legend');
    });
    $('#hamburger .about').click(function() {
        toggleMenu('#about');
    });
    $('#hamburger .feedback').click(function() {
        toggleMenu('#feedback');
    });
    $('#hamopen, #hamclose').click(toggleHamburger);
    $('#hamburger .button').click(function() {
        if(!$('#hamburger').hasClass('hidden')) {
            toggleHamburger();
        }
    });
    $('#hamburger .print').click(printResults);
    $('#hamburger .export').click(exportResults);
    $('input[type=text]').click(function() { //If description input is clicked
        descriptionMode = true;
        $('#input span.type').removeClass('type').addClass('last');
    });
    $('#input').click(function() { //If main input is clicked
        descriptionMode = false;
        $('#input span').removeClass('last');
    });
    $('#input').on('mousedown', 'span', function() { //If a bubble is clicked
        selectBubble($(this));
    });
    $('input[type=text]').on('keydown', function (e) { //If enter key is pressed in description input
        if(numberMode && descriptionMode && e.keyCode == 13) { //Enter
            e.preventDefault();
            alertToSend();
        }
    });
    $('#numbers').on('click', 'div .description', function() { //If description is clicked in number list
        $(this).parent('div').parent('div').toggleClass('visible'); //Expand the description in case it overflows
    });
    $('#numbers').on('click', 'div .number', function() { //If number is clicked
        document.location.href = 'tel:' + $(this).text();
    });
    if(pingInterval !== 0) {
        $.post(apiURI, {api: 'stats', stats: 'ping'});
        setInterval(function() {
            $.post(apiURI, {api: 'stats', stats: 'ping'});
        }, pingInterval * 1000);
    }
    //On page scroll
    $('#main').on('scroll', function(e) {
        if(e.target.scrollTop + e.target.clientHeight > e.target.scrollHeight - 500) {
            if(!mem.scrollTriggered) {
                searchTags(true, null);
            }
            mem.scrollTriggered = true;
        } else {
            mem.scrollTriggered = false;
        }
    });
    autoFillTag(); //Initiate Application
});
//Keypress action
$(document).on('keydown', function (e) {
    var type = $('#input .type');
    if(firstType) {
        firstType = false;
        $('#tip').remove();
    }
    if(!descriptionMode) {
        e.preventDefault(); //Disable any default key press actions
        if(e.keyCode == 32) { //On Space
            type.html(type.text()); //Capture autofill
            var searchCallback = function() {};
            if(!typeUnique()) { //If type is not unique remove it
                deleteSelectedBubble();
            }
            if(allValid() || numberMode && allFilled()) { //Create new bubble
                selectBubble($('<span></span>').appendTo('#input'));
                searchCallback = function() {
                    mem.availableTags = mem.tagsFromLastCall;
                };
            } else {
                selectBubble($('#input > span:last'));
            }
            searchTags(searchCallback, null);
        } else if(e.keyCode == 9) { //Tab
            type.html(type.text()); //Capture autofill
            searchTags(null, null);
        } else if(e.keyCode == 13) { //If enter is pressed
            if(numberMode) {
                alertToSend();
            } else {
                searchTags(null, null);
            }
        } else if(e.keyCode == 46) { //Delete
            var isFirstSelected = ($('#input > span:first')[0] == type[0]); //If the first bubble is selected
            var next = type.next();
            var prev = type.prev();
            type.text(''); //Clear the bubble text in case this is the last existing bubble
            deleteSelectedBubble();
            if(isFirstSelected) { //If first bubble was selected
                selectBubble(next); //Make next bubble typeable
            } else {
                selectBubble(prev); //Make previous bubble typeable
            }
            if(!numberMode) {
                searchTags(null, null);
            }
        } else if(e.keyCode == 37) { //Left Arrow
            selectBubble(type.prev());
        } else if(e.keyCode == 39) { //Right Arrow
            selectBubble(type.next());
        } else if(e.keyCode == 40 && numberMode) { //Arrow Down
            descriptionMode = true;
            type.removeClass('type').addClass('last');
            $('input[type=text]').focus();
        } else if(e.keyCode == 35 || e.keyCode == 27) { //Esc && End
            $('#numbers').html('');
            $('#input').html('<span class="type"></span>'); //Erase all content and reset
            $('.resultsMessage').hide();
        } else if(e.keyCode == 116) { //If F5
            location.reload(true);
        } else if(e.keyCode == 80 && e.ctrlKey) { //If CTRL + P
            printResults();
        } else if(e.key.length == 1 || e.keyCode == 8) { //Any other key pressed
            if(e.keyCode == 8) { //If backspace is pressed
                if(type.text() == '') { //If selected bubble has not text
                    deleteSelectedBubble();
                    selectBubble($('#input span:last')); //Make the last one typeable
                } else {
                    type.text(type.text().slice(0, -1)); //Remove one character from end of selected bubble
                    if(type.text() == '' || $('#input > span').length == 1) { //Search on backspace only if one bubble or empty bubble
                        searchTags(function() {
                            mem.availableTags = mem.tagsFromLastCall;
                        }, null);
                    }
                }
            } else {
                type.append(e.key.toLowerCase()); //Type the key
                $('#input .type .autofill').remove(); //Remove autofill
                var autoFill = autoFillTag(type.text().replace($('#input .type .autofill').text(), '')); //Grab non autofilled text
                if(autoFill !== '') {
                    $('<span class="autofill">'+autoFill+'</span>').appendTo(type); //Create autofill
                }
            }
            type.removeClass('saved');
            if(typeFilled() && !numberMode && !typeValid() && e.keyCode !== 8) { //If type is valid, or in number mode, or type has no text        
                type.text(type.text().slice(0, -1)); //Remove one character from end of selected bubble        
            }
            if($('#input span:first')[0] == type[0] && numberMode) { //If first bubble is number and changed
                loadNumberTags(type.text());
            }
            setTimeout(function() {
                if(!numberMode && allValid()) {
                    searchTags(null, null);
                }
            });
        }
        var currentType = $('#input .type'); //Get current type since it could have changed above.
        if(typeValid()) { //If type is valid, and unique
            currentType.addClass('valid');
        } else {
            currentType.removeClass('valid');
        }
    }
    if(e.keyCode == 38) {//UpArrow //Re-select main input 
        e.preventDefault();
        $('#input span.last').addClass('type').removeClass('last');
        $('input[type=text]').blur();
        descriptionMode = false;
    }
});