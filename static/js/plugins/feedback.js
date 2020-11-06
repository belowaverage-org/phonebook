$(document).on('bsloaded', function() {
    $('#feedback .button').click(function() {
        if ($('#feedback select')[0].selectedIndex == 0) {
            var message = $('<h4 style="color:red;">Please select a subject.</h4>').hide().appendTo('#feedback').fadeIn();
            setTimeout(function() {
                message.fadeOut(function() { $(this).remove(); });
            }, 5000);
            return;
        }
        if ($('#feedback textarea')[0].value == '') {
            var message = $('<h4 style="color:red;">Please enter a body to your message.</h4>').hide().appendTo('#feedback').fadeIn();
            setTimeout(function() {
                message.fadeOut(function() { $(this).remove(); });
            }, 5000);
            return;
        }
        send();
    });
    $('#hamburger .feedback').click(function() {
        $('#feedback select')[0].value = '';
        $('#feedback textarea')[0].value = '';
        retrieveSchema();
    });
});
function send() {
    $('#loading').removeClass('hidden').find('h1').text('Sending feedback...');
    toggleMenu('#feedback');
    $.ajax({
        type: 'post',
        async: true,
        url: apiURI,
        dataType: 'text',
        data: {
            api: 'feedback',
            feedback: 'submit',
            subject: $('#feedback select')[0].value,
            body: $('#feedback textarea')[0].value
        },
        success: function() {
            $('#loading').addClass('hidden');
        }
    });
}
function retrieveSchema() {
    $('#feedback option.api').remove();
    $.ajax({
        type: 'post',
        async: true,
        url: apiURI,
        dataType: 'json',
        data: {
            api: 'feedback',
            feedback: 'subjects'
        },
        success: function(subjects) {
            subjects.forEach(function(subject) {
                $('<option class="api"></option>').html(subject).appendTo('#feedback select');
            });
        } 
    });
}