<?php 
if(!isset($singlePointEntry)){http_response_code(403);exit;}

/* Import API implementation */

if(isset($_POST['import'])) {
	$import = json_decode($_POST['import'], true);
	if($import !== null) { //JSON Good
		require_once('library.api.php');
		$db->pdo->beginTransaction();
		foreach($import as $number => $object) { //For every number being imported...
			$tags = array();
			if(!is_numeric($number)) { //If number is not numeric, continue to the next number
				continue;
			}
			$row = array( //Start the objects in the row
				'number' => $number
			);
			foreach($object as $attribute => $value) { //For every attribute in an import object, check that the attribute exists, otherwise do not add it to the row
				if(isset(SCHEMA[$attribute]) && $attribute !== 'tags') { //If an attribute in the schema and not a tag list
					if(isset(SCHEMA[$attribute]['type'])) { //Check type constraints
						if(SCHEMA[$attribute]['type'] == 'number' && !ctype_digit($value)) { //Check number constraint
							break;
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
					foreach($value as $tag) { //Foreach tag
						array_push($tags, $tag);
					}
				}
			}
			$objectID = createModifyOrFindObject($row);
			if($objectID !== false) {
				foreach($tags as $tag) {
					$tagID = createOrFindTag($tag);
					createTagToObjectLink($tagID, $objectID);
				}
			}
		}
		$db->pdo->commit();
	}
}
?>