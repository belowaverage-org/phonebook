<?php
/*
Phone Book
----------
Database Schema Manager
----------
Dylan Bickerstaff
----------
Creates and updates the database schema as nessesary.
*/
if(!isset($singlePointEntry)) { http_response_code(403); exit; }
require(__DIR__.'/../database.cfg.php');
function insertLoop($table, $data) { //Function for inserting multiple rows into the database and splitting up the insert statements
	global $db;
	global $dbConfig;
	$offset = 0;
	while(count($data) > $offset) {
		$db->insert($table, array_slice($data, $offset, $dbConfig['insertLoopLimit']));
		$offset += $dbConfig['insertLoopLimit'];
	}
}
$schema_json_raw = json_encode(SCHEMA, true);
$schema_json_raw_cache = '';
if(file_exists(__DIR__.'/../../DB/schema.cache')) {
	$schema_json_raw_cache = file_get_contents(__DIR__.'/../../DB/schema.cache');
}
if($schema_json_raw !== $schema_json_raw_cache || !file_exists(__DIR__.'/../../DB/database.sqlite3')) { //Compare the schmea to a cached copy of the schema and check if the database even exists, otherwise create the database.
	$tags_objects = $db->select('tags_objects', '*'); //Copy the old database
	$objects = $db->select('objects', '*');
	$rows = '';
	$rows_array = array();
	$indexed_rows = '';
	foreach(json_decode($schema_json_raw, true) as $row => $settings) { //Apply schema attributes
		foreach($settings as $setting => $v) {
			array_push($rows_array, $row);
			if($setting == 'indexed' && $v) {
				$indexed_rows = $indexed_rows.','.$row;
			}
			if($setting == 'unique' && $v) {
				$row = $row.' UNIQUE NOT NULL';
			}
		}
		$rows = $rows.','.$row;
	}
	/* Create tables */
	$db->query('DROP TABLE IF EXISTS tags_objects;');
	$db->query('DROP TABLE IF EXISTS objects;');
	$db->query('
		CREATE TABLE IF NOT EXISTS tags (
			tagid PRIMARY KEY UNIQUE NOT NULL,
			text UNIQUE NOT NULL
		);
	');
	$db->query('
		CREATE UNIQUE INDEX IF NOT EXISTS tags_index ON tags (
			tagid,
			text
		);
	');
	$db->query('
		CREATE TABLE IF NOT EXISTS objects (
			objectid PRIMARY KEY UNIQUE NOT NULL
			'.$rows.'
		);
	');
	$db->query('
		CREATE INDEX IF NOT EXISTS objects_index ON objects (
			objectid
			'.$indexed_rows.'
		);
	');
	$db->query('
		CREATE TABLE IF NOT EXISTS tags_objects (
			tagid NOT NULL REFERENCES tags (tagid),
			objectid  NOT NULL REFERENCES objects (objectid) 
		);
	');
	$db->query('
		CREATE INDEX IF NOT EXISTS tags_objects_index ON tags_objects (
			tagid,
			objectid
		);
	');
	$db->query('
		CREATE TABLE IF NOT EXISTS sessions (
			id PRIMARY KEY NOT NULL,
			expire NOT NULL,
			username NOT NULL
		);
	');
	/* End create tables */
	if(!empty($objects)) { //Check if any old database columns exist in the new schema, if not delete the column from each data point
		foreach($objects as $obj_k => $obj_v) {
			foreach($obj_v as $row_k => $row_v) {
				if(!in_array($row_k, $rows_array) && $row_k !== 'objectid') {
					unset($objects[$obj_k][$row_k]);
				}
			}
		}
		insertLoop('objects', $objects); //Insert old database into the new one
	}
	if(!empty($tags_objects)) {
		insertLoop('tags_objects', $tags_objects); //Insert old database into the new one
	}
	file_put_contents(__DIR__.'/../../DB/schema.cache', $schema_json_raw); //Cache a new copy of the schema.
}
?>