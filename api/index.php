<?php
//June 2017 / Dylan Bickerstaff
header('Author: Dylan Bickerstaff');
//Settings
$pathToDB     = __DIR__.'/../DB/';
$ext          = '.dat';
$crypt        = '1234567812345678'; //16/24/32 Char long key
$cryptEnabled = false;
//
//Clean path input because its dirty
function cleanPath($path) {
    $path = str_replace('\\', '/', $path);
    $list = array('.', './', '../');
    $path = str_replace($list, '', $path);
    return $path;
}
//
//$array = loadDB('asdf\asdf\asdf'); //Loads a database to an array
function loadDB($path) {
    $path = cleanPath($path);
    global $pathToDB;
    global $ext;
    global $crypt;
    if(file_exists($pathToDB.$path.$ext)) {
        $locked = true;
        while($locked) {
            $file = @fopen($pathToDB.$path.$ext, "r"); //File Path Open
            if(@flock($file, LOCK_SH)) { //If can lock, read.
                $data = json_decode(decrypt(@file_get_contents($pathToDB.$path.$ext), $crypt), true);
                if($data == null) {
                    $data = array();
                }
                flock($file, LOCK_UN);
                $locked = false;
            }
        }
        fclose($file);
    } else {
        $data = array();
    }
    return $data;
}
//
//putDB($array, 'asdf\asdf\asdf'); //Save a database from array (locks file when writing to prevent file deletion.)
function putDB($arraydata, $path) {
    $path = cleanPath($path);
    global $pathToDB;
    global $ext;
    global $crypt;
    if(file_exists($pathToDB.$path.$ext)) {
        global $DBencryptionKey;
        $locked = true;
		set_time_limit(1);
        while($locked) {
            $file = @fopen($pathToDB.$path.$ext, "c"); //File Path Open
            if(@flock($file, LOCK_EX)) { //If can lock write
                $locked = false;
                ftruncate($file, 0);
                fwrite($file, encrypt(json_encode($arraydata),$crypt));
                fflush($file);
                flock($file, LOCK_UN);
            }
        }
        fclose($file);
        return true;
    } else {
        $dir = preg_replace('#[^\/\/]*$#', '', $pathToDB.$path); //Path minus last segment e.g. (asdf/asdf/asd) to (asdf/asdf)
        @mkdir($dir, 0777, true);
        file_put_contents($pathToDB.$path.$ext, encrypt(json_encode($arraydata),$crypt), LOCK_EX);
        return true;
    }
}
//
//dropDB('asdf\asdf\asdf'); //Delete the database
function dropDB($path) {
    $path = cleanPath($path);
    global $pathToDB;
    global $ext;
    @unlink($pathToDB.$path.$ext);
}
//
//listDB('asdf\asdf\asdf'); //List the database's in a folder
function listDB($path) {
    $path = cleanPath($path);
    global $pathToDB;
    global $ext;
    $glob = glob($pathToDB.$path.'/*'.$ext);
    $glob = str_replace($pathToDB.$path.'/','', $glob);
    $glob = str_replace($ext,'', $glob);
    return $glob;
}
//
//Encryption Functionality
function encrypt($data, $key) {
    global $cryptEnabled;
    if($cryptEnabled) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv)); 
    } else {
        return $data;
    }
}
//
function decrypt($data, $key) {
    if(base64_decode($data, true)) {
        $data = base64_decode($data);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = substr($data, 0, $iv_size);
        $data = substr($data, $iv_size);
        return rtrim(@mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv), chr(0));
    } else {
        return $data;
    }
}
//
//PHP Variables

//Global Functions
function getUID($PhoneNumber) { //Return UID of number provided. Return false on failure to find. (VERY SLOW NEEDS INDEXED)
	if(is_numeric($PhoneNumber)) { //If is number
		$numbers = loadDB('numbers');
		$uid = array_search($PhoneNumber, $numbers);
		if($uid !== false) {
			return $uid;
		}
	}
	return false;
}
function getNumber($uid) {
	$numbers = loadDB('numbers'); //Load numbers index
	if(array_key_exists($uid, $numbers)) {
		return $numbers[$uid];
	}
	return false;
}
function getTags($uid) { //Get list of tags from number UID
	$tags = loadDB('tags'); //Load tags
	$result = array();
	foreach($tags as $tag => $nums) { //For every tag
		if(in_array($uid, $nums)) { //If UID is in tag
			array_push($result, $tag); //Push UID to list
		}
	}
	sort($result);
	return $result; //Return the list
}
function setNumber($PhoneNumber = 0, $description = '') { //Set/Create the description on a number.
	$uid = getUID($PhoneNumber); //Search for number
	if(!$uid) { //If not found then create UID
		$uid = uniqid();
	}
	$numbers = loadDB('numbers'); //Load DB
	$numbers[$uid] = $PhoneNumber; //Set number
	putDB($numbers, 'numbers'); //Save DB
	$number = loadDB('numbers\\'.$uid); //Load DB
	$number['description'] = $description; //Set description
	putDB($number, 'numbers\\'.$uid); //Save DB
}
function setTags($uid = '', $tags = array()) { //Sets the tags on a number.
	$db = loadDB('tags');
	$tags = array_unique($tags);
	foreach($db as $tag => $uids) { //Remove number from all tags. And remove any empty tags. And remove tags with special characters.
		foreach($uids as $k => $tuid) { //Remove number's UID from tag.
			if($tuid == $uid) {
				unset($db[$tag][$k]);
			}
		}
		if(empty($db[$tag])) { //Remove tag if empty.
			unset($db[$tag]);
		}
	}
	foreach($tags as $tag) { //Push number to tags, and create any tag that doesnt exist.
		if(ctype_alpha($tag)) { //If tag is alpha characters only
			$tag = strtolower($tag);
			if(!isset($db[$tag])) { //Create tag if it doesnt exist.
				$db[$tag] = array();
			}
			array_push($db[$tag], $uid); //Push number's UID to the tag.
		}
	}
	putDB($db, 'tags'); //Push to database.
}
function deleteNumber($uid) { //Deletes a number using ID
	dropDB('numbers\\'.$uid);
	$numbers = loadDB('numbers');
	unset($numbers[$uid]);
	putDB($numbers, 'numbers');
	setTags($uid);
}
//API Methods
if(isset($_POST['api'])) {
	if($_POST['api'] == 'search' && isset($_POST['search'])) { //Search the numbers list for any matches using provided string.
		$postJson = json_decode($_POST['search'], true);
		if($postJson !== null) { //JSON is valid
			$tags = loadDB('tags');// Load tags from database
			$score = array(); //Define arrays
			$result = array();
			foreach($postJson as $k => $v) { //Foreach search term recieved
				$searchTag = strtolower($v); //Change caps to lower case
				foreach($tags as $k => $v) { //For every tag
					if(@strpos($k, $searchTag) === 0) { //If tag starts with search tag
						foreach($v as $id) { //NEED DESCRIPT
							if($k == $searchTag) {
								if(isset($score[$id])) { 
									$score[$id] = $score[$id] + 1;
								} else {
									$score[$id] = 1;
								}
							} elseif(isset($score[$id])) { 
								$score[$id] = $score[$id] + 1;
							} else {
								$score[$id] = 1;
							}
						}
					}
				}
			}
			arsort($score); //Sort the score array by the score big to small
			if(!(isset($_POST['count']) && is_numeric($_POST['count']))) { //No offset
				$_POST['count'] = 100;
			}
			foreach($score as $k => $v) { //For every number found in the search tags
				if(!(isset($_POST['offset']) && is_numeric($_POST['offset']))) { //No offset
					$_POST['offset'] = 0;
				}
				if(count($postJson) <= $v + $_POST['offset']) { //With offset
					array_push($result, getNumber($k)); //Otherwise add number to the result
				}
				if($_POST['count'] <= 1) {
					break;
				} else {
					$_POST['count']--;
				}
			}
			echo json_encode($result); //Output result in json
		}
	}
	if($_POST['api'] == 'import' && isset($_POST['import'])) { //Recieve JSON string and import data to database.
		$postJson = json_decode($_POST['import'], true);
		if($postJson !== null) { //JSON is valid
			foreach($postJson as $phoneNumber => $numArray) { //Each post object by number and array data.
				if(!empty($numArray['description']) || !empty($numArray['tags'])) { //If data is provided
					if(isset($numArray['description'])) { //Change description
						setNumber($phoneNumber, $numArray['description']);
					}
					if(isset($numArray['tags'])) {
						setTags(getUID($phoneNumber), $numArray['tags']);
					}
				} else { //No data provided, delete number.
					deleteNumber(getUID($phoneNumber));
				}
			}
		}
	}
	if($_POST['api'] == 'export' && isset($_POST['export'])) { //Export JSON from database
		if($_POST['export'] == 'tags') { //If export is tags
			$db = loadDB('tags');
			$tags = array();
			foreach($db as $tag => $v) {
				array_push($tags, $tag);
			}
			sort($tags);
			echo json_encode($tags);
		}
		if($_POST['export'] == 'numbers') { //If export is numbers
			$result = array();
			if(isset($_POST['numbers'])) {
				$numbers = json_decode($_POST['numbers'], true); //Decode post data
				if($numbers !== null) {
					foreach($numbers as $k => $v) { //For each number
						if(is_numeric($v)) { //If is number
							$numbers[$k] = getUID($v); //Place UID in temp array for processing later
						}
					}
				} else {
					$numbers = array(); //Blank out / Do nothing
				}
			} else { //Else dump all numbers
				$numbers = listDB('numbers');
			}
			foreach($numbers as $uid) {
				$data = loadDB('numbers\\'.$uid); //Load number
				if(!empty($data)) { //If contains data / exists
					$result[getNumber($uid)] = array( //Put in array for result
						'description' => $data['description']
					);
					if(isset($_POST['includeTags']) && $_POST['includeTags'] == true) {
						$result[getNumber($uid)] = array( //Put in array for result
							'description' => $data['description'],
							'tags' => getTags($uid)
						);
					}
				}
			}
			echo json_encode($result); //Return result
		}
		if($_POST['export'] == 'number') { //If export is number
			if(isset($_POST['number']) && !empty($_POST['number'])) {
				echo json_encode(loadDB('numbers\\'.getUID($_POST['number'])));
			}
		}
	}
	if($_POST['api'] == 'stats' && isset($_POST['stats'])) {
		if($_POST['stats'] == 'ping') {
			session_start();
			if(!isset($_SESSION['id']) || empty($_SESSION['id'])) {
				$_SESSION['id'] = uniqid();
			}
			session_write_close();
			$stats = loadDB('sessions');
			$stats[$_SESSION['id']] = time();
			foreach($stats as $uid => $time) {
				if($time < time() - 60) {
					unset($stats[$uid]);
				}
			}
			putDB($stats, 'sessions');
		}
		if($_POST['stats'] == 'count') {
			$stats = loadDB('sessions');
			foreach($stats as $uid => $time) {
				if($time < time() - 60) {
					unset($stats[$uid]);
				}
			}
			echo count($stats);
		}
	}
exit;
}
echo file_get_contents('phonebook.api.htm');
?>