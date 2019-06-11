$(document).on('bsloaded', function() {
   $('<link rel="stylesheet" href="static/css/demo.css">').appendTo('head');
    typeOut(
        'Hi!--------+--------' +
        'W---elc-ome t---o th-e Pho--ne Book!----------------^^' +
        'To begin a search:+Just start typing!-------------------^^' +
        'You can find more functions like:+Printing+Exporting+and Help+in this menu to the left.'
    , 60, function() {
        $('#hamopen').click();
    });
});

function typeOut(text, delay, callback) {
    var string = new String(text);
    setTimeout(callback, (string.length * delay));
    for(var count = 0; string.length > count; count++) {
        (function() {
            var ct = count;
            setTimeout(function() {
                if(string[ct] == '-') {
                    return;
                }
                if(string[ct] == '+') {
                    $('#input > span').removeClass('type');
                    $('<span class="type"></span>').appendTo('#input');
                    return;
                }
                if(string[ct] == '~') {
                    $('#input > span:last-child').addClass('valid');
                    return;
                }
                 if(string[ct] == '^') {
                    if($('#input > span').length == 1) {
                        $('#input > span:last-child').text('');
                    } else {
                        $('#input > span:last-child').remove();
                    }
                    $('#input > span:last-child').addClass('type');
                    return;
                }
                $('#input > span:last-child').append(string[ct]);
            }, (count * delay));
        })()
    }
}