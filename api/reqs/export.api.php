<?php
/*
Phone Book
----------
Export API
----------
Dylan Bickerstaff
----------
Exports object information from the database.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}
if(isset($_POST['export'])) {
    require_once('auth.lib.php');
    require_once('library.lib.php');
    if($_POST['export'] == 'tags') {
        echo json_encode($db->select('tags', 'text'));
    }
}
?>