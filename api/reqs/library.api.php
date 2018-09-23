<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}

function createModifyOrFindObject($row = array()) {
    global $db;
    $unique = array();
    foreach($row as $attribute => $value) { //For each attribute in a row
        if(isset(SCHEMA[$attribute]['unique']) && SCHEMA[$attribute]['unique']) { //If attribute is unique according to the schema
            $unique[$attribute] = $value;
        }
    }
    $objectID = $db->get('objects', array('objectid'), array('AND' => $unique));
    if(isset($objectID['objectid'])) { //Already exists, so just modify the data instead of inserting
        $objectID = $objectID['objectid'];
        foreach($unique as $cname => $cvalue) {
            unset($row[$cname]);
        }
        unset($row['objectid']);
        if(!empty($row)) { 
            $db->update('objects', $row, array('objectid' => $objectID));
        }
    } else { //Insert the data
        $row['objectid'] = $objectID = bin2hex(random_bytes(5));
        $db->insert('objects', $row);
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

function createTagToObjectLink($tagID, $objectID) {
    global $db;
    $row = array(
      'tagid' => $tagID,
      'objectid' => $objectID
    );
    if(isset($tagID) && !empty($tagID) && isset($objectID) && !empty($objectID) && !$db->has('tags_objects', $row)) {
        $db->insert('tags_objects', $row);
    }
}

function removeObject($objectID) {
    if(isset($objectID) && !empty($objectID) && $db->has('objects', array('objectid' => $objectID))) {

    }
}

?>