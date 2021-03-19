var oldTimeoutID = 0;
$(document).on('search', function() {
    clearTimeout(oldTimeoutID);
    oldTimeoutID = setTimeout(open, 300);
});
function open() {
    if ($('#numbers > div').length == 1) {
        $('#numbers > div .description').click();
    }
}