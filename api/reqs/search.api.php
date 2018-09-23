<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['search']) && !empty($_POST['search'])) {
    $validSearchTags = array();
    $searchTags = json_decode($_POST['search'], true);
    if($searchTags !== null) {
        foreach($searchTags as $tag) {
            if(!empty($tag) && ctype_alpha($tag) && $tag !== '') {
                array_push($validSearchTags, $tag);
            }
        }
        echo json_encode($db->select('tags', array(
            '[>]tags_objects' => array(
                'tags.tagid' => 'tagid'
            ),
            '[>]objects' => array(
                'tags_objects.objectid' => 'objectid'
            )
        ), 'objects.number', array(
            'tags.text[~]' => array(
                'AND' => $searchTags
            ),
            'LIMIT' => 100
        )));
        print_r($db->last());
    }
}
?>