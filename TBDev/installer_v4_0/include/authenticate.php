<?php
// if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
// //////////Authenticate staffpanel access///add your staffnames here////////////comment out if your server wont support this --->> for more info http://php.net/features.http-auth/////
function systemcheck()
{
    global $CURUSER;
    // users who are allowed access
    $checkem = array('Admin' => 1,
        'System' => 1
        );
    // check if they are allowed, have sent a username/pass and are using their own username
    if (isset($checkem[$CURUSER['username']]) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] === $CURUSER['username']) {
        // generate a passhash from the sent password
        $hash = md5($CURUSER['secret'] . $_SERVER['PHP_AUTH_PW'] . $CURUSER['secret']);
        // if the password is correct, exit this function
        if ($CURUSER['passhash'] === $hash) return true;
    }
    // they're not allowed, the username doesn't match their own, the password is
    // wrong or they have not sent user/pass yet so we exit
    header('WWW-Authenticate: Basic realm="Administration"');
    header('HTTP/1.0 401 Unauthorized');
    die('<b>Sorry! Access denied!</b>');
}
// //////////////authenticate staff by system/////////////////////
?>