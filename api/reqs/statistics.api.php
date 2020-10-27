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
if(!file_exists('../data/conf/statistics.cfg.php')) {
    file_put_contents('../data/conf/statistics.cfg.php',
'<?php
/*
Phone Book
----------
Statistics API Config
----------
Dylan Bickerstaff
----------
Statistics API Config
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

//This variable sets how long statistics should be kept in the database in seconds.
$statistics_expire_time = 3600;

?>'
    );
}
if(isset($_POST['stats'])) {
    require_once('auth.lib.php');
    require_once('../data/conf/statistics.cfg.php');
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
            echo '1';
            if(!isset($feedback['count']) || !is_numeric($feedback['count'])) return;
            echo '1';
            if(!isset($feedback['tags']) || !is_array($feedback['tags']) || count($feedback['tags']) == 0) return;
            echo '1';
            $db->delete('statistics', [
                "timestamp[<]" => (time() - $statistics_expire_time)
            ]);
            $db->insert('statistics', [
                "timestamp" => time(),
                "apispeed" => $feedback['speed'],
                "count" => $feedback['count'],
                "query" => json_encode($feedback['tags'])
            ]);
        }
    }
}
?>