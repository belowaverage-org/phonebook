<?php
/*
Phone Book
----------
Import API
----------
Dylan Bickerstaff
----------
Imports JSON from post request into the database.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
require_once('auth.lib.php');
if(isset($_POST['import']) && auth_authenticated()) {
	$import = json_decode($_POST['import'], true);
	if($import !== null) { //JSON Good
		require_once('library.lib.php');
		$db->pdo->beginTransaction();
		foreach($import as $key => $object) { //For every number being imported...
			$tags = array();
			$row = array();
			foreach($object as $attribute => $value) { //For every attribute in an import object, check that the attribute exists, otherwise do not add it to the row
				if(isset(SCHEMA[$attribute]) && $attribute !== 'tags') { //If an attribute in the schema and not a tag list
					if(isset(SCHEMA[$attribute]['type'])) { //Check type constraints
						if(SCHEMA[$attribute]['type'] == 'number') { //Check number constraint
							if(ctype_digit($value)) {
								$value = intval($value); //Convert string to number if it is a string.
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
					if(isset(SCHEMA[$attribute]['tagged']) && SCHEMA[$attribute]['tagged']) { //Apply tagged constraint.
						$tags = array_merge($tags, tagFilter($value));
					}
					$row[$attribute] = $value; //Add the attribute to the row
				} elseif($attribute == 'tags') { //If a tag list
					foreach($value as $rawTag) { //Foreach tag
						$tags = array_merge($tags, tagFilter($rawTag)); //Filter the input tag and append the output.
					}
				}
			}
			$objectID = createModifyOrFindObject($row);
			if($objectID !== false) {
				clearTagsFromObject($objectID);
				foreach($tags as $tag) {
					$tagID = createOrFindTag($tag);
					createTagLinkForObject($tagID, $objectID);
				}
			}
		}
		$db->pdo->commit();
	}
}
?>