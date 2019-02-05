<?php 
/*
Phone Book
----------
Index Router
----------
Dylan Bickerstaff
----------
Routes requests to the proper APIs.
*/
set_time_limit(300);
$singlePointEntry = true;
$prettyPrintIfRequested = 0;
header('phonebook-api-created-by: Dylan Bickerstaff');
header('phonebook-api-version: 2.0');
ob_start();
define('SCHEMA', json_decode(file_get_contents(__DIR__.'/schema.cfg.json'), true));
if(isset($_POST['api']) && !empty($_POST['api'])) {
	header('Content-Type: application/json');
	require('./reqs/database.run.php');
	if(isset($_POST['prettyprint'])) {
		$prettyPrintIfRequested = JSON_PRETTY_PRINT;
	}
	if($_POST['api'] == 'import') {
		if(isset($_POST['legacy'])) {
			require('./reqs/import.legacy.api.php');
		} else {
			require('./reqs/import.api.php');
		}
	} elseif($_POST['api'] == 'export') {
		require('./reqs/export.api.php');
	} elseif($_POST['api'] == 'search') {
		require('./reqs/search.api.php');
	}
} else {
	echo file_get_contents('documentation.api.htm');
}
header('phonebook-api-response-time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
ob_end_flush();
?>