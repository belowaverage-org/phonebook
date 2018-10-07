<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['search']) && !empty($_POST['search'])) {
    $validSearchTags = array();
    $searchTags = json_decode($_POST['search'], true);
    $count = 100;
    if(isset($_POST['count']) && is_numeric($_POST['count'])) {
        $count = $_POST['count'];
    }
    if($searchTags !== null) {
        foreach($searchTags as $tag) {
            if(!empty($tag) && ctype_alpha($tag) && $tag !== '' && strlen($tag) >= 2) {
                array_push($validSearchTags, $tag);
            }
        }
        if(count($validSearchTags) == 1) {
            $objects = $db->select('tags', array(
            '[>]tags_objects' => array(
                'tags.tagid' => 'tagid'
            )
            ), array(
                'tags_objects.objectid'
            ), array(
                'tags.text[~]' => $validSearchTags[0].'%',
                'LIMIT' => $count
            ));
        } else {
            $objects = $db->select('tags', array(
                '[>]tags_objects' => array(
                    'tags.tagid' => 'tagid'
                )
            ), array(
                'tags_objects.objectid'
            ), array(
                'tags.text' => $validSearchTags,
                'GROUP' => 'tags_objects.objectid',
                'HAVING' => $db->raw('COUNT(tags_objects.objectid) = '.count($validSearchTags)),
                'LIMIT' => $count
            ));
        }
        foreach($objects as $k => $object) {
            $objects[$k] = $object['objectid'];
        }
        $filteredTags = $db->select('tags_objects', array(
            '[>]tags' => array(
                 'tags_objects.tagid' => 'tagid'
            )
        ), array(
            'tags.text'
        ), array(
            'tags_objects.objectid' => $objects,
            'GROUP' => 'tags.text'
        ));
        foreach($filteredTags as $k => $tag) {
            $filteredTags[$k] = $tag['text'];
        }
        if($objects !== false && $filteredTags !== false) {
            echo json_encode(array(
                'tags' => $filteredTags,
                'objects' => $objects
            ));
        } else {
            echo '[]';
        }
    }
}
?>