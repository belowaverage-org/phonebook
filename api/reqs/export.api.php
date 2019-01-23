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
    if($_POST['export'] == 'tags') {
        echo json_encode($db->select('tags', 'text'));
    } elseif($_POST['export'] == 'objects' && isset($_POST['objects']) && !empty($_POST['objects'])) {
        $objects = json_decode($_POST['objects'], true);
        if($objects !== false) {
            $results = $db->select('objects', '*', array(
                'objectid' => $objects
            ));
            foreach($results as $k => $object) {
                if(isset($_POST['includeTags'])) {
                    require_once('library.lib.php');
                    $tags = getTagsFromObject($object['objectid'], true);
                    if($tags) {
                        $object['tags'] = $tags;
                    }
                }
                $results[$object['objectid']] = $object;
                unset($results[$k]);
                unset($results[$object['objectid']]['objectid']);
            }
            echo json_encode($results, $prettyPrintIfRequested);
        }
    }
}
?>