<?php
/*
Phone Book
----------
API Library
----------
Dylan Bickerstaff
----------
Functions used in other API implementations.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
function createModifyOrFindObject($row = array(), $rawTags = array()) {
    global $db;
    $unique = array();
    $rowWithoutUnique = $row;
    if(isset($row['objectid'])) { //If objectid given...
        if(!empty($row['objectid'])) { //If objectid is not empty or 0
            $unique['objectid'] = $row['objectid']; //Add to unique array.
        }
        unset($row['objectid']); //Unset the objectid from the row.
    }
    foreach($row as $attribute => $value) { //For each attribute in a row
        if(isset(SCHEMA[$attribute]['unique']) && SCHEMA[$attribute]['unique']) { //If attribute is unique according to the schema
            $unique[$attribute] = $value; //Add the value to the unique array.
        }
    }
    foreach($unique as $uk => $uv) { //Remove unique stuff.
        unset($rowWithoutUnique[$uk]);
    }
    unset($rowWithoutUnique['objectid']);
    $existing_row = $db->get('objects', '*', array('OR' => $unique)); //Search via the unique array to find an existing_row.
    if(isset($existing_row['objectid'])) { //If existing_row found from the above search, just modify the data instead of inserting.
        $objectID = $existing_row['objectid'];
        if(empty($rowWithoutUnique) && empty($rawTags)) { //If no data is to be modified, then delete.
            removeObject($objectID);
            return false;
        } else {
            if(!isset($row['modified'])) {
                $row['modified'] = time();
            }
            $db->update('objects', $row, array('objectid' => $objectID));
            $row = array_merge($existing_row, $row);
        }
    } else { //Insert the data
        if(!empty($rowWithoutUnique)) {
            $row['objectid'] = $objectID = bin2hex(random_bytes(5));
            if(!isset($row['created'])) {
                $row['created'] = time();
            }
            if(!isset($row['modified'])) {
                $row['modified'] = time();
            }
            $db->insert('objects', $row);
        } else {
            return false;
        }
    }
    if($objectID !== false) { //Rebuild tags if object is found / not deleted.
        $tags = array();
        if(empty($rawTags)) {
            $existing_tags = getTagsFromObject($objectID, true);
            print_r($existing_tags);
            foreach(rowToTags($existing_row) as $k => $existing_generated_tag) {
                if(in_array($existing_generated_tag, $existing_tags)) {
                    unset($existing_tags[$k]);
                }
            }
            $tags = array_merge($tags, $existing_tags);
        }
        print_r($tags);
        clearTagsFromObject($objectID);
        $tags = array_merge($tags, rowToTags($row));
        if(!empty($rawTags)) {
            foreach($rawTags as $rawTag) { //Foreach tag
                $tags = array_merge($tags, tagFilter($rawTag)); //Filter the input tag and append the output.
            }
        }
        foreach($tags as $tag) {
            $tagID = createOrFindTag($tag);
            createTagLinkForObject($tagID, $objectID);
        }
    }
    return $objectID;
}
function rowToTags($row = array()) {
    $tags = array();
    foreach($row as $attribute => $value) {
        if(isset(SCHEMA[$attribute]['tagged']) && SCHEMA[$attribute]['tagged']) { //Apply tagged constraint.
            $tags = array_merge($tags, tagFilter($value));
        }
    }
    return $tags;
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
function getTagsFromObject($objectID, $resolveTagText = false) {
    global $db;
    if($resolveTagText) {
        return $db->select('tags_objects', array(
            '[>]tags' => 'tagid'
        ), 'text', array(
            'tags_objects.objectid' => $objectID
        ));
    } else {
        return $db->select('tags_objects', 'tagid', array(
            'objectid' => $objectID
        ));
    }
}
function removeObject($objectID) {
    global $db;
    if(isset($objectID) && !empty($objectID) && $db->has('objects', array('objectid' => $objectID))) {
        clearTagsFromObject($objectID);
        $db->delete('objects', array('objectid' => $objectID));
    }
}
function tagTranslate($tag) {
    global $db;
    $return = array();
    $result = $db->get('translations', array('to'), array(
        'from' => $tag
    ));
    if(empty($result)) {
        $return[0] = $tag;
    } elseif(!empty($result['to'])) {
        $return = explode(' ', $result['to']);
    }
    return $return;
}
function tagFilter($string) {
    $return = array();
    $string = strtolower($string);
    $string = preg_replace('/[^a-z?![:space:]]/', ' ', $string);
    $string = trim($string);
    foreach(explode(' ', $string) as $rawTag) {
        $return = array_merge($return, tagTranslate($rawTag));
    }
    foreach($return as $k => $tag) {
        if(
            strlen($tag) < 2 ||
            empty($tag)
        ) {
            unset($return[$k]);
        }
    }
    return $return;
}
function organizeDatabaseObjects($objects, $includeTags = false) {
    if(is_array($objects)) {
        foreach($objects as $k => $object) {
            if($includeTags) {
                $tags = getTagsFromObject($object['objectid'], true);
                if($tags) {
                    $object['tags'] = $tags;
                }
            }
            $objects[$object['objectid']] = $object;
            unset($objects[$k]);
            unset($objects[$object['objectid']]['objectid']);
            unset($objects[$object['objectid']]['tagid']);
            unset($objects[$object['objectid']]['text']);
            foreach($object as $attributeName => $attributeValue) {
                if(empty($attributeValue)) {
                    unset($objects[$object['objectid']][$attributeName]);
                }
            }
        }
    } else {
        return array();
    }
    return $objects;
}
function importDatabaseObjects($objects) {
    global $db;
    $db->pdo->beginTransaction();
    foreach($objects as $key => $attributes) { //For every number being imported...
        $tags = array();
        $row = array("objectid" => $key);
        foreach($attributes as $attribute => $value) { //For every attribute in an import object, check that the attribute exists, otherwise do not add it to the row.
            if(isset(SCHEMA[$attribute]) && $attribute !== 'tags' && !empty($value)) { //If an attribute in the schema and not a tag list or empty.
                if(isset(SCHEMA[$attribute]['type'])) { //Check type constraints
                    if(SCHEMA[$attribute]['type'] == 'number' || SCHEMA[$attribute]['type'] == 'timestamp') { //Check number constraint
                        if(ctype_digit($value)) {
                            $value = intval($value); //Convert string to number if it is a string.
                        } elseif(SCHEMA[$attribute]['type'] == 'timestamp') {
                            $value = strtotime($value);
                        } else {
                            break;
                        }
                    }
                    if(SCHEMA[$attribute]['type'] == 'choice' && isset(SCHEMA[$attribute]['choices']) && !in_array($value, SCHEMA[$attribute]['choices'])) { //Check choice constraint
                        break;
                    }
                }
                if(isset(SCHEMA[$attribute]['length']) && strlen($value) > SCHEMA[$attribute]['length']) { //Check string length constraint
                    break;
                }
                $row[$attribute] = $value; //Add the attribute to the row
            } elseif($attribute == 'tags') { //If a tag list
                $tags = $value;
            }
        }
        $objectID = createModifyOrFindObject($row, $tags);
    }
    $db->pdo->commit();
}
function exportDatabaseObjects($includeTags = false) {
    global $db;
    $objects = $db->select('objects', '*');
    $objects = organizeDatabaseObjects($objects, $includeTags);
    return $objects;
}
?>