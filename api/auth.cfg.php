<?php
/*
Phone Book
----------
Auth Library Config
----------
Dylan Bickerstaff
----------
Auth Library Configuration
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

//This variable determines which authentication library is used.
$auth_lib_plugin = 'auth.ldap.lib.php';

?>