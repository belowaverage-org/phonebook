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
if(isset($_POST['translations'])) {
    if($_POST['translations'] == 'list') { //Return JSON list of translations table.
        echo json_encode($db->select('translations', '*'), $prettyPrintIfRequested);
    }
    if($_POST['translations'] == 'set' && auth_authenticated()) { //Add or modify a translation.
        if(!isset($_POST['from']) || empty($_POST['from'])) return;
        if(!isset($_POST['to'])) return;
        if(preg_filter('/[a-z]*/', '', $_POST['from']) !== '') return;
        if(preg_filter('/(([a-z]+ ?[a-z]?)*)/', '', $_POST['to']) !== '' && !empty($_POST['to'])) return;
        if($db->has('translations', [
            'from' => $_POST['from']
        ])) {
            $db->update('translations', [
                'to' => $_POST['to']
            ], [
                'from' => $_POST['from']
            ]);
        } else {
            $db->insert('translations', [
                'from' => $_POST['from'],
                'to' => $_POST['to']
            ]);
        }
    }
    if($_POST['translations'] == 'remove' && auth_authenticated()) { //Remove a translation.
        if(!isset($_POST['from']) || empty($_POST['from'])) return;
        $db->delete('translations', [
            'from' => $_POST['from']
        ]);
    }
}
?>