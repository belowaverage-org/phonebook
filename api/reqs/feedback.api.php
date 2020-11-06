<?php
/*
Phone Book
----------
Feedback API
----------
Dylan Bickerstaff
----------
Sends feedback via SMTP.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(!file_exists('../data/conf/feedback.cfg.php')) {
    file_put_contents('../data/conf/feedback.cfg.php',
'<?php
/*
Phone Book
----------
Feedback API Config
----------
Dylan Bickerstaff
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

//These variables setup the SMTP mail server.
$feedback_smtp_server = \'smtp.contoso.com\';
$feedback_smtp_port = 25;

//These variables setup where the email goes and how it is formatted.
$feedback_smtp_from = \'phonebook@contoso.com\';
$feedback_smtp_to = \'admins@contoso.com\';
$feedback_smtp_subject_prefix = \'PhoneBook: \';

//This variable determines what subjects are allowed.
$feedback_smtp_allowed_subjects = [
    \'Issue / Bug\',
    \'Missing / Wrong Information\',
    \'Suggestion\',
    \'Other\'
];

?>'
    );
}
require_once('../data/conf/feedback.cfg.php');
if(isset($_POST['feedback']) && $_POST['feedback'] == 'submit') {
    if(!isset($_POST['subject']) || empty($_POST['subject'])) return;
    if(!isset($_POST['body']) || empty($_POST['body'])) return;
    if(array_search($_POST['subject'], $feedback_smtp_allowed_subjects) == false) return;
    ini_set('SMTP', $feedback_smtp_server);
    ini_set('smtp_port', $feedback_smtp_port);
    ini_set('sendmail_from', $feedback_smtp_from);
    mail($feedback_smtp_to, $feedback_smtp_subject_prefix.$_POST['subject'], $_POST['body']);
}
if(isset($_POST['feedback']) && $_POST['feedback'] == 'subjects') {
    echo json_encode($feedback_smtp_allowed_subjects, $prettyPrintIfRequested);
}
?>