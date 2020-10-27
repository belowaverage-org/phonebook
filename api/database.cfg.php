<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}

/* PhoneBook database settings */
$dbConfig = array(
	'insertLoopLimit' => 100 //Determines how many rows can be inserted at a time before the Phone Book API will start splitting the database calls.
);

/* Database connection settings */
require_once('./reqs/medoo.lib.php');
use Medoo\Medoo;
$db = new Medoo([
	'database_type' => 'sqlite',
	'database_file' => './DB/database.sqlite3'
]);

/* Database technology specific settings */
$db->query('PRAGMA foreign_keys = ON;');
?>