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
if(!file_exists('../data/conf/feedback.cfg.php')) { //Build config file.
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
require_once('./reqs/phpmailer.lib.php');
if(isset($_POST['feedback']) && $_POST['feedback'] == 'submit') { //Send feedback message to SMTP server.
    if(!isset($_POST['subject']) || empty($_POST['subject'])) return;
    if(!isset($_POST['body']) || empty($_POST['body'])) return;
    if(array_search($_POST['subject'], $feedback_smtp_allowed_subjects) === false) return;
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $feedback_smtp_server;
    $mail->SMTPAuth = false;
    $mail->Port = $feedback_smtp_port;
    $mail->SMTPAutoTLS = false;
    $mail->setFrom($feedback_smtp_from, 'Phone Book');
    $mail->addAddress($feedback_smtp_to);
    $mail->isHTML(false);
    $mail->Subject = $feedback_smtp_subject_prefix.$_POST['subject'];
    $mail->Body = $_POST['body'];
    $mail->send();
}
if(isset($_POST['feedback']) && $_POST['feedback'] == 'subjects') { //Return JSON list of approved subjects.
    echo json_encode($feedback_smtp_allowed_subjects, $prettyPrintIfRequested);
}
?>