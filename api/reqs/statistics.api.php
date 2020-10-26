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
    if($_POST['stats'] == 'ping') { //Create session.
        auth_preauthenticate();
    }
    if($_POST['stats'] == 'count') { //Count active sessions.
        echo $db->count('sessions');
    }
    if($_POST['stats'] == 'feedback' && isset($_POST['feedback']) && !empty($_POST['feedback'])) { 
        $feedback = json_decode($_POST['feedback'], true);
        if($feedback !== null) {
            print_r($feedback);
            if(!isset($feedback['speed']) || !is_numeric($feedback['speed'])) return;
            if(!isset($feedback['tags']) || !is_array($feedback['tags']) || count($feedback['tags']) == 0) return;
            $db->insert('statistics', [
                "timestamp" => time(),
                "apispeed" => $feedback['speed'],
                "query" => json_encode($feedback['tags'])
            ]);
            echo 'NEEDS TO SELF CLEAN STILL';
        }
    }
}
?>