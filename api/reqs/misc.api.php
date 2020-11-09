<?php
/*
Phone Book
----------
Misc API
----------
Dylan Bickerstaff
----------
Contains various other methods 
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
include_once('./reqs/auth.lib.php');
if(isset($_POST['misc'])) {
    if($_POST['misc'] == 'rebuild' && auth_authenticated()) { //Invoke a database rebuild by deleting the schema cache.
        unlink('../data/db/schema.cache');
    }
}
?>