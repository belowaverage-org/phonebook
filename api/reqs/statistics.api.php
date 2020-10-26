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
    auth_preauthenticate(); //Generate session.
    if($_POST['stats'] == 'count') { //Count active sessions.
        echo $db->count('sessions');
    }
}
?>