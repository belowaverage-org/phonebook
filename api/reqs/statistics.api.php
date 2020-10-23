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
    if(!auth_session_get()) { //Generate session.
        auth_session_set();
    }
    if($_POST['stats'] == 'count') { //Count active sessions.
        echo $db->count('sessions');
    }
}
?>