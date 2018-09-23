<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}

function createModifyOrFindObject($row = array()) {
    global $db;
    $unique = array();
    $rowWithoutUnique = $row;
    foreach($row as $attribute => $value) { //For each attribute in a row
        if(isset(SCHEMA[$attribute]['unique']) && SCHEMA[$attribute]['unique']) { //If attribute is unique according to the schema
            $unique[$attribute] = $value;
        }
    }
    foreach($unique as $uk => $uv) {
        unset($rowWithoutUnique[$uk]);
    }
    unset($rowWithoutUnique['objectid']);
    $objectID = $db->get('objects', array('objectid'), array('AND' => $unique));
    if(isset($objectID['objectid'])) { //Already exists, so just modify the data instead of inserting
        $objectID = $objectID['objectid'];
        if(empty($rowWithoutUnique)) {
            removeObject($objectID);
        } else {
            $db->update('objects', $rowWithoutUnique, array('objectid' => $objectID));
        }
    } else { //Insert the data
        if(!empty($rowWithoutUnique)) {
            $row['objectid'] = $objectID = bin2hex(random_bytes(5));
            $db->insert('objects', $row);
        } else {
            $objectID = false;
        }
    }
    return $objectID;
}

function createOrFindTag($tag) {
    global $db;
    $tagID = $db->get('tags', array('tagid'), array('text' => $tag));
    if(empty($tagID)) {
        $tagID = bin2hex(random_bytes(5));
        $db->insert('tags', array(
            'tagid' => $tagID,
            'text' => $tag
        ));
    } else {
        $tagID = $tagID['tagid'];
    }
    return $tagID;
}

function createTagLinkForObject($tagID, $objectID) {
    global $db;
    $row = array(
      'tagid' => $tagID,
      'objectid' => $objectID
    );
    if(isset($tagID) && !empty($tagID) && isset($objectID) && !empty($objectID) && !$db->has('tags_objects', $row)) {
        $db->insert('tags_objects', $row);
    }
}

function clearTagsFromObject($objectID) {
    global $db;
    $tags_objects = $db->select('tags_objects', 'tagid', array('objectid' => $objectID));
    foreach($tags_objects as $tagID) {
        $db->delete('tags_objects', array('objectid' => $objectID, 'tagid' => $tagID));
        if($db->count('tags_objects', array('tagid' => $tagID)) == 0) {
            $db->delete('tags', array('tagid' => $tagID));
        }
    }
}

function removeObject($objectID) {
    global $db;
    if(isset($objectID) && !empty($objectID) && $db->has('objects', array('objectid' => $objectID))) {
        clearTagsFromObject($objectID);
        $db->delete('objects', array('objectid' => $objectID));
    }
}

?>