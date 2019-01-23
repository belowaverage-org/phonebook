<?php
/*
Phone Book
----------
Auth Library
----------
Dylan Bickerstaff
----------
Functions that authenticate and cache sessions.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

if(!file_exists('auth.cfg.php')) {
    file_put_contents('auth.cfg.php',
'<?php
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
$auth_lib_plugin = \'auth.ldap.lib.php\';

?>'
    );
}

require_once('auth.cfg.php');
require_once($auth_lib_plugin);

?>