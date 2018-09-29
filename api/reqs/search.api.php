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
            if(!empty($tag) && ctype_alpha($tag) && $tag !== '') {
                array_push($validSearchTags, $tag);
            }
        }
        $results = $db->select('tags', array(
            '[>]tags_objects' => array(
                'tags.tagid' => 'tagid'
            ),
            '[>]objects' => array(
                'tags_objects.objectid' => 'objectid'
            )
        ), 'objects.number', array(
            'tags.text[~]' => array(
                'OR' => $validSearchTags
            ),
            'LIMIT' => $count
        ));
        if($results !== false) {
            echo json_encode($results);
        } else {
            echo '[]';
        }   
    }
}
?>