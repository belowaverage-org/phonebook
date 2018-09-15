<?php 
if(!isset($singlePointEntry)) { http_response_code(403); exit; }

/* Import API implementation */

if(isset($_POST['import'])) {
	$import = json_decode($_POST['import'], true);
	if($import !== null) { //JSON Good
		$schema = json_decode(file_get_contents(__DIR__.'/../schema.config.json'), true);
		$objects = array();
		$tags = array();
		$unique = array();
		foreach($import as $number => $object) { //For every number being imported...
			if(!is_numeric($number)) { //If number is not numeric, continue to the next number
				continue;
			}
			$row = array( //Start the objects in the row
				'objectid' => bin2hex(random_bytes(5)),
				'number' => $number
			);
			foreach($object as $attribute => $value) { //For every attribute in an import object, check that the attribute exists, otherwise do not add it to the row
				if(isset($schema[$attribute]) && $attribute !== 'tags') { //If an attribute in the schema and not a tag list
					if(isset($schema[$attribute]['type'])) { //Check type constraints
						if($schema[$attribute]['type'] == 'number' && !ctype_digit($value)) { //Check number constraint
							break;
						}
						if($schema[$attribute]['type'] == 'choice' && isset($schema[$attribute]['choices']) && !in_array($value, $schema[$attribute]['choices'])) { //Check choice constraint
							break;
						}
					}
					if(isset($schema[$attribute]['length']) && strlen($value) > $schema[$attribute]['length']) { //Check string length constraint
						break;
					}
					$row[$attribute] = $value; //Add the attribute to the row
				} elseif($attribute == 'tags') { //If a tag list
					foreach($value as $tag) { //Foreach tag
						if(!in_array($tag, $tags)) { //If not already in the array
							array_push($tags, $tag); //Add to the array
						}
					}
				}
			}
			array_push($objects, $row); //Add the row to the objects set
		}
		$db->pdo->beginTransaction();
		foreach($objects as $row) { //For each row in the objects set
			$unique = array();
			$objectid = '';
			foreach($row as $attribute => $value) { //For each attribute in a row
				if(isset($schema[$attribute]['unique']) && $schema[$attribute]['unique']) { //If attribute is unique according to the schema
					$unique[$attribute] = $value;
				}
			}
			$objectid = $db->get('objects', array('objectid'), array('OR' => $unique));
			if($objectid != '') { //Already exists, so just modify the data instead of inserting
				foreach($unique as $cname => $cvalue) {
					unset($row[$cname]);
				}
				unset($row['objectid']);
				$db->update('objects', $row, array('objectid' => $objectid));
			} else { //Insert the data
				$db->insert('objects', $row);
			}
		}
		foreach($tags as $tag) {
			$tagid = '';
			$tagid = $db->get('tags', array('tagid'), array('text' => $tag));
			if($tagid != '') { //Already exists, so just modify the data instead of inserting
				$db->update('tags', array('text' => $tag), array('tagid' => $tagid));
			} else {
				$db->insert('tags', array(
					'tagid' => bin2hex(random_bytes(5)),
					'text' => $tag
				));
			}
		}
		$db->pdo->commit();
		print_r($db->error());
		print_r($db->last());
	}
}
?>