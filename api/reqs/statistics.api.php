<?php
/*
Phone Book
----------
Statistics API
----------
Dylan Bickerstaff
----------
Exports statistical information.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['stats'])) {
    require_once('auth.lib.php');
    if(auth_session_get()) {
        //Session Exists
        echo 'Ye';
    } else {
        echo 'No';
        auth_session_set();
    }
}
?>