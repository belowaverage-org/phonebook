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
    require('library.lib.php');
    //Initialize Variables.
    $count = 100;
    $offset = 0;
    $blankJson = '{"tags":[],"objects":{}}';
    $searchTags = array();
    $tagSearchObjects = array();
    $organizedObjects = array();
    $searchQuery = json_decode($_POST['search'], true); //JSON decode search input.
    if(isset($_POST['count']) && is_numeric($_POST['count'])) {
        $count = $_POST['count'];
    }
    if(isset($_POST['offset']) && is_numeric($_POST['offset'])) {
        $offset = $_POST['offset'];
    }
    if($searchQuery !== null) { //If search input is valid JSON.
        foreach($searchQuery['TAGS'] as $tag) { //For each tag in the search input.
            if(!empty($tag) && ctype_alpha($tag) && $tag !== '' && strlen($tag) >= 2) { //If tag is longer than 2 characters and only contains alpha characters.
                array_push($searchTags, $tag); //Add the tag to the $searchTags array.
            }
        }
        $databaseQuery = array();
        if(count($searchTags) == 1) { //If there is only one tag present. 
            $databaseQuery['tags.text[~]'] = $searchTags[0].'%';  
        } else { //If there are more than 1 search tags.
            $databaseQuery['tags.text'] = $searchTags;
            $databaseQuery['GROUP'] = 'tags_objects.objectid';
            $databaseQuery['HAVING'] = $db->raw('COUNT(tags_objects.objectid) = '.count($searchTags));
        }
        if(isset($searchQuery['WHERE']) && !empty($searchQuery['WHERE'])) { //If WHERE is specified, add it to the query.
            $databaseQuery = array_merge($databaseQuery, $searchQuery['WHERE']);
        }
        $objects = $db->select('tags', array( //Query the database.
            '[>]tags_objects' => array(
                'tags.tagid' => 'tagid'
            ), '[>]objects' => array(
                'tags_objects.objectid' => 'objectid'
            )
        ), '*', $databaseQuery);






/*echo $db->debug()->select('tags', array( //Query the database.
            '[>]tags_objects' => array(
                'tags.tagid' => 'tagid'
            ), '[>]objects' => array(
                'tags_objects.objectid' => 'objectid'
            )
        ), '*', $databaseQuery);*/





        if(is_array($objects) && !empty($objects)) { //If the search returned objects.
            $organizedObjects = organizeDatabaseObjects($objects);
            $filteredTags = $db->select('tags_objects', array( //Search the database for tags associated with the searched results.
                '[>]tags' => array(
                     'tags_objects.tagid' => 'tagid'
                )
            ), array(
                'tags.text'
            ), array(
                'tags_objects.objectid' => array_keys($organizedObjects),
                'GROUP' => 'tags.text'
            ));
            if(is_array($filteredTags)) { //If filteredTags is an array.
                foreach($filteredTags as $k => $tag) { //Remove other database attributes like objectid and only use the tag text.
                    $filteredTags[$k] = $tag['text'];
                }
                if($objects !== false && $filteredTags !== false) { //If objects and filteredTags is valid.
                    echo json_encode(array( //Return the results from this search request.
                        'tags' => $filteredTags,
                        'objects' => $organizedObjects
                    ), $prettyPrintIfRequested);
                }
            } else { //Otherwise return a blank response.
                echo $blankJson;
            }
        } else {
            echo $blankJson;
        }
    } else {
        echo $blankJson;
    }
}
?>