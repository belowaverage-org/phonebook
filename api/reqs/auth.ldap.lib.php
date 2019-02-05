<?php
/*
Phone Book
----------
LDAP Auth Library
----------
Dylan Bickerstaff
----------
Functions that authenticate and cache sessions.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

if(!file_exists('auth.ldap.cfg.php')) {
    file_put_contents('auth.ldap.cfg.php',
'<?php
/*
Phone Book
----------
LDAP Auth Library Config
----------
Dylan Bickerstaff
----------
AD Bind Configuration.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

$auth_ldap_domain_controller_hostname = \'ad.contoso.com\';
$auth_ldap_base_dn                    = \'OU=Generic,OU=Users,DC=ad,DC=contoso,DC=com\';
$auth_ldap_bind_user_dn               = \'CN=PhoneBook,OU=Generic,OU=Users,DC=ad,DC=contoso,DC=com\';
$auth_ldap_bind_user_pass             = \'password123\';

$auth_ldap_api_admin_groups = array(
    \'CN=PhoneBook-Admins,OU=Groups,DC=ad,DC=contoso,DC=com\'
);
?>'
    );
}

require_once('auth.ldap.cfg.php'); //Import config for LDAP.

function auth_plugin_authenticated() { //Main function called by AUTH Lib.
    global $auth_ldap_domain_controller_hostname;
    global $auth_ldap_base_dn;
    global $auth_ldap_bind_user_dn;
    global $auth_ldap_bind_user_pass;
    global $auth_ldap_api_admin_groups;
    if(count(explode('\\', auth_get_username(), 2)) == 2) { //If username contains a domain E.g: DOMAIN\UserName
        $username = explode('\\', auth_get_username(), 2)[1]; //If it does just grab the username.
    } else { //Otherwise just use whatever was passed by the Auth Lib.
        $username = auth_get_username();
    }
    $bound = false;
    $ldap = ldap_connect($auth_ldap_domain_controller_hostname); //Connect to LDAP.
    if(auth_get_password()) { //If password is provided.
        if(ldap_bind($ldap, auth_get_username(), auth_get_password())) { //If username and password combo can bind (authenticate with LDAP).
            $bound = true;
        }
    } else {
        if(ldap_bind($ldap, $auth_ldap_bind_user_dn, $auth_ldap_bind_user_pass)) { //If config's username and password works.
            $bound = true;
        }
    }
    if($bound) { //If bound. Check permissions.
        $results = ldap_search($ldap, $auth_ldap_base_dn, '(&(sAMAccountName='.ldap_escape($username, '', LDAP_ESCAPE_FILTER).')(objectClass=user))'); //Search the username in LDAP.
        if($results) { //If result is found.
            $result = ldap_first_entry($ldap, $results); //Grab the first entry.
            $groups = ldap_get_values($ldap, $result, 'memberOf'); //Get the groups the user is a member of.
            $in_admin_group = false;
            foreach($auth_ldap_api_admin_groups as $admin_group) { //Cycle through each admin group specified in the config and compare to what the user has.
                if(in_array($admin_group, $groups)) { //If groups match, then break and return true.
                    $in_admin_group = true;
                    break;
                }
            }
            if($in_admin_group) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

?>