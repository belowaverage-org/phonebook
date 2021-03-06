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
    require_once('library.lib.php');
    //Initialize Variables.
    $blankJson = '{"objects":{}}';
    $searchTags = array();
    $tagSearchObjects = array();
    $organizedObjects = array();
    $searchQuery = json_decode($_POST['search'], true); //JSON decode search input.
    if($searchQuery !== null) { //If search input is valid JSON.
        $databaseQuery = array();
        $databaseCols = array('objects.objectid');
        $queryContainsTags = (isset($searchQuery['SEARCH']['TAGS']) && !empty($searchQuery['SEARCH']['TAGS']));
        if(isset($searchQuery['OUTPUT']['ATTRIBUTES']) && !empty($searchQuery['OUTPUT']['ATTRIBUTES'])) {
            $databaseCols = array_merge($databaseCols, $searchQuery['OUTPUT']['ATTRIBUTES']);
        }
        if(isset($searchQuery['SEARCH']) && !empty($searchQuery['SEARCH'])) { //If WHERE is specified, add it to the query.
            if($queryContainsTags) { //Remove tags from array, because the rest can be understood my MEDOO
                $tags = $searchQuery['SEARCH']['TAGS'];
                unset($searchQuery['SEARCH']['TAGS']);
            }
            $databaseQuery = array_merge($databaseQuery, $searchQuery['SEARCH']); //Merge the posted query into the database query.
            if($queryContainsTags) { // Add tags back into the array for later.
                $searchQuery['SEARCH']['TAGS'] = $tags;
            }
        }
        if($queryContainsTags) {
            $atLeastOneTagIsBlank = false;
            foreach($searchQuery['SEARCH']['TAGS'] as $tag) { //For each tag in the search input.
                if($tag == '') {
                    $atLeastOneTagIsBlank = true;
                    continue;
                }
                if(!empty($tag) && ctype_alnum($tag) && strlen($tag) >= 2) { //If tag is longer than 2 characters and only contains alpha-numeric characters.
                    array_push($searchTags, $tag); //Add the tag to the $searchTags array.
                }
            }
            if(count($searchTags) == 1 && !$atLeastOneTagIsBlank) { //If there is only one tag present. And at least one tag is not blank.
                $databaseQuery['tags.text[~]'] = $searchTags[0].'%';  
            } else { //If there are more than 1 search tags.
                $databaseQuery['tags.text'] = $searchTags;
                $databaseQuery['GROUP'] = 'tags_objects.objectid';
                $databaseQuery['HAVING'] = $db->raw('COUNT(tags_objects.objectid) = '.count($searchTags));
            }
            $objects = $db->select('tags', array( //Query the tags table.
                '[>]tags_objects' => array(
                    'tags.tagid' => 'tagid'
                ), '[>]objects' => array(
                    'tags_objects.objectid' => 'objectid'
                )
            ), $databaseCols, $databaseQuery);
        } else {
            $objects = $db->select('objects', $databaseCols, $databaseQuery); //Query the objects table.
        }
        if(is_array($objects) && !empty($objects)) { //If the search returned objects.
            $includeTags = (isset($searchQuery['OUTPUT']['OPTIONS']) && in_array('showObjectTags', $searchQuery['OUTPUT']['OPTIONS']));
            $organizedObjects = organizeDatabaseObjects($objects, $includeTags);
            if(isset($searchQuery['OUTPUT']['OPTIONS']) && in_array('showAvailableTags', $searchQuery['OUTPUT']['OPTIONS'])) {
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
            }
            if(isset($filteredTags) && is_array($filteredTags)) { //If filteredTags is an array.
                foreach($filteredTags as $k => $tag) { //Remove other database attributes like objectid and only use the tag text.
                    $filteredTags[$k] = $tag['text'];
                }
                $filteredTags = array_values($filteredTags); //Remove numbered keys from array.
                if($objects !== false && $filteredTags !== false) { //If objects and filteredTags is valid.
                    echo json_encode(array( //Return the results from this search request.
                        'tags' => $filteredTags,
                        'objects' => $organizedObjects
                    ), $prettyPrintIfRequested);
                }
            } else { //Otherwise return a blank response.
                echo json_encode(array( //Return the results from this search request.
                        'objects' => $organizedObjects
                ), $prettyPrintIfRequested);
            }
        } else {
            echo $blankJson;
        }
    } else {
        echo $blankJson;
    }
}
?>