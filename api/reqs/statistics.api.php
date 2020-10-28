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
function deleteOldStatistics() {
    global $db;
    global $statistics_expire_time;
    $db->delete('statistics', [
        'timestamp[<]' => (time() - $statistics_expire_time)
    ]);
}
if(isset($_POST['stats'])) {
    require_once('auth.lib.php');
    require_once('../data/conf/statistics.cfg.php');
    if($_POST['stats'] == 'ping') { //Create session.
        auth_preauthenticate();
    }
    if($_POST['stats'] == 'count') { //Count database entries.
        echo json_encode(array(
            'objects' => $db->count('objects'),
            'sessions' => $db->count('sessions'),
            'statistics' => $db->count('statistics'),
            'tags' => $db->count('tags'),
            'tags_objects' => $db->count('tags_objects'),
            'translations' => $db->count('translations')
        ), $prettyPrintIfRequested);
    }
    if($_POST['stats'] == 'feedback' && isset($_POST['feedback']) && !empty($_POST['feedback'])) { //Filter and then commit incomming feedback.
        $feedback = json_decode($_POST['feedback'], true);
        if($feedback !== null) {
            if(!isset($feedback['apispeed']) || !is_numeric($feedback['apispeed'])) return;
            if(!isset($feedback['count']) || !is_numeric($feedback['count'])) return;
            if(!isset($feedback['query']) || !is_array($feedback['query']) || count($feedback['query']) == 0) return;
            deleteOldStatistics();
            $db->insert('statistics', [
                'timestamp' => time(),
                'apispeed' => $feedback['apispeed'],
                'count' => $feedback['count'],
                'query' => json_encode($feedback['query'])
            ]);
        }
    }
    if($_POST['stats'] == 'results') { //Compile feedback results.
        deleteOldStatistics();
        $stats = $db->select('statistics', '*', ['ORDER' => ['timestamp' => 'DESC']]);
        foreach($stats as $k => $stat) {
            $stats[$k]['query'] = json_decode($stat['query'], true);
        }
        echo json_encode($stats, $prettyPrintIfRequested);
    }
}
?>