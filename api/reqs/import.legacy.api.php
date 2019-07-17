<?php
/*
Phone Book
----------
Legacy Import API
----------
Dylan Bickerstaff
----------
Imports JSON from post request into the database.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
require_once('auth.lib.php');
if(isset($_POST['import']) && auth_authenticated()) {
	$numbers = json_decode($_POST['import'], true);
	if($numbers !== null) { //JSON Good
		require_once('library.lib.php');
		$objects = array();
		foreach($numbers as $number => $attributes) {
		    $attributes['number'] = $number;
			array_push($objects, $attributes);
		}
		print_r($objects);
		//importDatabaseObjects($objects);
	}
}
?>