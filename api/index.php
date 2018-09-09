<?php 
//September 2018 / Dylan Bickerstaff
set_time_limit(300);
$singlePointEntry = true;

header('Author: Dylan Bickerstaff');
if(isset($_POST['api']) && !empty($_POST['api'])) {
	require('./reqs/database.api.php');
	if($_POST['api'] == 'import') {
		require('./reqs/import.api.php');
	}
	exit;
}
echo file_get_contents('api.documentation.htm');
?>