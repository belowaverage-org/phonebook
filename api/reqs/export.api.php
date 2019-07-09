<?php
/*
Phone Book
----------
Export API
----------
Dylan Bickerstaff
----------
Exports object information from the database.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['export'])) {
    require_once('auth.lib.php');
    require_once('library.lib.php');
    if($_POST['export'] == 'tags') {
        echo json_encode($db->select('tags', 'text'));
    } elseif($_POST['export'] == 'objects' && isset($_POST['objects']) && !empty($_POST['objects'])) {
        $objects = json_decode($_POST['objects'], true);
        if($objects !== false) {
            $results = $db->select('objects', '*', array(
                'objectid' => $objects
            ));
            $results = organizeDatabaseObjects($results, isset($_POST['includeTags']));
            echo json_encode($results, $prettyPrintIfRequested);
        }
    } elseif($_POST['export'] == 'all' && auth_authenticated()) {
        $objects = $db->select('objects', '*');
        $objects = organizeDatabaseObjects($objects, isset($_POST['includeTags']));
        echo json_encode($objects, $prettyPrintIfRequested);
    }
}
?>