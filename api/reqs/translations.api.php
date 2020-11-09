<?php
/*
Phone Book
----------
Translations API
----------
Dylan Bickerstaff
----------
View and update translation information.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
include_once('./reqs/auth.lib.php');
if(isset($_POST['list'])) {
    
}
if(isset($_POST['set']) && auth_authenticated()) {
    
}
if(isset($_POST['remove']) && auth_authenticated()) {
    
}
?>