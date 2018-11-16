<?php
/*
Phone Book
----------
Search API
----------
Dylan Bickerstaff
----------
Searches the database for objects using search tags.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['search']) && !empty($_POST['search'])) {
    $validSearchTags = array();
    $tagSearchObjects = array();
    $organizedObjects = array();
    $objectJoin = array(
        '[>]tags_objects' => array(
            'tags.tagid' => 'tagid'
        ), '[>]objects' => array(
            'tags_objects.objectid' => 'objectid'
        )
    );
    $searchTags = json_decode($_POST['search'], true);
    $count = 100;
    $offset = 0;
    if(isset($_POST['count']) && is_numeric($_POST['count'])) {
        $count = $_POST['count'];
    }
    if(isset($_POST['offset']) && is_numeric($_POST['offset'])) {
        $offset = $_POST['offset'];
    }
    if($searchTags !== null) {
        foreach($searchTags as $tag) {
            if(!empty($tag) && ctype_alpha($tag) && $tag !== '' && strlen($tag) >= 2) {
                array_push($validSearchTags, $tag);
            }
        }
        if(count($validSearchTags) == 1) {
            $objects = $db->select('tags', $objectJoin, '*', array(
                'tags.text[~]' => $validSearchTags[0].'%'
            ));
        } else {
            $objects = $db->select('tags', $objectJoin, '*', array(
                'tags.text' => $validSearchTags,
                'GROUP' => 'tags_objects.objectid',
                'HAVING' => $db->raw('COUNT(tags_objects.objectid) = '.count($validSearchTags))
            ));
        }
        if(is_array($objects) && !empty($objects)) {
            if(isset($_POST['sort']) && !empty($_POST['sort']) && isset(SCHEMA[$_POST['sort']])) {
                usort($objects, function($a, $b) {
                    if(is_numeric($a[$_POST['sort']])) {
                        return $a[$_POST['sort']] - $b[$_POST['sort']];
                    } else {
                        return strcmp($a[$_POST['sort']], $b[$_POST['sort']]);
                    }
                });
            }
            if(isset($_POST['order']) && $_POST['order'] == 1) {
                $objects = array_reverse($objects);
            }
            foreach($objects as $k => $object) {
                $objectid = $object['objectid'];
                unset($object['tagid']);
                unset($object['text']);
                unset($object['objectid']);
                $tagSearchObjects[$k] = $objectid;
                if($count > 0 && $offset <= 0) {
                    $count--;
                    $organizedObjects[$objectid] = $object;
                } else {
                    $offset--;
                }
            }
            $filteredTags = $db->select('tags_objects', array(
                '[>]tags' => array(
                     'tags_objects.tagid' => 'tagid'
                )
            ), array(
                'tags.text'
            ), array(
                'tags_objects.objectid' => $tagSearchObjects,
                'GROUP' => 'tags.text'
            ));
            foreach($filteredTags as $k => $tag) {
                $filteredTags[$k] = $tag['text'];
            }
            if($objects !== false && $filteredTags !== false) {
                echo json_encode(array(
                    'tags' => $filteredTags,
                    'objects' => $organizedObjects
                ), $prettyPrintIfRequested);
            }
        } else {
            echo '[]';
        }
    } else {
        echo '[]';
    }
}
?>