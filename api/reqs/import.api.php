<?php 
if(!isset($singlePointEntry)) { http_response_code(403); exit; }

/* Import API implementation */

if(isset($_POST['import'])) {
	$import = json_decode($_POST['import'], true);
	if($import !== null) { //JSON Good
		$schema = json_decode(file_get_contents(__DIR__.'/../schema.config.json'), true);
		$data = array();
		$unique = array();
		foreach($import as $number => $object) { //For every number being imported...
			if(!is_numeric($number)) { //If number is not numeric, continue to the next number
				continue;
			}
			usleep(1);
			$row = array( //Start the data in the row
				'objectid' => uniqid(),
				'number' => $number
			);
			foreach($object as $attribute => $value) { //For every attribute in an import object, check that the attribute exists, otherwise do not add it to the row
				if(isset($schema[$attribute])) { //If attribute 
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
				}
			}
			array_push($data, $row); //Add the row to the data set
		}
		foreach($data as $row) { //For each row in the data set
			foreach($row as $attribute => $value) { //For each attribute in a row
				if(isset($schema[$attribute]['unique']) && $schema[$attribute]['unique']) { //If attribute is unique according to the schema
					if(!isset($unique[$attribute])) { //If unique attribute does not exist in the unique array
						$unique[$attribute] = array(); //Create the unique attribute in the array as an array of data points
					}
					array_push($unique[$attribute], $value); //Push the value of the unique attributes from this row into the array
					if(count($unique[$attribute]) >= $dbConfig['insertLoopLimit']) { //Every time the loop limit is reached reset the array and delete out of the database
						$db->delete('objects', $unique);
						unset($unique[$attribute]);
					}
				}
			}
			$db->delete('objects', $unique); //Delete the remainder
		}
		insertLoop('objects', $data); //Insert the new data
	}
}
?>