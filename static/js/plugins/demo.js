$(document).on('bsloaded', function() {
   $('<link rel="stylesheet" href="static/css/demo.css">').appendTo('head');
    typeOut(
        'Hi!--------+--------' +
        'Welcome to the Phone Book!'
    , 60);
});

function typeOut(text, delay) {
    var string = new String(text);
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
                $('#input > span:last-child').append(string[ct]);
            }, (count * delay));
        })()
    }
}