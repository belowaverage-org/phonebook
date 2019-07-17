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
	$objects = json_decode($_POST['import'], true);
	if($objects !== null) { //JSON Good
		require_once('library.lib.php');
		importDatabaseObjects($objects);
	}
}
?>