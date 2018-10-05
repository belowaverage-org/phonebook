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
        $possibleResults = $db->select('tags', array(
            '[>]tags_objects' => array(
                'tags.tagid' => 'tagid'
            )
        ), array(
            'tags.text',
            'tags_objects.objectid' //Filter remaining
        ), array(
            'tags.text[~]' => array(
                'OR' => $validSearchTags
            )
        ));
        $organizedPossibleResults = array();
        foreach($possibleResults as $possibleResult) {
            if(isset($organizedPossibleResults[$possibleResult['objectid']]) && is_array($organizedPossibleResults[$possibleResult['objectid']])) {
                array_push($organizedPossibleResults[$possibleResult['objectid']], $possibleResult['text']);
            } else {
                $organizedPossibleResults[$possibleResult['objectid']] = array($possibleResult['text']);
            }
        }
        $results = array();
        foreach($organizedPossibleResults as $organizedPossibleResult) {
            
            $found = 0;
            foreach($validSearchTags as $searchTag) {
                foreach($organizedPossibleResult as $resultTag) {
                    if(strpos($resultTag, $searchTag) !== false) {
                        $found++;
                        break;
                    }
                }
                
            }
            if($found == count($validSearchTags)) {
                array_push($results, $organizedPossibleResult);
            }
        }
        
        echo "\n";
        print_r($db->last());
        echo "\n";

        if($results !== false) {
            echo json_encode($results, JSON_PRETTY_PRINT);
        } else {
            echo '[]';
        }
    }
}
?>