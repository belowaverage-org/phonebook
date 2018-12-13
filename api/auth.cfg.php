<?php
/*
Phone Book
----------
Auth Library Config
----------
Dylan Bickerstaff
----------
AD Bind Configuration.
*/
if(!isset($singlePointEntry)){http_response_code(403);exit;}

$auth_ldap_domain_controller_hostname = 'ad.contoso.com';
$auth_ldap_bind_user_dn               = 'CN=PhoneBook,OU=Generic,OU=Users,DC=ad,DC=contoso,DC=com';
$auth_ldap_bind_user_pass             = 'password123';

$auth_ldap_api_admin_groups = array(
    'CN=PhoneBook-Admins,OU=Groups,DC=ad,DC=contoso,DC=com'
);
?>