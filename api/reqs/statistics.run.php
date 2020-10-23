<?php
/*
Phone Book
----------
Statistics Reporter
----------
Dylan Bickerstaff
----------
Logs statistical information.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['api']) && !empty($_POST['api'])) {
    $db->insert('statistics', [
        'timestamp' => time(),
        'apispeed' => $apiResponseTime,
        'query' => ''
    ]);
}
?>