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

//This variable sets the expire time on a session that is dormant. (in seconds)
$auth_session_expire = 300;

//This variable determines which authentication library is used.
$auth_lib_plugin = \'auth.ldap.lib.php\';

?>'
    );
}
require_once('auth.cfg.php'); //Load the auth config.
$db->delete('sessions', array( //Remove expired sessions from the database.
    'expire[<]' => time()
));
function auth_get_username() { //Return the username from either the server or the POST header.
    if(isset($_POST['username'])) {
        return $_POST['username'];
    } elseif(isset($_SERVER['REMOTE_USER'])) {
        return $_SERVER['REMOTE_USER'];
    } else {
        return false;
    }
}
function auth_get_password() { //Return the password from the POST header.
    if(isset($_POST['username'])) {
        return $_POST['username'];
    } else {
        return false;
    }
}
function auth_session_set($hasAdminPermission = false) { //Generate a session and send a cookie.
    global $db;
    global $auth_session_expire;
    $id = base64_encode(random_bytes(30));
    $db->insert('sessions', array(
        'id' => $id,
        'expire' => time() + $auth_session_expire,
        'admin' => $hasAdminPermission,
        'username' => auth_get_username()
    ));
    setcookie('phonebook-api-cookie', $id, time() + $auth_session_expire, '', '', true);
}
function auth_session_get() { //Get and update session information from the database / cookie.
    global $db;
    global $auth_session_expire;
    if(!isset($_COOKIE['phonebook-api-cookie'])) {
        return false;
    }
    $where = array(
        'id' => $_COOKIE['phonebook-api-cookie']
    );
    $session = $db->get('sessions', '*', $where);
    $expire = time() + $auth_session_expire;
    if($session) { //If session exists
        $db->update('sessions', array(
            'expire' => $expire
        ), $where);
    }
    setcookie('phonebook-api-cookie', $_COOKIE['phonebook-api-cookie'], $expire, '', '', true);
    return $session;
}
function auth_session_clear() { //Remove the session from the server and client.
    global $db;
    if(!isset($_COOKIE['phonebook-api-cookie'])) {
        return false;
    }
    $db->delete('sessions', array(
        'id' => $_COOKIE['phonebook-api-cookie']
    ));
    setcookie('phonebook-api-cookie', '', 0, '', '', true); //Clear the cookie.
    return true;
}
require_once($auth_lib_plugin); //Load the specified auth plugin.
function auth_authenticated() { //Function called by other APIs to deterimine if user is authenticated as admin.
    $session = auth_session_get();
    if($session) { //If session already exists
        if ($session['admin']) { //If session is an administrator session.
            return true;
        } else {
            return false;
        }
    } elseif(auth_plugin_authenticated()) { //If authentication plugin validates the credentials passed.
        auth_session_set(true);
        return true;
    } else { //Otherwise the user is not authenticated.
        http_response_code(403);
        return false;
    }
}
?>