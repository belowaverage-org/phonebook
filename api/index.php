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
header('phonebook-api-created-by: Dylan Bickerstaff');
header('phonebook-api-version: 2.0');
ob_start();
define('SCHEMA', json_decode(file_get_contents(__DIR__.'/schema.config.json'), true));
if(isset($_POST['api']) && !empty($_POST['api'])) {
	header('Content-Type: application/json');
	require('./reqs/database.api.php');
	if($_POST['api'] == 'import') {
		require('./reqs/import.api.php');
	} elseif($_POST['api'] == 'export') {
		require('./reqs/export.api.php');
	} elseif($_POST['api'] == 'search') {
		require('./reqs/search.api.php');
	}
} else {
	echo file_get_contents('api.documentation.htm');
}
header('phonebook-api-response-time: '.(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
ob_end_flush();
?>