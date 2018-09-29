<?php
if(!isset($singlePointEntry)){http_response_code(403);exit;}

if(isset($_POST['export'])) {
    if($_POST['export'] == 'tags') {
        echo json_encode($db->select('tags', 'text'));
    } elseif($_POST['export'] == 'numbers' && isset($_POST['numbers']) && !empty($_POST['numbers'])) {
        $numbers = json_decode($_POST['numbers'], true);
        if($numbers !== false) {
            foreach($numbers as $k => $number) {
                $numbers[$k] = intval($number);
            }
            $numbers = $db->select('objects', '*', array(
                'number' => $numbers
            ));
            foreach($numbers as $k => $number) {
                $numbers[$number['number']] = $number;
                unset($numbers[$k]);
            }
            echo json_encode($numbers);
        }
    }
}

?>