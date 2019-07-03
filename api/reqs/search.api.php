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
    //Initialize Variables.
    $count = 100;
    $offset = 0;
    $blankJson = '{"tags":[],"objects":{}}';
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
    $searchTags = json_decode($_POST['search'], true); //JSON decode search input.
    if(isset($_POST['count']) && is_numeric($_POST['count'])) {
        $count = $_POST['count'];
    }
    if(isset($_POST['offset']) && is_numeric($_POST['offset'])) {
        $offset = $_POST['offset'];
    }
    if($searchTags !== null) { //If search input is valid JSON.
        foreach($searchTags as $tag) { //For each tag in the search input.
            if(!empty($tag) && ctype_alpha($tag) && $tag !== '' && strlen($tag) >= 2) { //If tag is longer than 2 characters and only contains alpha characters.
                array_push($validSearchTags, $tag); //Add the tag to the validSearchTags array.
            }
        }
        if(count($validSearchTags) == 1) { //If there is only one tag present.




            $objects = $db->select('tags', $objectJoin, '*', array( //Search the database with a wildcard appeneded to the tag.
                
                'tags.text[~]' => $validSearchTags[0].'%'
            ));



            
        } else { //If there are more than 1 search tags.








            $objects = $db->select('tags', $objectJoin, '*', array( //Search the database for objects associated with the valid tags.
                'tags.text' => $validSearchTags,
                'GROUP' => 'tags_objects.objectid',
                'HAVING' => $db->raw('COUNT(tags_objects.objectid) = '.count($validSearchTags))
            ));








        }
        if(is_array($objects) && !empty($objects)) { //If the search returned objects.
            /*

                SORT IS FLAWED: DOES NOT SORT BY ALPHA ONLY NUMERIC.

            */
            if(isset($_POST['sort']) && !empty($_POST['sort']) && isset(SCHEMA[$_POST['sort']])) { //If a sort is specified from the post request.
                usort($objects, function($a, $b) {
                    if(is_numeric($a[$_POST['sort']])) { //Sort the objects based on the attribute provided in the "sort" post request.
                        return $a[$_POST['sort']] - $b[$_POST['sort']];
                    } else {
                        return strcmp($a[$_POST['sort']], $b[$_POST['sort']]);
                    }
                });
            }
            if(isset($_POST['order']) && $_POST['order'] == 1) { //Reverse the order if "order" equals "1".
                $objects = array_reverse($objects);
            }
            foreach($objects as $k => $object) { //For each object in the array, remove the tagid, text, and objectid fields, and apply a count and offset.
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
            $filteredTags = $db->select('tags_objects', array( //Search the database for tags associated with the searched results.
                '[>]tags' => array(
                     'tags_objects.tagid' => 'tagid'
                )
            ), array(
                'tags.text'
            ), array(
                'tags_objects.objectid' => $tagSearchObjects,
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