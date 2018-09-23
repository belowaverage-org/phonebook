<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}

if(isset($_POST['export'])) {
    if($_POST['export'] == 'tags') {
        echo json_encode($db->select('tags', 'text'));
    }
}

?>