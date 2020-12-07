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
        deleteOldStatistics();
        $statisticCount = $db->count('statistics');
        $topSearchQueries = [];
        $averageResponseObjects = 0;
        $averageResponseSpeed = 0;
        foreach($db->select('statistics', ['count', 'apispeed', 'query']) as $row) { //Compile random statistics.
            $averageResponseObjects += $row['count'];
            $averageResponseSpeed += $row['apispeed'];
            foreach(json_decode($row['query']) as $query) {
                $query = '~' . $query;
                if(isset($topSearchQueries[$query])) {
                    $topSearchQueries[$query] += 1;
                } else {
                    $topSearchQueries[$query] = 1;
                }
            }
        }
        if($statisticCount !== 0) {
            $averageResponseObjects = $averageResponseObjects / $statisticCount;
            $averageResponseSpeed = $averageResponseSpeed / $statisticCount;
            arsort($topSearchQueries);
            $topSearchQueries = array_slice($topSearchQueries, 0, 100, true); //Limit queries to return to 100.
        }
        echo json_encode([
            'objects' => $db->count('objects'),
            'sessions' => $db->count('sessions'),
            'statistics' => $statisticCount,
            'tags' => $db->count('tags'),
            'tags_objects' => $db->count('tags_objects'),
            'translations' => $db->count('translations'),
            'average_results_returned' => $averageResponseObjects,
            'average_response_speed' => $averageResponseSpeed,
            'top_search_queries' => $topSearchQueries
        ], $prettyPrintIfRequested);
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